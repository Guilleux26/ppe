<?php
declare(strict_types=1);

namespace ClasseMetier;

use ClasseTechnique\ColumnEmail;
use ClasseTechnique\ColumnInt;
use ClasseTechnique\ColumnText;
use ClasseTechnique\Database;
use ClasseTechnique\Select;
use ClasseTechnique\Table;
use ClasseTechnique\TextCase;
use ClasseTechnique\UserException;
use PDO;
use Throwable;

/**
 * Classe métier représentant un membre.
 *
 */
class Membre extends Table
{
    private const array MEMBRES_ORIGINE = [
        ['VERGHOTE', 'GUY', 'guy.verghote@saint-remi.net', 'ac.png', 1],
        ['BAKASSI', 'SOULAIMANE', 'soulaimane.bakassi@saint-remi.net', 'bakassi soulaimane.jpg', 0],
        ['BERNARD', 'JULIEN', 'julien.bernard@saint-remi.net', 'bernard julien.jpg', 0],
        ['BOULARBI', 'MEDDHY', 'meddhy.boularbi@saint-remi.net', 'boularbi meddhy.jpg', 0],
        ['CARON', 'ADAM', 'adam.caron@saint-remi.net', 'caron adam.jpg', 0],
        ['CHARKAOUI', 'RAYANE', 'rayane.charkaoui@saint-remi.net', 'charkaoui rayane.jpg', 0],
        ['CHASTAGNER', 'ARTHUR', 'arthur.chastagner@saint-remi.net', 'chastagner arthur.jpg', 0],
        ['COULON', 'ALEXANDRE', 'alexandre.coulon@saint-remi.net', 'coulon alexandre.jpg', 0],
        ['DUBOIS', 'ALEXANDRE', 'alexandre.dubois@saint-remi.net', 'dubois alexandre.jpg', 0],
        ['JOSSE', 'THOMAS', 'thomas.josse@saint-remi.net', 'josse thomas.jpg', 0],
        ['LE CANU', 'MATHIS', 'mathis.le-canu@saint-remi.net', 'le canu mathis.jpg', 0],
        ['LION', 'ZIGGY', 'ziggy.lion@saint-remi.net', 'lion ziggy.jpg', 0],
        ['LONGBY', 'RENEDI', 'renedi.longby@saint-remi.net', 'longby renedi.jpg', 0],
        ['LOURDEL', 'MATHIS', 'mathis.lourdel@saint-remi.net', 'lourdel mathis.jpg', 0],
        ['MORALES', 'SIMON', 'simon.morales@saint-remi.net', 'morales simon.jpg', 0],
        ['NEDELEC', 'FLORE', 'flore.nedelec@saint-remi.net', 'nedelec flore.jpg', 0],
        ['PARIS', 'THOMAS', 'thomas.paris@saint-remi.net', 'paris thomas.jpg', 0],
        ['RICHARD', 'TONNY', 'tonny.richard@saint-remi.net', null, 0],
        ['RICHARD', 'TOM', 'tom.richard@saint-remi.net', 'richard tom.jpg', 0],
        ['ROELENS', 'GABRIEL', 'gabriel.roelens@saint-remi.net', 'roelens gabriel.jpg', 0],
        ['SOUKTANI', 'LEO', 'leo.souktani@saint-remi.net', 'souktani leo.jpg', 0],
        ['SUBERU', 'MOUBARAK', 'moubarak.suberu@saint-remi.net', 'suberu moubarak.jpg', 0],
        ['TISON', 'CLAIRE', 'claire.tison@saint-remi.net', 'tison claire.jpg', 0],
        ['BALDE', 'AISSATOU', 'aissatou.balde@saint-remi.net', 'balde aissatou.jpg', 0],
        ['BOILET', 'KAMERON', 'kameron.boilet@saint-remi.net', 'boilet kameron.jpg', 0],
        ['BOULLY', 'ALEXANDRE', 'alexandre.boully@saint-remi.net', 'boully alexandre.jpg', 0],
        ['CAZIN', 'TOM', 'tom.cazin@saint-remi.net', 'cazin tom.jpg', 0],
        ['DIANI', 'ISMAEL', 'ismael.diani@saint-remi.net', 'diani ismael.jpg', 0],
        ['DUMONT', 'HUGO', 'hugo.dumont@saint-remi.net', 'dumont hugo.jpg', 0],
        ['DUPRESSOIR', 'MATHIEU', 'mathieu.dupressoir@saint-remi.net', 'dupressoir mathieu.jpg', 0],
        ['FOULON', 'MATHIS', 'mathys.foulon@saint-remi.net', 'foulon mathis.jpg', 0],
        ['GARNIER', 'KYLLIAN', 'kyllian.garnier@saint-remi.net', 'garnier kyllian.jpg', 0],
        ['KARACA', 'ATTILA', 'attila.karaca@saint-remi.net', 'karaca attila.jpg', 0],
        ['MARGOTIN', 'PAUL', 'paul.margotin@saint-remi.net', 'margotin paul.jpg', 0],
        ['MERCIER', 'ALEXI', 'alexi.mercier@saint-remi.net', 'mercier alexi.jpg', 0],
        ['MERVILLE', 'LUCAS', 'lucas.merville@saint-remi.net', 'merville lucas.jpg', 0],
        ['MORTELETTE', 'CLEMENT', 'clement.mortelette@saint-remi.net', 'mortelette clement.jpg', 0],
        ['NOUHI', 'MARWAN', 'marwan.nouhi@saint-remi.net', 'nouhi marwan.jpg', 0],
        ['ROUSELLE', 'ETIENNE', 'etienne.rouselle@saint-remi.net', 'rouselle etienne.jpg', 0],
        ['VASSEUR', 'LORENZO', 'lorenzo.vasseur@saint-remi.net', 'vasseur lorenzo.jpg', 0],
        ['YILDIZ', 'MUHAMMEDALI', 'muhammedali.yildiz@saint-remi.net', 'yildiz muhammedali.jpg', 0],
        ['ZON', 'JEREMY', 'jeremy.zon@saint-remi.net', 'zon jeremy.jpg', 0],
        ['BEN AHMED', 'ILYES', 'yles.benahmed@saint-remi.net', 'ben ahmed ilyes.jpg', 1],
        ['BENAALMA', 'YANIS', 'yanis.benaalma@saint-remi.net', 'benaalma yanis.jpg', 1],
        ['BOSSIAUX', 'ALEXANDRE', 'alexandre.bossiaux@saint-remi.net', 'bossiaux alexandre.jpg', 1],
        ['CAUET', 'QUENTIN', 'quentin.cauet@saint-remi.net', 'cauet quentin.jpg', 1],
        ['CZABAN', 'MATHIAS', 'mathias.czaban@saint-remi.net', 'czaban mathias.jpg', 1],
        ['DAUTREVAUX', 'THEO', 'theo.dautrevaux@saint-remi.net', 'dautrevaux theo.jpg', 1],
        ['DAY RODY', 'LUCAS', 'lucas.dayrody@saint-remi.net', 'day rody lucas.jpg', 1],
        ['DEGREMONT', 'MAXENCE', 'maxence.degremont@saint-remi.net', 'degremont maxence.jpg', 1],
        ['DELIGNIES', 'KYLIAN', 'kylian.delignies@saint-remi.net', 'delignies kylian.jpg', 1],
        ['DUBOS', 'MATHIS', 'mathis.dubos@saint-remi.net', 'dubos mathis.jpg', 1],
        ['DUSSY', 'LILIAN', 'lilian.dussy@saint-remi.net', 'dussy lilian.jpg', 1],
        ['FORBRAS', 'NOE', 'noe.forbras@saint-remi.net', 'forbras noe.jpg', 1],
        ['GUILLEUX', 'MAEL', 'mael.guilleux@saint-remi.net', 'guilleux mael.jpg', 1],
        ['HOUDERRANI', 'KARIM', 'karim.houderrani@saint-remi.net', 'houderrani karim.jpg', 1],
        ['KHUDOYAN', 'DIANA', 'diana.khudoyan@saint-remi.net', 'khudoyan diana.jpg', 1],
        ['KOUAM KAMDEM', 'NOE', 'noe.kouamkamdem@saint-remi.net', 'kouam kamdem noe.jpg', 1],
        ['KWIZERA', 'EDMOND', 'edmond.kwizera@saint-remi.net', 'kwizera edmond.jpg', 1],
        ['LAVARENNE', 'JOLAN', 'jolan.lavarenne@saint-remi.net', 'lavarenne jolan.jpg', 1],
        ['LECLERCQ', 'ZACKARY', 'zackary.leclercq@saint-remi.net', 'leclercq zackary.jpg', 1],
        ['LESIEUR', 'TOM', 'tom.lesieur@saint-remi.net', 'lesieur tom.jpg', 1],
        ['LOMBARD', 'THOMAS', 'thomas.lombard@saint-remi.net', 'lombard thomas.jpg', 1],
        ['MARQUANT', 'ALEXANDRE', 'alexandre.marquant@saint-remi.net', 'marquant alexandre.jpg', 1],
        ['METGY', 'LEO', 'leo.metgy@saint-remi.net', 'metgy leo.jpg', 1],
        ['MUKADI KABOMBO', 'JOSUE', 'josue.mukadikabombo@saint-remi.net', 'mukadi kabombo josue.jpg', 1],
        ['PODLUNSEK', 'CORENTIN', 'corentin.podlunsek@saint-remi.net', 'podlunsek corentin.jpg', 1],
        ['SCHAAF', 'NAEL', 'nael.schaaf@saint-remi.net', 'schaaf nael.jpg', 1],
        ['SECLET', 'BRYAN', 'bryan.seclet@saint-remi.net', 'seclet bryan.jpg', 1],
        ['SFIH', 'NOURA', 'noura.sfih@saint-remi.net', 'sfih noura.jpg', 1],
        ['SOW', 'MOUHAMED', 'mouhamed.sow@saint-remi.net', 'sow mouhamed.jpg', 1],
        ['TOUDJI', 'RHILES', 'rhiles.toudji@saint-remi.net', 'toudji rhiles.jpg', 1],
        ['VARLET', 'MATTEO', 'matteo.varlet@saint-remi.net', 'varlet matteo.jpg', 1]
    ];

