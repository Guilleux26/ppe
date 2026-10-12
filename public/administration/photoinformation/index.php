<?php

declare(strict_types=1);

use ClasseTechnique\FileManager;
use ClasseTechnique\Page;
use ClasseTechnique\Config;

/** @noinspection PhpIncludeInspection */
require $_SERVER['DOCUMENT_ROOT'] . "/../bootstrap/bootstrap.php";

// Instanciation directe du gestionnaire de fichiers pour le dossier PDF
$fileManager = new FileManager(DOSSIER_PHOTO_INFORMATION);

// alimentation et affichage de l'interface
$page = new Page();

$page->setTitre("Téléversement d'une image")
    ->setDonnee('lesFichiers',  $fileManager->getLesFichiers())
    ->setDonnee('lesParametres', Config::chargerPhp('image'))
    ->setDonnee('repertoire', substr(DOSSIER_PHOTO_INFORMATION, strlen(DOSSIER_WWW)))
    ->avecJeton()
    ->afficher();