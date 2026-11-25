"use strict";

// Import des fonctions permettant de lire les données de la page et d'envoyer des requêtes AJAX.
import {getData} from "/composant/fonction/page.js";
import {appelAjax} from "/composant/fonction/ajax.js";
import {confirmer} from "/composant/fonction/afficher.js";

// Récupère les discussions, l'identifiant du membre et les options d'affichage.
const discussions = getData("lesDiscussions");
const idMembre = Number(getData("idMembre"));
const estAdministrateur = getData("estAdministrateur");
const repertoirePhoto = getData("repertoirePhoto");

// Récupère les éléments HTML utilisés pour afficher le forum et ses actions.
const liste = document.getElementById("discussionList");
const filtre = document.getElementById("questionFilter");
const vide = document.getElementById("discussionEmpty");
const formulaireQuestion = document.getElementById("questionForm");
const lienAdministration = document.getElementById("forumAdminLink");

// Affiche le lien d'administration uniquement aux administrateurs.
lienAdministration.hidden = !estAdministrateur;

// Masque le filtre des discussions archivées aux membres ordinaires.
if (!estAdministrateur) {
    filtre.querySelector('option[value="archived"]').remove();
}

// Crée un élément HTML avec sa classe CSS et son contenu optionnel.
function element(tag, className, text) {
    const node = document.createElement(tag);
    if (className) node.className = className;
    if (text !== undefined) node.textContent = text;
    return node;
}

// Crée un bouton d'action relié à une discussion.
function bouton(label, action, id) {
    const node = element("button", "member-forum__action", label);
    node.type = "button";
    node.dataset.action = action;
    node.dataset.questionId = String(id);
    return node;
}

// Construit la carte complète d'une discussion avec son auteur, ses réponses et ses actions.
function afficherDiscussion(discussion) {
    const card = element("article", "member-forum__discussion");
    const question = element("div", "member-forum__question");

    // Affiche la première lettre du nom puis remplace éventuellement ce texte par la photo du membre.
    const avatar = element("span", "member-forum__avatar", discussion.auteur.slice(0, 1).toUpperCase());
    avatar.setAttribute("aria-hidden", "true");
    if (discussion.photoAuteur) {
        const photo = element("img", "member-forum__avatar-image");
        photo.src = `${repertoirePhoto}/${encodeURIComponent(discussion.photoAuteur)}`;
        photo.alt = "";
        photo.onerror = () => photo.remove();
        avatar.append(photo);
    }

    // Ajoute les informations principales de la discussion.
    const content = element("div");
    const meta = element("div", "member-forum__meta");
    meta.append(
        element("span", "member-forum__author", discussion.auteur),
        element("span", "", `Le ${discussion.dateQuestion}`)
    );
    content.append(meta, element("h3", "member-forum__title", discussion.titre));

    // Limite l'aperçu à 180 caractères et ajoute un bouton pour lire la suite.
    const longueurApercu = 180;
    const contenuQuestion = Array.from(discussion.question);
    const apercu = contenuQuestion.slice(0, longueurApercu).join("").trimEnd();
    content.append(element("p", "member-forum__content",
        contenuQuestion.length > longueurApercu ? `${apercu}…` : apercu));
    if (contenuQuestion.length > longueurApercu) {
        const suite = element("details", "member-forum__more");
        suite.append(
            element("summary", "", "Lire la suite"),
            element("p", "member-forum__content",
                contenuQuestion.slice(longueurApercu).join("").trimStart())
        );
        content.append(suite);
    }

    // Affiche le nombre de réponses publiées.
    const statistiques = element("div", "member-forum__stats");
    const nbReponses = discussion.reponses.length;
    statistiques.append(element("span", "", `${nbReponses} réponse${nbReponses > 1 ? "s" : ""}`));
    content.append(statistiques);

    // Indique si la discussion est archivée ou si une réponse a été validée.
    if (discussion.archive) {
        content.append(element("span", "member-forum__status",
            discussion.satisfaite ? "✓ Réponse satisfaisante" : "Discussion archivée"));
    }

    // Ajoute les actions réservées aux administrateurs.
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

    // Permet à l'auteur de valider une réponse lorsqu'au moins une réponse existe.
    if (!discussion.archive && discussion.idAuteur === idMembre) {
        const actions = element("div", "member-forum__actions");
        const close = bouton("✓ J'ai obtenu ma réponse", "satisfied", discussion.id);
        close.disabled = discussion.reponses.length === 0;
        if (close.disabled) close.title = "Une réponse doit d'abord être publiée.";
        actions.append(close);
        content.append(actions);
    }
    question.append(avatar, content);
    card.append(question);

    // Affiche les réponses existantes et le formulaire de réponse si la discussion est ouverte.
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

// Filtre les discussions selon le filtre sélectionné, puis met à jour la liste.
function afficherLesDiscussions() {
    const visibles = discussions.filter(discussion => {
        if (filtre.value === "open") return !discussion.archive;
        if (filtre.value === "archived") return discussion.archive;
        return true;
    });
    // Remplace toutes les cartes affichées par celles correspondant au filtre.
    liste.replaceChildren(...visibles.map(afficherDiscussion));
    // Affiche le message vide uniquement lorsqu'aucune discussion ne correspond.
    vide.hidden = visibles.length > 0;
}

// Envoie une nouvelle question au serveur lors de la soumission du formulaire principal.
formulaireQuestion.addEventListener("submit", async event => {
    event.preventDefault();
    const form = new FormData(formulaireQuestion);
    const reponse = await appelAjax({
        url: "/membre/forum/ajax/poserQuestion.php",
        data: {titre: form.get("titre"), contenu: form.get("contenu")}
    });
    if (reponse) window.location.reload();
});

// Envoie une réponse lorsqu'un formulaire de réponse contenu dans la liste est soumis.
liste.addEventListener("submit", async event => {
    const form = event.target.closest("form[data-question-id]");
    if (!form) return;
    event.preventDefault();
    const contenu = new FormData(form).get("contenu");
    const reponse = await appelAjax({
        url: "/membre/forum/ajax/repondre.php",
        data: {idQuestion: form.dataset.questionId, contenu}
    });
    if (reponse) window.location.reload();
});

// Gère les actions de validation, d'archivage, de restauration et de suppression.
liste.addEventListener("click", async event => {
    const button = event.target.closest("button[data-action]");
    if (!button) return;

    const action = button.dataset.action;
    const executeAction = async () => {
        if (action === "satisfied") {
            const reponse = await appelAjax({
                url: "/membre/forum/ajax/marquerSatisfaite.php",
                data: {idQuestion: button.dataset.questionId}
            });
            if (reponse) window.location.reload();
            return;
        }

        if (!["archive", "restore", "delete"].includes(action)) return;
        const reponse = await appelAjax({
            url: action === "delete"
                ? "/administration/forum/ajax/supprimer.php"
                : "/administration/forum/ajax/archiver.php",
            data: action === "delete"
                ? {idQuestion: button.dataset.questionId}
                : {idQuestion: button.dataset.questionId, archive: action === "archive" ? "1" : "0"}
        });
        if (reponse) window.location.reload();
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
});

// Met à jour la liste dès que le filtre change, puis affiche toutes les discussions au chargement.
filtre.addEventListener("change", afficherLesDiscussions);
afficherLesDiscussions();