    /**
     * Configuration de la table.
     */
    protected function configure(): void
    {
        $this->table = 'membre';
        $this->primaryKey = 'id';


        // ------------------------------------------------------
        // nom
        // ------------------------------------------------------

        $column = new ColumnText(
            required: true,
            insertable: true,
            updatable: false,
            casse: TextCase::Upper,
            supprimerAccent: true,
            supprimerEspaceSuperflu: true,
            pattern: "^[A-Z]( ?[A-Z]+)*$",
            maxLength: 30
        );

        $this->addColumn('nom', $column);


        // ------------------------------------------------------
        // prenom
        // ------------------------------------------------------

        $column = new ColumnText(
            required: true,
            insertable: true,
            updatable: false,
            casse: TextCase::Upper,
            supprimerAccent: true,
            supprimerEspaceSuperflu: true,
            pattern: "^[A-Z]( ?[A-Z]+)*$",
            maxLength: 30
        );
        $this->addColumn('prenom', $column);

        // ------------------------------------------------------
        // email
        // ------------------------------------------------------

        $column = new ColumnEmail(
            required: true,
            insertable: true,
            updatable: false,
            maxLength: 100
        );
        $this->addColumn('email', $column);

        // ------------------------------------------------------
        // photo
        // ------------------------------------------------------

        $column = new ColumnText(
            required: false,
            insertable: false,
            updatable: true
        );
        $this->addColumn('photo', $column);


        // ------------------------------------------------------
        // telephone
        // ------------------------------------------------------

        $column = new ColumnText(
            required: false,
            insertable: true,
            updatable: true
        );
        $this->addColumn('telephone', $column);

        // ------------------------------------------------------
        // autMail
        // ------------------------------------------------------

        $column = new ColumnInt(
            required: false,
            insertable: false,
            updatable: true,
            min: 0,
            max: 1
        );
        $this->addColumn('autMail', $column);

        $column = new ColumnInt(
            required: false,
            insertable: true,
            updatable: false,
            min: 2000,
            max: 2200
        );
        $this->addColumn('saison', $column);

        $this->addColumn('login', new ColumnText(
            required: false,
            insertable: true,
            updatable: false,
            maxLength: 50
        ));
        $this->addColumn('password', new ColumnText(
            required: false,
            insertable: true,
            updatable: false,
            maxLength: 64
        ));
    }

