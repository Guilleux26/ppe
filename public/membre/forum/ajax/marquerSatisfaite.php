<?php
declare(strict_types=1);

use ClasseMetier\Forum;
use ClasseTechnique\ReponseJson;
use ClasseTechnique\Requete;

/** @noinspection PhpIncludeInspection */
require $_SERVER['DOCUMENT_ROOT'] . "/../bootstrap/bootstrap.php";

Requete::exigerPost();
Forum::marquerSatisfaite(
    (int) $_SESSION['membre']['id'],
    Requete::postInt('idQuestion')
);
ReponseJson::envoyerMessage("La discussion a été clôturée car la réponse vous satisfait.");
