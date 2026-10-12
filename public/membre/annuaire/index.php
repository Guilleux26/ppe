<?php
declare(strict_types=1);

use ClasseMetier\Membre;
use ClasseTechnique\Page;

//* noinspection PhpIncludeInspection */
require $_SERVER['DOCUMENT_ROOT'] . "/../bootstrap/bootstrap.php";

// récupération du répertoire de stockage des photos de membres
$repertoire = substr(DOSSIER_PHOTO_MEMBRE, strlen(DOSSIER_WWW));

// génération de la page
$page = new Page();

$page->setTitre("Annuaire des membres")
    ->setDonnee("lesMembres",Membre::getLesMembres())
    ->setDonnee("lesSaisons", Membre::getLesSaisons())
    ->setDonnee("repertoire", $repertoire)
    ->afficher();