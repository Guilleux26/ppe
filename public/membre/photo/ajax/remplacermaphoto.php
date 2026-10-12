<?php
declare(strict_types=1);

use ClasseMetier\Membre;
use ClasseTechnique\Config;
use ClasseTechnique\ImageManager;
use ClasseTechnique\InputFileImg;
use ClasseTechnique\ReponseJson;
use ClasseTechnique\Requete;

/** @noinspection PhpIncludeInspection */
require $_SERVER['DOCUMENT_ROOT'] . '/../bootstrap/bootstrap.php';

// Vérification d'un appel AJAX POST sécurisé
Requete::exigerPost();

// récupération des données
$file = Requete::getFile('fichier');

// Vérification de la présence du fichier
if (!$file) {
    ReponseJson::envoyerErreur("Aucun fichier n'a été transmis.");
}

// Recherche du membre pour vérifier son existence et récupérer la photo actuelle
$id = $_SESSION['membre']['id'];
$leMembre = Membre::getById($id);
if ($leMembre === null) {
    ReponseJson::envoyerErreur("Ce membre n'existe pas.");
}

$anciennePhoto = $leMembre['photo'];

// Instanciation et validation de l'objet InputFileImg
$lesParametres = Config::chargerPhp('membre');
$inputFileImg = new InputFileImg($file, $lesParametres);

if (!$inputFileImg->checkValidity()) {
    ReponseJson::envoyerErreur( $inputFileImg->getValidationMessage());
}

// Instanciation du FileManager
$imageManager = new ImageManager(DOSSIER_PHOTO_MEMBRE, $lesParametres);

// Génération d'un nouveau nom unique (gère le changement d'extension)
$nouvellePhoto = $imageManager->genererNomUnique($inputFileImg->getValue());

// Sauvegarde physique du nouveau logo
$imageManager->copierImage($inputFileImg, $nouvellePhoto);

// Instanciation de l'objet métier
$membre = new Membre();

try {
    // modify($id, $data) retourne false en cas d'erreur de validation métier
    if (!$membre->modify($id, ['photo' => $nouvellePhoto])) {
        $imageManager->supprimer($nouvellePhoto);
        ReponseJson::envoyerLesErreurs($membre->getErrors());
    }
} catch (\Throwable $e) {
    // Si la BDD échoue, nettoyage du NOUVEAU logo créé sur le disque
    $imageManager->supprimer($nouvellePhoto);
    throw $e;
}

// Si la BDD est mise à jour avec succès, suppression de l me ANCIEN logo du disque
if ($anciennePhoto !== null) {
    $imageManager->supprimer($anciennePhoto);
}

ReponseJson::envoyerMessage("Votre photo est enregistrée.");