# Documentation technique du forum

## Périmètre et architecture

Le forum suit la séparation habituelle du projet :

1. Les pages PHP chargent l'application, vérifient le contexte utilisateur et préparent les données.
2. La classe métier `ClasseMetier\Forum` applique les règles fonctionnelles et lit ou modifie les données.
3. Les points d'accès AJAX valident les requêtes et appellent la classe métier.
4. Le JavaScript construit les cartes de discussion et relie l'interface aux points d'accès.

Les principaux fichiers sont :

| Fichier | Responsabilité |
|---|---|
| `src/ClasseMetier/Forum.php` | Lecture et opérations métier du forum |
| `public/membre/forum/index.php` | Préparation de la page membre |
| `public/membre/forum/index.html` | Structure et libellés de l'interface |
| `public/membre/forum/index.js` | Affichage, filtres et interactions AJAX |
| `public/membre/forum/index.css` | Mise en forme responsive |
| `public/membre/forum/ajax/` | Publication, réponse et clôture |
| `public/administration/forum/index.php` | Page de modération avec contrôle de droit |
| `public/administration/forum/ajax/` | Archivage, restauration et suppression |
| `sql/forum/create.sql` | Tables, relations, index et droits SQL du forum |

## Classes métier utilisées

### `ClasseMetier\Forum`

Cette classe centralise les règles du forum. Elle utilise `ClasseTechnique\Database` pour les requêtes préparées, `ClasseTechnique\UserException` pour les erreurs fonctionnelles et `PDO` pour parcourir les résultats.

| Méthode | Utilité et règles |
|---|---|
| `getLesDiscussions(bool $inclureArchivees = false, bool $inclureSatisfaites = false): array` | Retourne les discussions triées par date décroissante, avec leurs réponses regroupées, les noms et photos d'auteurs, les dates formatées et les états d'archivage/satisfaction. Le premier paramètre inclut toutes les discussions archivées (usage administration) ; le second inclut seulement les discussions archivées comme satisfaites (usage membre), en plus des ouvertes. |
| `poserQuestion(int $idMembre, string $titre, string $contenu): void` | Vérifie l'existence du membre, nettoie les espaces extérieurs, valide le titre (5–150 caractères) et le contenu (5–5 000), puis insère la question. |
| `repondre(int $idMembre, int $idQuestion, string $contenu): void` | Vérifie le membre et la longueur de la réponse (2–5 000), puis l'ajoute uniquement si la discussion existe et est ouverte. |
| `marquerSatisfaite(int $idMembre, int $idQuestion): void` | Clôture et archive la discussion de son auteur uniquement si elle est ouverte et possède au moins une réponse. Renseigne l'état « satisfaite ». |
| `definirArchivage(int $idQuestion, int $idAdministrateur, bool $archive): void` | Archive une discussion en enregistrant l'administrateur, ou la restaure. Une modération remet l'état « satisfaite » à faux. |
| `supprimerDiscussion(int $idQuestion): void` | Supprime la discussion. La clé étrangère supprime aussi ses réponses. |
| `verifierMembre(int $idMembre): void` *(privée)* | Vérifie que le membre existe encore. Si ce n'est pas le cas, ferme sa session et lève une erreur `401`. |

`definirArchivage` documente que le contrôle du droit d'administration est réalisé par le point d'accès appelant, pas dans la méthode métier elle-même. Il faut donc conserver le contrôle `Administrateur::peutAdministrer(..., 'forum')` au niveau de la page et des actions d'administration.

### Autres classes métier impliquées

- `ClasseMetier\Administrateur::peutAdministrer(int $id, string $repertoire): bool` contrôle le droit spécifique au répertoire `forum`. La page membre utilise ce droit pour afficher le lien et charger les discussions archivées ; la page d'administration et chacun de ses points d'accès le vérifient également.
- `ClasseMetier\Membre::getById(int $id): ?array` vérifie la persistance du compte utilisé par `Forum::verifierMembre`.
- `ClasseMetier\Membre::deconnexion(): void` ferme la session si le compte membre n'existe plus.

## Points d'accès et méthodes techniques

Les opérations AJAX utilisent `Requete::exigerPost()` : la requête doit être AJAX, utiliser POST et présenter un jeton CSRF valide. Les valeurs sont lues par `Requete::postString()` ou `Requete::postInt()`. Les réponses de succès utilisent `ReponseJson::envoyerMessage()`.

