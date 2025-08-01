const fs = require('fs');
const path = require('path');
const { execSync } = require('child_process');
const https = require('https');

class AutoUpdater {
    constructor() {
        this.projectRoot = path.join(__dirname, '..');
        this.configPath = path.join(this.projectRoot, 'config', 'update-config.json');
        this.backupDir = path.join(this.projectRoot, 'backups', 'updates');
        
        // Créer les dossiers nécessaires
        if (!fs.existsSync(this.backupDir)) {
            fs.mkdirSync(this.backupDir, { recursive: true });
        }
        
        this.loadConfig();
    }

    // Charger la configuration de mise à jour
    loadConfig() {
        try {
            if (fs.existsSync(this.configPath)) {
                this.config = JSON.parse(fs.readFileSync(this.configPath, 'utf8'));
            } else {
                // Configuration par défaut
                this.config = {
                    repository: "votre-username/wins-counter",
                    branch: "main",
                    auto_update: true,
                    check_interval: 3600000, // 1 heure
                    backup_before_update: true,
                    last_check: null,
                    current_version: "1.0.0"
                };
                this.saveConfig();
            }
        } catch (error) {
            console.error('❌ Erreur lors du chargement de la config:', error.message);
            this.config = {};
        }
    }

    // Sauvegarder la configuration
    saveConfig() {
        try {
            fs.writeFileSync(this.configPath, JSON.stringify(this.config, null, 2));
        } catch (error) {
            console.error('❌ Erreur lors de la sauvegarde config:', error.message);
        }
    }

    // Vérifier les mises à jour disponibles
    async checkForUpdates() {
        try {
            console.log('🔍 Vérification des mises à jour...');
            
            const apiUrl = `https://api.github.com/repos/${this.config.repository}/commits/${this.config.branch}`;
            
            return new Promise((resolve, reject) => {
                const req = https.get(apiUrl, {
                    headers: {
                        'User-Agent': 'Wins-Counter-Updater'
                    }
                }, (res) => {
                    let data = '';
                    
                    res.on('data', chunk => data += chunk);
                    res.on('end', () => {
                        try {
                            const commit = JSON.parse(data);
                            const latestCommit = commit.sha;
                            const commitDate = new Date(commit.commit.committer.date);
                            
                            this.config.last_check = new Date().toISOString();
                            this.saveConfig();
                            
                            resolve({
                                available: latestCommit !== this.config.current_version,
                                version: latestCommit.substring(0, 7),
                                date: commitDate.toLocaleString('fr-FR'),
                                message: commit.commit.message
                            });
                        } catch (parseError) {
                            reject(parseError);
                        }
                    });
                });
                
                req.on('error', reject);
                req.setTimeout(10000, () => {
                    req.destroy();
                    reject(new Error('Timeout'));
                });
            });
        } catch (error) {
            console.error('❌ Erreur lors de la vérification:', error.message);
            return { available: false, error: error.message };
        }
    }

    // Créer un backup avant mise à jour
    createUpdateBackup() {
        try {
            const timestamp = new Date().toISOString().replace(/[:.]/g, '-');
            const backupName = `update_backup_${timestamp}`;
            const backupPath = path.join(this.backupDir, backupName);
            
            console.log('📦 Création du backup avant mise à jour...');
            
            // Créer le dossier de backup
            fs.mkdirSync(backupPath, { recursive: true });
            
            // Lister les fichiers/dossiers à sauvegarder
            const itemsToBackup = [
                'web',
                'bot',
                'config',
                'database',
                'helpers',
                'scripts'
            ];
            
            itemsToBackup.forEach(item => {
                const sourcePath = path.join(this.projectRoot, item);
                const destPath = path.join(backupPath, item);
                
                if (fs.existsSync(sourcePath)) {
                    this.copyRecursive(sourcePath, destPath);
                }
            });
            
            console.log(`✅ Backup créé: ${backupName}`);
            return backupPath;
        } catch (error) {
            console.error('❌ Erreur lors du backup:', error.message);
            return null;
        }
    }

    // Copie récursive de fichiers/dossiers
    copyRecursive(src, dest) {
        if (fs.lstatSync(src).isDirectory()) {
            if (!fs.existsSync(dest)) {
                fs.mkdirSync(dest, { recursive: true });
            }
            fs.readdirSync(src).forEach(item => {
                this.copyRecursive(path.join(src, item), path.join(dest, item));
            });
        } else {
            fs.copyFileSync(src, dest);
        }
    }