    protected function populateData(): void
    {
        if ($this->getValue('saison') === null) {
            $this->setValue('saison', (int) date('Y'));
        }
    }

    protected function beforeInsert(): bool
    {
        $nom = (string) $this->getValue('nom');
        $prenom = (string) $this->getValue('prenom');
        $baseLogin = mb_strtolower(
            mb_substr($prenom, 0, 1, 'UTF-8') . str_replace(' ', '', $nom),
            'UTF-8'
        );
        $login = $baseLogin;
        $suffixe = 1;
        $select = new Select();

        while ($select->getValue('select 1 from membre where login = :login', ['login' => $login]) !== false) {
            $login = $baseLogin . $suffixe;
            $suffixe++;
        }

        $this->setValue('login', $login);
        $this->setValue('password', hash('sha256', '0000'));
        return true;
    }


    // ==========================================================
    // Consultation
    // ==========================================================

    /**
     * Liste des membres pour administration.
     */
    public static function getAll(): array
    {
        $sql = <<<SQL
            select id,
                   concat(nom, ' ', prenom) as nomPrenom,
                   nom, prenom, email,
                   ifnull(photo, 'Non renseignée') as photo,
                   coalesce(
                       nullif(trim(login), ''),
                       concat(lower(left(prenom, 1)), lower(replace(trim(nom), ' ', '')))
                   ) as login,
                   if(autMail, 'Oui', 'Non') as afficherMail,
                   ifnull(telephone, 'Non renseigné') as telephone,
                   saison
            from membre
            order by nom, prenom;
SQL;
        $select = new Select();
        return $select->getRows($sql);
    }

