<?php
declare(strict_types=1);

use ClasseTechnique\Config;
use ClasseTechnique\ImageManager;
use ClasseTechnique\InputFileImg;
use ClasseTechnique\ReponseJson;
use ClasseTechnique\Requete;
use ClasseTechnique\UserException;

// Chargement automatique des classes et des constantes (DOSSIER_IMAGE)
require $_SERVER['DOCUMENT_ROOT'] . '/../bootstrap/bootstrap.php';

// 1. Vérification d'un appel AJAX par la méthode POST
Requete::exigerPost();

// 2. Récupération du fichier téléversé
$fichier = Requete::getFile('fichier');

if (!$fichier) {
    throw new UserException("Aucun fichier n'a été transmis.");
}

// 3. Chargement de la configuration pour les images
$lesParametres = Config::chargerPhp('image');

// 4. Instanciation et validation du fichier
$inputFileImg = new InputFileImg($fichier, $lesParametres);

if (!$inputFileImg->checkValidity()) {
    $message = $inputFileImg->getValidationMessage() ?: "Le fichier image n'est pas valide.";
    throw new UserException($message);
}

// 5. Instanciation de l'ImageManager avec le répertoire et la configuration
$imageManager = new ImageManager(DOSSIER_PHOTO_INFORMATION, $lesParametres);

// 6. Génération d'un nom de fichier unique
$nomUnique = $imageManager->genererNomUnique($inputFileImg->getValue());

// 7. Enregistrement du fichier (ImageManager gère le redimensionnement en interne)
$imageManager->copierImage($inputFileImg, $nomUnique);

// 8. Envoi de la liste à jour des fichiers images
ReponseJson::envoyerLesDonnees($imageManager->getLesFichiers());