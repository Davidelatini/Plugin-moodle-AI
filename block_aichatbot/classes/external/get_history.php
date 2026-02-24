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
 * External function: get_history
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
use external_multiple_structure;
use external_value;
use context_block;

/**
 * External function to retrieve conversation history.
 */
class get_history extends external_api {

    /**
     * Parameter definitions.
     *
     * @return external_function_parameters
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'contextid' => new external_value(PARAM_INT,  'Block context ID'),
            'sessionid' => new external_value(PARAM_ALPHANUMEXT, 'Chat session ID'),
        ]);
    }

    /**
     * Execute the function.
     *
     * @param int    $contextid Block context ID.
     * @param string $sessionid Chat session ID.
     * @return array            List of message objects.
     */
    public static function execute(int $contextid, string $sessionid): array {
        global $DB, $USER;

        $params = self::validate_parameters(self::execute_parameters(), [
            'contextid' => $contextid,
            'sessionid' => $sessionid,
        ]);

        $context = context_block::instance($params['contextid']);
        self::validate_context($context);
        require_capability('block/aichatbot:chat', $context);

        $enablelogging = (bool) get_config('block_aichatbot', 'enablelogging');
        if (!$enablelogging) {
            return ['messages' => []];
        }

        $records = $DB->get_records_select(
            'block_aichatbot_log',
            'userid = :userid AND sessionid = :sessionid',
            ['userid' => $USER->id, 'sessionid' => $params['sessionid']],
            'timecreated ASC',
            'id, role, message, timecreated'
        );

        $messages = [];
        foreach ($records as $record) {
            $messages[] = [
                'id'          => $record->id,
                'role'        => $record->role,
                'message'     => $record->message,
                'timecreated' => $record->timecreated,
            ];
        }

        return ['messages' => $messages];
    }

    /**
     * Return value definitions.
     *
     * @return external_single_structure
     */
    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
            'messages' => new external_multiple_structure(
                new external_single_structure([
                    'id'          => new external_value(PARAM_INT,  'Record ID'),
                    'role'        => new external_value(PARAM_TEXT, 'user or assistant'),
                    'message'     => new external_value(PARAM_RAW,  'Message content'),
                    'timecreated' => new external_value(PARAM_INT,  'Timestamp'),
                ])
            ),
        ]);
    }
}
