// assets/js/chat.js
export default function initChatFeatures() {
  console.log("Initialisation des fonctionnalités du chat...");

  // S'assurer que les boutons existent avant d'attacher l'événement
  const chatOverlay = document.getElementById("chat-modal-overlay");
  const closeChatButton = document.getElementById("close-chat-modal");
  const openFullChatButton = document.getElementById("open-full-chat");

  // Variable pour stocker le nom d'utilisateur entre les sessions de chat
  if (!window.chatUserName) {
    window.chatUserName = localStorage.getItem("chat_user_name") || "";
  }

  // Handle all chat trigger buttons
  const chatTriggers = document.querySelectorAll(
    "#open-chat-modal, #contact-page-chat-btn, .chat-trigger"
  );

  chatTriggers.forEach((button) => {
    if (button) {
      console.log("Bouton de chat trouvé:", button.id || button.className);

      // Supprimer l'événement s'il existe déjà (pour éviter les doublons)
      button.removeEventListener("click", openChatHandler);

      // Attacher l'événement à nouveau
      button.addEventListener("click", openChatHandler);
    }
  });

  if (openFullChatButton) {
    openFullChatButton.removeEventListener("click", openFullChatHandler);
    openFullChatButton.addEventListener("click", openFullChatHandler);
  }

  if (closeChatButton) {
    closeChatButton.removeEventListener("click", closeChatHandler);
    closeChatButton.addEventListener("click", closeChatHandler);
  }

  if (chatOverlay) {
    chatOverlay.removeEventListener("click", overlayClickHandler);
    chatOverlay.addEventListener("click", overlayClickHandler);
  }
}

// Gestionnaires d'événements définis séparément
function openChatHandler() {
  document.getElementById("chat-modal-overlay").style.display = "block";
  document.getElementById("chat-modal-content").innerHTML =
    '<div class="text-center"><div class="spinner-border" role="status"></div><p>Chargement...</p></div>';

  fetch(window.chatSessionUrl, {
    headers: {
      "X-Requested-With": "XMLHttpRequest",
    },
  })
    .then((response) => {
      if (!response.ok) {
        throw new Error("Erreur réseau: " + response.status);
      }
      return response.text();
    })
    .then((html) => {
      const parser = new DOMParser();
      const doc = parser.parseFromString(html, "text/html");
      const chatContent = doc.querySelector(".chat-container");

      if (chatContent) {
        document.getElementById("chat-modal-content").innerHTML =
          chatContent.outerHTML;

        // Initialiser Mercure
        const roomId = chatContent.getAttribute("data-room-id");
        const mercureUrl = chatContent.getAttribute("data-mercure-url");
        if (window.initMercure && roomId && mercureUrl) {
          window.initMercure(roomId, mercureUrl);
        }

        // Gestionnaire de noms d'utilisateur pour le modal
        manageChatUsername();

        // Configurer le formulaire pour intercepter la soumission
        setupChatForm();
      } else {
        document.getElementById("chat-modal-content").innerHTML =
          "Impossible de charger le chat";
      }
    })
    .catch((error) => {
      console.error("Erreur de chargement du chat:", error);
      document.getElementById("chat-modal-content").innerHTML =
        "Erreur: Impossible de charger le chat";
    });
}

// Nouvelle fonction pour gérer le nom d'utilisateur
function manageChatUsername() {
  // Essaie d'abord le champ username par id, puis par name
  let usernameField = document.querySelector(
    "#chat-modal-content #chat-username"
  );
  if (!usernameField) {
    usernameField = document.querySelector(
      '#chat-modal-content input[name="username"]'
    );
  }

  if (usernameField) {
    // Si le champ est vide, essayer de remplir depuis localStorage
    if (usernameField.value === "") {
      const savedName = localStorage.getItem("chat_user_name");
      if (savedName) {
        usernameField.value = savedName;
      }
    }
    // Si le champ a une valeur (de la session serveur), la sauvegarder dans localStorage
    else if (usernameField.value) {
      localStorage.setItem("chat_user_name", usernameField.value);
      window.chatUserName = usernameField.value;

      // Si username est défini, rendre le champ readonly
      usernameField.readOnly = true;
      usernameField.classList.add("username-readonly");
    }
  }
}

