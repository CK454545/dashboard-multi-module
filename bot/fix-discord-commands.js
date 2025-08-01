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

// Définir EXACTEMENT les mêmes commandes que dans bot.js
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
    console.log(`\n✅ Bot connecté en tant que ${client.user.tag}`);
    console.log(`🆔 Bot ID: ${client.user.id}`);
    console.log(`📊 Nombre de serveurs: ${client.guilds.cache.size}\n`);
    
    try {
        console.log('🔧 RÉINITIALISATION COMPLÈTE DES COMMANDES DISCORD...\n');
        
        // Créer une instance REST pour les commandes globales
        const rest = new REST({ version: '10' }).setToken(TOKEN);
        
        // ÉTAPE 1 : Supprimer TOUTES les commandes globales
        console.log('🗑️  Suppression des commandes globales...');
        try {
            await rest.put(Routes.applicationCommands(client.user.id), { body: [] });
            console.log('✅ Commandes globales supprimées\n');
        } catch (error) {
            console.log('⚠️  Pas de commandes globales à supprimer\n');
        }
        
        // ÉTAPE 2 : Pour chaque serveur
        for (const guild of client.guilds.cache.values()) {
            console.log(`\n📋 Traitement du serveur: ${guild.name} (${guild.id})`);
            
            try {
                // Supprimer toutes les commandes du serveur
                console.log('  🗑️  Suppression des anciennes commandes...');
                await guild.commands.set([]);
                
                // Attendre que Discord synchronise
                await new Promise(resolve => setTimeout(resolve, 2000));
                
                // Enregistrer les nouvelles commandes
                console.log('  📝 Enregistrement des nouvelles commandes...');
                await guild.commands.set(commands);
                
                // Attendre encore un peu
                await new Promise(resolve => setTimeout(resolve, 1000));
                
                // Vérifier que les commandes sont bien enregistrées
                const registeredCommands = await guild.commands.fetch();
                console.log(`  ✅ ${registeredCommands.size} commandes enregistrées :`);
                
                registeredCommands.forEach(cmd => {
                    console.log(`     • /${cmd.name} - ${cmd.description}`);
                });
                
            } catch (error) {
                console.error(`  ❌ Erreur sur ${guild.name}:`, error.message);
                
                // Si erreur de permissions
                if (error.message.includes('Missing Access')) {
                    console.log('  ⚠️  Le bot n\'a pas les permissions nécessaires sur ce serveur');
                    console.log('  💡 Réinvitez le bot avec les permissions "applications.commands"');
                }
            }
        }
        
        console.log('\n' + '='.repeat(60));
        console.log('🎉 RÉINITIALISATION TERMINÉE !');
        console.log('='.repeat(60) + '\n');
        
        console.log('📌 IMPORTANT - Que faire maintenant :\n');
        console.log('1️⃣  Fermez complètement Discord (pas juste la fenêtre)');
        console.log('2️⃣  Rouvrez Discord');
        console.log('3️⃣  Allez dans un salon texte');
        console.log('4️⃣  Tapez / et attendez quelques secondes');
        console.log('5️⃣  Les commandes devraient apparaître !\n');
        
        console.log('⚠️  Si les commandes n\'apparaissent toujours pas :');
        console.log('   • Vérifiez que le bot a la permission "Utiliser les commandes slash"');
        console.log('   • Essayez dans un autre salon');
        console.log('   • Attendez jusqu\'à 1 heure (cache Discord)\n');
        
    } catch (error) {
        console.error('❌ Erreur critique:', error);
    }
    
    // Attendre 15 secondes puis quitter
    setTimeout(() => {
        console.log('👋 Fermeture du script de réparation...\n');
        process.exit(0);
    }, 15000);
});

// Gestion des erreurs
client.on('error', error => {
    console.error('❌ Erreur Discord:', error);
});

process.on('unhandledRejection', error => {
    console.error('❌ Erreur non gérée:', error);
});

// Connexion
console.log('🚀 Démarrage du script de réparation des commandes Discord...');
client.login(TOKEN).catch(error => {
    console.error('❌ Impossible de se connecter à Discord:', error.message);
    process.exit(1);
});