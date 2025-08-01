const fs = require('fs');
const path = require('path');
const readline = require('readline');

class ProductionSetup {
    constructor() {
        this.projectRoot = path.join(__dirname, '..');
        this.rl = readline.createInterface({
            input: process.stdin,
            output: process.stdout
        });
    }

    // Demander une entrée utilisateur
    async askQuestion(question) {
        return new Promise((resolve) => {
            this.rl.question(question, (answer) => {
                resolve(answer);
            });
        });
    }

    // Configuration interactive
    async setupProduction() {
        console.log(`
╔══════════════════════════════════════════════════════════════╗
║                🚀 CONFIGURATION PRODUCTION                   ║
║                     Wins Counter                             ║
╚══════════════════════════════════════════════════════════════╝

Ce script va configurer votre projet pour la production.
        `);

        try {
            // 1. Configuration du domaine
            const domain = await this.askQuestion('🌍 Domaine du site (ex: monsite.com): ');
            const useHttps = await this.askQuestion('🔒 Utiliser HTTPS? (oui/non) [oui]: ') || 'oui';
            
            // 2. Configuration Discord
            const discordToken = await this.askQuestion('🤖 Token Discord Bot: ');
            const discordClientId = await this.askQuestion('🆔 Client ID Discord: ');
            
            // 3. Configuration API
            const apiToken = await this.askQuestion('🔑 Token API sécurisé (ou appuyez sur Entrée pour générer): ') || this.generateSecureToken();
            
            // 4. Configuration backup
            const enableBackup = await this.askQuestion('💾 Activer les backups automatiques? (oui/non) [oui]: ') || 'oui';
            
            // 5. Configuration mises à jour
            const enableUpdates = await this.askQuestion('🔄 Activer les mises à jour automatiques? (oui/non) [oui]: ') || 'oui';
            const gitRepo = await this.askQuestion('📦 Repository GitHub (ex: username/wins-counter): ');

            // Créer la configuration
            const config = this.createProductionConfig({
                domain,
                useHttps: useHttps.toLowerCase() === 'oui',
                discordToken,
                discordClientId,
                apiToken,
                enableBackup: enableBackup.toLowerCase() === 'oui',
                enableUpdates: enableUpdates.toLowerCase() === 'oui',
                gitRepo
            });

            // Sauvegarder les configurations
            this.saveConfigurations(config);
            
            // Créer les scripts de démarrage
            this.createStartupScripts(config);
            
            // Créer le fichier Docker (optionnel)
            this.createDockerFiles(config);

            console.log(`
✅ Configuration terminée avec succès!

📁 Fichiers créés:
   - config/production.json
   - config/update-config.json  
   - scripts/start-production.sh
   - docker-compose.yml
   - .env.production

🚀 Prochaines étapes:
   1. Uploadez votre projet sur votre serveur
   2. Configurez votre serveur web (Nginx/Apache)
   3. Lancez: npm run production
   4. Configurez SSL avec Let's Encrypt

📖 Consultez docs/HEBERGEMENT_GUIDE.md pour plus de détails.
            `);

        } catch (error) {
            console.error('❌ Erreur lors de la configuration:', error.message);
        } finally {
            this.rl.close();
        }
    }

    // Générer un token sécurisé
    generateSecureToken() {
        const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
        let token = '';
        for (let i = 0; i < 32; i++) {
            token += chars.charAt(Math.floor(Math.random() * chars.length));
        }
        return token;
    }

    // Créer la configuration de production
    createProductionConfig(options) {
        const protocol = options.useHttps ? 'https' : 'http';
        const port = options.useHttps ? '443' : '80';

        return {
            production: {
                website: {
                    url: `${protocol}://${options.domain}`,
                    port: port,
                    domain: options.domain
                },
                discord: {
                    token: options.discordToken,
                    client_id: options.discordClientId
                },
                security: {
                    api_token: options.apiToken,
                    environment: "production"
                },
                backup: {
                    enabled: options.enableBackup,
                    interval: 21600000, // 6 heures
                    max_backups: 30,
                    path: "./backups"
                },
                update: {
                    enabled: options.enableUpdates,
                    repository: options.gitRepo,
                    branch: "main",
                    check_interval: 3600000 // 1 heure
                }
            }
        };
    }

