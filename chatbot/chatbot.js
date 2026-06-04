document.addEventListener("DOMContentLoaded", function () {
    const chatToggleBtn = document.getElementById("chatToggleBtn");
    const chatWindow = document.getElementById("chatWindow");
    const closeChatBtn = document.getElementById("closeChatBtn");
    const chatSuggestions = document.getElementById("chatSuggestions");
    const chatInputForm = document.getElementById("chatInputForm");
    const chatMessageInput = document.getElementById("chatMessageInput");
    const chatMessages = document.getElementById("chatMessages");

    // 1. Toggle Open / Close Window View
    if (chatToggleBtn && chatWindow) {
        chatToggleBtn.addEventListener("click", function() {
            if (chatWindow.style.display === "none" || chatWindow.style.display === "") {
                chatWindow.style.display = "flex";
                chatToggleBtn.style.transform = "scale(0)";
                loadFaqSuggestions(); // Fetch popular questions upon open
            }
        });
    }

    if (closeChatBtn && chatWindow && chatToggleBtn) {
        closeChatBtn.addEventListener("click", function() {
            chatWindow.style.display = "none";
            chatToggleBtn.style.transform = "scale(1)";
        });
    }

    // 2. Fetch and render suggestion chips from handler.php
    function loadFaqSuggestions() {
    if (!chatSuggestions) return;
    
    fetch("chatbot/handler.php?action=get_suggestions")
    .then(response => response.json())
    .then(res => {
        chatSuggestions.innerHTML = ""; // Clear loader text
        
        if (res.status === "success" && res.data && res.data.length > 0) {
            res.data.forEach(item => {
                // Double check to make sure the data object property exists
                if (!item.question) return;

                const chip = document.createElement("button");
                chip.innerText = item.question; // Core text mapping
                
                // Styling properties
                chip.style.cssText = "background-color: #fff; border: 1px solid #fbc02d; color: #111; padding: 8px 14px; border-radius: 20px; font-size: 12px; font-weight: 500; cursor: pointer; text-align: left; transition: all 0.2s; margin-bottom: 6px; width: 100%; box-shadow: 0 1px 3px rgba(0,0,0,0.05); font-family: inherit;";
                
                // Hover interactive visual states
                chip.onmouseover = () => {
                    chip.style.backgroundColor = "#fbc02d";
                    chip.style.color = "#111";
                };
                chip.onmouseout = () => {
                    chip.style.backgroundColor = "#fff";
                    chip.style.color = "#111";
                };
                
                // Click interaction action listener
                chip.addEventListener("click", () => {
                    appendMessage(item.question, "user");
                    sendMessageToBackend(item.question);
                });
                
                chatSuggestions.appendChild(chip);
            });
        } else {
            chatSuggestions.innerHTML = "<div style='font-size:11px; color:#999;'>Gunakan kotak input di bawah untuk bertanya.</div>";
        }
    })
    .catch(err => {
        console.error("Suggestions display engine breakdown:", err);
        chatSuggestions.innerHTML = "";
    });
}


    // 3. Helper to append dialog message nodes into scroll log view
    function appendMessage(text, sender) {
        if (!chatMessages) return;
        const msgDiv = document.createElement("div");
        msgDiv.innerText = text;
        
        if (sender === "user") {
            msgDiv.style.cssText = "align-self: flex-end; max-width: 80%; background-color: #fbc02d; color: #111; padding: 10px 14px; border-radius: 12px 12px 4px 12px; font-size: 13px; font-weight: 500; box-shadow: 0 1px 3px rgba(0,0,0,0.05);";
        } else {
            msgDiv.style.cssText = "align-self: flex-start; max-width: 80%; background-color: #fff; color: #333; padding: 10px 14px; border-radius: 4px 12px 12px 12px; font-size: 13px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); line-height: 1.4;";
        }
        
        chatMessages.appendChild(msgDiv);
        chatMessages.scrollTop = chatMessages.scrollHeight; // Auto-scroll to bottom
    }

    // 4. Submit typed text query down to backend engine
    if (chatInputForm) {
        chatInputForm.addEventListener("submit", function (e) {
            e.preventDefault();
            const userMsg = chatMessageInput.value.trim();
            if (!userMsg) return;

            appendMessage(userMsg, "user");
            chatMessageInput.value = ""; // Reset input
            
            sendMessageToBackend(userMsg);
        });
    }

    function sendMessageToBackend(msgText) {
        if (chatSuggestions) chatSuggestions.innerHTML = ""; // Clear shortcuts during standard messaging

        const formData = new FormData();
        formData.append("message", msgText);

        fetch("chatbot/handler.php", {
            method: "POST",
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === "success") {
                appendMessage(data.answer, "bot");
            } else {
                appendMessage(data.message, "bot");
            }
        })
        .catch(err => {
            console.error("Chat backend response error:", err);
            appendMessage("Terdapat masalah sambungan dengan pelayan. Sila cuba seketika lagi.", "bot");
        });
    }
});