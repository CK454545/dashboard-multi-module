const { exec } = require('child_process');
const fs = require('fs');
const path = require('path');
const https = require('https');

/**
 * 🔄 Auto-Update System pour Ubuntu
 * 
 * Ce script vérifie automatiquement les mises à jour depuis GitHub
 * et les applique sans toucher à la base de données des utilisateurs
 */

class AutoUpdater {
    constructor() {
        this.projectRoot = '/var/www/dashboard-multi-modules';
        this.configPath = path.join(this.projectRoot, 'config/config.json');
        this.dbPath = path.join(this.projectRoot, 'database.db');
        this.backupDir = path.join(this.projectRoot, 'backups');
        this.checkInterval = 3600000; // 1 heure
        this.githubRepo = 'CK454545/dashboard-multi-modules';
    }

    // Lire la configuration
    loadConfig() {
        try {
            return JSON.parse(fs.readFileSync(this.configPath, 'utf8'));
        } catch (error) {
            console.error('❌ Erreur chargement config:', error.message);
            return null;
        }
    }

    // Exécuter une commande shell
    execCommand(command) {
        return new Promise((resolve, reject) => {
            exec(command, { cwd: this.projectRoot }, (error, stdout, stderr) => {
                if (error) {
                    reject(error);
                    return;
                }
                resolve(stdout);
            });
        });
    }

    // Sauvegarder la base de données
    async backupDatabase() {
        const timestamp = new Date().toISOString().replace(/[:.]/g, '-');
        const backupPath = path.join(this.backupDir, 'database', `database_${timestamp}.db`);
        
        try {
            // Créer le dossier si nécessaire
            await this.execCommand(`mkdir -p ${path.join(this.backupDir, 'database')}`);
            
            // Copier la base de données
            if (fs.existsSync(this.dbPath)) {
                await this.execCommand(`cp "${this.dbPath}" "${backupPath}"`);
                console.log(`✅ Base de données sauvegardée: ${backupPath}`);
            }
        } catch (error) {
            console.error('❌ Erreur backup DB:', error.message);
        }
    }

    // Vérifier les mises à jour GitHub
    async checkForUpdates() {
        try {
            // Changer vers le répertoire du projet
            process.chdir(this.projectRoot);
            
            // Récupérer les informations du dernier commit
            const lastCommit = await this.execCommand('git log -1 --pretty=format:"%H|%an|%ae|%s"');
            if (!lastCommit) {
                console.log('⚠️ Impossible de récupérer les informations du dernier commit');
                return false;
            }
            
            const [hash, author, email, message] = lastCommit.split('|');
            
            // Vérifier s'il y a des mises à jour
            await this.execCommand('git fetch origin');
            const status = await this.execCommand('git status --porcelain');
            const behind = await this.execCommand('git rev-list HEAD..origin/main --count');
            
            if (behind && parseInt(behind) > 0) {
                console.log(`🔄 Mise à jour disponible: ${behind} commit(s) en retard`);
                return true;
            } else {
                console.log('✅ Déjà à jour');
                return false;
            }
        } catch (error) {
            console.error('❌ Erreur lors de la vérification des mises à jour:', error.message);
            return false;
        }
    }

    // Appliquer les mises à jour
    async applyUpdates() {
        try {
            console.log('🚀 Application des mises à jour...');
            
            // 1. Sauvegarder la base de données
            await this.backupDatabase();
            
            // 2. Sauvegarder la configuration
            const configBackup = `${this.configPath}.backup`;
            await this.execCommand(`cp "${this.configPath}" "${configBackup}"`);
            
            // 3. Stash les changements locaux
            await this.execCommand('git stash push -m "Auto-stash before update"');
            
            // 4. Pull les changements
            await this.execCommand('git pull origin main');
            
            // 5. Restaurer la configuration
            await this.execCommand(`cp "${configBackup}" "${this.configPath}"`);
            
            // 6. Installer les nouvelles dépendances
            console.log('📦 Installation des dépendances...');
            await this.execCommand('cd bot && npm install');
            
            // 7. Appliquer les migrations de DB si nécessaire
            if (fs.existsSync(path.join(this.projectRoot, 'scripts/migrate-db.js'))) {
                console.log('🔄 Application des migrations DB...');
                
                // Vérifier si une migration n'est pas déjà en cours
                const lockPath = path.join(this.projectRoot, 'migration.lock');
                if (!fs.existsSync(lockPath)) {
                    await this.execCommand('node scripts/migrate-db.js');
                } else {
                    console.log('⚠️ Migration déjà en cours, ignorée');
                }
            }
            
            // 8. Redémarrer les services
            console.log('🔄 Redémarrage des services...');
            await this.execCommand('pm2 restart all');
            
            console.log('✅ Mise à jour terminée avec succès!');
            
            // Envoyer une notification (optionnel)
            await this.sendUpdateNotification();
            
        } catch (error) {
            console.error('❌ Erreur pendant la mise à jour:', error.message);
            
            // Tenter de restaurer
            try {
                await this.execCommand('git stash pop');
            } catch (e) {
                console.error('❌ Erreur restauration:', e.message);
            }
        }
    }

    // Notification de mise à jour (via webhook Discord si configuré)
    async sendUpdateNotification() {
        const config = this.loadConfig();
        if (!config || !config.discord || !config.discord.webhook_url) return;

        const message = {
            embeds: [{
                title: '🔄 Mise à jour automatique',
                description: 'Le système a été mis à jour avec succès depuis GitHub.',
                color: 0x00ff00,
                timestamp: new Date().toISOString(),
                footer: {
                    text: 'Dashboard Multi-Modules'
                }
            }]
        };

        // Envoyer le webhook (implémenter si nécessaire)
    }

    // Nettoyer les vieux backups (garder les 30 derniers)
    async cleanOldBackups() {
        try {
            const dbBackupDir = path.join(this.backupDir, 'database');
            const files = await fs.promises.readdir(dbBackupDir);
            
            // Trier par date
            const sortedFiles = files
                .filter(f => f.startsWith('database_') && f.endsWith('.db'))
                .sort()
                .reverse();
            
            // Supprimer les anciens (garder 30)
            if (sortedFiles.length > 30) {
                for (let i = 30; i < sortedFiles.length; i++) {
                    const filePath = path.join(dbBackupDir, sortedFiles[i]);
                    await fs.promises.unlink(filePath);
                    console.log(`🗑️  Suppression ancien backup: ${sortedFiles[i]}`);
                }
            }
        } catch (error) {
            console.error('❌ Erreur nettoyage backups:', error.message);
        }
    }

    // Boucle principale
    async start() {
        console.log('🚀 Démarrage du système de mise à jour automatique');
        console.log(`📍 Projet: ${this.projectRoot}`);
        console.log(`⏰ Intervalle: ${this.checkInterval / 1000 / 60} minutes`);

        // Vérification initiale
        await this.run();

        // Lancer la boucle
        setInterval(() => {
            this.run();
        }, this.checkInterval);
    }

    // Exécution d'une vérification
    async run() {
        try {
            const hasUpdates = await this.checkForUpdates();
            
            if (hasUpdates) {
                await this.applyUpdates();
            }
            
            // Nettoyer les vieux backups une fois par jour
            if (new Date().getHours() === 3) {
                await this.cleanOldBackups();
            }
            
        } catch (error) {
            console.error('❌ Erreur dans la boucle de mise à jour:', error);
        }
    }
}

// Démarrer si exécuté directement
if (require.main === module) {
    const updater = new AutoUpdater();
    updater.start();
}

module.exports = AutoUpdater; 