| Point d'accès | Appel métier |
|---|---|
| `POST /membre/forum/ajax/poserQuestion.php` | `Forum::poserQuestion()` |
| `POST /membre/forum/ajax/repondre.php` | `Forum::repondre()` |
| `POST /membre/forum/ajax/marquerSatisfaite.php` | `Forum::marquerSatisfaite()` |
| `POST /administration/forum/ajax/archiver.php` | `Forum::definirArchivage()` |
| `POST /administration/forum/ajax/supprimer.php` | `Forum::supprimerDiscussion()` |

Les points d'accès d'administration vérifient `peutAdministrer($idAdministrateur, 'forum')` avant de traiter la requête. Le point d'accès d'archivage n'accepte que les valeurs `"0"` (restaurer) et `"1"` (archiver).

`ClasseTechnique\Page` prépare les pages avec `setTitre()`, `setDonnee()`, `addStyle()` et `avecJeton()`, puis les rend avec `afficher()`. Dans ce flux, les données fournies au JavaScript comprennent la liste des discussions, l'identifiant du membre, son statut d'administrateur et le chemin des photos. `ClasseTechnique\ReponseJson` uniformise les réponses JSON et termine le traitement après leur envoi.

## JavaScript de l'interface

Le script `public/membre/forum/index.js` utilise `getData()` pour lire les données de page, `appelAjax()` pour envoyer les formulaires/actions et `confirmer()` avant de marquer une réponse satisfaisante ou de supprimer une discussion. Le script d'administration utilise aussi `getData()` et `appelAjax()` ; il filtre par état, recherche dans le nom du membre, le titre et le contenu de la question, et utilise `window.confirm()` avant une suppression.

Ses fonctions locales principales :

- `element()` crée un élément HTML et lui associe une classe et un texte ;
- `bouton()` crée un bouton d'action associé à une discussion ;
- `afficherDiscussion()` construit une carte avec question, auteur, réponses et actions autorisées ;
- `afficherLesDiscussions()` applique le filtre et actualise la liste.

Après une opération réussie, l'interface recharge la page afin d'afficher l'état actualisé. Le texte injecté est placé avec `textContent` plutôt qu'interprété comme du HTML.

## Modèle de données

Le script `sql/forum/create.sql` définit deux tables :

- `forum_question` contient l'auteur, le titre, le contenu, la date, les indicateurs `archive` et `satisfaite`, ainsi que les informations d'archivage ;
- `forum_reponse` contient la question associée, l'auteur, le contenu et la date.

Les deux tables référencent `membre`. Les réponses référencent la question avec suppression en cascade. Les indicateurs d'état sont limités à `0` ou `1`; un index accélère la lecture des questions par état et date et un autre celle des réponses par question et date.

## Petite mise en pratique

Pour publier une question, l'interface envoie les champs `titre` et `contenu` en POST au point d'accès de création. Le contrôleur valide la requête et délègue au métier :

```php
Requete::exigerPost();
Forum::poserQuestion(
    (int) $_SESSION['membre']['id'],
    Requete::postString('titre'),
    Requete::postString('contenu')
);
ReponseJson::envoyerMessage("Votre question a été publiée.");
```

Pour vérifier le parcours complet en environnement de développement : se connecter comme membre, publier une question valide, répondre depuis un autre compte, puis clôturer avec le compte auteur. Vérifier ensuite qu'un compte sans droit de modération ne peut pas accéder à `/administration/forum`, et qu'un administrateur autorisé peut archiver puis restaurer la discussion.

## Interface de l'application

La page membre s'organise en quatre zones : en-tête du forum, formulaire de publication, filtre des discussions et liste de cartes. Les réponses apparaissent sous leur question. Le formulaire de réponse et l'action de clôture ne sont proposés que pour les discussions ouvertes ; la clôture est proposée à l'auteur et devient activable après la première réponse.

L'interface d'administration réutilise la présentation des discussions et ajoute les filtres par état, la recherche par nom de membre/titre/contenu de question et les actions d'archivage/restauration et de suppression. Les styles se replient pour les petits écrans et les éléments interactifs disposent d'un état de focus visible.
