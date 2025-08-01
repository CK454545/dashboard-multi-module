const sqlite3 = require('sqlite3').verbose();
const fs = require('fs');
const path = require('path');

/**
 * 🔄 Système de Migration de Base de Données
 * 
 * Ce script gère les migrations de la base de données
 * lors de l'ajout de nouveaux modules (timer, team vs team, etc.)
 * sans perdre les données existantes des utilisateurs
 */

class DatabaseMigrator {
    constructor() {
        this.dbPath = path.join(__dirname, '..', 'database.db');
        this.migrationsPath = path.join(__dirname, '..', 'migrations');
        this.lockPath = path.join(__dirname, '..', 'migration.lock');
        this.backupPath = path.join(__dirname, '..', 'backups', `migration_backup_${Date.now()}.db`);
        this.db = null;
    }

    // Vérifier si une migration est déjà en cours
    isLocked() {
        if (fs.existsSync(this.lockPath)) {
            try {
                const lockData = JSON.parse(fs.readFileSync(this.lockPath, 'utf8'));
                const lockAge = Date.now() - lockData.timestamp;
                
                // Si le lock a plus de 10 minutes, on le considère comme obsolète
                if (lockAge > 10 * 60 * 1000) {
                    console.log('⚠️ Lock obsolète détecté, suppression...');
                    fs.unlinkSync(this.lockPath);
                    return false;
                }
                
                // Vérifier si le processus existe encore
                try {
                    process.kill(lockData.pid, 0);
                    console.log('🔒 Migration déjà en cours (PID:', lockData.pid, ')');
                    return true;
                } catch (err) {
                    // Le processus n'existe plus, supprimer le lock
                    console.log('⚠️ Processus inexistant, suppression du lock...');
                    fs.unlinkSync(this.lockPath);
                    return false;
                }
            } catch (error) {
                // Fichier de lock corrompu, on le supprime
                fs.unlinkSync(this.lockPath);
                return false;
            }
        }
        return false;
    }

    // Créer un fichier de verrouillage
    createLock() {
        const lockData = {
            pid: process.pid,
            timestamp: Date.now(),
            started: new Date().toISOString()
        };
        fs.writeFileSync(this.lockPath, JSON.stringify(lockData, null, 2));
        console.log('🔒 Verrouillage créé (PID:', process.pid, ')');
    }

    // Supprimer le fichier de verrouillage
    removeLock() {
        try {
            if (fs.existsSync(this.lockPath)) {
                fs.unlinkSync(this.lockPath);
                console.log('🔓 Verrouillage supprimé');
            }
        } catch (error) {
            console.error('⚠️ Erreur lors de la suppression du lock:', error.message);
        }
    }

    // Ouvrir la connexion à la base de données
    async openDatabase() {
        return new Promise((resolve, reject) => {
            this.db = new sqlite3.Database(this.dbPath, (err) => {
                if (err) {
                    reject(err);
                } else {
                    console.log('✅ Connexion à la base de données établie');
                    resolve();
                }
            });
        });
    }

    // Fermer la connexion
    async closeDatabase() {
        return new Promise((resolve, reject) => {
            this.db.close((err) => {
                if (err) {
                    reject(err);
                } else {
                    resolve();
                }
            });
        });
    }

    // Exécuter une requête SQL
    async runQuery(sql, params = []) {
        return new Promise((resolve, reject) => {
            this.db.run(sql, params, function(err) {
                if (err) {
                    reject(err);
                } else {
                    resolve(this);
                }
            });
        });
    }

    // Récupérer des données
    async all(sql, params = []) {
        return new Promise((resolve, reject) => {
            this.db.all(sql, params, (err, rows) => {
                if (err) {
                    reject(err);
                } else {
                    resolve(rows);
                }
            });
        });
    }

    // Créer la table des migrations si elle n'existe pas
    async createMigrationsTable() {
        const sql = `
            CREATE TABLE IF NOT EXISTS migrations (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT UNIQUE NOT NULL,
                applied_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        `;
        await this.runQuery(sql);
        console.log('✅ Table des migrations prête');
    }

    // Vérifier si une migration a déjà été appliquée
    async isMigrationApplied(name) {
        const result = await this.all('SELECT * FROM migrations WHERE name = ?', [name]);
        return result.length > 0;
    }

