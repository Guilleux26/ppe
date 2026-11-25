"use strict";

// Import de la fonction permettant de lire les données transmises à la page.
import {getData} from "/composant/fonction/page.js";

// Import de la fonction permettant d'initialiser les cartes ouvertes ou fermées.
import {initialiserToutesLesCartes} from "/composant/fonction/openclose.js";

// Récupère les informations de la prochaine épreuve depuis la page.
const laProchaineEpreuve = getData("laProchaineEpreuve");

// Convertit la date reçue en objet JavaScript afin de pouvoir la formater.
const dateObj = new Date(laProchaineEpreuve.date);

// Formate la date en français avec le jour de la semaine, le jour, le mois et l'année.
const dateFormatee = dateObj.toLocaleDateString('fr-FR', {
    weekday: 'long',
    day: 'numeric',
    month: 'long',
    year: 'numeric'
});

// Affiche la date formatée dans l'élément prévu pour la prochaine épreuve.
document.getElementById("dateEpreuve").innerText = dateFormatee;

// Initialise le comportement d'ouverture et de fermeture de toutes les cartes.
initialiserToutesLesCartes();

