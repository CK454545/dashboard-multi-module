const { Client, GatewayIntentBits, SlashCommandBuilder, EmbedBuilder, ActionRowBuilder, ButtonBuilder, ButtonStyle, ModalBuilder, TextInputBuilder, TextInputStyle, PermissionFlagsBits, AttachmentBuilder, StringSelectMenuBuilder } = require('discord.js');
const sqlite3 = require('sqlite3').verbose();
const crypto = require('crypto');
const fs = require('fs');
const path = require('path');
const { createCanvas, loadImage } = require('canvas');
const QRCode = require('qrcode');

// ==================== VÉRIFICATION DES PERMISSIONS DE LA BASE DE DONNÉES ====================
function checkDatabasePermissions() {
    // Toujours utiliser le chemin absolu depuis la racine du projet
    let dbPath = config.database.file;
    
    // Le chemin est déjà défini comme absolu dans la configuration
    logInfo('Vérification du chemin de la base de données', { 
        path: dbPath 
    });
    
    try {
        // Vérifier si le fichier existe
        if (!fs.existsSync(dbPath)) {
            logWarning('Base de données introuvable, création...', { path: dbPath });
            // Créer le fichier s'il n'existe pas
            fs.writeFileSync(dbPath, '');
            logSuccess('Base de données créée');
        }
        
        // Vérifier les permissions d'écriture
        fs.accessSync(dbPath, fs.constants.W_OK);
        logSuccess('Base de données accessible en écriture', { path: dbPath });
        return true;
    } catch (error) {
        logError('ERREUR CRITIQUE : Base de données non accessible en écriture', error, { path: dbPath });
        
        // CORRECTION AUTOMATIQUE DES PERMISSIONS
        logWarning('Tentative de correction automatique des permissions...', { path: dbPath });
        
        try {
            // Utiliser child_process pour exécuter les commandes sudo
            const { execSync } = require('child_process');
            
            // Corriger les permissions automatiquement
            execSync(`sudo chown ubuntu:ubuntu "${dbPath}"`, { stdio: 'pipe' });
            execSync(`sudo chmod 664 "${dbPath}"`, { stdio: 'pipe' });
            
            logSuccess('✅ Permissions corrigées automatiquement', { path: dbPath });
            
            // Vérifier à nouveau
            fs.accessSync(dbPath, fs.constants.W_OK);
            logSuccess('✅ Base de données maintenant accessible en écriture', { path: dbPath });
            return true;
            
        } catch (fixError) {
            logError('❌ Échec de la correction automatique des permissions', fixError, { path: dbPath });
            logError('Exécutez manuellement : sudo chmod 666 ' + dbPath);
            logError('Ou : sudo chown ubuntu:ubuntu ' + dbPath);
            logError('Puis : sudo chmod 664 ' + dbPath);
            process.exit(1);
        }
    }
}

// ==================== FONCTION DE DIAGNOSTIC TEMPORAIRE ====================
function testDatabaseAccess() {
    return new Promise((resolve, reject) => {
        try {
            // Utiliser directement le chemin absolu défini dans la configuration
            let dbPath = config.database.file;
            
            logInfo('Test d\'accès à la base de données', { path: dbPath });
            
            // Test de lecture
            fs.accessSync(dbPath, fs.constants.R_OK);
            logSuccess('✅ Lecture autorisée');
            
            // Test d'écriture
            fs.accessSync(dbPath, fs.constants.W_OK);
            logSuccess('✅ Écriture autorisée');
            
            // Test de connexion SQLite
            const testDb = new sqlite3.Database(dbPath, (err) => {
                if (err) {
                    logError('❌ Erreur connexion SQLite', err);
                    reject(err);
                } else {
                    logSuccess('✅ Connexion SQLite réussie');
                    testDb.close();
                    resolve(true);
                }
            });
        } catch (error) {
            logError('❌ Erreur test d\'accès', error);
            
            // CORRECTION AUTOMATIQUE SI POSSIBLE
            logWarning('Tentative de correction automatique...', { path: config.database.file });
            
            try {
                const { execSync } = require('child_process');
                // Utiliser directement le chemin absolu défini dans la configuration
                let dbPath = config.database.file;
                
                execSync(`sudo chown ubuntu:ubuntu "${dbPath}"`, { stdio: 'pipe' });
                execSync(`sudo chmod 664 "${dbPath}"`, { stdio: 'pipe' });
                
                logSuccess('✅ Permissions corrigées automatiquement');
                
                // Retester après correction
                fs.accessSync(dbPath, fs.constants.R_OK);
                fs.accessSync(dbPath, fs.constants.W_OK);
                logSuccess('✅ Accès maintenant autorisé');
                
                resolve(true);
            } catch (fixError) {
                logError('❌ Échec de la correction automatique', fixError);
                reject(error);
            }
        }
    });
}

// ==================== SYSTÈME DE LOGS AMÉLIORÉ ====================
function logInfo(message, data = null) {
    const timestamp = new Date().toISOString();
    console.log(`[${timestamp}] ℹ️  ${message}`);
    if (data) console.log(`[${timestamp}] 📊 Données:`, data);
}

function logSuccess(message, data = null) {
    const timestamp = new Date().toISOString();
    console.log(`[${timestamp}] ✅ ${message}`);
    if (data) console.log(`[${timestamp}] 📊 Données:`, data);
}

function logWarning(message, data = null) {
    const timestamp = new Date().toISOString();
    console.log(`[${timestamp}] ⚠️  ${message}`);
    if (data) console.log(`[${timestamp}] 📊 Données:`, data);
}

function logError(message, error = null, data = null) {
    const timestamp = new Date().toISOString();
    console.error(`[${timestamp}] ❌ ${message}`);
    if (error) {
        console.error(`[${timestamp}] 🔍 Erreur:`, error.message);
        console.error(`[${timestamp}] 📍 Stack:`, error.stack);
    }
    if (data) console.error(`[${timestamp}] 📊 Données contextuelles:`, data);
}

function logDatabase(operation, success, details = null) {
    const timestamp = new Date().toISOString();
    const status = success ? '✅' : '❌';
    console.log(`[${timestamp}] 🗄️  ${status} DB ${operation}`);
    if (details) console.log(`[${timestamp}] 📊 Détails:`, details);
}

// ==================== CHARGEMENT DE LA CONFIGURATION ====================
let config;
try {
    logInfo('Chargement de la configuration...');
    // Utiliser le chemin absolu depuis la racine du projet
    const projectRoot = path.resolve(__dirname, '..');
    const configPath = path.join(projectRoot, 'config', 'config.json');
    const configData = fs.readFileSync(configPath, 'utf8');
    config = JSON.parse(configData);
    logSuccess('Configuration chargée avec succès', {
        app_name: config.app.name,
        website_url: config.website.url,
        database_file: config.database.file
    });
} catch (error) {
    logError('ERREUR CRITIQUE : Impossible de charger config/config.json', error);
    logError('Veuillez vérifier que le fichier config/config.json existe et est valide');
    logError('Chemin attendu: /var/www/dashboard-multi-modules/config/config.json');
    process.exit(1);
}

// Configuration
const TOKEN = config.discord.token;
const WEBSITE_URL = config.website.url;
const CHAT_CHANNEL_ID = (config.discord && config.discord.chatChannelId) ? config.discord.chatChannelId : null;

// Correction du chemin de la base de données - toujours utiliser le chemin depuis la racine du projet
const projectRoot = path.resolve(__dirname, '..');
config.database.file = path.join(projectRoot, 'database', 'database.db');

logInfo('Configuration active', {
    token_configured: TOKEN !== 'VOTRE_TOKEN_BOT_DISCORD',
    website_url: WEBSITE_URL,
    database_path: config.database.file
});

// Vérifier les permissions de la base de données au démarrage
checkDatabasePermissions();

// Maps pour stocker temporairement les tokens et pseudos en mémoire
const userTokens = new Map();
const userPseudos = new Map();
const STREAMER_ROLE_ID = '1387780681748451403';

// Attribuer le rôle streameur à un utilisateur (avec diagnostics détaillés)
async function assignStreamerRole(guild, userId) {
    try {
        if (!guild) {
            logWarning('assignStreamerRole: guild manquant');
            return false;
        }

        // Vérifier que le rôle existe bien dans le serveur
        const role = guild.roles.cache.get(STREAMER_ROLE_ID) || await guild.roles.fetch(STREAMER_ROLE_ID).catch(() => null);
        if (!role) {
            logError('assignStreamerRole: rôle introuvable dans ce serveur', null, {
                roleId: STREAMER_ROLE_ID,
                guildId: guild.id,
                guildName: guild.name
            });
            return false;
        }

        // Vérifier les permissions/hierarchie du bot
        const me = guild.members.me || await guild.members.fetchMe();
        const hasManageRoles = me.permissions.has(PermissionFlagsBits.ManageRoles);
        const hierarchyOk = me.roles.highest.comparePositionTo(role) > 0;
        if (!hasManageRoles || !hierarchyOk) {
            logError('assignStreamerRole: permissions insuffisantes ou hiérarchie invalide', null, {
                hasManageRoles,
                botHighestRole: me.roles.highest?.id,
                botHighestRolePos: me.roles.highest?.position,
                targetRolePos: role.position,
                targetRoleId: STREAMER_ROLE_ID
            });
            return false;
        }

        // Récupérer le membre
        const member = await guild.members.fetch(userId).catch(() => null);
        if (!member) {
            logWarning('assignStreamerRole: membre introuvable', { userId, guildId: guild.id });
            return false;
        }

        // Si déjà présent, ne rien faire
        if (member.roles.cache.has(STREAMER_ROLE_ID)) {
            logInfo('Rôle streameur déjà présent', { userId, roleId: STREAMER_ROLE_ID });
            return true;
        }

        await member.roles.add(role, 'Auto-attribution MFA');
        logSuccess('Rôle streameur attribué', {
            userId,
            roleId: STREAMER_ROLE_ID,
            guildId: guild.id,
            guildName: guild.name
        });
        return true;
    } catch (error) {
        // Journaliser les détails utiles pour Missing Permissions (50013) ou autres
        const extra = {
            code: error?.code,
            httpStatus: error?.status,
            discordMessage: error?.rawError?.message,
            userId,
            roleId: STREAMER_ROLE_ID,
            guildId: guild?.id,
            guildName: guild?.name
        };
        logError('Erreur attribution rôle streameur', error, extra);
        return false;
    }
}

