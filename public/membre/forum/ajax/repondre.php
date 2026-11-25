<?php
declare(strict_types=1);

use ClasseMetier\Forum;
use ClasseTechnique\ReponseJson;
use ClasseTechnique\Requete;

/** @noinspection PhpIncludeInspection */
require $_SERVER['DOCUMENT_ROOT'] . "/../bootstrap/bootstrap.php";

Requete::exigerPost();
Forum::repondre(
    (int) $_SESSION['membre']['id'],
    Requete::postInt('idQuestion'),
    Requete::postString('contenu')
);
ReponseJson::envoyerMessage("Votre réponse a été publiée.");
