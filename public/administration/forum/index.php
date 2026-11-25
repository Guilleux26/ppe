<?php
declare(strict_types=1);

use ClasseMetier\Forum;
use ClasseMetier\Administrateur;
use ClasseTechnique\Page;
use ClasseTechnique\UserException;

/** @noinspection PhpIncludeInspection */
require $_SERVER['DOCUMENT_ROOT'] . "/../bootstrap/bootstrap.php";

$idAdministrateur = (int) $_SESSION['membre']['id'];
if (!Administrateur::peutAdministrer($idAdministrateur, 'forum')) {
    throw new UserException("Vous n'êtes pas autorisé à gérer le forum.", 403);
}

$page = new Page();
$page->setTitre("Gestion du forum")
    ->addStyle("/membre/forum/index.css")
    ->setDonnee('lesDiscussions', Forum::getLesDiscussions(true))
    ->setDonnee('repertoirePhoto', substr(DOSSIER_PHOTO_MEMBRE, strlen(DOSSIER_WWW)))
    ->avecJeton()
    ->afficher();