// Rôles autorisés pour chaque commande
const AUTHORIZED_ROLES = {
    // Commandes Admin uniquement
    'wait': ['1387780681748451407', '1387780681748451406', '1387780681748451405', '1397241042776096880'],
    'mfa': ['1387780681748451407', '1387780681748451406', '1387780681748451405', '1397241042776096880'],
    'start-mfa': ['1387780681748451407', '1387780681748451406', '1387780681748451405', '1397241042776096880'],
    'mfa-list': ['1387780681748451407', '1387780681748451406', '1387780681748451405', '1397241042776096880'],
    'voc': ['1387780681748451407', '1387780681748451406', '1387780681748451405', '1397241042776096880'],
    'end': ['1387780681748451407', '1387780681748451406', '1387780681748451405', '1397241042776096880'],
    'supptoken': ['1387780681748451407', '1387780681748451406', '1387780681748451405', '1397241042776096880'],
    
    // Commandes Admin + User
    'carte': ['1387780681748451407', '1387780681748451406', '1387780681748451405', '1397241042776096880', '1387780681748451403'],
    'infos': ['1387780681748451407', '1387780681748451406', '1387780681748451405', '1397241042776096880', '1387780681748451403']
};

// Fonction pour vérifier si l'utilisateur a un rôle autorisé pour une commande spécifique
function hasAuthorizedRole(member, commandName) {
    const allowedRoles = AUTHORIZED_ROLES[commandName] || [];
    return member.roles.cache.some(role => allowedRoles.includes(role.id));
}

// ==================== INITIALISATION DE LA BASE DE DONNÉES ====================
let db;
try {
    // Utiliser directement le chemin absolu défini dans la configuration
    let dbPath = config.database.file;
    
    logInfo('Connexion à la base de données...', { path: dbPath });
    db = new sqlite3.Database(dbPath, (err) => {
        if (err) {
            logError('Erreur connexion base de données', err, { path: dbPath });
        } else {
            logSuccess('Connexion à la base de données SQLite réussie');
            // Préparer les tables nécessaires pour le bridge de chat
            db.serialize(() => {
                db.run(`CREATE TABLE IF NOT EXISTS chat_links (
                    message_id TEXT PRIMARY KEY,
                    token TEXT NOT NULL,
                    created_at INTEGER NOT NULL
                )`);
                db.run(`CREATE TABLE IF NOT EXISTS chat_threads (
                    token TEXT PRIMARY KEY,
                    thread_id TEXT,
                    channel_id TEXT,
                    created_at INTEGER
                )`);
            });
            
            // Vérifier la structure de la base
            db.all("SELECT name FROM sqlite_master WHERE type='table'", (err, tables) => {
                if (err) {
                    logError('Erreur lors de la vérification des tables', err);
                } else {
                    logDatabase('Structure vérifiée', true, { 
                        tables: tables.map(t => t.name),
                        count: tables.length 
                    });
                }
            });
        }
    });
} catch (error) {
    logError('Erreur fatale lors de l\'initialisation de la base de données', error);
    process.exit(1);
}

// Initialisation du client Discord
const client = new Client({ 
    intents: [
        GatewayIntentBits.Guilds,
        GatewayIntentBits.GuildMessages,
        GatewayIntentBits.MessageContent, // Lire le contenu des messages
        GatewayIntentBits.GuildMembers // Requis pour fetch les membres et attribuer des rôles
    ] 
});

// Fonction pour générer un token unique
function generateToken() {
    return crypto.randomBytes(32).toString('hex');
}

// ==================== FONCTIONS DE BASE DE DONNÉES AMÉLIORÉES ====================
// Fonction pour vérifier si un utilisateur existe
function getUserToken(discordId) {
    return new Promise((resolve, reject) => {
        logInfo('Recherche utilisateur', { discord_id: discordId });
        const stmt = db.prepare('SELECT token, pseudo FROM users WHERE discord_id = ?');
        stmt.get([discordId], (err, row) => {
            if (err) {
                logError('Erreur lors de la recherche utilisateur', err, { discord_id: discordId });
                reject(err);
            } else {
                if (row) {
                    logSuccess('Utilisateur trouvé', { 
                        discord_id: discordId, 
                        pseudo: row.pseudo,
                        token_preview: row.token.substring(0, 8) + '...'
                    });
                    
                    // Mettre à jour les caches
                    userTokens.set(discordId, row.token);
                    userPseudos.set(discordId, row.pseudo);
                    
                    resolve(row.token);
                } else {
                    logInfo('Aucun utilisateur trouvé', { discord_id: discordId });
                    resolve(null);
                }
            }
        });
        stmt.finalize();
    });
}

// Fonction pour créer un nouvel utilisateur avec pseudo
function createUserWithPseudo(discordId, token, pseudo) {
    return new Promise((resolve, reject) => {
        logInfo('Création nouvel utilisateur', { 
            discord_id: discordId, 
            pseudo: pseudo,
            token_preview: token.substring(0, 8) + '...'
        });
        
        const stmt = db.prepare('INSERT INTO users (token, discord_id, pseudo, created_at) VALUES (?, ?, ?, CURRENT_TIMESTAMP)');
        stmt.run([token, discordId, pseudo], function(err) {
            if (err) {
                logError('Erreur SQL lors de la création utilisateur', err, {
                    discord_id: discordId,
                    pseudo: pseudo,
                    error_code: err.code,
                    error_message: err.message
                });
                
                // Si c'est une erreur de contrainte UNIQUE
                if (err.message && err.message.includes('UNIQUE constraint failed')) {
                    logWarning(`Discord ID ${discordId} existe déjà dans la base de données`);
                    
                    // Essayer de trouver l'utilisateur existant
                    db.get('SELECT pseudo, token FROM users WHERE discord_id = ?', [discordId], (err2, row) => {
                        if (!err2 && row) {
                            logInfo('Utilisateur existant trouvé', {
                                existing_pseudo: row.pseudo,
                                existing_token_preview: row.token.substring(0, 8) + '...'
                            });
                        }
                    });
                    
                    reject(new Error('DISCORD_ID_EXISTS'));
                } else {
                    reject(err);
                }
            } else {
                logSuccess(`Utilisateur créé avec succès`, {
                    pseudo: pseudo,
                    discord_id: discordId,
                    row_id: this.lastID
                });
                
                // Stocker dans les Maps temporaires
                userTokens.set(discordId, token);
                userPseudos.set(discordId, pseudo);
                logInfo('Token et pseudo stockés en mémoire cache');
                
                resolve(this.lastID);
            }
        });
        stmt.finalize();
    });
}

// Fonction pour initialiser les données par défaut d'un utilisateur
function initializeUserData(token) {
    return new Promise((resolve, reject) => {
        logInfo('Initialisation des données utilisateur', { token_preview: token.substring(0, 8) + '...' });
        
        const defaultData = [
            { module: 'wins', key: 'count', value: '0' },
            { module: 'wins', key: 'multiplier', value: '1' },
            { module: 'wins', key: 'multiplier_active', value: '1' }, 
            { module: 'timer', key: 'minutes', value: '5' },
            { module: 'timer', key: 'seconds', value: '0' },
            { module: 'timer', key: 'active', value: 'false' }
        ];
        
        // Styles par défaut pour wins et timer
        const defaultStyles = {
            wins: {
                'wins-color': '#ffffff',
                'wins-stroke-color': '#000000',
                'wins-stroke-width': '8',
                'wins-font-size': '65',
                'multi-color': '#ff0000',
                'multi-stroke-color': '#000000',
                'multi-stroke-width': '5',
                'multi-font-size': '48',
                'font': 'Luckiest Guy',
                'vertical-position': '-40',
                'text-shadow': 'true',
                'transparent-bg': 'true',
                'hide-multiplier': 'false'
            },
            timer: {
                'timer-color': '#ffffff',
                'timer-stroke-color': '#000000',
                'timer-stroke-width': '5',
                'timer-font-size': '72',
                'font': 'Orbitron',
                'vertical-position': '0',
                'text-shadow': 'true',
                'transparent-bg': 'true'
            }
        };

        const stmt = db.prepare('INSERT OR IGNORE INTO user_data (token, module, key, value) VALUES (?, ?, ?, ?)');
        
        let completed = 0;
        let errors = [];
        
        defaultData.forEach((data, index) => {
            stmt.run([token, data.module, data.key, data.value], (err) => {
                if (err) {
                    errors.push({ index, data, error: err.message });
                    logError(`Erreur initialisation donnée ${index}`, err, data);
                } else {
                    logInfo(`Donnée initialisée: ${data.module}.${data.key} = ${data.value}`);
                }
                
                completed++;
                if (completed === defaultData.length) {
                    if (errors.length > 0) {
                        logError('Erreurs lors de l\'initialisation', null, { errors });
                        reject(new Error(`${errors.length} erreurs lors de l'initialisation`));
                    } else {
                        logSuccess('Toutes les données utilisateur initialisées', { 
                            count: defaultData.length,
                            token_preview: token.substring(0, 8) + '...'
                        });
                        
                        // Maintenant initialiser les styles par défaut
                        // Combiner tous les styles en un seul objet JSON
                        const allStyles = {
                            wins: defaultStyles.wins,
                            timer: defaultStyles.timer
                        };
                        
                        const styleStmt = db.prepare('INSERT OR REPLACE INTO user_styles (token, styles) VALUES (?, ?)');
                        
                        // Insérer tous les styles en une seule fois
                        styleStmt.run([token, JSON.stringify(allStyles)], (err) => {
                            if (err) {
                                logError('Erreur lors de l\'initialisation des styles', err);
                            } else {
                                logSuccess('Styles par défaut initialisés avec succès', {
                                    modules: ['wins', 'timer'],
                                    token_preview: token.substring(0, 8) + '...'
                                });
                            }
                        });
                        
                        styleStmt.finalize();
                        resolve();
                    }
                }
            });
        });
        stmt.finalize();
    });
}