    // Marquer une migration comme appliquée
    async markMigrationAsApplied(name) {
        await this.runQuery('INSERT INTO migrations (name) VALUES (?)', [name]);
    }

    // Créer une sauvegarde complète de la base de données
    async createFullBackup() {
        console.log('💾 Création d\'une sauvegarde complète...');
        
        // Créer le dossier backups s'il n'existe pas
        const backupDir = path.dirname(this.backupPath);
        if (!fs.existsSync(backupDir)) {
            fs.mkdirSync(backupDir, { recursive: true });
        }
        
        // Copier la base de données
        fs.copyFileSync(this.dbPath, this.backupPath);
        console.log(`✅ Sauvegarde créée: ${this.backupPath}`);
        
        return this.backupPath;
    }

    // Migrations intégrées pour les nouveaux modules
    async applyBuiltInMigrations() {
        console.log('🔄 Application des migrations intégrées...');

        // Migration 0: Table wins pour compatibilité
        if (!await this.isMigrationApplied('add_wins_table')) {
            console.log('📦 Ajout de la table wins...');
            await this.runQuery(`
                CREATE TABLE IF NOT EXISTS wins (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    user_id TEXT NOT NULL,
                    value INTEGER DEFAULT 1,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (user_id) REFERENCES users(token) ON DELETE CASCADE
                )
            `);
            await this.runQuery('CREATE INDEX IF NOT EXISTS idx_wins_user_id ON wins(user_id)');
            await this.markMigrationAsApplied('add_wins_table');
            console.log('✅ Table wins ajoutée');
        }

        // Migration 1: Module Timer
        if (!await this.isMigrationApplied('add_timer_module')) {
            console.log('📦 Ajout du module Timer...');
            await this.runQuery(`
                CREATE TABLE IF NOT EXISTS timer_settings (
                    user_id TEXT PRIMARY KEY,
                    duration INTEGER DEFAULT 300,
                    title TEXT DEFAULT 'Timer',
                    color TEXT DEFAULT '#FF0000',
                    font_size INTEGER DEFAULT 48,
                    show_seconds BOOLEAN DEFAULT 1,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
                )
            `);
            await this.markMigrationAsApplied('add_timer_module');
            console.log('✅ Module Timer ajouté');
        }

        // Migration 2: Module Team vs Team
        if (!await this.isMigrationApplied('add_team_vs_team_module')) {
            console.log('📦 Ajout du module Team vs Team...');
            await this.runQuery(`
                CREATE TABLE IF NOT EXISTS team_vs_team (
                    user_id TEXT PRIMARY KEY,
                    team1_name TEXT DEFAULT 'Team 1',
                    team1_score INTEGER DEFAULT 0,
                    team1_color TEXT DEFAULT '#FF0000',
                    team2_name TEXT DEFAULT 'Team 2',
                    team2_score INTEGER DEFAULT 0,
                    team2_color TEXT DEFAULT '#0000FF',
                    font_size INTEGER DEFAULT 36,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
                )
            `);
            await this.markMigrationAsApplied('add_team_vs_team_module');
            console.log('✅ Module Team vs Team ajouté');
        }

        // Migration 3: Ajout de colonnes pour les webhooks Discord
        if (!await this.isMigrationApplied('add_webhook_columns')) {
            console.log('📦 Ajout du support webhooks...');
            
            // Vérifier si la colonne existe déjà
            const tableInfo = await this.all("PRAGMA table_info(users)");
            const hasWebhookColumn = tableInfo.some(col => col.name === 'webhook_url');
            
            if (!hasWebhookColumn) {
                await this.runQuery(`
                    ALTER TABLE users 
                    ADD COLUMN webhook_url TEXT
                `);
            }
            
            await this.markMigrationAsApplied('add_webhook_columns');
            console.log('✅ Support webhooks ajouté');
        }

        // Migration 4: Index pour améliorer les performances
        if (!await this.isMigrationApplied('add_performance_indexes')) {
            console.log('📦 Ajout des index de performance...');
            
            await this.runQuery('CREATE INDEX IF NOT EXISTS idx_users_token ON users(token)');
            await this.runQuery('CREATE INDEX IF NOT EXISTS idx_user_data_token_module ON user_data(token, module)');
            await this.runQuery('CREATE INDEX IF NOT EXISTS idx_user_styles_token ON user_styles(token)');
            
            await this.markMigrationAsApplied('add_performance_indexes');
            console.log('✅ Index de performance ajoutés');
        }
    }