    // Sauvegarder les configurations
    saveConfigurations(config) {
        // 1. Configuration principale
        const configPath = path.join(this.projectRoot, 'config', 'production.json');
        fs.writeFileSync(configPath, JSON.stringify(config.production, null, 2));

        // 2. Configuration des mises à jour
        const updateConfigPath = path.join(this.projectRoot, 'config', 'update-config.json');
        const updateConfig = {
            repository: config.production.update.repository,
            branch: config.production.update.branch,
            auto_update: config.production.update.enabled,
            check_interval: config.production.update.check_interval,
            backup_before_update: true,
            last_check: null,
            current_version: "1.0.0"
        };
        fs.writeFileSync(updateConfigPath, JSON.stringify(updateConfig, null, 2));

        // 3. Fichier .env pour la production
        const envPath = path.join(this.projectRoot, '.env.production');
        const envContent = `
# Configuration Production - Wins Counter
NODE_ENV=production
WEBSITE_URL=${config.production.website.url}
DISCORD_TOKEN=${config.production.discord.token}
DISCORD_CLIENT_ID=${config.production.discord.client_id}
API_TOKEN=${config.production.security.api_token}
BACKUP_ENABLED=${config.production.backup.enabled}
UPDATE_ENABLED=${config.production.update.enabled}
        `.trim();
        fs.writeFileSync(envPath, envContent);

        console.log('✅ Configurations sauvegardées');
    }

    // Créer les scripts de démarrage
    createStartupScripts(config) {
        // Script de démarrage Linux
        const startScript = `#!/bin/bash

echo "🚀 Démarrage en mode PRODUCTION"
echo "Domaine: ${config.production.website.domain}"

# Vérifier Node.js
if ! command -v node &> /dev/null; then
    echo "❌ Node.js non installé"
    exit 1
fi

# Vérifier PM2
if ! command -v pm2 &> /dev/null; then
    echo "📦 Installation de PM2..."
    npm install -g pm2
fi

# Créer les dossiers nécessaires
mkdir -p backups/database backups/updates logs

# Démarrer les services avec PM2
echo "🤖 Démarrage du bot Discord..."
pm2 start bot/bot.js --name "wins-counter-bot" --env production

echo "💾 Démarrage du système de backup..."
pm2 start scripts/auto-backup.js --name "wins-backup" --env production -- auto

echo "🔄 Démarrage du système de mise à jour..."
pm2 start scripts/auto-update.js --name "wins-updater" --env production -- auto

# Sauvegarder la configuration PM2
pm2 save
pm2 startup

echo "✅ Tous les services sont démarrés!"
echo "📊 Monitoring: pm2 monit"
echo "📋 Status: pm2 status"
echo "📝 Logs: pm2 logs"

        `;

        const scriptPath = path.join(this.projectRoot, 'scripts', 'start-production.sh');
        fs.writeFileSync(scriptPath, startScript);
        
        // Rendre exécutable sur Linux
        try {
            fs.chmodSync(scriptPath, '755');
        } catch (error) {
            // Ignore sur Windows
        }

        // Script Windows
        const winScript = `@echo off
title Wins Counter - Production

echo 🚀 Démarrage en mode PRODUCTION
echo Domaine: ${config.production.website.domain}

REM Créer les dossiers nécessaires
if not exist "backups\\database" mkdir "backups\\database"
if not exist "backups\\updates" mkdir "backups\\updates"
if not exist "logs" mkdir "logs"

REM Démarrer les services
echo 🤖 Démarrage du bot Discord...
start "Bot Discord" cmd /k "cd bot && node bot.js"

echo 💾 Démarrage du système de backup...
start "Backup System" cmd /k "node scripts\\auto-backup.js auto"

echo 🔄 Démarrage du système de mise à jour...
start "Update System" cmd /k "node scripts\\auto-update.js auto"

echo ✅ Tous les services sont démarrés!
pause
        `;

        const winScriptPath = path.join(this.projectRoot, 'scripts', 'start-production.bat');
        fs.writeFileSync(winScriptPath, winScript);

        console.log('✅ Scripts de démarrage créés');
    }