// Quand le bot est prêt
client.once('ready', async () => {
    logSuccess(`Bot connecté en tant que ${client.user.tag}`, {
        bot_id: client.user.id,
        guilds_count: client.guilds.cache.size
    });
    
    try {
        // Vérifier les commandes existantes au lieu de les supprimer
        logInfo('Vérification des commandes existantes...');
        
        // Vérifier les commandes globales existantes
        const globalCommands = await client.application.commands.fetch();
        logInfo(`Commandes globales existantes: ${globalCommands.size}`);
        
        // Vérifier les commandes de chaque serveur
        for (const guild of client.guilds.cache.values()) {
            try {
                const guildCommands = await guild.commands.fetch();
                logInfo(`Commandes existantes sur ${guild.name}: ${guildCommands.size}`);
            } catch (err) {
                logWarning(`Impossible de vérifier les commandes du serveur ${guild.name}`, err);
            }
        }
        
        // Attendre un peu pour que Discord synchronise
        await new Promise(resolve => setTimeout(resolve, 1000));
        
        // Enregistrer les nouvelles commandes slash
        const commands = [
            new SlashCommandBuilder()
                .setName('mfa')
                .setDescription('Lance une session de création MFA ouverte à tous (Admin uniquement)'),
            
            new SlashCommandBuilder()
                .setName('start-mfa')
                .setDescription('Démarre ton onboarding My Full Agency personnel (Admin uniquement)'),
            
            new SlashCommandBuilder()
                .setName('mfa-list')
                .setDescription('Affiche la liste de tous les comptes MFA créés'),
            
            new SlashCommandBuilder()
                .setName('voc')
                .setDescription('Envoie un message pour rejoindre le vocal'),
            
            new SlashCommandBuilder()
                .setName('end')
                .setDescription('Clôture une demande et confirme qu\'un agent a répondu'),
            
            new SlashCommandBuilder()
                .setName('supptoken')
                .setDescription('Supprime un token MFA de la mémoire du bot')
                .addStringOption(option =>
                    option.setName('token')
                        .setDescription('Le token à supprimer (optionnel, laisse vide pour supprimer ton propre token)')
                        .setRequired(false)),
            
            new SlashCommandBuilder()
                .setName('carte')
                .setDescription('Génère ta carte MFA dynamique avec QR code'),
            
            new SlashCommandBuilder()
                .setName('wait')
                .setDescription('Informe qu\'un agent va bientôt répondre'),
            
            new SlashCommandBuilder()
                .setName('infos')
                .setDescription('Affiche les informations de ton compte MFA')
        ];
        
        logInfo('Enregistrement des nouvelles commandes...', { count: commands.length });
        
        // Enregistrer les commandes seulement si elles n'existent pas déjà
        logInfo('Vérification et enregistrement des commandes si nécessaire...');
        
        for (const guild of client.guilds.cache.values()) {
            try {
                const existingCommands = await guild.commands.fetch();
                if (existingCommands.size === 0) {
                    await guild.commands.set(commands);
                    logSuccess(`Commandes enregistrées dans le serveur ${guild.name}`);
                } else {
                    logInfo(`Commandes déjà présentes dans ${guild.name} (${existingCommands.size})`);
                }
            } catch (err) {
                logWarning(`Impossible d'enregistrer les commandes dans ${guild.name}`, err);
            }
        }
        
        // Lister toutes les commandes enregistrées
        logInfo('Commandes disponibles :');
        for (const cmd of commands) {
            logInfo(`  /${cmd.name} - ${cmd.description}`);
        }
        
        logSuccess('Toutes les commandes enregistrées et forcées à l\'affichage');
        
        // FORCER l'enregistrement des commandes (même si elles existent déjà)
        logInfo('FORÇAGE de l\'enregistrement des commandes...');
        
        for (const guild of client.guilds.cache.values()) {
            try {
                // Forcer l'enregistrement même si les commandes existent
                await guild.commands.set(commands);
                logSuccess(`Commandes FORCÉES dans le serveur ${guild.name}`);
                
                // Attendre un peu pour que Discord synchronise
                await new Promise(resolve => setTimeout(resolve, 1000));
                
                // Vérifier que les commandes sont bien enregistrées
                const verifiedCommands = await guild.commands.fetch();
                logInfo(`Commandes vérifiées dans ${guild.name}: ${verifiedCommands.size}`);
                
            } catch (err) {
                logWarning(`Impossible d'enregistrer les commandes dans ${guild.name}`, err);
            }
        }
        
    } catch (error) {
        logError('Erreur lors de l\'enregistrement des commandes', error);
    }

    // Initialiser le bridge chat Dashboard <-> Discord si configuré
    try {
        if (!CHAT_CHANNEL_ID) {
            logWarning('Bridge chat désactivé: config.discord.chatChannelId manquant');
        } else {
            const channel = await client.channels.fetch(CHAT_CHANNEL_ID).catch(() => null);
            if (!channel) {
                logWarning('Bridge chat: salon introuvable, vérifiez chatChannelId dans config');
            } else {
                logSuccess('Bridge chat activé', { channel: channel.name, channel_id: channel.id });
                startChatBridge(channel);
            }
        }
    } catch (e) {
        logError('Erreur initialisation bridge chat', e);
    }
});

// Gestion des interactions
client.on('interactionCreate', async interaction => {
    const interactionData = {
        type: interaction.type,
        user: interaction.user.tag,
        user_id: interaction.user.id,
        guild: interaction.guild?.name || 'DM',
        channel: interaction.channel?.name || 'DM'
    };
    
    try {
        if (interaction.isCommand()) {
            logInfo(`Commande reçue: /${interaction.commandName}`, interactionData);
            
            if (interaction.commandName === 'mfa') {
                await handleMfaCommand(interaction);
            } else if (interaction.commandName === 'start-mfa') {
                await handleStartMfaCommand(interaction);
            } else if (interaction.commandName === 'mfa-list') {
                await handleMfaListCommand(interaction);
            } else if (interaction.commandName === 'voc') {
                await handleVocCommand(interaction);
            } else if (interaction.commandName === 'end') {
                await handleEndCommand(interaction);
            } else if (interaction.commandName === 'supptoken') {
                await handleSuppTokenCommand(interaction);
            } else if (interaction.commandName === 'carte') {
                await handleCarteCommand(interaction);
            } else if (interaction.commandName === 'wait') {
                await handleWaitCommand(interaction);
            } else if (interaction.commandName === 'infos') {
                await handleInfosCommand(interaction);
            }
        } else if (interaction.isButton()) {
            logInfo(`Bouton cliqué: ${interaction.customId}`, interactionData);
            
            if (interaction.customId === 'voir_jeux') {
                // Liste des salons de jeux
                const jeux = [
                    '<#1399908748557811742>',
                    '<#1400268845830377552>',
                    '<#1400269015850422292>',
                    '<#1400269091721183397>',
                    '<#1400269237427109898>',
                    '<#1400293047358587044>'
                ];
                
                await interaction.reply({
                    content: `# 🎮 **Jeux Interactifs Disponibles :**\n\n${jeux.join('\n')}\n\n_Clique sur un salon pour voir les détails du jeu !_`,
                    ephemeral: true
                });
            } else if (interaction.customId.startsWith('generer_carte_')) {
                // Extraire l'ID Discord du customId
                const targetUserId = interaction.customId.replace('generer_carte_', '');
                
                // Vérifier que c'est bien l'utilisateur concerné qui clique
                if (interaction.user.id !== targetUserId) {
                    await interaction.reply({
                        content: '❌ **Ce bouton n\'est pas pour toi !**',
                        ephemeral: true
                    });
                    return;
                }
                
                await interaction.deferReply();
                
                try {
                    // Récupérer les informations de l'utilisateur
                    const userInfo = await new Promise((resolve, reject) => {
                        db.get('SELECT token, pseudo FROM users WHERE discord_id = ?', [targetUserId], (err, row) => {
                            if (err) reject(err);
                            else resolve(row);
                        });
                    });
                    
                    if (!userInfo) {
                        await interaction.editReply({
                            content: '❌ **Aucun compte MFA trouvé !**'
                        });
                        return;
                    }
                    
                    const dashboardURL = `${WEBSITE_URL}/dashboard.php?token=${userInfo.token}`;
                    
                    // Génération du QR Code
                    const qrDataURL = await QRCode.toDataURL(dashboardURL, { 
                        width: 300, 
                        margin: 2,
                        color: {
                            dark: '#000000',
                            light: '#FFFFFF'
                        }
                    });
                    const qrImg = await loadImage(qrDataURL);
                    
                    // Création Canvas Carte
                    const width = 1000;
                    const height = 500;
                    const canvas = createCanvas(width, height);
                    const ctx = canvas.getContext('2d');
                    
                    // Fond dégradé
                    const gradient = ctx.createLinearGradient(0, 0, width, height);
                    gradient.addColorStop(0, '#0f2027');
                    gradient.addColorStop(0.5, '#203a43');
                    gradient.addColorStop(1, '#2c5364');
                    ctx.fillStyle = gradient;
                    ctx.fillRect(0, 0, width, height);
                    
                    // Encadrement lumineux
                    ctx.strokeStyle = '#FFD700';
                    ctx.lineWidth = 8;
                    ctx.strokeRect(20, 20, width - 40, height - 40);
                    
                    // Texte principal
                    ctx.fillStyle = '#FFFFFF';
                    ctx.font = 'bold 48px Sans';
                    ctx.fillText('MY FULL AGENCY', 50, 80);
                    
                    ctx.font = '32px Sans';
                    ctx.fillStyle = '#00FFAA';
                    ctx.fillText(`🎯 Pseudo TikTok : ${userInfo.pseudo}`, 50, 150);
                    
                    ctx.font = '22px Sans';
                    ctx.fillStyle = '#AAAAAA';
                    ctx.fillText('Scanne ce QR Code pour accéder à ton Dashboard MFA', 50, 200);
                    
                    // Footer avec date
                    ctx.font = '18px Sans';
                    ctx.fillStyle = '#888888';
                    const date = new Date().toLocaleDateString('fr-FR');
                    ctx.fillText(`Généré le ${date} | ${config.app.name}`, 50, height - 50);
                    
                    // Intégration QR Code
                    ctx.drawImage(qrImg, width - 320, height / 2 - 150, 300, 300);
                    
                    // Envoi en pièce jointe
                    const buffer = canvas.toBuffer();
                    const attachment = new AttachmentBuilder(buffer, { name: `Carte_MFA_${userInfo.pseudo}.png` });
                    
                    await interaction.editReply({
                        content: `# 🪪 **Voici ta Carte MFA dynamique !**

💾 **IMPORTANT : Enregistre cette carte dans ta galerie pour ne pas perdre ton token !**

_Scanne le QR Code ou clique [ici](${dashboardURL}) pour accéder à ton dashboard._`,
                        files: [attachment]
                    });
                    
                    logSuccess('Carte MFA générée via bouton', { 
                        user: interaction.user.tag,
                        pseudo: userInfo.pseudo
                    });
                    
                } catch (error) {
                    logError('Erreur lors de la génération de la carte via bouton', error);
                    await interaction.editReply({
                        content: '❌ **Erreur lors de la génération de la carte. Réessaye plus tard.**'
                    });
                }
            }
        }
    } catch (error) {
        logError('Erreur lors de l\'interaction', error, interactionData);
        
        if (!interaction.replied && !interaction.deferred) {
            try {
                await interaction.reply({
                    content: '❌ Une erreur est survenue. Veuillez réessayer plus tard.',
                    ephemeral: true
                });
            } catch (replyError) {
                logError('Erreur lors de la réponse d\'erreur', replyError);
            }
        }
    }
});







