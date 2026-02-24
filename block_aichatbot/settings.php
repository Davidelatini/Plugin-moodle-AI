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
 * AI Chatbot block settings.
 *
 * @package    block_aichatbot
 * @copyright  2024 AI Chatbot Plugin
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($ADMIN->fulltree) {

    // ── API Settings ─────────────────────────────────────────────────────────
    $settings->add(new admin_setting_heading(
        'block_aichatbot/apisettings',
        get_string('apisettings', 'block_aichatbot'),
        get_string('apisettings_desc', 'block_aichatbot')
    ));

    // API provider selector.
    $providers = [
        'openai'  => get_string('provider_openai', 'block_aichatbot'),
        'custom'  => get_string('provider_custom', 'block_aichatbot'),
    ];
    $settings->add(new admin_setting_configselect(
        'block_aichatbot/provider',
        get_string('provider', 'block_aichatbot'),
        get_string('provider_desc', 'block_aichatbot'),
        'openai',
        $providers
    ));

    // API URL.
    $settings->add(new admin_setting_configtext(
        'block_aichatbot/apiurl',
        get_string('apiurl', 'block_aichatbot'),
        get_string('apiurl_desc', 'block_aichatbot'),
        'https://api.openai.com/v1/chat/completions',
        PARAM_URL
    ));

    // API Key (stored encrypted).
    $settings->add(new admin_setting_configpasswordunmask(
        'block_aichatbot/apikey',
        get_string('apikey', 'block_aichatbot'),
        get_string('apikey_desc', 'block_aichatbot'),
        ''
    ));

    // Model name.
    $settings->add(new admin_setting_configtext(
        'block_aichatbot/model',
        get_string('model', 'block_aichatbot'),
        get_string('model_desc', 'block_aichatbot'),
        'gpt-4o-mini',
        PARAM_TEXT
    ));

    // Max tokens.
    $settings->add(new admin_setting_configtext(
        'block_aichatbot/maxtokens',
        get_string('maxtokens', 'block_aichatbot'),
        get_string('maxtokens_desc', 'block_aichatbot'),
        '1024',
        PARAM_INT
    ));

    // Temperature.
    $settings->add(new admin_setting_configtext(
        'block_aichatbot/temperature',
        get_string('temperature', 'block_aichatbot'),
        get_string('temperature_desc', 'block_aichatbot'),
        '0.7',
        PARAM_FLOAT
    ));

    // System prompt.
    $settings->add(new admin_setting_configtextarea(
        'block_aichatbot/systemprompt',
        get_string('systemprompt', 'block_aichatbot'),
        get_string('systemprompt_desc', 'block_aichatbot'),
        get_string('defaultsystemprompt', 'block_aichatbot'),
        PARAM_TEXT
    ));

    // ── Chatbot Appearance ────────────────────────────────────────────────────
    $settings->add(new admin_setting_heading(
        'block_aichatbot/appearancesettings',
        get_string('appearancesettings', 'block_aichatbot'),
        ''
    ));

    // Bot name.
    $settings->add(new admin_setting_configtext(
        'block_aichatbot/botname',
        get_string('botname', 'block_aichatbot'),
        get_string('botname_desc', 'block_aichatbot'),
        get_string('defaultbotname', 'block_aichatbot'),
        PARAM_TEXT
    ));

    // Welcome message.
    $settings->add(new admin_setting_configtextarea(
        'block_aichatbot/welcomemessage',
        get_string('welcomemessage', 'block_aichatbot'),
        get_string('welcomemessage_desc', 'block_aichatbot'),
        get_string('defaultwelcome', 'block_aichatbot'),
        PARAM_TEXT
    ));

    // ── Rate Limiting ─────────────────────────────────────────────────────────
    $settings->add(new admin_setting_heading(
        'block_aichatbot/ratelimitsettings',
        get_string('ratelimitsettings', 'block_aichatbot'),
        ''
    ));

    // Max messages per hour per user.
    $settings->add(new admin_setting_configtext(
        'block_aichatbot/maxmessagesperhour',
        get_string('maxmessagesperhour', 'block_aichatbot'),
        get_string('maxmessagesperhour_desc', 'block_aichatbot'),
        '30',
        PARAM_INT
    ));

    // Enable/disable logging of conversations.
    $settings->add(new admin_setting_configcheckbox(
        'block_aichatbot/enablelogging',
        get_string('enablelogging', 'block_aichatbot'),
        get_string('enablelogging_desc', 'block_aichatbot'),
        1
    ));
}
