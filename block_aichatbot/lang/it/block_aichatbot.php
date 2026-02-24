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
 * Italian language strings for block_aichatbot.
 *
 * @package    block_aichatbot
 * @copyright  2024 AI Chatbot Plugin
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

// Plugin.
$string['pluginname']         = 'AI Chatbot';
$string['aichatbot:addinstance'] = 'Aggiungi un nuovo blocco AI Chatbot';
$string['aichatbot:myaddinstance'] = 'Aggiungi un nuovo blocco AI Chatbot alla mia pagina';

// UI strings.
$string['logintochat']        = 'Accedi per utilizzare il chatbot.';
$string['notconfigured']      = 'Il chatbot AI non è ancora configurato. Visita le impostazioni del plugin.';
$string['unavailable']        = 'Il chatbot non è momentaneamente disponibile.';
$string['defaultbotname']     = 'Assistente AI';
$string['defaultwelcome']     = 'Ciao! Sono il tuo assistente AI. Come posso aiutarti oggi?';
$string['inputplaceholder']   = 'Scrivi il tuo messaggio qui…';
$string['send']               = 'Invia';
$string['clearchat']          = 'Cancella conversazione';
$string['you']                = 'Tu';
$string['erroroccurred']      = 'Si è verificato un errore. Riprova.';
$string['ratelimitreached']   = 'Hai raggiunto il numero massimo di messaggi per questa ora. Riprova più tardi.';
$string['emptymessage']       = 'Inserisci un messaggio.';
$string['thinking']           = 'Sto elaborando…';

// Settings headings.
$string['apisettings']        = 'Configurazione API';
$string['apisettings_desc']   = 'Configura la connessione al provider API AI.';
$string['appearancesettings'] = 'Aspetto';
$string['ratelimitsettings']  = 'Limiti di utilizzo';

// API settings.
$string['provider']           = 'Provider API';
$string['provider_desc']      = 'Seleziona il provider API AI da utilizzare.';
$string['provider_openai']    = 'OpenAI (ChatGPT)';
$string['provider_custom']    = 'API personalizzata (compatibile OpenAI)';
$string['apiurl']             = 'URL endpoint API';
$string['apiurl_desc']        = 'L\'URL dell\'endpoint per le chat completions. Predefinito: https://api.openai.com/v1/chat/completions';
$string['apikey']             = 'Chiave API';
$string['apikey_desc']        = 'La tua chiave API segreta. Viene salvata cifrata nel database.';
$string['model']              = 'Modello';
$string['model_desc']         = 'Identificatore del modello da utilizzare (es. gpt-4o-mini, gpt-4o, llama3).';
$string['maxtokens']          = 'Token massimi';
$string['maxtokens_desc']     = 'Numero massimo di token nella risposta (1–4096).';
$string['temperature']        = 'Temperatura';
$string['temperature_desc']   = 'Controlla la casualità. Valori bassi (0.0–0.3) danno risposte più precise; valori alti (0.7–1.0) danno risposte più creative.';
$string['systemprompt']       = 'System Prompt';
$string['systemprompt_desc']  = 'L\'istruzione di sistema fornita all\'AI all\'inizio di ogni conversazione.';
$string['defaultsystemprompt'] = 'Sei un assistente AI integrato in una piattaforma Moodle. Aiuta studenti e docenti con domande didattiche, contenuti dei corsi e risorse di apprendimento. Sii conciso, chiaro e incoraggiante.';

// Appearance settings.
$string['botname']            = 'Nome del bot';
$string['botname_desc']       = 'Il nome visualizzato del chatbot.';
$string['welcomemessage']     = 'Messaggio di benvenuto';
$string['welcomemessage_desc'] = 'Il primo messaggio mostrato all\'utente quando il chatbot si apre.';

// Rate limiting.
$string['maxmessagesperhour'] = 'Max messaggi all\'ora (per utente)';
$string['maxmessagesperhour_desc'] = 'Numero massimo di messaggi che un singolo utente può inviare per ora (0 = illimitato).';
$string['enablelogging']      = 'Abilita log delle conversazioni';
$string['enablelogging_desc'] = 'Registra tutte le conversazioni nel database. Utile per audit e per migliorare il system prompt.';

// Privacy.
$string['privacy:metadata']            = 'Il blocco AI Chatbot memorizza i dati delle conversazioni per erogare il servizio.';
$string['privacy:metadata:aichatbot_log'] = 'Log delle conversazioni tra utenti e AI.';
$string['privacy:metadata:aichatbot_log:userid']   = 'L\'ID dell\'utente che ha inviato il messaggio.';
$string['privacy:metadata:aichatbot_log:message']  = 'Il messaggio inviato dall\'utente.';
$string['privacy:metadata:aichatbot_log:response'] = 'La risposta dell\'AI.';
$string['privacy:metadata:aichatbot_log:timecreated'] = 'Il momento in cui il messaggio è stato inviato.';