// =========================
// COMMANDE /MFA (Admin lance - tous peuvent répondre)  
// =========================
async function handleMfaCommand(interaction) {
    await interaction.deferReply({ ephemeral: false });

    const user = interaction.user;
    const salon = interaction.channel;

    logInfo('Traitement commande /mfa', { user: user.tag });

    // Vérifier si l'utilisateur a un rôle autorisé (admin uniquement)
    if (!hasAuthorizedRole(interaction.member, 'mfa')) {
        logWarning('Utilisateur sans rôle autorisé tente la commande MFA', { user: user.tag });
        await interaction.editReply({
            content: '❌ **Tu n\'as pas la permission d\'utiliser cette commande.**\n_Seuls les administrateurs peuvent lancer une session MFA._'
        });
        return;
    }

    // Message initial
    const initMessage = await interaction.editReply({ content: "# 🚀 **Initialisation MFA en cours...**" });
    const initTimestamp = Date.now();

    // Message de bienvenue adapté pour tous les utilisateurs
    const intro = await salon.send(
`# 👋 **BIENVENUE DANS LA 🌟 MY FULL AGENCY 🌟**
# 🎯 **Création de compte MFA**

Avant de démarrer, **écris ton pseudo TikTok avec le @** 
> Exemple : \`@ZeMask\`

⚠️ **Important** : Tu as 24h pour répondre avec ton @pseudo et créer ton token !`
    );

    // Collecteur pour TOUS les messages du salon
    const filter = m => !m.author.bot && m.content.startsWith('@');
    
    try {
        const collected = await salon.awaitMessages({ filter, max: 1, time: 86400000 }); // 24 heures
        if (!collected.size) {
            await salon.send("⏱️ **Temps écoulé (24h) ! Un admin doit relancer la commande `/mfa`.**");
            return;
        }
        
        let messageCollected = collected.first();
        let respondingUser = messageCollected.author;
        let pseudo = messageCollected.content;

        // Vérifier si l'utilisateur qui répond a déjà un token
        let respondingUserToken = await getUserToken(respondingUser.id);
        if (respondingUserToken) {
            await salon.send(`❌ **<@${respondingUser.id}>, tu as déjà un token actif ! Utilise \`/supptoken\` pour le supprimer avant d'en créer un nouveau.**`);
            // Relancer l'attente pour un autre utilisateur
            const retry = await salon.awaitMessages({ filter, max: 1, time: 86400000 }); // 24 heures
            if (!retry.size) {
                await salon.send("⏱️ **Temps écoulé ! Un admin doit relancer la commande `/mfa`.**");
                return;
            }
            // Traiter le nouvel utilisateur
            messageCollected = retry.first();
            respondingUser = messageCollected.author;
            pseudo = messageCollected.content;
            const newUserToken = await getUserToken(respondingUser.id);
            if (newUserToken) {
                await salon.send(`❌ **<@${respondingUser.id}>, tu as aussi déjà un token ! La procédure est annulée.**`);
                return;
            }
        }

        let pseudoConfirmed = false;
        let finalPseudo = pseudo;
        
        // Filtre pour les réponses de l'utilisateur qui a donné son pseudo
        const userFilter = m => m.author.id === respondingUser.id;
        
        while (!pseudoConfirmed) {
            await salon.send(`# 🤖 **<@${respondingUser.id}>, ${finalPseudo} est bien ton pseudo TikTok ? (oui/non)**`);

            const confirm = await salon.awaitMessages({ filter: userFilter, max: 1, time: 86400000 }); // 24 heures
            if (!confirm.size) {
                await salon.send("⏱️ **Temps écoulé ! Un admin doit relancer la commande `/mfa`.**");
                return;
            }
            
            if (confirm.first().content.toLowerCase() === 'oui') {
                pseudoConfirmed = true;
            } else {
                await salon.send(`# 🔄 **Pas de problème <@${respondingUser.id}> ! Réécris ton pseudo TikTok avec le @**`);
                const newPseudo = await salon.awaitMessages({ filter: userFilter, max: 1, time: 86400000 }); // 24 heures
                if (!newPseudo.size) {
                    await salon.send("⏱️ **Temps écoulé ! Un admin doit relancer la commande `/mfa`.**");
                    return;
                }
                finalPseudo = newPseudo.first().content;
            }
        }

        // Animation style "console/terminal"
        const steps = [
            "# 🖥️ **Initialisation du Module MFA...**",
            "# 🔌 **Connexion au serveur sécurisé...** `█▒▒▒▒▒▒▒▒▒▒ 20%`",
            "# 🛰️ **Vérification de l'environnement Discord...** `████▒▒▒▒▒▒ 40%`",
            "# 📱 **Synchronisation TikTok...** `██████▒▒▒▒ 60%`",
            "# 🔑 **Génération du Token sécurisé...** `████████▒▒ 80%`",
            "# 📦 **Préparation de ton espace MFA...** `██████████ 100%`",
            "# ✅ **Connexion validée !**"
        ];

        for (const step of steps) {
            await salon.send(step);
            await new Promise(res => setTimeout(res, 1200));
        }

        // Attendre un peu avant de continuer
        await new Promise(res => setTimeout(res, 2000));

        // Créer le token pour l'utilisateur qui a répondu
        const newToken = generateToken();
        const cleanPseudo = finalPseudo.replace('@', '');
        
        try {
            await createUserWithPseudo(respondingUser.id, newToken, cleanPseudo);
            await initializeUserData(newToken);
            
            // Enregistrer l'ID Discord avec le token pour la traçabilité
            logSuccess(`Token créé via /mfa`, { 
                created_by: user.tag,
                for_user: respondingUser.tag,
                discord_id: respondingUser.id,
                pseudo: cleanPseudo,
                token_preview: newToken.substring(0, 8) + '...'
            });

            // Nettoyage COMPLET - supprimer tous les messages du processus
            logInfo('🧽 Début du nettoyage COMPLET du salon...');
            try {
                // Supprimer le message d'initialisation
                try {
                    await initMessage.delete();
                    logSuccess('✅ Message "Initialisation MFA en cours..." supprimé');
                } catch (initDeleteError) {
                    logWarning('⚠️ Impossible de supprimer le message d\'initialisation', initDeleteError);
                }
                
                // Supprimer tous les autres messages du processus
                const messages = await salon.messages.fetch({ limit: 100 });
                const messagesToDelete = messages.filter(msg => {
                    return msg.createdTimestamp >= initTimestamp;
                });
                
                if (messagesToDelete.size > 0) {
                    await salon.bulkDelete(messagesToDelete, true).catch(err => {
                        logWarning('⚠️ Impossible de supprimer certains messages', err);
                    });
                    logSuccess('✅ Messages de conversation nettoyés');
                }
            } catch (cleanupError) {
                logWarning('⚠️ Erreur lors du nettoyage', cleanupError);
            }

            const dashboardURL = `${WEBSITE_URL}/dashboard.php?token=${newToken}`;
            const winsURL = `${WEBSITE_URL}/?module=wins&token=${newToken}&control=true`;
            const timerURL = `${WEBSITE_URL}/modules/timer.php?token=${newToken}&control=true`;
            const teamBattleURL = `${WEBSITE_URL}/modules/team-battle.php?token=${newToken}&control=true`;

            // Créer les boutons
            const jeuxButton = new ButtonBuilder()
                .setCustomId('voir_jeux')
                .setLabel('🎮 Voir les jeux interactifs')
                .setStyle(ButtonStyle.Primary);

            const carteButton = new ButtonBuilder()
                .setCustomId(`generer_carte_${respondingUser.id}`)
                .setLabel('🪪 Générer ma carte MFA')
                .setStyle(ButtonStyle.Success);

            const row = new ActionRowBuilder()
                .addComponents(jeuxButton, carteButton);

            // Message final épinglé
            const finalMessage = await salon.send({
                content: `# ⚙️ **INFOS DE <@${respondingUser.id}> !**

_Ici, tu trouveras toutes les infos essentielles pour ton aventure TikTok !_  

## 📱 **TikTok :** <https://www.tiktok.com/${finalPseudo.replace('@','@')}>
## 🔑 **TON TOKEN :**
\`\`\`${newToken}\`\`\`
# ⚠️ _**Utilise ce token avec précaution**_ ⚠️

---

## 📌 **Liens utiles :**
- 💻 **[Accéder au Dashboard](${dashboardURL})**
- 🏆 **[Ouvrir le panneau Wins](${winsURL})**
- ⏱️ **[Timer Interactif](${timerURL})**
- 🆚 **[Team Battle](${teamBattleURL})**

---

## ℹ️ **Conseils de base :**
- ⏱️ Vise au moins **1h de live** pour que l'algorithme TikTok te mette en avant.
- 📆 **Sois régulier** : même heure chaque jour = bonus visibilité.
- 🧘‍♂️ **Sois patient** : les performances arrivent avec la constance.
- 🤝 **Si tu as une question**, pose-la dans ton salon, un agent ou un staff te répondra.

---

## ✅ **Étapes à suivre :**
- 🔓 **Installe tout** dans <#1390122226015273133>
- 🎮 **Choisis tes jeux interactifs** (clique sur le bouton ci-dessous)
- 📖 **Lis attentivement** <#1390781917707763914> pour respecter les règles TikTok
- 💡 **Consulte la rubrique "bon à savoir"** dans <#1391781161319010394>
- 🧠 **Forme-toi** : formation gratuite **chaque dimanche à 20h** 🎓`,
                components: [row]
            });
            
            await finalMessage.pin();
            logSuccess('Message final épinglé avec succès');

            logSuccess(`Token créé via /mfa pour ${finalPseudo}`, { 
                user: user.tag,
                token_preview: newToken.substring(0, 8) + '...'
            });

            // Attribution automatique du rôle streameur
            await assignStreamerRole(interaction.guild, respondingUser.id);

        } catch (dbError) {
            logError('Erreur lors de la création du compte', dbError);
            await salon.send(`❌ **Erreur lors de la création du compte pour <@${respondingUser.id}>. Veuillez réessayer.**`);
        }

    } catch (error) {
        logError('Erreur dans /mfa', error);
        await salon.send('❌ Une erreur est survenue. Veuillez réessayer.');
    }
}

