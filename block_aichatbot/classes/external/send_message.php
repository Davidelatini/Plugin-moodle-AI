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
 * External function: send_message
 *
 * @package    block_aichatbot
 * @copyright  2024 AI Chatbot Plugin
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_aichatbot\external;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/externallib.php');

use external_api;
use external_function_parameters;
use external_single_structure;
use external_value;
use context_block;

/**
 * External function to send a message to the AI and get a response.
 */
class send_message extends external_api {

    /**
     * Parameter definitions.
     *
     * @return external_function_parameters
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'contextid' => new external_value(PARAM_INT,  'Block context ID'),
            'sessionid' => new external_value(PARAM_ALPHANUMEXT, 'Chat session ID (max 64 chars)'),
            'message'   => new external_value(PARAM_TEXT, 'User message (max 2000 chars)'),
        ]);
    }

    /**
     * Execute the function.
     *
     * @param int    $contextid Block context ID.
     * @param string $sessionid Chat session ID.
     * @param string $message   User message.
     * @return array            Response from the AI.
     */
    public static function execute(int $contextid, string $sessionid, string $message): array {
        global $DB, $USER;

        // Validate parameters.
        $params = self::validate_parameters(self::execute_parameters(), [
            'contextid' => $contextid,
            'sessionid' => $sessionid,
            'message'   => $message,
        ]);

        // Validate context and capabilities.
        $context = context_block::instance($params['contextid']);
        self::validate_context($context);
        require_capability('block/aichatbot:chat', $context);

        // Trim and validate message length.
        $usermessage = trim($params['message']);
        if (empty($usermessage)) {
            return ['success' => false, 'response' => get_string('emptymessage', 'block_aichatbot'), 'sessionid' => $params['sessionid']];
        }
        if (core_text::strlen($usermessage) > 2000) {
            $usermessage = core_text::substr($usermessage, 0, 2000);
        }

        // Rate limit check.
        $maxperhour = (int) get_config('block_aichatbot', 'maxmessagesperhour');
        if ($maxperhour > 0) {
            $hourslot = (int) (time() / 3600) * 3600;
            $rl = $DB->get_record('block_aichatbot_ratelimit', ['userid' => $USER->id, 'hour_slot' => $hourslot]);
            if ($rl && $rl->msgcount >= $maxperhour) {
                return ['success' => false, 'response' => get_string('ratelimitreached', 'block_aichatbot'), 'sessionid' => $params['sessionid']];
            }
        }

        // Build conversation history for context (last 10 exchanges).
        $history = self::build_history($params['sessionid'], $USER->id);

        // Call the AI API.
        $airesponse = self::call_ai_api($history, $usermessage);

        if ($airesponse['success']) {
            // Persist log.
            $enablelogging = (bool) get_config('block_aichatbot', 'enablelogging');
            if ($enablelogging) {
                $now = time();
                $DB->insert_record('block_aichatbot_log', (object) [
                    'userid'      => $USER->id,
                    'contextid'   => $params['contextid'],
                    'sessionid'   => $params['sessionid'],
                    'role'        => 'user',
                    'message'     => $usermessage,
                    'timecreated' => $now,
                ]);
                $DB->insert_record('block_aichatbot_log', (object) [
                    'userid'      => $USER->id,
                    'contextid'   => $params['contextid'],
                    'sessionid'   => $params['sessionid'],
                    'role'        => 'assistant',
                    'message'     => $airesponse['response'],
                    'timecreated' => $now + 1,
                ]);
            }

            // Update rate limit counter.
            if ($maxperhour > 0) {
                $hourslot = (int) (time() / 3600) * 3600;
                $rl = $DB->get_record('block_aichatbot_ratelimit', ['userid' => $USER->id, 'hour_slot' => $hourslot]);
                if ($rl) {
                    $DB->set_field('block_aichatbot_ratelimit', 'msgcount', $rl->msgcount + 1, ['id' => $rl->id]);
                } else {
                    $DB->insert_record('block_aichatbot_ratelimit', (object) [
                        'userid'    => $USER->id,
                        'hour_slot' => $hourslot,
                        'msgcount'  => 1,
                    ]);
                }
            }
        }

        return [
            'success'   => $airesponse['success'],
            'response'  => $airesponse['response'],
            'sessionid' => $params['sessionid'],
        ];
    }

