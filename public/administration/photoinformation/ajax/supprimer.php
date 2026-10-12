<?php
declare(strict_types=1);

use ClasseTechnique\FileManager;
use ClasseTechnique\ReponseJson;
use ClasseTechnique\Requete;

// Chargement automatique des classes
require $_SERVER['DOCUMENT_ROOT'] . '/../bootstrap/bootstrap.php';

// Vérification d'un appel AJAX par la méthode POST sécurisé par un jeton
Requete::exigerPost();


// Récupération contrôlée des données transmises
$nomFichier = Requete::postString('nomFichier');

// création d'un objet FileManager pour gérer les fichiers PDF
$fileManager = new FileManager(DOSSIER_PHOTO_INFORMATION);

// Suppression du fichier
if (!$fileManager->supprimer($nomFichier)) {
    ReponseJson::envoyerLesErreurs(['global' => ['Le fichier n\'a pas pu être supprimé.']]);
}

// Retour des données actualisées
ReponseJson::envoyerLesDonnees($fileManager->getLesFichiers());