// =========================
// COMMANDE /START-MFA (Admin only - création personnelle)
// =========================
async function handleStartMfaCommand(interaction) {
    await interaction.deferReply({ ephemeral: false });

    const user = interaction.user;
    const salon = interaction.channel;

    logInfo('Traitement commande /start-mfa', { user: user.tag });

    // Vérifier si l'utilisateur a déjà un token
    const existingToken = await getUserToken(user.id);
    if (existingToken) {
        const dashboardURL = `${WEBSITE_URL}/dashboard.php?token=${existingToken}`;
        const winsURL = `${WEBSITE_URL}/?module=wins&token=${existingToken}&control=true`;
        const timerURL = `${WEBSITE_URL}/modules/timer.php?token=${existingToken}&control=true`;
        const teamBattleURL = `${WEBSITE_URL}/modules/team-battle.php?token=${existingToken}&control=true`;
        
        await interaction.editReply({
            content: `# ✅ **Tu as déjà un token actif !**\n\n## 📌 **Liens utiles :**\n- 💻 **[Accéder au Dashboard](${dashboardURL})**\n- 🏆 **[Ouvrir le panneau Wins](${winsURL})**\n- ⏱️ **[Timer Interactif](${timerURL})**\n- 🆚 **[Team Battle](${teamBattleURL})**\n\n⚠️ _Utilise ce token avec précaution_`
        });
        return;
    }

    // Vérifier si l'utilisateur a un rôle autorisé
    if (!hasAuthorizedRole(interaction.member, 'start-mfa')) {
        logWarning('Utilisateur sans rôle autorisé tente la commande START-MFA', { user: user.tag });
        await interaction.editReply({
            content: '❌ **Tu n\'as pas la permission d\'utiliser cette commande.**\n_Contacte un administrateur si tu penses que c\'est une erreur._'
        });
        return;
    }

    // Génère un token unique
    const token = generateToken();

    // Message initial de bienvenue
    const initMessage = await interaction.editReply({ content: "# 🚀 **Initialisation MFA en cours...**" });
    const initTimestamp = Date.now();

    // Demande du pseudo TikTok
    const intro = await salon.send(
`# 👋 **BONJOUR ET BIENVENUE DANS LA 🌟 MY FULL AGENCY 🌟**
# 🏠 **Tu es dans ton salon privé !**
Avant de démarrer ton aventure, **écris ton pseudo TikTok avec le @** 🎯
> Exemple : \`@ZeMask\``
    );

    // Collecteur de message utilisateur
    const filter = m => m.author.id === user.id;
    
    try {
        const collected = await salon.awaitMessages({ filter, max: 1, time: 86400000 }); // 24 heures
        if (!collected.size) {
            await salon.send("⏱️ **Temps écoulé ! Relance la commande `/start-mfa`.**");
            return;
        }
        
        const pseudo = collected.first().content;

        let pseudoConfirmed = false;
        let finalPseudo = pseudo;
        
        while (!pseudoConfirmed) {
            await salon.send(`# 🤖 **${finalPseudo} est bien ton pseudo TikTok ? (oui/non)**`);

            const confirm = await salon.awaitMessages({ filter, max: 1, time: 86400000 }); // 24 heures
            if (!confirm.size) {
                await salon.send("⏱️ **Temps écoulé ! Relance la commande `/start-mfa`.**");
                return;
            }
            
            if (confirm.first().content.toLowerCase() === 'oui') {
                pseudoConfirmed = true;
            } else {
                await salon.send("# 🔄 **Pas de problème ! Réécris ton pseudo TikTok avec le @**");
                const newPseudo = await salon.awaitMessages({ filter, max: 1, time: 86400000 }); // 24 heures
                if (!newPseudo.size) {
                    await salon.send("⏱️ **Temps écoulé ! Relance la commande `/start-mfa`.**");
                    return;
                }
                finalPseudo = newPseudo.first().content;
            }
        }

        // Animation style "console/terminal"
        const steps = [
            "# 🖥️ **Initialisation du Module MFA...**",
            "# 🔌 **Connexion au serveur sécurisé...** `█▒▒▒▒▒▒▒▒▒▒ 20%`",
            "# 🛰️ **Vérification de l'environnement Discord...** `████▒▒▒▒▒▒ 40%`",
            "# 📱 **Synchronisation TikTok...** `██████▒▒▒▒ 60%`",
            "# 🔑 **Génération du Token sécurisé...** `████████▒▒ 80%`",
            "# 📦 **Préparation de ton espace MFA...** `██████████ 100%`",
            "# ✅ **Connexion validée !**"
        ];

        for (const step of steps) {
            await salon.send(step);
            await new Promise(res => setTimeout(res, 1200));
        }

        // Attendre un peu avant de continuer
        await new Promise(res => setTimeout(res, 2000));

        // Créer l'utilisateur dans la base de données
        const cleanPseudo = finalPseudo.replace('@', '');
        
        try {
            await createUserWithPseudo(user.id, token, cleanPseudo);
            await initializeUserData(token);
            
            logSuccess('✅ Utilisateur créé avec succès');

            // Nettoyage COMPLET
            logInfo('🧽 Début du nettoyage COMPLET du salon...');
            try {
                // Supprimer le message d'initialisation
                try {
                    await initMessage.delete();
                    logSuccess('✅ Message "Initialisation MFA en cours..." supprimé');
                } catch (initDeleteError) {
                    logWarning('⚠️ Impossible de supprimer le message d\'initialisation', initDeleteError);
                }
                
                // Supprimer tous les autres messages du processus
                const messages = await salon.messages.fetch({ limit: 100 });
                const messagesToDelete = messages.filter(msg => {
                    return msg.createdTimestamp >= initTimestamp;
                });
                
                if (messagesToDelete.size > 0) {
                    await salon.bulkDelete(messagesToDelete, true).catch(err => {
                        logWarning('⚠️ Impossible de supprimer certains messages', err);
                    });
                    logSuccess('✅ Messages de conversation nettoyés');
                }
            } catch (cleanupError) {
                logWarning('⚠️ Erreur lors du nettoyage', cleanupError);
            }

            const dashboardURL = `${WEBSITE_URL}/dashboard.php?token=${token}`;
            const winsURL = `${WEBSITE_URL}/?module=wins&token=${token}&control=true`;
            const timerURL = `${WEBSITE_URL}/modules/timer.php?token=${token}&control=true`;
            const teamBattleURL = `${WEBSITE_URL}/modules/team-battle.php?token=${token}&control=true`;

            // Créer les boutons
            const jeuxButton = new ButtonBuilder()
                .setCustomId('voir_jeux')
                .setLabel('🎮 Voir les jeux interactifs')
                .setStyle(ButtonStyle.Primary);

            const carteButton = new ButtonBuilder()
                .setCustomId(`generer_carte_${user.id}`)
                .setLabel('🪪 Générer ma carte MFA')
                .setStyle(ButtonStyle.Success);

            const row = new ActionRowBuilder()
                .addComponents(jeuxButton, carteButton);

            // Message final épinglé
            const finalMessage = await salon.send({
                content: `# ⚙️ **INFOS DE <@${user.id}> !**

_Ici, tu trouveras toutes les infos essentielles pour ton aventure TikTok !_  

## 📱 **TikTok :** <https://www.tiktok.com/${finalPseudo.replace('@','@')}>
## 🔑 **TON TOKEN :**
\`\`\`${token}\`\`\`
# ⚠️ _**Utilise ce token avec précaution**_ ⚠️

---

## 📌 **Liens utiles :**
- 💻 **[Accéder au Dashboard](${dashboardURL})**
- 🏆 **[Ouvrir le panneau Wins](${winsURL})**
- ⏱️ **[Timer Interactif](${timerURL})**
- 🆚 **[Team Battle](${teamBattleURL})**

---

## ℹ️ **Conseils de base :**
- ⏱️ Vise au moins **1h de live** pour que l'algorithme TikTok te mette en avant.
- 📆 **Sois régulier** : même heure chaque jour = bonus visibilité.
- 🧘‍♂️ **Sois patient** : les performances arrivent avec la constance.
- 🤝 **Si tu as une question**, pose-la dans ton salon, un agent ou un staff te répondra.

---

## ✅ **Étapes à suivre :**
- 🔓 **Installe tout** dans <#1390122226015273133>
- 🎮 **Choisis tes jeux interactifs** (clique sur le bouton ci-dessous)
- 📖 **Lis attentivement** <#1390781917707763914> pour respecter les règles TikTok
- 💡 **Consulte la rubrique "bon à savoir"** dans <#1391781161319010394>
- 🧠 **Forme-toi** : formation gratuite **chaque dimanche à 20h** 🎓`,
                components: [row]
            });
            
            await finalMessage.pin();
            logSuccess('Message final épinglé avec succès');

            logSuccess(`Token créé via /start-mfa pour ${finalPseudo}`, { 
                user: user.tag,
                token_preview: token.substring(0, 8) + '...'
            });

            // Attribution automatique du rôle streameur
            await assignStreamerRole(interaction.guild, user.id);

        } catch (dbError) {
            logError('Erreur lors de la création du compte', dbError);
            await salon.send('❌ Erreur lors de la création de votre compte. Veuillez réessayer.');
            throw dbError;
        }

    } catch (error) {
        logError('Erreur lors de la création du token MFA', error);
        
        if (error.message === 'DISCORD_ID_EXISTS') {
            await salon.send('❌ Un compte existe déjà pour cet utilisateur Discord. Contacte un administrateur pour supprimer ton ancien compte.');
        } else {
            await salon.send('❌ Une erreur est survenue lors de la création du token. Contacte un administrateur.');
        }
    }
}