    // Télécharger et appliquer la mise à jour
    async downloadUpdate() {
        try {
            console.log('⬇️ Téléchargement de la mise à jour...');
            
            const zipUrl = `https://github.com/${this.config.repository}/archive/${this.config.branch}.zip`;
            const tempZip = path.join(this.backupDir, 'update.zip');
            const tempDir = path.join(this.backupDir, 'temp_update');
            
            // Télécharger le ZIP
            await this.downloadFile(zipUrl, tempZip);
            
            // Extraire (nécessite 7zip ou équivalent)
            try {
                execSync(`powershell -command "Expand-Archive -Path '${tempZip}' -DestinationPath '${tempDir}' -Force"`, {
                    stdio: 'inherit'
                });
            } catch (error) {
                console.error('❌ Erreur extraction, essai avec tar...');
                execSync(`tar -xf "${tempZip}" -C "${tempDir}"`, { stdio: 'inherit' });
            }
            
            // Trouver le dossier extrait
            const extractedDir = fs.readdirSync(tempDir)[0];
            const sourcePath = path.join(tempDir, extractedDir);
            
            console.log('📁 Application de la mise à jour...');
            
            // Copier les nouveaux fichiers
            const itemsToUpdate = ['web', 'bot', 'helpers', 'scripts'];
            itemsToUpdate.forEach(item => {
                const srcPath = path.join(sourcePath, item);
                const destPath = path.join(this.projectRoot, item);
                
                if (fs.existsSync(srcPath)) {
                    if (fs.existsSync(destPath)) {
                        fs.rmSync(destPath, { recursive: true, force: true });
                    }
                    this.copyRecursive(srcPath, destPath);
                    console.log(`✅ ${item} mis à jour`);
                }
            });
            
            // Nettoyer les fichiers temporaires
            fs.rmSync(tempZip, { force: true });
            fs.rmSync(tempDir, { recursive: true, force: true });
            
            // Mettre à jour la version
            const updateInfo = await this.checkForUpdates();
            this.config.current_version = updateInfo.version;
            this.saveConfig();
            
            console.log('✅ Mise à jour terminée avec succès!');
            return true;
            
        } catch (error) {
            console.error('❌ Erreur lors de la mise à jour:', error.message);
            return false;
        }
    }

    // Télécharger un fichier
    downloadFile(url, dest) {
        return new Promise((resolve, reject) => {
            const file = fs.createWriteStream(dest);
            
            https.get(url, response => {
                // Gérer les redirections
                if (response.statusCode === 302 || response.statusCode === 301) {
                    return https.get(response.headers.location, response => {
                        response.pipe(file);
                        file.on('finish', () => {
                            file.close();
                            resolve();
                        });
                    }).on('error', reject);
                }
                
                response.pipe(file);
                file.on('finish', () => {
                    file.close();
                    resolve();
                });
            }).on('error', reject);
        });
    }

    // Démarrer la vérification automatique
    startAutoCheck() {
        console.log('🔄 Démarrage de la vérification automatique des mises à jour');
        console.log(`⏰ Intervalle: ${this.config.check_interval / 60000} minutes`);
        
        // Vérification immédiate
        this.performAutoCheck();
        
        // Programmer les vérifications automatiques
        setInterval(() => {
            this.performAutoCheck();
        }, this.config.check_interval);
    }

    // Effectuer une vérification automatique
    async performAutoCheck() {
        try {
            const updateInfo = await this.checkForUpdates();
            
            if (updateInfo.available && this.config.auto_update) {
                console.log('🆕 Mise à jour disponible, application automatique...');
                
                if (this.config.backup_before_update) {
                    this.createUpdateBackup();
                }
                
                await this.downloadUpdate();
                console.log('🎉 Mise à jour automatique terminée!');
            } else if (updateInfo.available) {
                console.log('🆕 Mise à jour disponible (auto-update désactivé)');
            } else {
                console.log('✅ Aucune mise à jour disponible');
            }
        } catch (error) {
            console.error('❌ Erreur lors de la vérification auto:', error.message);
        }
    }

    // Configurer le repository
    setRepository(repo, branch = 'main') {
        this.config.repository = repo;
        this.config.branch = branch;
        this.saveConfig();
        console.log(`✅ Repository configuré: ${repo} (${branch})`);
    }
}

// Utilisation en ligne de commande
if (require.main === module) {
    const updater = new AutoUpdater();
    const args = process.argv.slice(2);
    
    switch (args[0]) {
        case 'check':
            updater.checkForUpdates().then(info => {
                console.log('\n📋 État des mises à jour:');
                if (info.available) {
                    console.log(`🆕 Mise à jour disponible: ${info.version}`);
                    console.log(`📅 Date: ${info.date}`);
                    console.log(`💬 Message: ${info.message}`);
                } else {
                    console.log('✅ Aucune mise à jour disponible');
                }
            });
            break;
            
        case 'update':
            (async () => {
                const info = await updater.checkForUpdates();
                if (info.available) {
                    if (updater.config.backup_before_update) {
                        updater.createUpdateBackup();
                    }
                    await updater.downloadUpdate();
                } else {
                    console.log('✅ Aucune mise à jour disponible');
                }
            })();
            break;
            
        case 'auto':
            updater.startAutoCheck();
            break;
            
        case 'config':
            if (args[1] && args[2]) {
                updater.setRepository(args[1], args[2]);
            } else if (args[1]) {
                updater.setRepository(args[1]);
            } else {
                console.log('❌ Usage: node auto-update.js config username/repo [branch]');
            }
            break;
            
        default:
            console.log(`
🔄 Système de Mise à Jour Automatique

Commandes disponibles:
  check   - Vérifier les mises à jour
  update  - Télécharger et appliquer les mises à jour
  auto    - Démarrer la vérification automatique
  config  - Configurer le repository GitHub

Exemples:
  node auto-update.js check
  node auto-update.js update
  node auto-update.js auto
  node auto-update.js config username/wins-counter main
            `);
    }
}

module.exports = AutoUpdater; 