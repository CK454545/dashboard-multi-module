const fs = require('fs');
const path = require('path');

// Configuration
const CONFIG_PATH = path.join(__dirname, '..', 'config', 'config.json');
const PROJECT_ROOT = path.join(__dirname, '..');

// Extensions de fichiers à traiter
const FILE_EXTENSIONS = ['.js', '.php', '.bat', '.json', '.md'];

// Fichiers à exclure
const EXCLUDE_FILES = [
    'node_modules',
    '.git',
    'database',
    'backups',
    'logs',
    'temp',
    'cache',
    'package-lock.json',
    'composer.lock'
];

/**
 * Lire la configuration
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
 * Détecter les anciennes URLs dans le contenu
 */
function detectOldUrls(content) {
    const urlPatterns = [
        // URLs complètes avec protocole
        /https?:\/\/[a-zA-Z0-9.-]+(?::\d+)?(?:\/[^\s"']*)?/g,
        // localhost avec port
        /localhost:\d+/g,
        // IPs avec port
        /\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}:\d+/g,
        // Domaines avec port
        /[a-zA-Z0-9.-]+\.(?:com|net|org|fr|trycloudflare\.com)(?::\d+)?/g
    ];
    
    const foundUrls = new Set();
    
    urlPatterns.forEach(pattern => {
        const matches = content.match(pattern);
        if (matches) {
            matches.forEach(url => foundUrls.add(url));
        }
    });
    
    return Array.from(foundUrls);
}

/**
 * Remplacer les URLs dans le contenu
 */
function replaceUrlsInContent(content, newUrl, fileName) {
    let updatedContent = content;
    let replacements = 0;
    
    // Détecter les anciennes URLs
    const oldUrls = detectOldUrls(content);
    
    if (oldUrls.length === 0) {
        return { content: updatedContent, replacements };
    }
    
    console.log(`   📋 URLs détectées dans ${fileName}:`);
    oldUrls.forEach(url => console.log(`      - ${url}`));
    
    // Remplacer chaque ancienne URL par la nouvelle
    oldUrls.forEach(oldUrl => {
        // Éviter de remplacer la nouvelle URL par elle-même
        if (oldUrl === newUrl || oldUrl.includes(newUrl.replace(/^https?:\/\//, ''))) {
            return;
        }
        
        // Cas spéciaux pour certains fichiers
        let replacementUrl = newUrl;
        
        // Pour les commandes PHP -S, garder localhost
        if (fileName.endsWith('.bat') && content.includes('php -S')) {
            const phpServerMatch = content.match(/php -S ([^\s"']+)/);
            if (phpServerMatch && phpServerMatch[1] === oldUrl) {
                // Ne pas remplacer l'adresse de bind du serveur PHP
                return;
            }
        }
        
        // Compter et remplacer
        const beforeReplace = updatedContent;
        updatedContent = updatedContent.replace(new RegExp(escapeRegExp(oldUrl), 'g'), replacementUrl);
        
        if (beforeReplace !== updatedContent) {
            replacements++;
            console.log(`      ✅ ${oldUrl} → ${replacementUrl}`);
        }
    });
    
    return { content: updatedContent, replacements };
}

/**
 * Échapper les caractères spéciaux pour regex
 */
function escapeRegExp(string) {
    return string.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
}

/**
 * Traiter un fichier
 */
function processFile(filePath, newUrl) {
    try {
        const content = fs.readFileSync(filePath, 'utf8');
        const fileName = path.basename(filePath);
        
        const result = replaceUrlsInContent(content, newUrl, fileName);
        
        if (result.replacements > 0) {
            fs.writeFileSync(filePath, result.content, 'utf8');
            console.log(`   ✅ ${result.replacements} remplacement(s) effectué(s)`);
            return result.replacements;
        } else {
            console.log(`   ℹ️  Aucune URL à remplacer`);
            return 0;
        }
    } catch (error) {
        console.error(`   ❌ Erreur: ${error.message}`);
        return 0;
    }
}

/**
 * Scanner récursivement les fichiers
 */
function scanDirectory(dirPath, newUrl, stats = { files: 0, replacements: 0 }) {
    try {
        const items = fs.readdirSync(dirPath);
        
        for (const item of items) {
            const itemPath = path.join(dirPath, item);
            const relativePath = path.relative(PROJECT_ROOT, itemPath);
            
            // Ignorer les fichiers/dossiers exclus
            if (EXCLUDE_FILES.some(exclude => relativePath.includes(exclude))) {
                continue;
            }
            
            const stat = fs.statSync(itemPath);
            
            if (stat.isDirectory()) {
                scanDirectory(itemPath, newUrl, stats);
            } else if (stat.isFile()) {
                const ext = path.extname(item);
                if (FILE_EXTENSIONS.includes(ext)) {
                    console.log(`\n📄 Traitement: ${relativePath}`);
                    const replacements = processFile(itemPath, newUrl);
                    stats.files++;
                    stats.replacements += replacements;
                }
            }
        }
    } catch (error) {
        console.error(`❌ Erreur scan dossier ${dirPath}:`, error.message);
    }
    
    return stats;
}

/**
 * Point d'entrée principal
 */
function main() {
    console.log('🔄 Force Update URLs - Dashboard Multi-Modules');
    console.log('===============================================');
    
    // Lire la configuration
    const config = readConfig();
    if (!config) {
        console.error('❌ Impossible de lire la configuration');
        process.exit(1);
    }
    
    const newUrl = config.website?.url;
    if (!newUrl) {
        console.error('❌ URL non trouvée dans la configuration');
        process.exit(1);
    }
    
    console.log(`🎯 URL cible: ${newUrl}`);
    console.log(`📁 Dossier projet: ${PROJECT_ROOT}`);
    console.log('');
    
    // Scanner et remplacer
    console.log('🔍 Scan des fichiers...');
    const stats = scanDirectory(PROJECT_ROOT, newUrl);
    
    console.log('\n📊 RÉSUMÉ');
    console.log('=========');
    console.log(`📄 Fichiers traités: ${stats.files}`);
    console.log(`🔄 Remplacements effectués: ${stats.replacements}`);
    
    if (stats.replacements > 0) {
        console.log('\n✅ Mise à jour terminée avec succès !');
        console.log('\n🔄 Prochaines étapes:');
        console.log('   1. Redémarrez le serveur pour appliquer les changements');
        console.log('   2. Testez les fonctionnalités principales');
    } else {
        console.log('\nℹ️  Aucune URL à mettre à jour trouvée');
    }
}

// Exécuter si appelé directement
if (require.main === module) {
    main();
}

module.exports = { replaceUrlsInContent, detectOldUrls, scanDirectory }; 