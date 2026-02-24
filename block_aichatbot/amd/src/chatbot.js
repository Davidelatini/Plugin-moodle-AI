// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * AI Chatbot AMD module.
 *
 * @module     block_aichatbot/chatbot
 * @copyright  2024 AI Chatbot Plugin
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import Ajax from 'core/ajax';
import Notification from 'core/notification';

// ─── Helpers ─────────────────────────────────────────────────────────────────

/**
 * Generate a random alphanumeric session ID.
 * @returns {string}
 */
const generateSessionId = () => {
    const arr = new Uint8Array(24);
    window.crypto.getRandomValues(arr);
    return Array.from(arr, b => b.toString(16).padStart(2, '0')).join('');
};

/**
 * Escape HTML to prevent XSS.
 * @param {string} text
 * @returns {string}
 */
const escapeHtml = (text) => {
    const div = document.createElement('div');
    div.appendChild(document.createTextNode(text));
    return div.innerHTML;
};

/**
 * Basic markdown-like formatting (bold, italic, code blocks, inline code, links).
 * @param {string} text
 * @returns {string} HTML string
 */
const formatMessage = (text) => {
    // Escape HTML first.
    let html = escapeHtml(text);

    // Code blocks (``` ... ```).
    html = html.replace(/```([\s\S]*?)```/g, '<pre><code>$1</code></pre>');

    // Inline code.
    html = html.replace(/`([^`]+)`/g, '<code>$1</code>');

    // Bold (**text** or __text__).
    html = html.replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>');
    html = html.replace(/__(.+?)__/g, '<strong>$1</strong>');

    // Italic (*text* or _text_).
    html = html.replace(/\*(.+?)\*/g, '<em>$1</em>');
    html = html.replace(/_(.+?)_/g, '<em>$1</em>');

    // Newlines to <br>.
    html = html.replace(/\n/g, '<br>');

    return html;
};

// ─── Main Init ────────────────────────────────────────────────────────────────

/**
 * Initialise the chatbot block.
 *
 * @param {Object} config  Configuration passed from PHP.
 * @param {number} config.contextid  Block context ID.
 * @param {number} config.userid     Current user ID.
 * @param {string} config.username   Current user full name.
 * @param {string} config.sesskey    Moodle session key.
 * @param {string} config.botname    Chatbot display name.
 * @param {string} config.placeholder Input placeholder text.
 */
export const init = (config) => {
    const container  = document.getElementById('aichatbot-container');
    const messages   = document.getElementById('aichatbot-messages');
    const input      = document.getElementById('aichatbot-input');
    const sendBtn    = document.getElementById('aichatbot-send');
    const clearBtn   = document.getElementById('aichatbot-clear');

    if (!container || !messages || !input || !sendBtn) {
        return;
    }

    // Session ID persisted in sessionStorage so it survives page refresh within the tab.
    const storageKey = `aichatbot_session_${config.contextid}_${config.userid}`;
    let sessionId = sessionStorage.getItem(storageKey) || generateSessionId();
    sessionStorage.setItem(storageKey, sessionId);

    let isWaiting = false;

    // ── Render helpers ─────────────────────────────────────────────────────

    /**
     * Append a message bubble to the chat window.
     * @param {'user'|'assistant'} role
     * @param {string} text
     * @param {boolean} [isHtml=false] If true, insert text as raw HTML.
     * @returns {HTMLElement} The message element.
     */
    const appendMessage = (role, text, isHtml = false) => {
        const wrapper = document.createElement('div');
        wrapper.className = `aichatbot-message aichatbot-message-${role === 'user' ? 'user' : 'bot'}`;

        const bubble = document.createElement('div');
        bubble.className = 'aichatbot-bubble';

        if (isHtml) {
            bubble.innerHTML = text;
        } else {
            bubble.innerHTML = formatMessage(text);
        }

        wrapper.appendChild(bubble);
        messages.appendChild(wrapper);
        scrollToBottom();
        return wrapper;
    };

    /**
     * Show a "thinking" indicator while waiting for the API.
     * @returns {HTMLElement}
     */
    const showThinking = () => {
        const wrapper = document.createElement('div');
        wrapper.className = 'aichatbot-message aichatbot-message-bot aichatbot-thinking';
        wrapper.id = 'aichatbot-thinking';
        wrapper.innerHTML = `
            <div class="aichatbot-bubble aichatbot-bubble-thinking">
                <span class="aichatbot-dot"></span>
                <span class="aichatbot-dot"></span>
                <span class="aichatbot-dot"></span>
            </div>`;
        messages.appendChild(wrapper);
        scrollToBottom();
        return wrapper;
    };

    const removeThinking = () => {
        const el = document.getElementById('aichatbot-thinking');
        if (el) {
            el.remove();
        }
    };

    const scrollToBottom = () => {
        messages.scrollTop = messages.scrollHeight;
    };

    const setInputDisabled = (disabled) => {
        input.disabled = disabled;
        sendBtn.disabled = disabled;
        sendBtn.classList.toggle('aichatbot-send-btn--loading', disabled);
    };

    // ── Send message ───────────────────────────────────────────────────────

    const sendMessage = async() => {
        const text = input.value.trim();
        if (!text || isWaiting) {
            return;
        }

        isWaiting = true;
        setInputDisabled(true);
        input.value = '';
        input.style.height = 'auto';

        appendMessage('user', text);
        showThinking();

        try {
            const [result] = await Ajax.call([{
                methodname: 'block_aichatbot_send_message',
                args: {
                    contextid: config.contextid,
                    sessionid: sessionId,
                    message: text,
                },
            }]);

            removeThinking();

            if (result.success) {
                appendMessage('assistant', result.response);
            } else {
                appendMessage('assistant', `⚠️ ${escapeHtml(result.response)}`, true);
            }

        } catch (err) {
            removeThinking();
            Notification.exception(err);
            appendMessage('assistant', '⚠️ ' + escapeHtml(err.message || 'Unexpected error.'), true);
        } finally {
            isWaiting = false;
            setInputDisabled(false);
            input.focus();
        }
    };

    // ── Event listeners ────────────────────────────────────────────────────

    sendBtn.addEventListener('click', sendMessage);

    input.addEventListener('keydown', (e) => {
        // Send on Enter (without Shift for multiline support).
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            sendMessage();
        }
    });

    // Auto-resize textarea.
    input.addEventListener('input', () => {
        input.style.height = 'auto';
        input.style.height = Math.min(input.scrollHeight, 120) + 'px';
    });

    // Clear conversation.
    if (clearBtn) {
        clearBtn.addEventListener('click', () => {
            // Re-generate session ID so history is reset.
            sessionId = generateSessionId();
            sessionStorage.setItem(storageKey, sessionId);

            // Remove all messages except the first (welcome).
            const allMessages = messages.querySelectorAll('.aichatbot-message');
            allMessages.forEach((msg, i) => {
                if (i > 0) {
                    msg.remove();
                }
            });

            input.focus();
        });
    }

    // Focus input on load.
    input.focus();
};
