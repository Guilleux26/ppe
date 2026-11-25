"use strict";

// -----------------------------------------------------------------------------------
// Import des fonctions nécessaires
// -----------------------------------------------------------------------------------

import {getData} from "/composant/fonction/page.js";
import {appelAjax} from "/composant/fonction/ajax.js";
import {confirmer, messageBox} from "/composant/fonction/afficher.js";

// -----------------------------------------------------------------------------------
// Déclaration des variables globales
// -----------------------------------------------------------------------------------

// Discussions et chemin des photos transmis par la page d'administration.
const discussions = getData("lesDiscussions");
const repertoirePhoto = getData("repertoirePhoto");

// Éléments HTML de la liste, des filtres, de la recherche et du message d'état vide.
const liste = document.getElementById("adminDiscussionList");
const filtre = document.getElementById("forumFilter");
const recherche = document.getElementById("forumSearch");
const vide = document.getElementById("adminDiscussionEmpty");

// -----------------------------------------------------------------------------------
// Procédures évènementielles
// -----------------------------------------------------------------------------------

// Délégation d'événement pour archiver, restaurer ou supprimer une discussion.
async function traiterActionDiscussion(event) {
    const button = event.target.closest("button[data-action]");
    if (!button) return;
    const {action, questionId} = button.dataset;

    if (action === "delete") {
        confirmer(
            () => executerActionDiscussion(action, questionId),
            "Supprimer définitivement cette discussion et toutes ses réponses ?"
        );
        return;
    }

    await executerActionDiscussion(action, questionId);
}

async function executerActionDiscussion(action, questionId) {
    const url = action === "delete"
        ? "/administration/forum/ajax/supprimer.php"
        : "/administration/forum/ajax/archiver.php";
    const reponse = await appelAjax({
        url,
        data: action === "delete"
            ? {idQuestion: questionId}
            : {idQuestion: questionId, archive: action === "archive" ? "1" : "0"}
    });
    if (reponse) {
        const typeMessage = action === "archive" ? "warning"
            : action === "restore" ? "info"
                : "success";
        const message = action === "delete"
            ? "La discussion et ses réponses ont été supprimées."
            : reponse.message;
        afficherMessagePuisRecharger(message, typeMessage);
    }
}

// -----------------------------------------------------------------------------------
// Fonctions de traitement
// -----------------------------------------------------------------------------------

// Affiche la confirmation et actualise la liste quand l'utilisateur ferme la boîte de dialogue.
function afficherMessagePuisRecharger(message, type) {
    messageBox(message, type);

    const observer = new MutationObserver(() => {
        if (!document.getElementById("avertir-modal")) {
            observer.disconnect();
            window.location.reload();
        }
    });
    observer.observe(document.body, {childList: true, subtree: true});
}

// Crée un élément et ajoute son texte sans l'interpréter comme du HTML.
function element(tag, className, text) {
    const node = document.createElement(tag);
    if (className) node.className = className;
    if (text !== undefined) node.textContent = text;
    return node;
}

