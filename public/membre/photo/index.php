<?php
declare(strict_types=1);

use ClasseMetier\Membre;
use ClasseTechnique\Config;
use ClasseTechnique\Page;

/** @noinspection PhpIncludeInspection */
require $_SERVER['DOCUMENT_ROOT'] . "/../bootstrap/bootstrap.php";

// chargement des données

// génération de la page
$page = new Page();

$page->setTitre("Ma Photo")
    ->setDonnee("photo", Membre::getPhoto($_SESSION["membre"]["id"]))
    ->setDonnee('lesParametres', Config::chargerPhp('membre'))
    ->avecJeton()
    ->afficher();
