const fs = require('fs');
const path = require('path');

// Lire la configuration
function readConfig() {
    try {
        const configPath = path.join(__dirname, '..', 'config', 'config.json');
        const configData = fs.readFileSync(configPath, 'utf8');
        return JSON.parse(configData);
    } catch (error) {
        console.error('Erreur lors de la lecture de config.json:', error.message);
        process.exit(1);
    }
}

// Mettre à jour un fichier avec les nouvelles URLs
function updateFile(filePath, config) {
    try {
        let content = fs.readFileSync(filePath, 'utf8');
        let modified = false;
        
        // Remplacer les URLs hardcodées
        const websiteUrl = config.website.url;
        const port = config.website.port;
        const testToken = config.database.test_token || 'undefined';
        
        // Extraire le domaine de la nouvelle URL
        const domain = websiteUrl.replace(/^https?:\/\//, '');
        
        // Patterns à remplacer - plus génériques pour n'importe quelle URL
        const replacements = [
            // Remplacer les URLs complètes (http et https)
            { from: /https?:\/\/[^\/\s]+\.trycloudflare\.com/g, to: websiteUrl },
            { from: /https?:\/\/localhost:\d+/g, to: websiteUrl },
            { from: /https?:\/\/127\.0\.0\.1:\d+/g, to: websiteUrl },
            
            // Remplacer les domaines seuls
            { from: /[a-zA-Z0-9-]+\.trycloudflare\.com/g, to: domain },
            { from: /localhost:\d+/g, to: domain },
            { from: /127\.0\.0\.1:\d+/g, to: domain },
            
            // Remplacer les tokens de test
            { from: /undefined/g, to: testToken },
            
            // Remplacer les ports
            { from: /:8080/g, to: `:${port}` },
            { from: /:8080/g, to: `:${port}` }
        ];
        
        // Appliquer les remplacements
        replacements.forEach(replacement => {
            const newContent = content.replace(replacement.from, replacement.to);
            if (newContent !== content) {
                content = newContent;
                modified = true;
            }
        });
        
        // Écrire le fichier mis à jour seulement s'il a été modifié
        if (modified) {
            fs.writeFileSync(filePath, content, 'utf8');
            console.log(`✅ ${path.basename(filePath)} mis à jour`);
        } else {
            console.log(`ℹ️  ${path.basename(filePath)} déjà à jour`);
        }
        
    } catch (error) {
        console.error(`❌ Erreur lors de la mise à jour de ${filePath}:`, error.message);
    }
}

// Fonction pour scanner récursivement les fichiers
function scanAndUpdateFiles(dir, config, extensions = ['.js', '.php', '.bat', '.json']) {
    const files = fs.readdirSync(dir);
    
    files.forEach(file => {
        const filePath = path.join(dir, file);
        const stat = fs.statSync(filePath);
        
        if (stat.isDirectory()) {
            // Ignorer certains dossiers
            if (!['node_modules', '.git', 'dist', 'build'].includes(file)) {
                scanAndUpdateFiles(filePath, config, extensions);
            }
        } else if (stat.isFile()) {
            const ext = path.extname(file);
            if (extensions.includes(ext)) {
                updateFile(filePath, config);
            }
        }
    });
}

// Fonction principale
function main() {
    console.log('🔄 Mise à jour des URLs dans tous les fichiers...');
    
    const config = readConfig();
    console.log(`📋 Configuration chargée:`);
    console.log(`   • URL: ${config.website.url}`);
    console.log(`   • Port: ${config.website.port}`);
    console.log(`   • Token de test: ${config.database.test_token || 'undefined'}`);
    console.log('');
    
    // Scanner et mettre à jour tous les fichiers
    const projectRoot = path.join(__dirname, '..');
    scanAndUpdateFiles(projectRoot, config);
    
    console.log('');
    console.log('✅ Mise à jour terminée !');
    console.log('');
    console.log('📝 URLs mises à jour :');
    console.log(`   • Dashboard: ${config.website.url}/dashboard.php`);
    console.log(`   • Widget Wins: ${config.website.url}/modules/win.php`);
    console.log(`   • Configuration: ${config.website.url}/modules/wins-config.php`);
    console.log(`   • API: ${config.website.url}/api.php`);
}

// Exécuter si appelé directement
if (require.main === module) {
    main();
}

module.exports = { readConfig, updateFile, scanAndUpdateFiles }; 