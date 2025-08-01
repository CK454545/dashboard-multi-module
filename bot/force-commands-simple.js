const { Client, GatewayIntentBits, SlashCommandBuilder, REST, Routes } = require('discord.js');
const fs = require('fs');
const path = require('path');

// Charger la configuration
const projectRoot = path.resolve(__dirname, '..');
const configPath = path.join(projectRoot, 'config', 'config.json');
const config = JSON.parse(fs.readFileSync(configPath, 'utf8'));

const TOKEN = config.discord.token;

// Créer le client Discord
const client = new Client({ 
    intents: [
        GatewayIntentBits.Guilds,
        GatewayIntentBits.GuildMessages,
        GatewayIntentBits.MessageContent
    ] 
});

// Commandes complètes
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

client.once('ready', async () => {
    console.log(`\n✅ Bot connecté : ${client.user.tag}`);
    console.log(`🆔 Bot ID : ${client.user.id}\n`);
    
    try {
        console.log('🚀 FORÇAGE SIMPLE DES COMMANDES...\n');
        
        // Créer une instance REST
        const rest = new REST({ version: '10' }).setToken(TOKEN);
        
        // Pour chaque serveur
        for (const guild of client.guilds.cache.values()) {
            console.log(`📋 Traitement de : ${guild.name}`);
            
            try {
                // Méthode 1 : Utiliser l'API REST directement
                console.log('  🔄 Méthode 1 : API REST directe...');
                await rest.put(
                    Routes.applicationGuildCommands(client.user.id, guild.id),
                    { body: commands }
                );
                console.log('  ✅ Commandes enregistrées via API REST');
                
                // Attendre un peu
                await new Promise(resolve => setTimeout(resolve, 2000));
                
                // Vérifier
                const registeredCommands = await guild.commands.fetch();
                console.log(`  📊 ${registeredCommands.size} commandes vérifiées :`);
                registeredCommands.forEach(cmd => {
                    console.log(`     • /${cmd.name}`);
                });
                
            } catch (error) {
                console.error(`  ❌ Erreur API REST :`, error.message);
                
                // Méthode 2 : Utiliser guild.commands.set()
                try {
                    console.log('  🔄 Méthode 2 : guild.commands.set()...');
                    await guild.commands.set(commands);
                    console.log('  ✅ Commandes enregistrées via guild.commands.set()');
                    
                    // Vérifier
                    const registeredCommands = await guild.commands.fetch();
                    console.log(`  📊 ${registeredCommands.size} commandes vérifiées :`);
                    registeredCommands.forEach(cmd => {
                        console.log(`     • /${cmd.name}`);
                    });
                    
                } catch (error2) {
                    console.error(`  ❌ Erreur guild.commands.set() :`, error2.message);
                    
                    // Méthode 3 : Enregistrer une par une
                    try {
                        console.log('  🔄 Méthode 3 : Enregistrement une par une...');
                        for (const command of commands) {
                            await guild.commands.create(command);
                            console.log(`     ✅ /${command.name} enregistrée`);
                            await new Promise(resolve => setTimeout(resolve, 500));
                        }
                        
                        // Vérifier
                        const registeredCommands = await guild.commands.fetch();
                        console.log(`  📊 ${registeredCommands.size} commandes vérifiées :`);
                        registeredCommands.forEach(cmd => {
                            console.log(`     • /${cmd.name}`);
                        });
                        
                    } catch (error3) {
                        console.error(`  ❌ Erreur enregistrement une par une :`, error3.message);
                    }
                }
            }
        }
        
        console.log('\n' + '='.repeat(60));
        console.log('🎉 FORÇAGE TERMINÉ !');
        console.log('='.repeat(60) + '\n');
        
        console.log('📌 INSTRUCTIONS :');
        console.log('1. Fermez complètement Discord');
        console.log('2. Rouvrez Discord');
        console.log('3. Allez dans un salon texte');
        console.log('4. Tapez / et attendez');
        console.log('5. Les commandes devraient apparaître !\n');
        
    } catch (error) {
        console.error('❌ Erreur critique :', error);
    }
    
    // Quitter après 10 secondes
    setTimeout(() => {
        console.log('👋 Fermeture...\n');
        process.exit(0);
    }, 10000);
});

// Gestion des erreurs
client.on('error', error => {
    console.error('❌ Erreur Discord:', error);
});

process.on('unhandledRejection', error => {
    console.error('❌ Erreur non gérée:', error);
});

// Connexion
console.log('🚀 Démarrage du forçage simple...');
client.login(TOKEN).catch(error => {
    console.error('❌ Impossible de se connecter:', error.message);
    process.exit(1);
}); 