    /**
     * Build conversation history for the current session.
     *
     * @param string $sessionid
     * @param int    $userid
     * @return array Array of ['role' => ..., 'content' => ...] messages.
     */
    private static function build_history(string $sessionid, int $userid): array {
        global $DB;

        $enablelogging = (bool) get_config('block_aichatbot', 'enablelogging');
        if (!$enablelogging) {
            return [];
        }

        $records = $DB->get_records_select(
            'block_aichatbot_log',
            'userid = :userid AND sessionid = :sessionid',
            ['userid' => $userid, 'sessionid' => $sessionid],
            'timecreated ASC',
            'id, role, message',   // 'id' is required so Moodle can key the result array
            0, 20  // last 20 rows = ~10 exchanges
        );

        $history = [];
        foreach ($records as $record) {
            $history[] = ['role' => $record->role, 'content' => $record->message];
        }
        return $history;
    }

    /**
     * Call the configured AI API.
     *
     * @param array  $history     Prior conversation messages.
     * @param string $usermessage The new user message.
     * @return array ['success' => bool, 'response' => string]
     */
    private static function call_ai_api(array $history, string $usermessage): array {
        $apiurl      = get_config('block_aichatbot', 'apiurl');
        $apikey      = get_config('block_aichatbot', 'apikey');
        $model       = get_config('block_aichatbot', 'model') ?: 'gpt-4o-mini';
        $maxtokens   = (int) (get_config('block_aichatbot', 'maxtokens') ?: 1024);
        $temperature = (float) (get_config('block_aichatbot', 'temperature') ?: 0.7);
        $systemprompt = get_config('block_aichatbot', 'systemprompt') ?: get_string('defaultsystemprompt', 'block_aichatbot');

        if (empty($apiurl) || empty($apikey)) {
            return ['success' => false, 'response' => get_string('notconfigured', 'block_aichatbot')];
        }

        // Build messages array (OpenAI Chat Completions format).
        $messages = [['role' => 'system', 'content' => $systemprompt]];
        foreach ($history as $h) {
            $messages[] = ['role' => $h['role'], 'content' => $h['content']];
        }
        $messages[] = ['role' => 'user', 'content' => $usermessage];

        $payload = json_encode([
            'model'       => $model,
            'messages'    => $messages,
            'max_tokens'  => $maxtokens,
            'temperature' => $temperature,
        ]);

        // Use Moodle's curl wrapper for proper proxy/SSL support.
        $curl = new \curl();
        $curl->setHeader([
            'Content-Type: application/json',
            'Authorization: Bearer ' . $apikey,
        ]);
        $curl->setopt([
            'CURLOPT_TIMEOUT'        => 30,
            'CURLOPT_RETURNTRANSFER' => true,
        ]);

        $rawresponse = $curl->post($apiurl, $payload);
        $httpcode    = $curl->get_info()['http_code'] ?? 0;

        if ($curl->errno !== 0 || $httpcode < 200 || $httpcode >= 300) {
            debugging('AI Chatbot API error. HTTP ' . $httpcode . ': ' . $curl->error, DEBUG_DEVELOPER);
            return ['success' => false, 'response' => get_string('erroroccurred', 'block_aichatbot')];
        }

        $data = json_decode($rawresponse, true);

        // Extract the assistant reply (OpenAI-compatible response format).
        $reply = $data['choices'][0]['message']['content'] ?? null;
        if ($reply === null) {
            debugging('AI Chatbot: unexpected API response: ' . $rawresponse, DEBUG_DEVELOPER);
            return ['success' => false, 'response' => get_string('erroroccurred', 'block_aichatbot')];
        }

        return ['success' => true, 'response' => trim($reply)];
    }

    /**
     * Return value definitions.
     *
     * @return external_single_structure
     */
    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
            'success'   => new external_value(PARAM_BOOL, 'Whether the call succeeded'),
            'response'  => new external_value(PARAM_RAW,  'AI response or error message'),
            'sessionid' => new external_value(PARAM_ALPHANUMEXT, 'Chat session ID'),
        ]);
    }
}
