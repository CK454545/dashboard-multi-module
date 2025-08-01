const { Client, GatewayIntentBits, SlashCommandBuilder } = require('discord.js');
const fs = require('fs');
const path = require('path');

// Charger la configuration
const projectRoot = path.resolve(__dirname, '..');
const configPath = path.join(projectRoot, 'config', 'config.json');
const config = JSON.parse(fs.readFileSync(configPath, 'utf8'));

const client = new Client({ 
    intents: [
        GatewayIntentBits.Guilds,
        GatewayIntentBits.GuildMessages,
        GatewayIntentBits.MessageContent
    ] 
});

// Commandes à forcer
const commands = [
    new SlashCommandBuilder()
        .setName('wait')
        .setDescription('Attendre son tour'),
    
    new SlashCommandBuilder()
        .setName('mfa')
        .setDescription('Accéder au dashboard MFA'),
    
    new SlashCommandBuilder()
        .setName('start-mfa')
        .setDescription('Démarrer une session MFA'),
    
    new SlashCommandBuilder()
        .setName('mfa-list')
        .setDescription('Voir la liste des sessions MFA'),
    
    new SlashCommandBuilder()
        .setName('voc')
        .setDescription('Accéder au dashboard VOC'),
    
    new SlashCommandBuilder()
        .setName('end')
        .setDescription('Terminer une session'),
    
    new SlashCommandBuilder()
        .setName('supptoken')
        .setDescription('Supprimer un token')
        .addStringOption(option =>
            option.setName('token')
                .setDescription('Le token à supprimer')
                .setRequired(false)),
    
    new SlashCommandBuilder()
        .setName('carte')
        .setDescription('Voir sa carte utilisateur'),
    
    new SlashCommandBuilder()
        .setName('infos')
        .setDescription('Voir les informations utilisateur')
];

client.once('ready', async () => {
    console.log(`✅ Bot connecté en tant que ${client.user.tag}`);
    console.log(`🆔 Bot ID: ${client.user.id}`);
    
    try {
        console.log('🚀 FORÇAGE IMMÉDIAT DES COMMANDES...');
        
        // Forcer l'enregistrement sur chaque serveur
        for (const guild of client.guilds.cache.values()) {
            try {
                console.log(`📋 Forçage sur: ${guild.name} (${guild.id})`);
                
                // Supprimer d'abord toutes les commandes
                await guild.commands.set([]);
                console.log(`🗑️ Commandes supprimées sur ${guild.name}`);
                
                // Attendre un peu
                await new Promise(resolve => setTimeout(resolve, 2000));
                
                // Forcer l'enregistrement des nouvelles commandes
                await guild.commands.set(commands);
                console.log(`✅ Commandes FORCÉES sur ${guild.name}`);
                
                // Vérifier
                const verifiedCommands = await guild.commands.fetch();
                console.log(`📊 Commandes vérifiées: ${verifiedCommands.size}`);
                
                // Lister les commandes
                verifiedCommands.forEach(cmd => {
                    console.log(`  • /${cmd.name} - ${cmd.description}`);
                });
                
            } catch (error) {
                console.error(`❌ Erreur sur ${guild.name}:`, error.message);
            }
        }
        
        console.log('');
        console.log('🎉 FORÇAGE TERMINÉ !');
        console.log('');
        console.log('💡 Les commandes devraient apparaître IMMÉDIATEMENT dans Discord');
        console.log('   Si elles n\'apparaissent pas :');
        console.log('   1. Rafraîchissez Discord (Ctrl+R)');
        console.log('   2. Tapez / dans un canal');
        console.log('   3. Attendez 30 secondes maximum');
        
    } catch (error) {
        console.error('❌ Erreur lors du forçage:', error);
    }
    
    // Quitter après 10 secondes
    setTimeout(() => {
        console.log('👋 Fermeture du script...');
        process.exit(0);
    }, 10000);
});

client.login(config.discord.token); 