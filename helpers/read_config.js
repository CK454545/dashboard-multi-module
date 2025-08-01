const fs = require('fs');

// Lire la configuration
let config;
try {
    const configData = fs.readFileSync('config/config.json', 'utf8');
    config = JSON.parse(configData);
} catch (error) {
    // Configuration par défaut si le fichier n'existe pas
    config = {
        website: { url: 'https://petroleum-advisory-mongolia-save.trycloudflare.com', port: 8080 },
        discord: { token: 'VOTRE_TOKEN_BOT_DISCORD', support_url: 'https://discord.gg/TbXYYsEgqz' },
        database: { file: 'database.db', test_token: 'undefined' },
        app: { 
            name: 'StreamPro Studio', 
            description: 'Solutions Professionnelles pour Créateurs de Contenu', 
            version: '1.0.0', 
            copyright: '© Copyright 2025/2026 MFA & Développement/Design by CK' 
        }
    };
}

// Récupérer l'argument (url, port, test_token, etc.)
const arg = process.argv[2];

switch (arg) {
    case 'url':
        console.log(config.website.url);
        break;
    case 'port':
        console.log(config.website.port);
        break;
    case 'test_token':
        console.log(config.database.test_token);
        break;
    case 'support_url':
        console.log(config.discord.support_url);
        break;
    case 'app_name':
        console.log(config.app.name);
        break;
    case 'all':
        console.log(JSON.stringify(config));
        break;
    default:
        console.log('Usage: node read_config.js [url|port|test_token|support_url|app_name|all]');
} 