// Dans la fonction setupChatForm() de chat.js
function setupChatForm() {
  const form = document.querySelector("#chat-modal-content form");
  if (form) {
    form.addEventListener("submit", function (e) {
      // Empêcher la soumission standard du formulaire
      e.preventDefault();

      // Récupérer les valeurs du formulaire
      const usernameField =
        this.querySelector("#chat-username") ||
        this.querySelector('input[name="username"]');
      const messageField =
        this.querySelector("#chat-message") ||
        this.querySelector('input[name="message"]');

      const username = usernameField ? usernameField.value : "";
      const message = messageField ? messageField.value : "";

      if (username && message) {
        // 1. AFFICHAGE IMMÉDIAT - Ajouter le message localement avant l'envoi
        const messagesContainer = document.querySelector(
          "#chat-modal-content .messages"
        );
        if (messagesContainer) {
          const now = new Date();
          const timestamp = now.toLocaleTimeString();

          const messageHTML = `
              <div class="message message-pending">
                <strong>${username}</strong>
                <span class="timestamp">${timestamp}</span>
                <p>${message}</p>
              </div>
            `;
          messagesContainer.insertAdjacentHTML("beforeend", messageHTML);

          // Auto-scroll
          messagesContainer.scrollTop = messagesContainer.scrollHeight;
        }

        // Stocker le nom d'utilisateur
        if (username) {
          localStorage.setItem("chat_user_name", username);

          // Rendre le champ username readonly après le premier message
          if (usernameField) {
            usernameField.readOnly = true;
            usernameField.classList.add("username-readonly");
          }
        }

        // 2. ENVOI AU SERVEUR
        const formData = new FormData(this);
        const action = this.getAttribute("action");

        fetch(action, {
          method: "POST",
          body: formData,
          headers: {
            "X-Requested-With": "XMLHttpRequest",
          },
        })
          .then((response) => {
            // Vider le champ message
            if (messageField) {
              messageField.value = "";
              messageField.focus();
            }

            // Si la requête échoue, marquer le message comme non envoyé
            if (!response.ok) {
              const pendingMessage = document.querySelector(".message-pending");
              if (pendingMessage) {
                pendingMessage.classList.add("message-error");
                pendingMessage.insertAdjacentHTML(
                  "beforeend",
                  '<div class="message-error-info">⚠️ Erreur d\'envoi</div>'
                );
              }
            } else {
              // Succès - retirer la classe "pending"
              const pendingMessage = document.querySelector(".message-pending");
              if (pendingMessage) {
                pendingMessage.classList.remove("message-pending");
              }
            }
          })
          .catch((error) => {
            console.error("Erreur d'envoi:", error);
            // Marquer le message comme non envoyé
            const pendingMessage = document.querySelector(".message-pending");
            if (pendingMessage) {
              pendingMessage.classList.add("message-error");
              pendingMessage.insertAdjacentHTML(
                "beforeend",
                '<div class="message-error-info">⚠️ Erreur réseau</div>'
              );
            }
          });
      }
    });
  }
}

// Fonction utilitaire pour réinitialiser le champ message
function resetMessageField(form) {
  // Essaie d'abord le champ message par id, puis par name
  let messageField = form.querySelector("#chat-message");
  if (!messageField) {
    messageField = form.querySelector('input[name="message"]');
  }

  if (messageField) {
    messageField.value = "";
    messageField.focus();
  }
}

function openFullChatHandler() {
  const chatContent = document
    .getElementById("chat-modal-content")
    .querySelector(".chat-container");
  const roomId = chatContent ? chatContent.getAttribute("data-room-id") : null;

  if (roomId) {
    if (window.isAdmin) {
      window.location.href = window.chatPrivateUrl.replace("/0", "/" + roomId);
    } else {
      window.location.href = window.chatSessionUrl;
    }
  }
}

function closeChatHandler() {
  document.getElementById("chat-modal-overlay").style.display = "none";
}

function overlayClickHandler(e) {
  if (e.target === this) {
    this.style.display = "none";
  }
}

// CSS pour le champ désactivé
const style = document.createElement("style");
style.textContent = `
    .username-readonly {
      background-color: #f5f5f5;
      cursor: not-allowed;
    }
  `;
document.head.appendChild(style);

// Export pour utilisation externe
export {
  openChatHandler,
  openFullChatHandler,
  closeChatHandler,
  overlayClickHandler,
  setupChatForm,
};