// =========================
// COMMANDE /MFA-LIST
// =========================
async function handleMfaListCommand(interaction) {
    logInfo('Traitement commande /mfa-list', { user: interaction.user.tag });

    // Vérifier si l'utilisateur a un rôle autorisé
    if (!hasAuthorizedRole(interaction.member, 'mfa-list')) {
        await interaction.reply({
            content: '❌ **Tu n\'as pas la permission d\'utiliser cette commande.**\n_Contacte un administrateur si tu penses que c\'est une erreur._',
            ephemeral: true
        });
        return;
    }

    await interaction.deferReply({ ephemeral: true });

    try {
        // Récupérer tous les utilisateurs de la base de données avec leurs tokens
        const users = await new Promise((resolve, reject) => {
            db.all('SELECT discord_id, pseudo, token, created_at FROM users ORDER BY created_at DESC', (err, rows) => {
                if (err) reject(err);
                else resolve(rows);
            });
        });

        if (users.length === 0) {
            await interaction.editReply({
                content: "# 📋 **Aucun compte MFA créé pour le moment.**"
            });
            return;
        }

        // Créer la liste formatée
        let userList = "# 📋 **Liste des comptes MFA**\n\n";
        let count = 0;

        for (const user of users) {
            count++;
            const createdDate = new Date(user.created_at).toLocaleDateString('fr-FR');
            const tokenPreview = user.token.substring(0, 8) + '...';
            userList += `**${count}.** 📱 **@${user.pseudo}** | 🆔 Discord: \`${user.discord_id}\` | 🔑 Token: \`${tokenPreview}\` | 📅 Créé le: ${createdDate}\n`;
            
            // Discord a une limite de 2000 caractères par message
            if (userList.length > 1700) {
                userList += `\n_... et ${users.length - count} autres comptes_`;
                break;
            }
        }

        userList += `\n**Total : ${users.length} comptes MFA actifs**`;

        await interaction.editReply({
            content: userList
        });

        logSuccess('Liste MFA affichée', { 
            user: interaction.user.tag,
            total_accounts: users.length 
        });

    } catch (error) {
        logError('Erreur lors de la récupération de la liste MFA', error);
        await interaction.editReply({
            content: "❌ **Erreur lors de la récupération de la liste. Veuillez réessayer.**"
        });
    }
}

// =========================
// COMMANDE /VOC
// =========================
async function handleVocCommand(interaction) {
    logInfo('Traitement commande /voc', { user: interaction.user.tag });
    
    // Vérifier si l'utilisateur a un rôle autorisé
    if (!hasAuthorizedRole(interaction.member, 'voc')) {
        await interaction.reply({
            content: '❌ **Tu n\'as pas la permission d\'utiliser cette commande.**\n_Contacte un administrateur si tu penses que c\'est une erreur._',
            ephemeral: true
        });
        return;
    }
    
    const voiceChannelId = '1387780682306158722';
    const row = new ActionRowBuilder()
        .addComponents(
            new ButtonBuilder()
                .setLabel('🔊 Rejoindre le vocal')
                .setStyle(ButtonStyle.Link)
                .setURL(`https://discord.com/channels/${interaction.guild.id}/${voiceChannelId}`)
        );

    await interaction.reply({
        content: "# 🫡 **Un agent t'attend !**\nClique sur le bouton ci-dessous pour rejoindre le vocal.",
        components: [row]
    });
    
    logSuccess('Bouton vocal affiché', { user: interaction.user.tag });
}

// =========================
// COMMANDE /END
// =========================
async function handleEndCommand(interaction) {
    logInfo('Traitement commande /end', { user: interaction.user.tag });
    
    // Vérifier si l'utilisateur a un rôle autorisé
    if (!hasAuthorizedRole(interaction.member, 'end') && !interaction.member.permissions.has(PermissionFlagsBits.Administrator)) {
        await interaction.reply({
            content: '❌ **Tu n\'as pas la permission d\'utiliser cette commande.**\n_Contacte un administrateur si tu penses que c\'est une erreur._',
            ephemeral: true
        });
        return;
    }
    
    await interaction.reply({
        content: "# ✅ **Problème résolu / Demande terminée !**\n\n**Un agent a répondu à la demande du créateur.**"
    });
    
    logSuccess('Commande /end exécutée', { 
        user: interaction.user.tag,
        channel: interaction.channel.name 
    });
}