    // Appliquer les migrations depuis des fichiers
    async applyFileMigrations() {
        // Créer le dossier migrations s'il n'existe pas
        if (!fs.existsSync(this.migrationsPath)) {
            fs.mkdirSync(this.migrationsPath, { recursive: true });
            return;
        }

        // Lire tous les fichiers de migration
        const files = fs.readdirSync(this.migrationsPath)
            .filter(f => f.endsWith('.sql'))
            .sort();

        for (const file of files) {
            const migrationName = path.basename(file, '.sql');
            
            if (!await this.isMigrationApplied(migrationName)) {
                console.log(`📄 Application de la migration: ${file}`);
                
                const sql = fs.readFileSync(path.join(this.migrationsPath, file), 'utf8');
                
                try {
                    // Exécuter chaque instruction SQL séparément
                    const statements = sql.split(';').filter(s => s.trim());
                    for (const statement of statements) {
                        await this.runQuery(statement);
                    }
                    
                    await this.markMigrationAsApplied(migrationName);
                    console.log(`✅ Migration ${file} appliquée`);
                } catch (error) {
                    console.error(`❌ Erreur lors de la migration ${file}:`, error);
                    throw error;
                }
            }
        }
    }

    // Nettoyer et optimiser la base de données
    async optimizeDatabase() {
        console.log('🔧 Optimisation de la base de données...');
        await this.runQuery('VACUUM');
        await this.runQuery('ANALYZE');
        console.log('✅ Base de données optimisée');
    }

    // Vérifier l'intégrité de la base de données
    async checkIntegrity() {
        console.log('🔍 Vérification de l\'intégrité...');
        const result = await this.all('PRAGMA integrity_check');
        
        if (result[0].integrity_check === 'ok') {
            console.log('✅ Intégrité de la base de données OK');
            return true;
        } else {
            console.error('❌ Problème d\'intégrité détecté:', result);
            return false;
        }
    }

    // Vérifier et protéger les données utilisateur existantes
    async protectUserData() {
        console.log('🛡️ Protection des données utilisateur existantes...');
        
        // Compter les utilisateurs existants
        const userCount = await this.all('SELECT COUNT(*) as count FROM users');
        const existingUsers = userCount[0].count;
        
        if (existingUsers > 0) {
            console.log(`✅ ${existingUsers} utilisateur(s) existant(s) détecté(s) - PROTECTION ACTIVÉE`);
            
            // Créer une sauvegarde complète des données utilisateur
            const userData = await this.all('SELECT * FROM users');
            const userDataBackup = userData.map(user => ({
                token: user.token,
                username: user.username,
                created_at: user.created_at,
                webhook_url: user.webhook_url || null
            }));
            
            // Sauvegarder aussi les données user_data et user_styles
            const userDataRows = await this.all('SELECT * FROM user_data');
            const userStylesRows = await this.all('SELECT * FROM user_styles');
            
            const completeBackup = {
                users: userDataBackup,
                user_data: userDataRows,
                user_styles: userStylesRows,
                timestamp: Date.now()
            };
            
            console.log(`💾 Sauvegarde complète créée: ${userDataBackup.length} utilisateurs, ${userDataRows.length} données, ${userStylesRows.length} styles`);
            return completeBackup;
        } else {
            console.log('ℹ️ Aucun utilisateur existant détecté');
            return { users: [], user_data: [], user_styles: [], timestamp: Date.now() };
        }
    }

