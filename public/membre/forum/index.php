<?php
declare(strict_types=1);

use ClasseMetier\Administrateur;
use ClasseTechnique\Page;
use ClasseTechnique\UserException;

/** @noinspection PhpIncludeInspection */
require $_SERVER['DOCUMENT_ROOT'] . "/../bootstrap/bootstrap.php";

if (!isset($_SESSION['membre']['id'])
    || !Administrateur::estUnAdministrateur((int) $_SESSION['membre']['id'])) {
    throw new UserException("Vous devez être administrateur pour accéder au forum.");
}

$page = new Page();
$page->setTitre("Administration du forum")->afficher();