// =========================
// COMMANDE /SUPPTOKEN
// =========================
async function handleSuppTokenCommand(interaction) {
    const user = interaction.user;
    const providedToken = interaction.options.getString('token');
    logInfo('Traitement commande /supptoken', { user: user.tag, providedToken: providedToken ? 'Oui' : 'Non' });

    // Vérifier si l'utilisateur a un rôle autorisé
    if (!hasAuthorizedRole(interaction.member, 'supptoken')) {
        await interaction.reply({
            content: '❌ **Tu n\'as pas la permission d\'utiliser cette commande.**\n_Contacte un administrateur si tu penses que c\'est une erreur._',
            ephemeral: true
        });
        return;
    }

    // Si un token est fourni directement, le supprimer
    if (providedToken) {
        await interaction.deferReply({ ephemeral: true });
        
        try {
            // Chercher l'utilisateur correspondant
            const userRow = await new Promise((resolve, reject) => {
                db.get('SELECT discord_id, pseudo FROM users WHERE token = ?', [providedToken], (err, row) => {
                    if (err) reject(err);
                    else resolve(row);
                });
            });
            
            if (!userRow) {
                await interaction.editReply({ 
                    content: "# ❌ **Token invalide ou introuvable dans la base de données.**"
                });
                return;
            }
            
            // Effectuer la suppression directement
            await performTokenDeletion(providedToken, userRow.discord_id, userRow.pseudo);
            
            await interaction.editReply({ 
                content: `# ✅ **Compte supprimé avec succès !**\n\n📱 Pseudo: **@${userRow.pseudo}**\n🆔 Discord ID: \`${userRow.discord_id}\`\n🔑 Token: \`${providedToken.substring(0, 8)}...\`\n\n_Le compte a été définitivement supprimé de la base de données._`
            });
            
            logSuccess('Token supprimé via commande directe', {
                deleted_by: user.tag,
                deleted_user: userRow.pseudo,
                discord_id: userRow.discord_id
            });
            
        } catch (error) {
            logError('Erreur lors de la suppression du token', error);
            await interaction.editReply({ 
                content: "# ❌ **Erreur lors de la suppression. Contacte un administrateur.**"
            });
        }
        
        return;
    }

    // Si aucun token fourni, afficher le menu de sélection
    await interaction.deferReply({ ephemeral: true });

    try {
        // Récupérer tous les utilisateurs
        const users = await new Promise((resolve, reject) => {
            db.all('SELECT discord_id, pseudo, token FROM users ORDER BY pseudo ASC LIMIT 25', (err, rows) => {
                if (err) reject(err);
                else resolve(rows);
            });
        });

        if (users.length === 0) {
            await interaction.editReply({
                content: "# 📋 **Aucun compte MFA trouvé.**"
            });
            return;
        }

        // Créer les options pour le menu
        const options = users.map((user, index) => ({
            label: `@${user.pseudo}`,
            description: `Discord ID: ${user.discord_id}`,
            value: `${index}_${user.token}` // Index + token pour éviter les conflits
        }));

        // Créer le menu de sélection
        const selectMenu = new StringSelectMenuBuilder()
            .setCustomId(`supptoken_select_${interaction.id}`)
            .setPlaceholder('🗑️ Sélectionne un compte à supprimer')
            .addOptions(options);

        const row = new ActionRowBuilder()
            .addComponents(selectMenu);

        await interaction.editReply({
            content: "# 🗑️ **Suppression de compte MFA**\n\nSélectionne le compte à supprimer dans le menu ci-dessous :",
            components: [row]
        });

        // Créer un collecteur pour cette interaction spécifique
        const collector = interaction.channel.createMessageComponentCollector({
            filter: i => i.customId === `supptoken_select_${interaction.id}` && i.user.id === user.id,
            time: 60000, // 60 secondes
            max: 1
        });

        collector.on('collect', async i => {
            const [index, selectedToken] = i.values[0].split('_');
            const selectedUser = users[parseInt(index)];

            if (!selectedUser) {
                await i.reply({
                    content: '❌ **Erreur lors de la sélection.**',
                    ephemeral: true
                });
                return;
            }

            // Créer les boutons de confirmation
            const confirmButton = new ButtonBuilder()
                .setCustomId(`confirm_supp_${interaction.id}`)
                .setLabel('✅ Confirmer la suppression')
                .setStyle(ButtonStyle.Danger);

            const cancelButton = new ButtonBuilder()
                .setCustomId(`cancel_supp_${interaction.id}`)
                .setLabel('❌ Annuler')
                .setStyle(ButtonStyle.Secondary);

            const confirmRow = new ActionRowBuilder()
                .addComponents(confirmButton, cancelButton);

            await i.update({
                content: `# ⚠️ **Confirmation de suppression**\n\nEs-tu sûr de vouloir supprimer le compte de **@${selectedUser.pseudo}** ?\n\n🆔 Discord ID: \`${selectedUser.discord_id}\`\n🔑 Token: \`${selectedToken.substring(0, 8)}...\`\n\n**Cette action est irréversible !**`,
                components: [confirmRow]
            });

            // Collecteur pour les boutons
            const buttonCollector = interaction.channel.createMessageComponentCollector({
                filter: btn => (btn.customId === `confirm_supp_${interaction.id}` || btn.customId === `cancel_supp_${interaction.id}`) && btn.user.id === user.id,
                time: 30000,
                max: 1
            });

            buttonCollector.on('collect', async btn => {
                if (btn.customId === `confirm_supp_${interaction.id}`) {
                    // Effectuer la suppression
                    try {
                        await performTokenDeletion(selectedToken, selectedUser.discord_id, selectedUser.pseudo);
                        
                        await btn.update({
                            content: `# ✅ **Compte supprimé avec succès !**\n\n📱 Pseudo: **@${selectedUser.pseudo}**\n🆔 Discord ID: \`${selectedUser.discord_id}\`\n🔑 Token: \`${selectedToken.substring(0, 8)}...\`\n\n_Le compte a été définitivement supprimé de la base de données._`,
                            components: []
                        });
                        
                        logSuccess('Token supprimé via menu', {
                            deleted_by: user.tag,
                            deleted_user: selectedUser.pseudo,
                            discord_id: selectedUser.discord_id
                        });
                        
                    } catch (error) {
                        logError('Erreur lors de la suppression', error);
                        await btn.update({
                            content: '❌ **Erreur lors de la suppression. Veuillez réessayer.**',
                            components: []
                        });
                    }
                } else {
                    await btn.update({
                        content: '❌ **Suppression annulée.**',
                        components: []
                    });
                }
            });

            buttonCollector.on('end', collected => {
                if (collected.size === 0) {
                    i.editReply({
                        content: '⏱️ **Temps écoulé. Suppression annulée.**',
                        components: []
                    }).catch(() => {});
                }
            });
        });

        collector.on('end', collected => {
            if (collected.size === 0) {
                interaction.editReply({
                    content: '⏱️ **Temps écoulé. Aucune sélection effectuée.**',
                    components: []
                }).catch(() => {});
            }
        });

    } catch (error) {
        logError('Erreur lors de la récupération des utilisateurs', error);
        await interaction.editReply({ 
            content: "# ❌ **Erreur lors de la récupération de la liste. Veuillez réessayer.**"
        });
    }
}

// Fonction helper pour effectuer la suppression
async function performTokenDeletion(token, discordId, pseudo) {
    // Nettoyer les caches
    userTokens.delete(discordId);
    userPseudos.delete(discordId);
    
    // Supprimer de la base de données
    return new Promise((resolve, reject) => {
        db.serialize(() => {
            db.run('DELETE FROM user_styles WHERE token = ?', [token], (err) => {
                if (err) logError('Erreur suppression user_styles:', err);
            });
            
            db.run('DELETE FROM user_data WHERE token = ?', [token], (err) => {
                if (err) logError('Erreur suppression user_data:', err);
            });
            
            db.run('DELETE FROM users WHERE token = ?', [token], (err) => {
                if (err) {
                    reject(err);
                } else {
                    logSuccess(`Utilisateur ${pseudo} (${discordId}) supprimé de la base de données`);
                    resolve();
                }
            });
        });
    });
}

// =========================
// COMMANDE /CARTE
// =========================
async function handleCarteCommand(interaction) {
    const user = interaction.user;
    logInfo('Traitement commande /carte', { user: user.tag });

    // Vérifier si l'utilisateur a un rôle autorisé
    if (!hasAuthorizedRole(interaction.member, 'carte')) {
        await interaction.reply({
            content: '❌ **Tu n\'as pas la permission d\'utiliser cette commande.**\n_Contacte un administrateur si tu penses que c\'est une erreur._',
            ephemeral: true
        });
        return;
    }

    await interaction.deferReply();

    try {
        // Vérifie si l'utilisateur a déjà un token
        const existingToken = await getUserToken(user.id);
        if (!existingToken) {
            await interaction.editReply({ 
                content: "# ❌ **Tu dois d'abord créer ton token MFA avec `/mfa`.**"
            });
            return;
        }

        // Récupérer le pseudo depuis la base de données
        const userInfo = await new Promise((resolve, reject) => {
            db.get('SELECT pseudo FROM users WHERE discord_id = ?', [user.id], (err, row) => {
                if (err) reject(err);
                else resolve(row);
            });
        });

        const pseudo = userInfo?.pseudo || user.username;
        const dashboardURL = `${WEBSITE_URL}/dashboard.php?token=${existingToken}`;

        // 🔹 Génération du QR Code
        const qrDataURL = await QRCode.toDataURL(dashboardURL, { 
            width: 300, 
            margin: 2,
            color: {
                dark: '#000000',
                light: '#FFFFFF'
            }
        });
        const qrImg = await loadImage(qrDataURL);

        // 🔹 Création Canvas Carte
        const width = 1000;
        const height = 500;
        const canvas = createCanvas(width, height);
        const ctx = canvas.getContext('2d');

        // 🔹 Fond dégradé
        const gradient = ctx.createLinearGradient(0, 0, width, height);
        gradient.addColorStop(0, '#0f2027');
        gradient.addColorStop(0.5, '#203a43');
        gradient.addColorStop(1, '#2c5364');
        ctx.fillStyle = gradient;
        ctx.fillRect(0, 0, width, height);

        // 🔹 Encadrement lumineux
        ctx.strokeStyle = '#FFD700';
        ctx.lineWidth = 8;
        ctx.strokeRect(20, 20, width - 40, height - 40);

        // 🔹 Texte principal
        ctx.fillStyle = '#FFFFFF';
        ctx.font = 'bold 48px Sans';
        ctx.fillText('MY FULL AGENCY', 50, 80);

        ctx.font = '32px Sans';
        ctx.fillStyle = '#00FFAA';
        ctx.fillText(`🎯 Pseudo TikTok : ${pseudo}`, 50, 150);

        ctx.font = '22px Sans';
        ctx.fillStyle = '#AAAAAA';
        ctx.fillText('Scanne ce QR Code pour accéder à ton Dashboard MFA', 50, 200);

        // 🔹 Footer avec date
        ctx.font = '18px Sans';
        ctx.fillStyle = '#888888';
        const date = new Date().toLocaleDateString('fr-FR');
        ctx.fillText(`Généré le ${date} | ${config.app.name}`, 50, height - 50);

        // 🔹 Intégration QR Code
        ctx.drawImage(qrImg, width - 320, height / 2 - 150, 300, 300);

        // 🔹 Envoi en pièce jointe
        const buffer = canvas.toBuffer();
        const attachment = new AttachmentBuilder(buffer, { name: `Carte_MFA_${user.username}.png` });

        await interaction.editReply({
            content: `# 🪪 **Voici ta Carte MFA dynamique, <@${user.id}> !**

💾 **IMPORTANT : Enregistre cette carte dans ta galerie pour ne pas perdre ton token !**

_Scanne le QR Code ou clique [ici](${dashboardURL}) pour accéder à ton dashboard._`,
            files: [attachment]
        });

        logSuccess('Carte MFA générée', { 
            user: user.tag,
            pseudo: pseudo,
            token_preview: existingToken.substring(0, 8) + '...'
        });

    } catch (error) {
        logError('Erreur lors de la génération de la carte MFA', error);
        
        if (interaction.deferred) {
            await interaction.editReply({ 
                content: "# ❌ **Erreur lors de la génération de la carte. Réessaye plus tard.**"
            });
        }
    }
}

