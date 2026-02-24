# block_aichatbot — AI Chatbot per Moodle

Un plugin **block** per Moodle 4.0+ che integra un chatbot AI direttamente nel corso o nella dashboard, collegandosi a qualsiasi API compatibile con lo standard **OpenAI Chat Completions** (OpenAI, Azure OpenAI, Ollama, LM Studio, ecc.).

---

## Funzionalità

| Funzione | Dettaglio |
|---|---|
| **Interfaccia chat** | Bubble user/bot, auto-scroll, formattazione Markdown di base |
| **Storico contestuale** | Ultimi 10 scambi inviati all'API per risposte coerenti |
| **Rate limiting** | Limite messaggi per ora per utente (configurabile) |
| **Log conversazioni** | Opzionale, GDPR-compliant con Privacy API |
| **Multilingua** | Italiano e inglese inclusi |
| **Dark mode** | CSS `prefers-color-scheme: dark` |

---

## Requisiti

- Moodle **4.0** o superiore (`$plugin->requires = 2022041900`)
- PHP 7.4+
- Accesso in uscita (cURL) verso l'endpoint API scelto

---

## Installazione

1. Copia la cartella `block_aichatbot` dentro `<moodle_root>/blocks/`
2. Vai su **Amministrazione del sito → Notifiche** e avvia l'upgrade
3. Vai su **Amministrazione del sito → Plugin → Blocchi → AI Chatbot** e configura il plugin

---

## Configurazione

### Impostazioni API

| Parametro | Descrizione | Esempio |
|---|---|---|
| **Provider** | OpenAI o API personalizzata | `openai` |
| **API Endpoint URL** | URL chat completions | `https://api.openai.com/v1/chat/completions` |
| **API Key** | Chiave segreta (cifrata nel DB) | `sk-...` |
| **Model** | Identificatore del modello | `gpt-4o-mini` |
| **Max Tokens** | Lunghezza massima risposta | `1024` |
| **Temperature** | Creatività (0.0–1.0) | `0.7` |
| **System Prompt** | Istruzione di sistema | _"Sei un assistente Moodle..."_ |

### API compatibili (esempi)

```
OpenAI:       https://api.openai.com/v1/chat/completions
Azure OpenAI: https://<resource>.openai.azure.com/openai/deployments/<model>/chat/completions?api-version=2024-02-01
Ollama:       http://localhost:11434/v1/chat/completions
LM Studio:    http://localhost:1234/v1/chat/completions
```

---

## Struttura del plugin

```
block_aichatbot/
├── block_aichatbot.php          # Classe principale del blocco
├── version.php                  # Versione e dipendenze
├── settings.php                 # Impostazioni amministratore
├── styles.css                   # Stili CSS
├── lang/
│   ├── en/block_aichatbot.php   # Stringhe inglese
│   └── it/block_aichatbot.php   # Stringhe italiano
├── classes/
│   ├── external/
│   │   ├── send_message.php     # Web service: invia messaggio
│   │   └── get_history.php      # Web service: storico
│   └── privacy/
│       └── provider.php         # GDPR Privacy API
├── db/
│   ├── access.php               # Capability definitions
│   ├── services.php             # Web service definitions
│   └── install.xml              # Schema DB (tabelle log)
└── amd/
    └── src/
        └── chatbot.js           # Modulo JavaScript AMD
```

---

## Permessi (capabilities)

| Capability | Descrizione | Default |
|---|---|---|
| `block/aichatbot:addinstance` | Aggiunge il blocco a un corso | Manager, Docente |
| `block/aichatbot:chat` | Usa il chatbot | Studente, Docente, Manager |
| `block/aichatbot:viewlogs` | Vede i log delle conversazioni | Manager |

---

## Privacy & GDPR

Il plugin implementa la **Moodle Privacy API**. Gli utenti possono richiedere l'esportazione e la cancellazione dei propri dati dalla pagina privacy di Moodle.

I dati memorizzati:
- `userid` — ID utente Moodle
- `message` — testo del messaggio
- `timecreated` — timestamp

Il logging può essere disabilitato completamente dalle impostazioni del plugin.

---

## Licenza

GNU GPL v3 o successiva — https://www.gnu.org/licenses/gpl-3.0.html
