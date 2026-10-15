"use strict";

const storageKey = "ppe.forum.admin.questions";
const initialQuestions = [
    {
        id: 1,
        author: "LucasR",
        date: "30/09/2026 à 14:23",
        title: "Recherche partenaire pour le 10km",
        body: "Salut à tous, je cherche un partenaire pour le 10km de la semaine prochaine. Quelqu'un est motivé ? Niveau peu importe, l'important c'est de partager la course !",
        replies: 3,
        archived: false,
        satisfied: false
    },
    {
        id: 2,
        author: "Mathilde_80",
        date: "29/09/2026 à 18:47",
        title: "Coéquipier pour les entraînements du mardi soir",
        body: "Bonjour à tous, je cherche un coéquipier pour les entraînements du mardi soir. Je suis motivée et ouverte à tous les niveaux !",
        replies: 5,
        archived: false,
        satisfied: false
    },
    {
        id: 3,
        author: "ThomasC",
        date: "28/09/2026 à 20:15",
        title: "Conseil sur les chaussures de running",
        body: "Salut, vous auriez des conseils sur des chaussures adaptées pour l'asphalte et les sorties longues ? Je cours surtout 10 et 15 km.",
        replies: 7,
        archived: false,
        satisfied: false
    },
    {
        id: 4,
        author: "Sarah_19",
        date: "27/09/2026 à 12:32",
        title: "Sortie dimanche matin",
        body: "Je propose une sortie collective dimanche matin à 9h au parc St-Pierre. Départ depuis le club, tout le monde est le bienvenu !",
        replies: 4,
        archived: false,
        satisfied: false
    },
    {
        id: 5,
        author: "AlexFit",
        date: "26/09/2026 à 16:10",
        title: "Plan d'entraînement marathon",
        body: "Bonjour, est-ce que quelqu'un aurait un plan d'entraînement pour préparer un marathon ? Merci d'avance pour vos retours !",
        replies: 2,
        archived: false,
        satisfied: false
    },
    {
        id: 6,
        author: "JulieA",
        date: "25/09/2026 à 09:18",
        title: "Merci pour vos conseils de récupération",
        body: "Merci pour vos réponses, elles m'ont beaucoup aidée à organiser ma récupération après les sorties longues.",
        replies: 6,
        archived: true,
        satisfied: true,
        archiveReason: "satisfaction",
        archivedAt: "25/09/2026"
    },
    {
        id: 7,
        author: "Nico80",
        date: "24/09/2026 à 19:05",
        title: "Quel parcours pour une sortie de 12 km ?",
        body: "Je cherchais un parcours tranquille autour d'Amiens. Merci à tous pour les idées partagées !",
        replies: 4,
        archived: true,
        satisfied: false,
        archiveReason: "admin",
        archivedAt: "25/09/2026"
    },
    {
        id: 8,
        author: "CamilleP",
        date: "23/09/2026 à 11:42",
        title: "Débuter le fractionné",
        body: "J'ai reçu plusieurs conseils pour ma première séance de fractionné, merci pour votre aide.",
        replies: 8,
        archived: true,
        satisfied: true,
        archiveReason: "satisfaction",
        archivedAt: "24/09/2026"
    },
    {
        id: 9,
        author: "MarcD",
        date: "22/09/2026 à 17:26",
        title: "Covoiturage pour la course de dimanche",
        body: "La question est réglée, merci aux membres qui ont proposé une place en voiture.",
        replies: 2,
        archived: true,
        satisfied: false,
        archiveReason: "admin",
        archivedAt: "23/09/2026"
    }
];

const openQuestions = document.getElementById("openQuestions");
const archivedQuestions = document.getElementById("archivedQuestions");
const openEmpty = document.getElementById("openEmpty");
const historyEmpty = document.getElementById("historyEmpty");
const historyFilter = document.getElementById("historyFilter");
const historySearch = document.getElementById("historySearch");
const toast = document.getElementById("forumToast");
let toastTimer;
let questions = loadQuestions();

function loadQuestions() {
    const savedQuestions = localStorage.getItem(storageKey);
    const loadedQuestions = savedQuestions === null
        ? initialQuestions.map(question => ({ ...question }))
        : JSON.parse(savedQuestions);

    for (const question of loadedQuestions) {
        if (question.satisfied && !question.archived) {
            question.archived = true;
            question.archiveReason = "satisfaction";
            question.archivedAt = new Date().toLocaleDateString("fr-FR");
        }
    }
    return loadedQuestions;
}