    // Restaurer les données utilisateur si nécessaire
    async restoreUserData(userDataBackup) {
        if (!userDataBackup || userDataBackup.users.length === 0) {
            return;
        }
        
        console.log('🔄 Restauration des données utilisateur...');
        
        // Restaurer les utilisateurs
        for (const user of userDataBackup.users) {
            try {
                // Vérifier si l'utilisateur existe déjà
                const existingUser = await this.all('SELECT token FROM users WHERE token = ?', [user.token]);
                
                if (existingUser.length === 0) {
                    // Insérer l'utilisateur s'il n'existe pas
                    await this.runQuery(
                        'INSERT INTO users (token, username, created_at, webhook_url) VALUES (?, ?, ?, ?)',
                        [user.token, user.username, user.created_at, user.webhook_url]
                    );
                    console.log(`✅ Utilisateur ${user.username} restauré`);
                } else {
                    console.log(`ℹ️ Utilisateur ${user.username} existe déjà`);
                }
            } catch (error) {
                console.error(`⚠️ Erreur lors de la restauration de l'utilisateur ${user.username}:`, error.message);
            }
        }
        
        // Restaurer les données user_data
        for (const data of userDataBackup.user_data) {
            try {
                // Vérifier si la donnée existe déjà
                const existingData = await this.all(
                    'SELECT * FROM user_data WHERE token = ? AND module = ? AND key = ?',
                    [data.token, data.module, data.key]
                );
                
                if (existingData.length === 0) {
                    await this.runQuery(
                        'INSERT INTO user_data (token, module, key, value, updated_at) VALUES (?, ?, ?, ?, ?)',
                        [data.token, data.module, data.key, data.value, data.updated_at]
                    );
                    console.log(`✅ Donnée user_data restaurée pour ${data.token}`);
                }
            } catch (error) {
                console.error(`⚠️ Erreur lors de la restauration des données user_data:`, error.message);
            }
        }
        
        // Restaurer les styles user_styles
        for (const style of userDataBackup.user_styles) {
            try {
                // Vérifier si le style existe déjà
                const existingStyle = await this.all(
                    'SELECT * FROM user_styles WHERE token = ?',
                    [style.token]
                );
                
                if (existingStyle.length === 0) {
                    await this.runQuery(
                        'INSERT INTO user_styles (token, styles, updated_at) VALUES (?, ?, ?)',
                        [style.token, style.styles, style.updated_at]
                    );
                    console.log(`✅ Style user_styles restauré pour ${style.token}`);
                }
            } catch (error) {
                console.error(`⚠️ Erreur lors de la restauration des styles user_styles:`, error.message);
            }
        }
        
        console.log('✅ Restauration des données utilisateur terminée');
    }

    // Exécuter toutes les migrations
    async run() {
        try {
            // Vérifier si une migration est déjà en cours
            if (this.isLocked()) {
                console.log('❌ Migration déjà en cours, abandon');
                process.exit(0);
            }
            
            console.log('🚀 Démarrage des migrations de base de données');
            
            // Créer le verrou avant d'ouvrir la DB
            this.createLock();
            
            await this.openDatabase();
            await this.createMigrationsTable();
            
            // Vérifier l'intégrité avant les migrations
            const integrityOk = await this.checkIntegrity();
            if (!integrityOk) {
                console.error('❌ Intégrité compromise, arrêt des migrations');
                return;
            }
            
            // PROTECTION CRITIQUE : Créer une sauvegarde complète
            const backupPath = await this.createFullBackup();
            
            // PROTECTION CRITIQUE : Sauvegarder les données utilisateur existantes
            const userDataBackup = await this.protectUserData();
            
            try {
                // Appliquer les migrations
                await this.applyBuiltInMigrations();
                await this.applyFileMigrations();
                
                // RESTAURATION CRITIQUE : Restaurer les données utilisateur si nécessaire
                await this.restoreUserData(userDataBackup);
                
                // Optimiser après les migrations
                await this.optimizeDatabase();
                
                console.log('✅ Toutes les migrations ont été appliquées avec succès!');
                
            } catch (error) {
                console.error('❌ Erreur lors des migrations, restauration de la sauvegarde...');
                
                // Fermer la connexion actuelle
                await this.closeDatabase();
                
                // Restaurer la sauvegarde
                if (fs.existsSync(backupPath)) {
                    fs.copyFileSync(backupPath, this.dbPath);
                    console.log('✅ Base de données restaurée depuis la sauvegarde');
                }
                
                throw error;
            }
            
            await this.closeDatabase();
            
        } catch (error) {
            console.error('❌ Erreur lors des migrations:', error);
            process.exit(1);
        } finally {
            // Toujours supprimer le verrou
            this.removeLock();
        }
    }
}

// Exécuter si appelé directement
if (require.main === module) {
    const migrator = new DatabaseMigrator();
    migrator.run();
}

module.exports = DatabaseMigrator; 