// =========================
// COMMANDE /WAIT
// =========================
async function handleWaitCommand(interaction) {
    logInfo('Traitement commande /wait', { user: interaction.user.tag });
    
    // Vérifier si l'utilisateur a un rôle autorisé
    if (!hasAuthorizedRole(interaction.member, 'wait')) {
        await interaction.reply({
            content: '❌ **Tu n\'as pas la permission d\'utiliser cette commande.**\n_Contacte un administrateur si tu penses que c\'est une erreur._',
            ephemeral: true
        });
        return;
    }
    
    await interaction.reply({
        content: `# ⏳ **Un agent va bientôt te répondre !**

_Merci de patienter quelques instants. Si la demande est urgente, écris **"urgent"** sous ce message._`
    });
    
    logSuccess('Commande /wait exécutée', { 
        user: interaction.user.tag,
        channel: interaction.channel.name 
    });
}

// =========================
// COMMANDE /INFOS (Recréer le message d'infos complet)
// =========================
async function handleInfosCommand(interaction) {
    const user = interaction.user;
    logInfo('Traitement commande /infos', { user: user.tag });

    // Vérifier si l'utilisateur a un rôle autorisé
    if (!hasAuthorizedRole(interaction.member, 'infos')) {
        await interaction.reply({
            content: '❌ **Tu n\'as pas la permission d\'utiliser cette commande.**\n_Contacte un administrateur si tu penses que c\'est une erreur._',
            ephemeral: true
        });
        return;
    }

    // Réponse NON ephemeral pour que le message reste visible
    await interaction.deferReply({ ephemeral: false });

    try {
        // Vérifier si l'utilisateur a un token
        const existingToken = await getUserToken(user.id);
        
        if (!existingToken) {
            await interaction.editReply({
                content: "# ❌ **Tu n'as pas encore de compte MFA !**\n\nUtilise la commande `/mfa` pour créer ton compte."
            });
            return;
        }

        // Récupérer les informations complètes de l'utilisateur
        const userInfo = await new Promise((resolve, reject) => {
            db.get('SELECT pseudo, created_at, token FROM users WHERE discord_id = ?', [user.id], (err, row) => {
                if (err) reject(err);
                else resolve(row);
            });
        });

        if (!userInfo) {
            await interaction.editReply({
                content: "❌ **Aucune information trouvée pour ton compte.**"
            });
            return;
        }

        const dashboardURL = `${WEBSITE_URL}/dashboard.php?token=${userInfo.token}`;
        const winsURL = `${WEBSITE_URL}/?module=wins&token=${userInfo.token}&control=true`;
        const timerURL = `${WEBSITE_URL}/modules/timer.php?token=${userInfo.token}&control=true`;
        const teamBattleURL = `${WEBSITE_URL}/modules/team-battle.php?token=${userInfo.token}&control=true`;

        // Créer les boutons
        const jeuxButton = new ButtonBuilder()
            .setCustomId('voir_jeux')
            .setLabel('🎮 Voir les jeux interactifs')
            .setStyle(ButtonStyle.Primary);

        const carteButton = new ButtonBuilder()
            .setCustomId(`generer_carte_${user.id}`)
            .setLabel('🪪 Générer ma carte MFA')
            .setStyle(ButtonStyle.Success);

        const row = new ActionRowBuilder()
            .addComponents(jeuxButton, carteButton);

        // Recréer le message final complet (identique à celui de la création)
        await interaction.editReply({
            content: `# ⚙️ **INFOS DE <@${user.id}> !**

_Ici, tu trouveras toutes les infos essentielles pour ton aventure TikTok !_  

## 📱 **TikTok :** <https://www.tiktok.com/@${userInfo.pseudo}>
## 🔑 **TON TOKEN :**
\`\`\`${userInfo.token}\`\`\`
# ⚠️ _**Utilise ce token avec précaution**_ ⚠️

---

## 📌 **Liens utiles :**
- 💻 **[Accéder au Dashboard](${dashboardURL})**
- 🏆 **[Ouvrir le panneau Wins](${winsURL})**
- ⏱️ **[Timer Interactif](${timerURL})**
- 🆚 **[Team Battle](${teamBattleURL})**

---

## ℹ️ **Conseils de base :**
- ⏱️ Vise au moins **1h de live** pour que l'algorithme TikTok te mette en avant.
- 📆 **Sois régulier** : même heure chaque jour = bonus visibilité.
- 🧘‍♂️ **Sois patient** : les performances arrivent avec la constance.
- 🤝 **Si tu as une question**, pose-la dans ton salon, un agent ou un staff te répondra.

---

## ✅ **Étapes à suivre :**
- 🔓 **Installe tout** dans <#1390122226015273133>
- 🎮 **Choisis tes jeux interactifs** (clique sur le bouton ci-dessous)
- 📖 **Lis attentivement** <#1390781917707763914> pour respecter les règles TikTok
- 💡 **Consulte la rubrique "bon à savoir"** dans <#1391781161319010394>
- 🧠 **Forme-toi** : formation gratuite **chaque dimanche à 20h** 🎓`,
            components: [row]
        });

        logSuccess('Message d\'infos recréé', { 
            user: user.tag,
            pseudo: userInfo.pseudo,
            token_preview: userInfo.token.substring(0, 8) + '...'
        });

    } catch (error) {
        logError('Erreur lors de la récupération des informations', error);
        await interaction.editReply({
            content: "❌ **Erreur lors de la récupération de tes informations. Veuillez réessayer.**"
        });
    }
}

// Gestion des erreurs
process.on('unhandledRejection', error => {
    console.error('❌ Erreur non gérée:', error);
});

// Fermeture propre de la base de données
process.on('SIGINT', () => {
    console.log('\n🔄 Fermeture du bot...');
    db.close((err) => {
        if (err) {
            console.error('❌ Erreur fermeture base de données:', err.message);
        } else {
            console.log('✅ Base de données fermée');
        }
        process.exit(0);
    });
});

// Connexion du bot
client.login(TOKEN);

console.log(`🤖 Démarrage du bot ${config.app.name}...`);
console.log('🌐 URL du site:', WEBSITE_URL);
console.log('📊 Bot Discord avec gestion complète des tokens utilisateurs');

// =========================
// BRIDGE CHAT DASHBOARD <-> DISCORD
// =========================
let lastProcessedChatId = 0;

function initLastProcessedChatId() {
    return new Promise((resolve) => {
        try {
            db.get("SELECT MAX(id) AS maxId FROM chat_messages WHERE source = 'dashboard'", (err, row) => {
                if (err) {
                    logWarning('Bridge chat: impossible de lire MAX(id)', err);
                    lastProcessedChatId = 0;
                } else {
                    lastProcessedChatId = row && row.maxId ? row.maxId : 0;
                }
                logInfo('Bridge chat: position initiale', { lastProcessedChatId });
                resolve();
            });
        } catch (e) {
            logWarning('Bridge chat: init lastProcessedChatId erreur', e);
            lastProcessedChatId = 0;
            resolve();
        }
    });
}

async function startChatBridge(channel) {
    await initLastProcessedChatId();
    setInterval(processNewDashboardMessages.bind(null, channel), 2000);
}

function processNewDashboardMessages(channel) {
    try {
        db.all("SELECT id, token, message, created_at FROM chat_messages WHERE source = 'dashboard' AND id > ? ORDER BY id ASC LIMIT 100", [lastProcessedChatId], async (err, rows) => {
            if (err) {
                logWarning('Bridge chat: erreur lecture messages dashboard', err);
                return;
            }
            for (const row of rows) {
                const token = row.token;
                const userInfo = await getUserByToken(token);
                const pseudo = userInfo?.pseudo || 'inconnu';
                const authorMention = userInfo?.discord_id ? `<@${userInfo.discord_id}>` : `@${pseudo}`;
                // Affichage spécial si message close système
                const isClose = row.message.startsWith('[Système] Discussion close');
                const content = isClose
                    ? `🔒 [Close] ${authorMention} \`\`${token.substring(0,8)}...\`\``
                    : `🟣 [Dashboard] ${authorMention} \`\`${token.substring(0,8)}...\`\`:\n${row.message}`;
                try {
                    const sent = await channel.send({ content });
                    // Mémoriser le lien message -> token pour router les réponses
                    db.run('INSERT OR REPLACE INTO chat_links (message_id, token, created_at) VALUES (?, ?, ?)', [sent.id, token, Date.now()]);
                } catch (sendErr) {
                    logWarning('Bridge chat: envoi Discord échoué', sendErr);
                }
                lastProcessedChatId = Math.max(lastProcessedChatId, row.id);
            }
        });
    } catch (e) {
        logWarning('Bridge chat: exception processNewDashboardMessages', e);
    }
}

function getUserByToken(token) {
    return new Promise((resolve) => {
        db.get('SELECT discord_id, pseudo FROM users WHERE token = ?', [token], (err, row) => {
            if (err) resolve(null);
            else resolve(row);
        });
    });
}

// Ecoute des réponses côté Discord (répondre AU MESSAGE du dashboard)
client.on('messageCreate', async (message) => {
    try {
        if (!CHAT_CHANNEL_ID || message.channelId !== CHAT_CHANNEL_ID) return;
        if (message.author.bot) return;
        const ref = message.reference?.messageId;
        if (!ref) return; // on ne route que les réponses à un message du dashboard

        // Retrouver le token via chat_links
        db.get('SELECT token FROM chat_links WHERE message_id = ?', [ref], (err, row) => {
            if (err || !row) return;
            const token = row.token;
            const staffLabel = `Staff ${message.author.tag}`;
            const text = `[${staffLabel}] ${message.content}`;
            db.run('INSERT INTO chat_messages (token, source, message, created_at) VALUES (?, ?, ?, ?)', [token, 'discord', text, Math.floor(Date.now()/1000)], (insErr) => {
                if (insErr) logWarning('Bridge chat: insertion message discord -> sqlite échouée', insErr);
            });
        });
    } catch (e) {
        logWarning('Bridge chat: erreur messageCreate', e);
    }
});