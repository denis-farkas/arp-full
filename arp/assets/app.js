import { registerReactControllerComponents } from "@symfony/ux-react";

import "./bootstrap.js";
import "bootstrap/dist/css/bootstrap.min.css";
import "./styles/app.css";
// Import Bootstrap JavaScript
import "@popperjs/core";
import "bootstrap"; // Add this line
// assets/app.js (ajout à la fin du fichier existant)
import initChatFeatures from "./js/chat.js";

// Écoute les événements Turbo
document.addEventListener("turbo:load", function () {
  initChatFeatures();
});

// Fallback pour le premier chargement
document.addEventListener("DOMContentLoaded", function () {
  initChatFeatures();
});

// Variables globales pour les URLs
window.chatSessionUrl = "/chat/session";
window.chatPrivateUrl = "/chat/0";
/*
 * Welcome to your app's main JavaScript file!
 *
 * This file will be included onto the page via the importmap() Twig function,
 * which should already be in your base.html.twig.
 */

registerReactControllerComponents(
  require.context("./react/controllers", true, /\.(j|t)sx?$/)
);
