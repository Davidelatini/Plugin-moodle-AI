<?php
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
 * AI Chatbot block main class.
 *
 * @package    block_aichatbot
 * @copyright  2024 AI Chatbot Plugin
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * AI Chatbot block class.
 */
class block_aichatbot extends block_base {

    /**
     * Initialise the block.
     */
    public function init() {
        $this->title = get_string('pluginname', 'block_aichatbot');
    }

    /**
     * Return the content of this block.
     *
     * @return stdClass The block content.
     */
    public function get_content() {
        global $USER, $OUTPUT, $PAGE;

        if ($this->content !== null) {
            return $this->content;
        }

        $this->content = new stdClass();
        $this->content->footer = '';

        // Check if user is logged in.
        if (!isloggedin() || isguestuser()) {
            $this->content->text = get_string('logintochat', 'block_aichatbot');
            return $this->content;
        }

        // Check plugin is configured.
        $apiurl = get_config('block_aichatbot', 'apiurl');
        if (empty($apiurl)) {
            if (has_capability('moodle/site:config', context_system::instance())) {
                $this->content->text = get_string('notconfigured', 'block_aichatbot');
            } else {
                $this->content->text = get_string('unavailable', 'block_aichatbot');
            }
            return $this->content;
        }

        // Load AMD module.
        $PAGE->requires->js_call_amd('block_aichatbot/chatbot', 'init', [
            [
                'contextid'   => $this->context->id,
                'userid'      => $USER->id,
                'username'    => fullname($USER),
                'sesskey'     => sesskey(),
                'botname'     => get_config('block_aichatbot', 'botname') ?: get_string('defaultbotname', 'block_aichatbot'),
                'placeholder' => get_string('inputplaceholder', 'block_aichatbot'),
            ]
        ]);

        $this->content->text = $this->render_chatbot_html();

        return $this->content;
    }

    /**
     * Render the chatbot HTML interface.
     *
     * @return string HTML output.
     */
    private function render_chatbot_html(): string {
        $botname = get_config('block_aichatbot', 'botname') ?: get_string('defaultbotname', 'block_aichatbot');
        $welcomemsg = get_config('block_aichatbot', 'welcomemessage') ?: get_string('defaultwelcome', 'block_aichatbot');

        $html  = '<div id="aichatbot-container" class="aichatbot-container" role="region" aria-label="' . s($botname) . '">';
        $html .= '  <div class="aichatbot-header">';
        $html .= '    <span class="aichatbot-status-dot" aria-hidden="true"></span>';
        $html .= '    <span class="aichatbot-bot-name">' . s($botname) . '</span>';
        $html .= '    <button class="aichatbot-clear-btn" id="aichatbot-clear" title="' . get_string('clearchat', 'block_aichatbot') . '" aria-label="' . get_string('clearchat', 'block_aichatbot') . '">';
        $html .= '      &#x1F5D1;';
        $html .= '    </button>';
        $html .= '  </div>';
        $html .= '  <div class="aichatbot-messages" id="aichatbot-messages" role="log" aria-live="polite" aria-relevant="additions">';
        $html .= '    <div class="aichatbot-message aichatbot-message-bot">';
        $html .= '      <div class="aichatbot-bubble">' . format_text($welcomemsg, FORMAT_PLAIN) . '</div>';
        $html .= '    </div>';
        $html .= '  </div>';
        $html .= '  <div class="aichatbot-input-area">';
        $html .= '    <textarea id="aichatbot-input" class="aichatbot-input"';
        $html .= '      placeholder="' . get_string('inputplaceholder', 'block_aichatbot') . '"';
        $html .= '      rows="1" aria-label="' . get_string('inputplaceholder', 'block_aichatbot') . '"';
        $html .= '      maxlength="2000"></textarea>';
        $html .= '    <button id="aichatbot-send" class="aichatbot-send-btn" aria-label="' . get_string('send', 'block_aichatbot') . '">';
        $html .= '      <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">';
        $html .= '        <path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/>';
        $html .= '      </svg>';
        $html .= '    </button>';
        $html .= '  </div>';
        $html .= '</div>';

        return $html;
    }

    /**
     * Allow multiple instances of this block.
     *
     * @return bool True if multiple instances are allowed.
     */
    public function instance_allow_multiple() {
        return false;
    }

    /**
     * This block has global configuration.
     *
     * @return bool True if global config exists.
     */
    public function has_config() {
        return true;
    }

    /**
     * Which page formats this block may appear on.
     *
     * @return array Array of page formats.
     */
    public function applicable_formats() {
        return [
            'all'           => true,
            'tag'           => false,
            'mod-quiz-view' => false,
        ];
    }
}
