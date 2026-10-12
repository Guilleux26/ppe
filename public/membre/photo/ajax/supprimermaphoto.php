<?php
declare(strict_types=1);

use ClasseMetier\Membre;
use ClasseTechnique\Config;
use ClasseTechnique\ImageManager;
use ClasseTechnique\ReponseJson;
use ClasseTechnique\Requete;

/** @noinspection PhpIncludeInspection */
require $_SERVER['DOCUMENT_ROOT'] . '/../bootstrap/bootstrap.php';

// Vérification d'un appel AJAX POST sécurisé
Requete::exigerPost();


// Recherche du membre pour vérifier son existence et récupérer la photo actuelle
$id = $_SESSION['membre']['id'];
$leMembre = Membre::getById($id);
if ($leMembre === null) {
    ReponseJson::envoyerErreur("Ce membre n'existe pas.");
}

$anciennePhoto = $leMembre['photo'];
if ($anciennePhoto === null) {
    ReponseJson::envoyerErreur("Aucune photo n'est associée à ce membre.");
}

// Instanciation de l'objet métier
$membre = new Membre();

// effacer le champ photo dans la BDD
if (!$membre->modify($id, ['photo' => null])) {
    ReponseJson::envoyerLesErreurs($membre->getErrors());
}

// Si la BDD est mise à jour avec succès, suppression de l'ancienne photo sur le disque

// Instanciation et validation de l'objet InputFileImg

// Instanciation du FileManager
$imageManager = new ImageManager(DOSSIER_PHOTO_MEMBRE, Config::chargerPhp('membre'));
$imageManager->supprimer($anciennePhoto);

ReponseJson::envoyerMessage("Votre photo est enregistrée.");