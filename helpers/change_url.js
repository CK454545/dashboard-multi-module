const fs = require('fs');
const path = require('path');

// Configuration des chemins
const CONFIG_PATH = path.join(__dirname, '..', 'config', 'config.json');
const PROJECT_ROOT = path.join(__dirname, '..');

/**
 * Lire la configuration actuelle
 */
function readConfig() {
    try {
        if (!fs.existsSync(CONFIG_PATH)) {
            console.error('❌ Fichier config.json non trouvé');
            return null;
        }
        const configData = fs.readFileSync(CONFIG_PATH, 'utf8');
        return JSON.parse(configData);
    } catch (error) {
        console.error('❌ Erreur lecture config:', error.message);
        return null;
    }
}

/**
 * Sauvegarder la configuration
 */
function saveConfig(config) {
    try {
        fs.writeFileSync(CONFIG_PATH, JSON.stringify(config, null, 4), 'utf8');
        return true;
    } catch (error) {
        console.error('❌ Erreur sauvegarde config:', error.message);
        return false;
    }
}

/**
 * Extraire le port d'une URL
 */
function extractPort(url) {
    try {
        const urlObj = new URL(url);
        if (urlObj.port) {
            return urlObj.port;
        }
        // Ports par défaut
        return urlObj.protocol === 'https:' ? '443' : '80';
    } catch {
        // Si ce n'est pas une URL complète, chercher le port dans la chaîne
        const portMatch = url.match(/:(\d+)/);
        return portMatch ? portMatch[1] : '8080';
    }
}

/**
 * Normaliser une URL
 */
function normalizeUrl(url) {
    // Supprimer les slashes de fin
    url = url.replace(/\/+$/, '');
    
    // Ajouter http:// si pas de protocole
    if (!url.match(/^https?:\/\//)) {
        url = 'http://' + url;
    }
    
    return url;
}

/**
 * Mettre à jour l'URL dans la configuration
 */
function updateUrlInConfig(newUrl) {
    console.log('🔧 Mise à jour de la configuration...');
    
    const config = readConfig();
    if (!config) return false;
    
    const normalizedUrl = normalizeUrl(newUrl);
    const newPort = extractPort(normalizedUrl);
    
    // Sauvegarder l'ancienne URL pour les logs
    const oldUrl = config.website?.url || 'localhost:8080';
    
    // Mettre à jour la configuration
    if (!config.website) config.website = {};
    config.website.url = normalizedUrl;
    config.website.port = newPort;
    
    if (saveConfig(config)) {
        console.log(`✅ Configuration mise à jour:`);
        console.log(`   Ancienne URL: ${oldUrl}`);
        console.log(`   Nouvelle URL: ${normalizedUrl}`);
        console.log(`   Port: ${newPort}`);
        return { oldUrl, newUrl: normalizedUrl, port: newPort };
    }
    
    return false;
}

/**
 * Point d'entrée principal
 */
function main() {
    const args = process.argv.slice(2);
    
    if (args.length === 0) {
        console.error('❌ Usage: node change_url.js <nouvelle_url>');
        console.error('');
        console.error('📋 Exemples:');
        console.error('   node change_url.js https://mon-domaine.com');
        console.error('   node change_url.js http://localhost:3000');
        console.error('   node change_url.js https://mon-tunnel.trycloudflare.com');
        console.error('   node change_url.js mon-domaine.com:8080');
        process.exit(1);
    }
    
    const newUrl = args[0];
    console.log('🔗 Changement d\'URL du Dashboard Multi-Modules');
    console.log('================================================');
    console.log(`🎯 Nouvelle URL: ${newUrl}`);
    console.log('');
    
    const result = updateUrlInConfig(newUrl);
    if (result) {
        console.log('');
        console.log('✅ URL mise à jour avec succès dans config.json');
        console.log('');
        console.log('🔄 Prochaines étapes:');
        console.log('   1. Lancez force-update-urls.bat pour propager partout');
        console.log('   2. Redémarrez le serveur pour appliquer les changements');
        console.log('');
        
        // Retourner les informations pour le script batch
        process.exit(0);
    } else {
        console.log('');
        console.error('❌ Échec de la mise à jour');
        process.exit(1);
    }
}

// Exécuter si appelé directement
if (require.main === module) {
    main();
}

module.exports = { updateUrlInConfig, normalizeUrl, extractPort }; 