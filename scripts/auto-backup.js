const fs = require('fs');
const path = require('path');
const { execSync } = require('child_process');

class DatabaseBackup {
    constructor() {
        this.backupDir = path.join(__dirname, '..', 'backups');
        this.dbPath = path.join(__dirname, '..', 'database', 'database.db'); // Chemin correct vers database/database.db
        this.maxBackups = 10; // Garder seulement les 10 derniers backups
        
        // Créer le dossier de backup s'il n'existe pas
        if (!fs.existsSync(this.backupDir)) {
            fs.mkdirSync(this.backupDir, { recursive: true });
        }
    }

    // Créer un backup avec timestamp
    createBackup() {
        const timestamp = new Date().toISOString().replace(/[:.]/g, '-');
        const backupName = `database_backup_${timestamp}.db`;
        const backupPath = path.join(this.backupDir, backupName);
        
        try {
            // Vérifier que la base de données source existe
            if (!fs.existsSync(this.dbPath)) {
                console.error(`❌ Base de données introuvable: ${this.dbPath}`);
                return false;
            }
            
            // Copier la base de données
            fs.copyFileSync(this.dbPath, backupPath);
            
            // Vérifier que la copie a réussi
            if (fs.existsSync(backupPath)) {
                const stats = fs.statSync(backupPath);
                console.log(`✅ Backup créé: ${backupName} (${(stats.size / 1024).toFixed(2)} KB)`);
                return true;
            } else {
                console.error('❌ Échec de la création du backup');
                return false;
            }
        } catch (error) {
            console.error('❌ Erreur lors de la création du backup:', error.message);
            return false;
        }
    }

    // Nettoyer les anciens backups
    cleanOldBackups() {
        try {
            const files = fs.readdirSync(this.backupDir)
                .filter(file => file.startsWith('backup_') && file.endsWith('.db'))
                .map(file => ({
                    name: file,
                    path: path.join(this.backupDir, file),
                    time: fs.statSync(path.join(this.backupDir, file)).mtime
                }))
                .sort((a, b) => b.time - a.time);

            // Supprimer les backups en trop
            if (files.length > this.maxBackups) {
                const toDelete = files.slice(this.maxBackups);
                toDelete.forEach(file => {
                    fs.unlinkSync(file.path);
                    console.log(`🗑️ Ancien backup supprimé: ${file.name}`);
                });
            }
        } catch (error) {
            console.error('❌ Erreur lors du nettoyage:', error.message);
        }
    }

    // Restaurer un backup
    restoreBackup(backupName) {
        try {
            const backupPath = path.join(this.backupDir, backupName);
            
            if (!fs.existsSync(backupPath)) {
                console.error('❌ Backup introuvable:', backupName);
                return false;
            }

            // Créer un backup de sécurité avant restauration
            const securityBackup = `security_backup_${Date.now()}.db`;
            if (fs.existsSync(this.dbPath)) {
                fs.copyFileSync(this.dbPath, path.join(this.backupDir, securityBackup));
            }

            // Restaurer le backup
            fs.copyFileSync(backupPath, this.dbPath);
            console.log(`✅ Base de données restaurée depuis: ${backupName}`);
            console.log(`🔒 Backup de sécurité créé: ${securityBackup}`);
            
            return true;
        } catch (error) {
            console.error('❌ Erreur lors de la restauration:', error.message);
            return false;
        }
    }

    // Lister les backups disponibles
    listBackups() {
        try {
            const files = fs.readdirSync(this.backupDir)
                .filter(file => file.startsWith('backup_') && file.endsWith('.db'))
                .map(file => {
                    const filePath = path.join(this.backupDir, file);
                    const stats = fs.statSync(filePath);
                    return {
                        name: file,
                        date: stats.mtime.toLocaleString('fr-FR'),
                        size: (stats.size / 1024).toFixed(2) + ' KB'
                    };
                })
                .sort((a, b) => new Date(b.date) - new Date(a.date));

            return files;
        } catch (error) {
            console.error('❌ Erreur lors de la liste des backups:', error.message);
            return [];
        }
    }

    // Démarrer les backups automatiques (toutes les 6 heures)
    startAutoBackup() {
        console.log('🚀 Démarrage des backups automatiques (toutes les 6 heures)');
        
        // Backup immédiat
        this.createBackup();
        
        // Programmer les backups automatiques
        setInterval(() => {
            console.log('⏰ Backup automatique programmé...');
            this.createBackup();
        }, 6 * 60 * 60 * 1000); // 6 heures en millisecondes
    }
}

// Utilisation en ligne de commande
if (require.main === module) {
    const backup = new DatabaseBackup();
    const args = process.argv.slice(2);
    
    switch (args[0]) {
        case 'create':
            backup.createBackup();
            break;
            
        case 'restore':
            if (args[1]) {
                backup.restoreBackup(args[1]);
            } else {
                console.error('❌ Spécifiez le nom du backup à restaurer');
                console.log('Usage: node auto-backup.js restore backup_2024-01-01T12-00-00-000Z.db');
            }
            break;
            
        case 'list':
            const backups = backup.listBackups();
            console.log('\n📋 Backups disponibles:');
            backups.forEach((b, i) => {
                console.log(`${i + 1}. ${b.name} - ${b.date} (${b.size})`);
            });
            break;
            
        case 'auto':
            backup.startAutoBackup();
            break;
            
        default:
            console.log(`
🔄 Système de Backup Automatique

Commandes disponibles:
  create  - Créer un backup manuel
  restore - Restaurer un backup
  list    - Lister les backups
  auto    - Démarrer les backups automatiques

Exemples:
  node auto-backup.js create
  node auto-backup.js list
  node auto-backup.js restore backup_2024-01-01T12-00-00-000Z.db
  node auto-backup.js auto
            `);
    }
}

module.exports = DatabaseBackup; 