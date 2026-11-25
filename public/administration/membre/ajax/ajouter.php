<?php
declare(strict_types=1);

use ClasseMetier\Membre;
use ClasseTechnique\ReponseJson;
use ClasseTechnique\Requete;

/** @noinspection PhpIncludeInspection */
require $_SERVER['DOCUMENT_ROOT'] . '/../bootstrap/bootstrap.php';

// Vérification d'un appel AJAX par la méthode POST sécurisé par un jeton
Requete::exigerPost();

if (Requete::postString('action') !== 'ajouter') {
    ReponseJson::envoyerErreur("L'action demandée n'est pas reconnue.");
}

// récupération des données transmises
$columns = Requete::postArray('columns');

// création de l'objet métier
$membre = new Membre();

// ajout du membre
$resultat = $membre->add($columns);

if ($resultat === true) {
    ReponseJson::envoyerMessage("Membre ajouté");
}

// en cas d'erreur
ReponseJson::envoyerLesErreurs($membre->getErrors());