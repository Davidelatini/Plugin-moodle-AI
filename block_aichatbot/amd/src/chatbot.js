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

define(['core/ajax', 'core/notification'], function(Ajax, Notification) {

    // ─── Helpers ─────────────────────────────────────────────────────────────

    /**
     * Generate a random hex session ID.
     * @returns {string}
     */
    var generateSessionId = function() {
        var arr = new Uint8Array(24);
        window.crypto.getRandomValues(arr);
        return Array.prototype.map.call(arr, function(b) {
            return b.toString(16).padStart(2, '0');
        }).join('');
    };

    /**
     * Escape HTML to prevent XSS.
     * @param {string} text
     * @returns {string}
     */
    var escapeHtml = function(text) {
        var div = document.createElement('div');
        div.appendChild(document.createTextNode(text));
        return div.innerHTML;
    };

    /**
     * Basic Markdown-like formatting.
     * @param {string} text
     * @returns {string}
     */
    var formatMessage = function(text) {
        var html = escapeHtml(text);

        // Code blocks.
        html = html.replace(/```([\s\S]*?)```/g, '<pre><code>$1</code></pre>');
        // Inline code.
        html = html.replace(/`([^`]+)`/g, '<code>$1</code>');
        // Bold.
        html = html.replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>');
        html = html.replace(/__(.+?)__/g, '<strong>$1</strong>');
        // Italic.
        html = html.replace(/\*(.+?)\*/g, '<em>$1</em>');
        html = html.replace(/_(.+?)_/g, '<em>$1</em>');
        // Newlines.
        html = html.replace(/\n/g, '<br>');

        return html;
    };

    // ─── Public API ───────────────────────────────────────────────────────────

    return {
        /**
         * Initialise the chatbot block.
         *
         * @param {Object} config
         * @param {number} config.contextid  Block context ID.
         * @param {number} config.userid     Current user ID.
         * @param {string} config.botname    Chatbot display name.
         */
        init: function(config) {
            var container = document.getElementById('aichatbot-container');
            var messages  = document.getElementById('aichatbot-messages');
            var input     = document.getElementById('aichatbot-input');
            var sendBtn   = document.getElementById('aichatbot-send');
            var clearBtn  = document.getElementById('aichatbot-clear');

            if (!container || !messages || !input || !sendBtn) {
                return;
            }

            // Persist session ID in sessionStorage.
            var storageKey = 'aichatbot_session_' + config.contextid + '_' + config.userid;
            var sessionId  = sessionStorage.getItem(storageKey) || generateSessionId();
            sessionStorage.setItem(storageKey, sessionId);

            var isWaiting = false;

            // ── Render helpers ──────────────────────────────────────────────

            var scrollToBottom = function() {
                messages.scrollTop = messages.scrollHeight;
            };

            var appendMessage = function(role, text, isHtml) {
                var wrapper = document.createElement('div');
                wrapper.className = 'aichatbot-message aichatbot-message-' + (role === 'user' ? 'user' : 'bot');

                var bubble = document.createElement('div');
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

            var showThinking = function() {
                var wrapper = document.createElement('div');
                wrapper.className = 'aichatbot-message aichatbot-message-bot aichatbot-thinking';
                wrapper.id = 'aichatbot-thinking';
                wrapper.innerHTML =
                    '<div class="aichatbot-bubble aichatbot-bubble-thinking">' +
                    '<span class="aichatbot-dot"></span>' +
                    '<span class="aichatbot-dot"></span>' +
                    '<span class="aichatbot-dot"></span>' +
                    '</div>';
                messages.appendChild(wrapper);
                scrollToBottom();
            };

            var removeThinking = function() {
                var el = document.getElementById('aichatbot-thinking');
                if (el) {
                    el.remove();
                }
            };

            var setInputDisabled = function(disabled) {
                input.disabled   = disabled;
                sendBtn.disabled = disabled;
                if (disabled) {
                    sendBtn.classList.add('aichatbot-send-btn--loading');
                } else {
                    sendBtn.classList.remove('aichatbot-send-btn--loading');
                }
            };

            // ── Send message ────────────────────────────────────────────────

            var sendMessage = function() {
                var text = input.value.trim();
                if (!text || isWaiting) {
                    return;
                }

                isWaiting = true;
                setInputDisabled(true);
                input.value = '';
                input.style.height = 'auto';

                appendMessage('user', text, false);
                showThinking();

                var calls = Ajax.call([{
                    methodname: 'block_aichatbot_send_message',
                    args: {
                        contextid: config.contextid,
                        sessionid: sessionId,
                        message: text
                    }
                }]);

                calls[0].then(function(result) {
                    removeThinking();
                    if (result.success) {
                        appendMessage('assistant', result.response, false);
                    } else {
                        appendMessage('assistant', '&#9888;&#65039; ' + escapeHtml(result.response), true);
                    }
                    return result;
                }).catch(function(err) {
                    removeThinking();
                    Notification.exception(err);
                    appendMessage('assistant', '&#9888;&#65039; ' + escapeHtml(err.message || 'Unexpected error.'), true);
                }).then(function() {
                    isWaiting = false;
                    setInputDisabled(false);
                    input.focus();
                    return;
                }).catch(function() {
                    isWaiting = false;
                    setInputDisabled(false);
                    input.focus();
                });
            };

            // ── Event listeners ─────────────────────────────────────────────

            sendBtn.addEventListener('click', sendMessage);

            input.addEventListener('keydown', function(e) {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    sendMessage();
                }
            });

            // Auto-resize textarea.
            input.addEventListener('input', function() {
                input.style.height = 'auto';
                input.style.height = Math.min(input.scrollHeight, 120) + 'px';
            });

            // Clear conversation.
            if (clearBtn) {
                clearBtn.addEventListener('click', function() {
                    sessionId = generateSessionId();
                    sessionStorage.setItem(storageKey, sessionId);

                    var allMessages = messages.querySelectorAll('.aichatbot-message');
                    allMessages.forEach(function(msg, i) {
                        if (i > 0) {
                            msg.remove();
                        }
                    });

                    input.focus();
                });
            }

            input.focus();
        }
    };
});
