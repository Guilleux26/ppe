<?php
declare(strict_types=1);

use ClasseMetier\Forum;
use ClasseTechnique\ReponseJson;
use ClasseTechnique\Requete;

/** @noinspection PhpIncludeInspection */
require $_SERVER['DOCUMENT_ROOT'] . "/../bootstrap/bootstrap.php";

Requete::exigerPost();
Forum::poserQuestion(
    (int) $_SESSION['membre']['id'],
    Requete::postString('titre'),
    Requete::postString('contenu')
);
ReponseJson::envoyerMessage("Votre question a été publiée.");
