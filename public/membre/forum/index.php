<?php
declare(strict_types=1);

use ClasseMetier\Forum;
use ClasseMetier\Administrateur;
use ClasseTechnique\Page;

/** @noinspection PhpIncludeInspection */
require $_SERVER['DOCUMENT_ROOT'] . "/../bootstrap/bootstrap.php";

$idMembre = (int) $_SESSION['membre']['id'];
$estAdministrateur = (bool) Administrateur::estUnAdministrateur($idMembre);
$repertoirePhoto = substr(DOSSIER_PHOTO_MEMBRE, strlen(DOSSIER_WWW));

$page = new Page();
$page->setTitre("Forum des membres")
    ->setDonnee('lesDiscussions', Forum::getLesDiscussions($estAdministrateur))
    ->setDonnee('idMembre', $idMembre)
    ->setDonnee('repertoirePhoto', $repertoirePhoto)
    ->setDonnee('estAdministrateur', $estAdministrateur)
    ->avecJeton()
    ->afficher();