// Construit une carte d'administration avec les réponses et les actions de modération.
function afficherDiscussion(discussion) {
    const card = element("article", "member-forum__discussion");
    const question = element("div", "member-forum__question");
    // Affiche une initiale par défaut, remplacée par la photo si elle est disponible.
    const avatar = element("span", "member-forum__avatar", discussion.auteur.slice(0, 1).toUpperCase());
    avatar.setAttribute("aria-hidden", "true");
    if (discussion.photoAuteur) {
        const photo = element("img", "member-forum__avatar-image");
        photo.src = `${repertoirePhoto}/${encodeURIComponent(discussion.photoAuteur)}`;
        photo.alt = "";
        photo.onerror = () => photo.remove();
        avatar.append(photo);
    }

    const content = element("div");
    const meta = element("div", "member-forum__meta");
    meta.append(
        element("span", "member-forum__author", discussion.auteur),
        element("span", "", `Le ${discussion.dateQuestion}`)
    );
    content.append(meta, element("h2", "member-forum__title", discussion.titre));

    // Affiche un aperçu de 180 caractères et permet de développer le texte complet.
    const longueurApercu = 180;
    const contenuQuestion = Array.from(discussion.question);
    const apercu = contenuQuestion.slice(0, longueurApercu).join("").trimEnd();
    const description = element("p", "member-forum__content",
        contenuQuestion.length > longueurApercu ? `${apercu}…` : apercu);
    content.append(description);
    if (contenuQuestion.length > longueurApercu) {
        const boutonDescription = element("button", "member-forum__more-toggle", "▸ Afficher plus");
        let estDeveloppee = false;
        boutonDescription.type = "button";
        boutonDescription.addEventListener("click", () => {
            estDeveloppee = !estDeveloppee;
            description.textContent = estDeveloppee
                ? `${apercu}…`
                : discussion.question;
            boutonDescription.textContent = estDeveloppee ? "▸ Afficher plus" : "▾ Afficher moins";
        });
        content.append(boutonDescription);
    }

    const statistiques = element("div", "member-forum__stats");
    const nbReponses = discussion.reponses.length;
    statistiques.append(element("span", "", `${nbReponses} réponse${nbReponses > 1 ? "s" : ""}`));
    content.append(statistiques);
    if (discussion.archive) {
        const badgeText = discussion.satisfaite ? "✓ Réponse satisfaisante" : "Discussion archivée";
        content.append(element("span", "member-forum__status", badgeText));
    }

    const actions = element("div", "member-forum__actions");
    const archiveButton = element("button", "member-forum__action",
        discussion.archive ? "Restaurer" : "Archiver");
    archiveButton.type = "button";
    archiveButton.dataset.action = discussion.archive ? "restore" : "archive";
    archiveButton.dataset.questionId = String(discussion.id);
    actions.append(archiveButton);

    const deleteButton = element("button", "member-forum__action member-forum__action--delete",
        "Supprimer");
    deleteButton.type = "button";
    deleteButton.dataset.action = "delete";
    deleteButton.dataset.questionId = String(discussion.id);
    actions.append(deleteButton);
    content.append(actions);
    question.append(avatar, content);
    card.append(question);

    if (discussion.reponses.length > 0) {
        const replies = element("div", "member-forum__replies");
        for (const reponse of discussion.reponses) {
            const reply = element("article", "member-forum__reply");
            const replyMeta = element("div", "member-forum__reply-meta");
            replyMeta.append(
                element("strong", "member-forum__author", reponse.auteur),
                element("span", "", `Le ${reponse.date}`)
            );
            reply.append(replyMeta, element("p", "member-forum__reply-text", reponse.contenu));
            replies.append(reply);
        }
        card.append(replies);
    }
    return card;
}

// Combine le filtre d'état et le texte recherché, puis met à jour la liste des discussions.
function afficherLesDiscussions() {
    const term = recherche.value.trim().toLocaleLowerCase("fr");
    const visibles = discussions.filter(discussion => {
        if (filtre.value === "open" && discussion.archive) return false;
        if (filtre.value === "archived" && !discussion.archive) return false;
        const texte = `${discussion.auteur} ${discussion.titre} ${discussion.question}`.toLocaleLowerCase("fr");
        return texte.includes(term);
    });
    liste.replaceChildren(...visibles.map(afficherDiscussion));
    vide.hidden = visibles.length > 0;
}

// -----------------------------------------------------------------------------------
// Programme principal
// -----------------------------------------------------------------------------------

// Branche les interactions de modération, les filtres et la recherche en direct.
function initialiserForumAdministration() {
    liste.addEventListener("click", traiterActionDiscussion);
    filtre.addEventListener("change", afficherLesDiscussions);
    recherche.addEventListener("input", afficherLesDiscussions);
    afficherLesDiscussions();
}

initialiserForumAdministration();
