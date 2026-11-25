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
Forum::supprimerDiscussion(Requete::postInt('idQuestion'));
ReponseJson::envoyerMessage("La discussion et ses réponses ont été supprimées.");
