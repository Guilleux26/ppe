<?php
declare(strict_types=1);

namespace ClasseMetier;

use ClasseTechnique\Database;
use ClasseTechnique\UserException;
use PDO;

final class Forum
{
    /**
     * Vérifie que le membre existe encore avant une action sur le forum.
     * Si son compte n'existe plus, sa session est fermée et une erreur est levée.
     */
    private static function verifierMembre(int $idMembre): void
    {
        if (Membre::getById($idMembre) !== null) {
            return;
        }

        Membre::deconnexion();
        throw new UserException(
            "Votre session ne correspond plus à un membre enregistré. Reconnectez-vous avant de publier dans le forum.",
            401
        );
    }

    /**
     * Récupère les discussions et regroupe leurs réponses par question.
     *
     * @param bool $inclureArchivees Indique si toutes les discussions archivées doivent être incluses.
     * @param bool $inclureSatisfaites Indique si les discussions clôturées comme satisfaites doivent être incluses.
     * @return list<array<string, mixed>>
     */
    public static function getLesDiscussions(
        bool $inclureArchivees = false,
        bool $inclureSatisfaites = false
    ): array
    {
        $db = Database::getInstance();
        if ($inclureArchivees) {
            $conditionArchive = '';
        } elseif ($inclureSatisfaites) {
            $conditionArchive = 'where q.archive = 0 or q.satisfaite = 1';
        } else {
            $conditionArchive = 'where q.archive = 0';
        }
        $sql = <<<SQL
            select q.id as idQuestion, q.idMembre as idAuteur, q.titre,
                   q.contenu as question,
                   date_format(q.dateCreation, '%d/%m/%Y à %H:%i') as dateQuestion,
                   q.archive, q.satisfaite,
                   concat(m.prenom, ' ', m.nom) as auteur, m.photo as photoAuteur,
                   r.id as idReponse,
                   r.contenu as reponse,
                   date_format(r.dateCreation, '%d/%m/%Y à %H:%i') as dateReponse,
                   concat(mr.prenom, ' ', mr.nom) as auteurReponse
            from forum_question q
                join membre m on m.id = q.idMembre
                left join forum_reponse r on r.idQuestion = q.id
                left join membre mr on mr.id = r.idMembre
            $conditionArchive
            order by q.dateCreation desc, r.dateCreation
        SQL;
        $statement = $db->prepare($sql);
        $statement->execute();
        $discussions = [];

        while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
            $idQuestion = (int) $row['idQuestion'];
            if (!isset($discussions[$idQuestion])) {
                $discussions[$idQuestion] = [
                    'id' => $idQuestion,
                    'idAuteur' => (int) $row['idAuteur'],
                    'titre' => $row['titre'],
                    'question' => $row['question'],
                    'dateQuestion' => $row['dateQuestion'],
                    'archive' => (bool) $row['archive'],
                    'satisfaite' => (bool) $row['satisfaite'],
                    'auteur' => $row['auteur'],
                    'photoAuteur' => $row['photoAuteur'],
                    'reponses' => []
                ];
            }

            if ($row['idReponse'] !== null) {
                $discussions[$idQuestion]['reponses'][] = [
                    'id' => (int) $row['idReponse'],
                    'auteur' => $row['auteurReponse'],
                    'contenu' => $row['reponse'],
                    'date' => $row['dateReponse']
                ];
            }
        }