    public static function restaurerMembresOrigine(): int
    {
        $db = Database::getInstance();
        $existants = [];
        foreach (self::getAll() as $membre) {
            $existants[$membre['email']] = $membre;
        }

        $annee = (int) date('Y');
        $ajoutes = 0;
        $db->beginTransaction();

        try {
            $membre = new self();
            foreach (self::MEMBRES_ORIGINE as [$nom, $prenom, $email, $photo, $decalageSaison]) {
                $saison = $annee + $decalageSaison;
                if (isset($existants[$email])) {
                    continue;
                }

                if (!$membre->add([
                    'nom' => $nom,
                    'prenom' => $prenom,
                    'email' => $email,
                    'saison' => $saison
                ])) {
                    throw new UserException("Impossible de restaurer le membre $prenom $nom.");
                }

                $idNouveauMembre = $membre->getLastInsertId();
                if ($photo !== null) {
                    if ($idNouveauMembre === false || !$membre->modify($idNouveauMembre, ['photo' => $photo])) {
                        throw new UserException("Impossible de restaurer la photo du membre $prenom $nom.");
                    }
                }
                $ajoutes++;
            }
            $db->commit();
        } catch (Throwable $exception) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            throw $exception;
        }

        return $ajoutes;
    }

    /**
     * Récupère les membres pour affichage dans l'annuaire public.
     */
    public static function getLesMembres(): array
    {
        $sql = <<<SQL
            select nom, prenom,
                   if(autMail, email, 'Non communiqué') as mail,
                   ifnull(telephone, 'Non renseigné') as telephone,
                   ifnull(photo, 'Non renseignée') as photo,
                   saison
            from membre
            order by nom, prenom;
SQL;

        $select = new Select();
        return $select->getRows($sql);
    }

    /**
     * Récupère les données d'un membre à partir de son login.
     */
    public static function getByLogin(string $login): ?array
    {
        $sql = <<<SQL
            select id, login, email, nom, prenom from membre
            where login = :login;
SQL;
        $select = new Select();
        return $select->getRow($sql, ['login' => $login]);
    }

    /**
     * Récupère les données d'un membre à partir de son identifiant.
     */
    public static function getById(int $id): ?array
    {
        $sql = <<<SQL
            select id, nom, prenom, email, login, telephone, autMail, photo
            from membre
            where id = :id;
SQL;
        $select = new Select();
        return $select->getRow($sql, ['id' => $id]);
    }

    /**
     * Récupère le nom de la photo associée à un membre.
     *
     * Cette méthode retourne uniquement la valeur enregistrée
     * dans la table membre. La vérification de l'existence
     * physique du fichier relève du service.
     *
     * @param int $id Identifiant du membre.
     * @return array|null
     */
    public static function getPhoto(int $id): ?array
    {
        $sql = <<<SQL
            select id, photo
            from membre
            where id = :id;
SQL;

        $select = new Select();
        $ligne = $select->getRow($sql, ['id' => $id]);
        return $ligne;
    }


    /**
     * Récupère les identifiants de tous les membres.
     *
     * Cette méthode retourne un tableau simple (liste de valeurs),
     * ce que la classe Select ne permet pas de produire directement.
     *
     * @return array<int, int> Liste des identifiants des membres.
     */
    public static function getLesIds(): array
    {
        $db = Database::getInstance();
        $cmd = $db->query('select id from membre');
        return array_map('intval', $cmd->fetchAll(PDO::FETCH_COLUMN));
    }

    /**
     * Récupère les saisons distinctes des membres actifs.
     *
     * @return array<int, array<string, mixed>> Liste des saisons.
     */
    public static function getLesSaisons()
    {
        $sql = <<<SQL
        Select distinct saison
        from membre 
        order by saison;
SQL;
        $select = new Select();
        return $select->getRows($sql);
    }

    // ==========================================================
    // Gestion de la connexion
    // ==========================================================


    /**
     * Vérifie le mot de passe d'un membre.
     */
    public static function verifierPassword(int $id, string $password): bool
    {

        $sql = <<<SQL
            select password
            from membre
            where id = :id;
SQL;

        $select = new Select();
        $ligne = $select->getRow($sql, ['id' => $id]);
        return $ligne !== null && $ligne['password'] === hash('sha256', $password);
    }

    /**
     * Mémorise le membre connecté dans la session.
     */
    public static function connexion(array $membre): void
    {
        $_SESSION['membre'] = $membre;
    }


    /**
     * Déconnexion du membre courant.
     */
    public static function deconnexion(): void
    {
        unset($_SESSION['membre']);
    }





}