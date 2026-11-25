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

// Données de forum transmises par la page PHP.
const discussions = getData("lesDiscussions");
const idMembre = Number(getData("idMembre"));
const estAdministrateur = getData("estAdministrateur");
const repertoirePhoto = getData("repertoirePhoto");

// Éléments HTML utilisés pour afficher la liste et les formulaires.
const liste = document.getElementById("discussionList");
const filtre = document.getElementById("questionFilter");
const vide = document.getElementById("discussionEmpty");
const formulaireQuestion = document.getElementById("questionForm");
const lienAdministration = document.getElementById("forumAdminLink");

// -----------------------------------------------------------------------------------
// Procédures évènementielles
// -----------------------------------------------------------------------------------

// Publie une nouvelle question puis recharge la page si le serveur confirme l'opération.
async function traiterPublicationQuestion(event) {
    event.preventDefault();
    const form = new FormData(formulaireQuestion);
    const reponse = await appelAjax({
        url: "/membre/forum/ajax/poserQuestion.php",
        data: {titre: form.get("titre"), contenu: form.get("contenu")}
    });
    if (reponse) afficherMessagePuisRecharger(reponse.message, "success");
}

// Délégation d'événement : traite la soumission des formulaires de réponse dans la liste.
async function traiterPublicationReponse(event) {
    const form = event.target.closest("form[data-question-id]");
    if (!form) return;
    event.preventDefault();
    const contenu = new FormData(form).get("contenu");
    const reponse = await appelAjax({
        url: "/membre/forum/ajax/repondre.php",
        data: {idQuestion: form.dataset.questionId, contenu}
    });
    if (reponse) afficherMessagePuisRecharger(reponse.message, "success");
}

// Traite la clôture par l'auteur et les actions de modération proposées dans la liste.
async function traiterActionDiscussion(event) {
    const button = event.target.closest("button[data-action]");
    if (!button) return;

    const action = button.dataset.action;
    const executeAction = async () => {
        // L'auteur clôt sa discussion comme satisfaite après confirmation.
        if (action === "satisfied") {
            const reponse = await appelAjax({
                url: "/membre/forum/ajax/marquerSatisfaite.php",
                data: {idQuestion: button.dataset.questionId}
            });
            if (reponse) afficherMessagePuisRecharger(reponse.message, "success");
            return;
        }

        // Les actions de modération utilisent les points d'accès d'administration.
        if (!["archive", "restore", "delete"].includes(action)) return;
        const reponse = await appelAjax({
            url: action === "delete"
                ? "/administration/forum/ajax/supprimer.php"
                : "/administration/forum/ajax/archiver.php",
            data: action === "delete"
                ? {idQuestion: button.dataset.questionId}
                : {idQuestion: button.dataset.questionId, archive: action === "archive" ? "1" : "0"}
        });
        if (reponse) {
            const typeMessage = action === "archive" ? "warning"
                : action === "restore" ? "info"
                    : "success";
            afficherMessagePuisRecharger(reponse.message, typeMessage);
        }
    };

    if (action === "satisfied") {
        confirmer(executeAction, "Confirmez-vous que la réponse vous satisfait ? La discussion sera archivée.");
        return;
    }
    if (action === "delete") {
        confirmer(executeAction, "Supprimer définitivement cette discussion et toutes ses réponses ?");
        return;
    }
    executeAction();
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

// Crée un élément et ajoute son texte avec textContent pour ne pas interpréter le contenu comme du HTML.
function element(tag, className, text) {
    const node = document.createElement(tag);
    if (className) node.className = className;
    if (text !== undefined) node.textContent = text;
    return node;
}

// Prépare un bouton d'action en y associant son type d'action et l'identifiant de la question.
function bouton(label, action, id) {
    const node = element("button", "member-forum__action", label);
    node.type = "button";
    node.dataset.action = action;
    node.dataset.questionId = String(id);
    return node;
}

// Construit une carte de discussion avec les informations, réponses et actions accessibles au membre.
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
    content.append(meta, element("h3", "member-forum__title", discussion.titre));

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
        content.append(element("span", "member-forum__status",
            discussion.satisfaite ? "✓ Réponse satisfaisante" : "Discussion archivée"));
    }

    // Les actions de modération ne sont rendues que pour les personnes autorisées.
    if (estAdministrateur) {
        const actions = element("div", "member-forum__actions");
        const toggle = bouton(
            discussion.archive ? "↶ Restaurer" : "▣ Archiver",
            discussion.archive ? "restore" : "archive",
            discussion.id
        );
        toggle.classList.add("member-forum__action--moderation");
        actions.append(toggle);
        const remove = bouton("Supprimer", "delete", discussion.id);
        remove.classList.add("member-forum__action--delete");
        actions.append(remove);
        content.append(actions);
    }

    // Seul l'auteur peut clôturer sa discussion, et seulement après une première réponse.
    if (!discussion.archive && discussion.idAuteur === idMembre) {
        const actions = element("div", "member-forum__actions");
        const close = bouton("✓ J'ai obtenu ma réponse", "satisfied", discussion.id);
        close.disabled = discussion.reponses.length === 0;
        if (close.disabled) {
            close.title = "Une réponse doit d'abord être publiée.";
        }
        actions.append(close);
        content.append(actions);
    }

    question.append(avatar, content);
    card.append(question);

    // Les réponses restent visibles sur une discussion clôturée ; seules les discussions ouvertes acceptent une réponse.
    if (discussion.reponses.length > 0 || !discussion.archive) {
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
        if (!discussion.archive) {
            const form = element("form", "member-forum__reply-form");
            form.dataset.questionId = String(discussion.id);
            const input = element("textarea");
            input.name = "contenu";
            input.rows = 2;
            input.minLength = 2;
            input.maxLength = 5000;
            input.placeholder = "Écrire une réponse…";
            input.required = true;
            const submit = element("button", "member-forum__button", "Répondre");
            submit.type = "submit";
            form.append(input, submit);
            replies.append(form);
        }
        card.append(replies);
    }
    return card;
}

// Applique le filtre sélectionné, reconstruit les cartes et affiche l'état vide si nécessaire.
function afficherLesDiscussions() {
    const visibles = discussions.filter(discussion => {
        if (filtre.value === "open") return !discussion.archive;
        if (filtre.value === "archived") return discussion.archive;
        return true;
    });
    liste.replaceChildren(...visibles.map(afficherDiscussion));
    vide.hidden = visibles.length > 0;
}

// -----------------------------------------------------------------------------------
// Programme principal
// -----------------------------------------------------------------------------------

// Configure l'affichage selon les droits du membre et branche les événements du forum.
function initialiserForum() {
    lienAdministration.hidden = !estAdministrateur;
    if (!estAdministrateur) {
        filtre.querySelector('option[value="archived"]').remove();
    }

    formulaireQuestion.addEventListener("submit", traiterPublicationQuestion);
    liste.addEventListener("submit", traiterPublicationReponse);
    liste.addEventListener("click", traiterActionDiscussion);
    filtre.addEventListener("change", afficherLesDiscussions);
    afficherLesDiscussions();
}

initialiserForum();
