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
 * English language strings for block_aichatbot.
 *
 * @package    block_aichatbot
 * @copyright  2024 AI Chatbot Plugin
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

// Plugin.
$string['pluginname']         = 'AI Chatbot';
$string['aichatbot:addinstance'] = 'Add a new AI Chatbot block';
$string['aichatbot:myaddinstance'] = 'Add a new AI Chatbot block to My Moodle';

// UI strings.
$string['logintochat']        = 'Please log in to use the chatbot.';
$string['notconfigured']      = 'The AI Chatbot is not configured yet. Please visit the plugin settings.';
$string['unavailable']        = 'The chatbot is temporarily unavailable.';
$string['defaultbotname']     = 'AI Assistant';
$string['defaultwelcome']     = 'Hello! I am your AI assistant. How can I help you today?';
$string['inputplaceholder']   = 'Type your message here…';
$string['send']               = 'Send';
$string['clearchat']          = 'Clear conversation';
$string['you']                = 'You';
$string['erroroccurred']      = 'An error occurred. Please try again.';
$string['ratelimitreached']   = 'You have reached the maximum number of messages for this hour. Please try again later.';
$string['emptymessage']       = 'Please enter a message.';
$string['thinking']           = 'Thinking…';

// Settings headings.
$string['apisettings']        = 'API Configuration';
$string['apisettings_desc']   = 'Configure the connection to the AI API provider.';
$string['appearancesettings'] = 'Appearance';
$string['ratelimitsettings']  = 'Rate Limiting';

// API settings.
$string['provider']           = 'API Provider';
$string['provider_desc']      = 'Select the AI API provider to use.';
$string['provider_openai']    = 'OpenAI (ChatGPT)';
$string['provider_custom']    = 'Custom API (OpenAI-compatible)';
$string['apiurl']             = 'API Endpoint URL';
$string['apiurl_desc']        = 'The URL of the chat completions endpoint. Default: https://api.openai.com/v1/chat/completions';
$string['apikey']             = 'API Key';
$string['apikey_desc']        = 'Your secret API key. This is stored encrypted in the database.';
$string['model']              = 'Model';
$string['model_desc']         = 'The model identifier to use (e.g. gpt-4o-mini, gpt-4o, llama3).';
$string['maxtokens']          = 'Max Tokens';
$string['maxtokens_desc']     = 'Maximum number of tokens in the response (1–4096).';
$string['temperature']        = 'Temperature';
$string['temperature_desc']   = 'Controls randomness. Lower values (0.0–0.3) give more focused answers; higher values (0.7–1.0) give more creative answers.';
$string['systemprompt']       = 'System Prompt';
$string['systemprompt_desc']  = 'The system-level instruction given to the AI at the start of every conversation.';
$string['defaultsystemprompt'] = 'You are a helpful AI assistant integrated into a Moodle learning platform. Help students and teachers with educational questions, course content, and learning resources. Be concise, clear, and encouraging.';

// Appearance settings.
$string['botname']            = 'Bot Name';
$string['botname_desc']       = 'The display name of the chatbot shown to users.';
$string['welcomemessage']     = 'Welcome Message';
$string['welcomemessage_desc'] = 'The first message shown to the user when the chatbot opens.';

// Rate limiting.
$string['maxmessagesperhour'] = 'Max Messages per Hour (per user)';
$string['maxmessagesperhour_desc'] = 'Maximum number of messages a single user can send per hour (0 = unlimited).';
$string['enablelogging']      = 'Enable Conversation Logging';
$string['enablelogging_desc'] = 'Log all chatbot conversations to the database. Useful for auditing and improving the system prompt.';

// Privacy.
$string['privacy:metadata']            = 'The AI Chatbot block stores conversation data to provide the service.';
$string['privacy:metadata:aichatbot_log'] = 'Conversation logs between users and the AI.';
$string['privacy:metadata:aichatbot_log:userid']   = 'The ID of the user who sent the message.';
$string['privacy:metadata:aichatbot_log:message']  = 'The message sent by the user.';
$string['privacy:metadata:aichatbot_log:response'] = 'The response from the AI.';
$string['privacy:metadata:aichatbot_log:timecreated'] = 'The time the message was sent.';
