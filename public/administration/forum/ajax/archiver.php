<?php
declare(strict_types=1);

use ClasseMetier\Administrateur;
use ClasseMetier\Forum;
use ClasseTechnique\ReponseJson;
use ClasseTechnique\Requete;
use ClasseTechnique\UserException;

/** @noinspection PhpIncludeInspection */
require $_SERVER['DOCUMENT_ROOT'] . "/../bootstrap/bootstrap.php";

$idAdministrateur = (int) $_SESSION['membre']['id'];
if (!Administrateur::peutAdministrer($idAdministrateur, 'forum')) {
    throw new UserException("Vous n'êtes pas autorisé à gérer le forum.", 403);
}

Requete::exigerPost();
$etat = Requete::postString('archive');
if (!in_array($etat, ['0', '1'], true)) {
    throw new UserException("L'état d'archivage demandé est invalide.");
}

Forum::definirArchivage(
    Requete::postInt('idQuestion'),
    $idAdministrateur,
    $etat === '1'
);
ReponseJson::envoyerMessage($etat === '1' ? "La discussion a été archivée." : "La discussion a été restaurée.");