    // Créer les fichiers Docker
    createDockerFiles(config) {
        // Dockerfile
        const dockerfile = `FROM node:18-alpine

# Installer les dépendances système
RUN apk add --no-cache sqlite php81 php81-sqlite3 php81-session

# Créer le dossier de l'app
WORKDIR /app

# Copier les fichiers de dépendances
COPY bot/package*.json ./bot/
COPY package*.json ./

# Installer les dépendances Node.js
RUN cd bot && npm ci --only=production

# Copier le code source
COPY . .

# Créer les dossiers nécessaires
RUN mkdir -p backups/database backups/updates logs database

# Permissions
RUN chmod -R 755 scripts/
RUN chmod 777 database backups

# Exposer les ports
EXPOSE 80 3000

# Démarrer l'application
CMD ["node", "scripts/docker-start.js"]
        `;

        // docker-compose.yml
        const dockerCompose = `version: '3.8'

services:
  wins-counter:
    build: .
    container_name: wins-counter
    restart: unless-stopped
    environment:
      - NODE_ENV=production
      - WEBSITE_URL=${config.production.website.url}
      - DISCORD_TOKEN=${config.production.discord.token}
      - DISCORD_CLIENT_ID=${config.production.discord.client_id}
      - API_TOKEN=${config.production.security.api_token}
    ports:
      - "80:80"
      - "3000:3000"
    volumes:
      - ./database:/app/database
      - ./backups:/app/backups
      - ./logs:/app/logs
    networks:
      - wins-network

  nginx:
    image: nginx:alpine
    container_name: wins-nginx
    restart: unless-stopped
    ports:
      - "443:443"
    volumes:
      - ./nginx.conf:/etc/nginx/nginx.conf
      - ./ssl:/etc/nginx/ssl
    depends_on:
      - wins-counter
    networks:
      - wins-network

networks:
  wins-network:
    driver: bridge
        `;

        // Script de démarrage Docker
        const dockerStart = `const { spawn } = require('child_process');

console.log('🐳 Démarrage Docker - Wins Counter');

// Démarrer le serveur PHP
const phpServer = spawn('php', ['-S', '0.0.0.0:80', '-t', 'web'], {
    stdio: 'inherit'
});

// Démarrer le bot Discord
const discordBot = spawn('node', ['bot/bot.js'], {
    stdio: 'inherit'
});

// Démarrer les backups
const backupSystem = spawn('node', ['scripts/auto-backup.js', 'auto'], {
    stdio: 'inherit'
});

// Démarrer les mises à jour
const updateSystem = spawn('node', ['scripts/auto-update.js', 'auto'], {
    stdio: 'inherit'
});

// Gérer les signaux
process.on('SIGTERM', () => {
    console.log('🛑 Arrêt des services...');
    phpServer.kill();
    discordBot.kill();
    backupSystem.kill();
    updateSystem.kill();
    process.exit(0);
});
        `;

        // Sauvegarder les fichiers
        fs.writeFileSync(path.join(this.projectRoot, 'Dockerfile'), dockerfile);
        fs.writeFileSync(path.join(this.projectRoot, 'docker-compose.yml'), dockerCompose);
        fs.writeFileSync(path.join(this.projectRoot, 'scripts', 'docker-start.js'), dockerStart);

        console.log('✅ Fichiers Docker créés');
    }
}

// Exécution
if (require.main === module) {
    const setup = new ProductionSetup();
    setup.setupProduction();
}

module.exports = ProductionSetup; 