        $statement->closeCursor();
        return array_values($discussions);
    }

    /**
     * Valide puis enregistre une nouvelle question publiée par un membre.
     *
     * @param int $idMembre Identifiant de l'auteur de la question.
     * @param string $titre Titre de la question (5 à 150 caractères).
     * @param string $contenu Texte de la question (5 à 5 000 caractères).
     * @throws UserException Si le membre n'existe plus ou si les champs sont invalides.
     */
    public static function poserQuestion(int $idMembre, string $titre, string $contenu): void
    {
        self::verifierMembre($idMembre);

        $titre = trim($titre);
        $contenu = trim($contenu);
        if (mb_strlen($titre) < 5 || mb_strlen($titre) > 150) {
            throw new UserException("Le titre doit contenir entre 5 et 150 caractères.");
        }
        if (mb_strlen($contenu) < 5 || mb_strlen($contenu) > 5000) {
            throw new UserException("La question doit contenir entre 5 et 5 000 caractères.");
        }

        $statement = Database::getInstance()->prepare(
            'insert into forum_question (idMembre, titre, contenu) values (:idMembre, :titre, :contenu)'
        );
        $statement->execute(['idMembre' => $idMembre, 'titre' => $titre, 'contenu' => $contenu]);
    }

    /**
     * Ajoute une réponse à une discussion ouverte après validation du membre et du texte.
     *
     * @param int $idMembre Identifiant de l'auteur de la réponse.
     * @param int $idQuestion Identifiant de la discussion à laquelle répondre.
     * @param string $contenu Texte de la réponse (2 à 5 000 caractères).
     * @throws UserException Si le membre n'existe plus, le texte est invalide ou la discussion est fermée.
     */
    public static function repondre(int $idMembre, int $idQuestion, string $contenu): void
    {
        self::verifierMembre($idMembre);

        $contenu = trim($contenu);
        if (mb_strlen($contenu) < 2 || mb_strlen($contenu) > 5000) {
            throw new UserException("La réponse doit contenir entre 2 et 5 000 caractères.");
        }

        $statement = Database::getInstance()->prepare(<<<SQL
            insert into forum_reponse (idQuestion, idMembre, contenu)
            select id, :idMembre, :contenu
            from forum_question
            where id = :idQuestion and archive = 0
        SQL);
        $statement->execute([
            'idMembre' => $idMembre,
            'contenu' => $contenu,
            'idQuestion' => $idQuestion
        ]);
        if ($statement->rowCount() !== 1) {
            throw new UserException("Cette discussion n'existe pas ou est archivée.");
        }
    }

    /**
     * Clôture comme satisfaite la discussion de l'auteur, uniquement si elle a reçu une réponse.
     *
     * @param int $idMembre Identifiant de l'auteur de la discussion.
     * @param int $idQuestion Identifiant de la discussion à clôturer.
     * @throws UserException Si le membre n'existe plus ou si la discussion ne peut pas être clôturée.
     */
    public static function marquerSatisfaite(int $idMembre, int $idQuestion): void
    {
        self::verifierMembre($idMembre);

        $statement = Database::getInstance()->prepare(<<<SQL
            update forum_question
            set satisfaite = 1, archive = 1, archiveLe = current_timestamp, archivePar = null
            where id = :idQuestion and idMembre = :idMembre and archive = 0
                and exists (
                    select 1 from forum_reponse r where r.idQuestion = forum_question.id
                )
        SQL);
        $statement->execute(['idQuestion' => $idQuestion, 'idMembre' => $idMembre]);
        if ($statement->rowCount() !== 1) {
            throw new UserException("Cette discussion ne peut pas être clôturée.");
        }
    }

    /**
     * Archive ou restaure une discussion à la demande d'un administrateur autorisé.
     *
     * Le droit d'administration est vérifié par le point d'accès qui appelle cette méthode.
     *
     * @param int $idQuestion Identifiant de la discussion à modifier.
     * @param int $idAdministrateur Identifiant de l'administrateur qui archive la discussion.
     * @param bool $archive true pour archiver, false pour restaurer.
     * @throws UserException Si la discussion n'existe pas ou si son état a déjà changé.
     */
    public static function definirArchivage(int $idQuestion, int $idAdministrateur, bool $archive): void
    {
        $sql = $archive
            ? 'update forum_question set archive = 1, satisfaite = 0, archiveLe = current_timestamp, archivePar = :idAdministrateur where id = :idQuestion and archive = 0'
            : 'update forum_question set archive = 0, satisfaite = 0, archiveLe = null, archivePar = null where id = :idQuestion and archive = 1';
        $statement = Database::getInstance()->prepare($sql);
        $parameters = ['idQuestion' => $idQuestion];
        if ($archive) {
            $parameters['idAdministrateur'] = $idAdministrateur;
        }
        $statement->execute($parameters);
        if ($statement->rowCount() !== 1) {
            throw new UserException("Cette discussion n'existe pas ou son état a déjà changé.");
        }
    }

    /**
     * Supprime une discussion ; ses réponses sont supprimées par la clé étrangère en cascade.
     *
     * @param int $idQuestion Identifiant de la discussion à supprimer.
     * @throws UserException Si la discussion n'existe plus.
     */
    public static function supprimerDiscussion(int $idQuestion): void
    {
        $statement = Database::getInstance()->prepare('delete from forum_question where id = :idQuestion');
        $statement->execute(['idQuestion' => $idQuestion]);
        if ($statement->rowCount() !== 1) {
            throw new UserException("Cette discussion n'existe plus.");
        }
    }
}
