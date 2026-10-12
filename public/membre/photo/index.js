"use strict";

// -----------------------------------------------------------------------------------
// Import des fonctions nécessaires
// -----------------------------------------------------------------------------------

import {getData} from "/composant/fonction/page.js";
import {appelAjax} from "/composant/fonction/ajax.js";
import {afficherToast,} from "/composant/fonction/afficher.js";
import {configurerFormulaire, effacerLesErreurs} from "/composant/fonction/formulaire.js";
import {fichierValide, verifierImage} from "/composant/fonction/fichier.js";

// -----------------------------------------------------------------------------------
// Déclaration des variables globales
// -----------------------------------------------------------------------------------


const photo = getData("photo");
const lesParametres = getData("lesParametres");

const msg = document.getElementById('msg');
const cible = document.getElementById('cible');
const fichier = document.getElementById('fichier');
const btnSupprimer = document.getElementById('btnSupprimer');


// Extraction des paramètres
const repertoire = lesParametres.repertoire;
const lesExtensions = lesParametres.lesExtensions;
const maxSize = lesParametres.maxSize;
const redimensionner = lesParametres.resized;
const largeurMax = lesParametres.maxWidth;
const hauteurMax = lesParametres.maxHeight;

// -----------------------------------------------------------------------------------
// procédures évènementielles
// -----------------------------------------------------------------------------------

/// Déclencher le clic sur le champ de type file lors d'un clic dans la zone cible
cible.onclick = () => fichier.click();

// // ajout du glisser déposer dans la zone cible
cible.ondragover = (e) => e.preventDefault();
cible.ondrop = (e) => {
    e.preventDefault();
    controlerFichier(e.dataTransfer.files[0]);
};

// traitement du champ file associé aux modifications de photos
fichier.onchange = function () {
    if (this.files.length > 0) {
        controlerFichier(this.files[0]);
    }
};

// suppression de la photo
btnSupprimer.onclick = supprimerPhoto;

// -----------------------------------------------------------------------------------
// Fonctions de traitement
// -----------------------------------------------------------------------------------

/**
 * Contrôle le fichier sélectionné au niveau de son extension et de sa taille
 * Vérifie que le fichier est bien une image et que ses dimensions sont correctes si le redimensionnement n'est pas demandé
 * lancer la demande de remplacement de l'image
 * @param file {object} fichier à ajouter
 */
function controlerFichier(file) {
    // Efface les erreurs précédentes
    effacerLesErreurs();
    // Vérification de taille et d'extension
    if (!fichierValide(file, maxSize, lesExtensions)) {
        return;
    }
    // Vérifications spécifiques pour un fichier image
    // La fonction de rappel reçoit implicitement en paramètre l'objet file et l'objet Image créé
    verifierImage(file, redimensionner, largeurMax, hauteurMax, majPhoto);
}

/**
 * Remplace le fichier sélectionné par le nouveau fichier téléversé
 * @param file
 * @param img
 */
function majPhoto(file, img) {
    // Vider la zone de message utilisateur
    msg.innerHTML = "";

    // Créer un objet FormData pour envoyer les données du formulaire
    const formData = new FormData();
    formData.append('fichier', file);

    appelAjax({
        url: 'ajax/remplacermaphoto.php',
        data: formData,
        success: () => {
            // il faut afficher le bouton pour supprimer la photo
            btnSupprimer.style.display = 'block';
            cible.innerHTML = '';
            cible.appendChild(img);
            afficherToast('La photo a été modifiée');
        }
    });
}

/**
 * Supprime la photo
 */
function supprimerPhoto() {
    // Vider la zone de message utilisateur
    msg.innerHTML = "";

    appelAjax({
        url: 'ajax/supprimermaphoto.php',
        success: () => initialiserUpload()
    });
}


function initialiserUpload() {
    // Pas encore de photo ou plus de photo : on affiche l'aide.
    cible.innerHTML = '';
    const icone = document.createElement('span');
    icone.className = 'photo-upload-icon';
    icone.textContent = '📷';
    const texte = document.createElement('span');
    texte.className = 'photo-upload-text';
    texte.innerHTML = 'Déposer une photo<br>ou cliquer ici';
    cible.append(icone, texte);
    btnSupprimer.style.display = 'none';
}

// -----------------------------------------------------------------------------------
// Programme principal
// -----------------------------------------------------------------------------------

configurerFormulaire();

// Si un nom de fichier photo est spécifié
if (photo.photo) {
    const img = document.createElement('img');
    img.alt = 'photo du membre';

    // 1. Si l'image est trouvée et chargée avec succès
    img.onload = () => {
        cible.replaceChildren(img);
        btnSupprimer.style.display = 'block';
    };

    // 2. Si le fichier n'existe plus sur le réseau / serveur (erreur 404)
    img.onerror = () => {
        img.remove();
        initialiserUpload();
    };

    // 3. Déclenchement du chargement
    img.src = `${repertoire}/${photo.photo}`;
} else {
    initialiserUpload();
}