function saveQuestions() {
    localStorage.setItem(storageKey, JSON.stringify(questions));
}

function element(tagName, className, text) {
    const node = document.createElement(tagName);
    if (className) {
        node.className = className;
    }
    if (text !== undefined) {
        node.textContent = text;
    }
    return node;
}

function createQuestionCard(question, archived) {
    const card = element("article", `forum-question${archived ? " forum-question--history" : ""}`);
    const avatar = element("span", "forum-avatar", archived ? "☏" : question.author.slice(0, 1).toUpperCase());
    avatar.setAttribute("aria-hidden", "true");

    const content = element("div", "forum-question__content");
    const meta = element("div", "forum-question__meta");
    meta.append(
        element("span", "forum-question__author", question.author),
        element("span", "forum-question__date", `Posté le ${question.date}`)
    );

    const title = element("h3", "forum-question__title", question.title);
    const body = element("p", "forum-question__body", question.body);
    const footer = element("div", "forum-question__footer");
    footer.append(element("span", "forum-question__replies", `▣ ${question.replies} réponse${question.replies > 1 ? "s" : ""}`));

    if (archived) {
        const reason = question.archiveReason === "satisfaction"
            ? "Réponse satisfaisante"
            : "Archivée par l'administration";
        footer.append(element("span", "forum-badge", `✓ ${reason}`));
        footer.append(element("span", "", `Archivée le ${question.archivedAt || "—"}`));
    }

    content.append(meta, title, body, footer);

    const action = element(
        "button",
        `forum-question__action${archived ? " forum-question__action--restore" : ""}`,
        archived ? "↶ Restaurer" : "▣ Archiver"
    );
    action.type = "button";
    action.dataset.questionId = String(question.id);
    action.dataset.action = archived ? "restore" : "archive";
    action.setAttribute("aria-label", `${archived ? "Restaurer" : "Archiver"} la question : ${question.title}`);

    card.append(avatar, content, action);
    return card;
}

function render() {
    const active = questions.filter(question => !question.archived);
    const archived = questions.filter(question => question.archived);
    const search = historySearch.value.trim().toLocaleLowerCase("fr");
    const filter = historyFilter.value;
    const visibleArchived = archived.filter(question => {
        const matchesFilter = filter === "all" || question.archiveReason === filter;
        const searchableText = `${question.author} ${question.title} ${question.body}`.toLocaleLowerCase("fr");
        return matchesFilter && searchableText.includes(search);
    });

    openQuestions.replaceChildren(...active.map(question => createQuestionCard(question, false)));
    archivedQuestions.replaceChildren(...visibleArchived.map(question => createQuestionCard(question, true)));
    openEmpty.hidden = active.length > 0;
    historyEmpty.hidden = visibleArchived.length > 0;
    document.getElementById("openCount").textContent = String(active.length);
    document.getElementById("archivedCount").textContent = String(archived.length);
    document.getElementById("openPanelCount").textContent = String(active.length);
}

function showToast(message) {
    toast.textContent = message;
    toast.classList.add("is-visible");
    window.clearTimeout(toastTimer);
    toastTimer = window.setTimeout(() => toast.classList.remove("is-visible"), 2800);
}

function archiveQuestion(question, reason) {
    question.archived = true;
    question.satisfied = reason === "satisfaction";
    question.archiveReason = reason;
    question.archivedAt = new Date().toLocaleDateString("fr-FR");
}

document.addEventListener("click", event => {
    const button = event.target.closest("button[data-question-id]");
    if (!button) {
        return;
    }

    const question = questions.find(item => String(item.id) === button.dataset.questionId);
    if (!question) {
        return;
    }

    if (button.dataset.action === "archive") {
        archiveQuestion(question, "admin");
        showToast("La discussion a été archivée.");
    } else {
        question.archived = false;
        question.satisfied = false;
        question.archiveReason = null;
        question.archivedAt = null;
        showToast("La discussion a été restaurée.");
    }
    saveQuestions();
    render();
});

historyFilter.addEventListener("change", render);
historySearch.addEventListener("input", render);

document.addEventListener("forum:reponse-satisfaite", event => {
    const { questionId } = event.detail || {};
    const question = questions.find(item => String(item.id) === String(questionId));
    if (!question) {
        return;
    }
    archiveQuestion(question, "satisfaction");
    saveQuestions();
    render();
    showToast("La réponse a été validée : la discussion est archivée automatiquement.");
});

saveQuestions();
render();
