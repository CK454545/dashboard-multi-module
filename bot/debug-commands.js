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

// Commandes simplifiées pour test
const commands = [
    new SlashCommandBuilder()
        .setName('test')
        .setDescription('Commande de test'),
    
    new SlashCommandBuilder()
        .setName('ping')
        .setDescription('Répond pong')
];

client.once('ready', async () => {
    console.log(`\n${'='.repeat(60)}`);
    console.log('🔍 DIAGNOSTIC DÉTAILLÉ DES COMMANDES DISCORD');
    console.log(`${'='.repeat(60)}\n`);
    
    console.log(`✅ Bot connecté : ${client.user.tag}`);
    console.log(`🆔 Bot ID : ${client.user.id}`);
    console.log(`📊 Nombre de serveurs : ${client.guilds.cache.size}\n`);
    
    try {
        // Test 1 : Vérifier l'accès à l'API Discord
        console.log('🧪 Test 1 : Vérification de l\'accès à l\'API Discord...');
        const rest = new REST({ version: '10' }).setToken(TOKEN);
        
        try {
            const globalCommands = await rest.get(Routes.applicationCommands(client.user.id));
            console.log(`✅ API accessible - ${globalCommands.length} commandes globales trouvées`);
        } catch (error) {
            console.log(`❌ Erreur API : ${error.message}`);
            if (error.code === 50001) {
                console.log('💡 Le bot n\'a pas les permissions pour gérer les commandes globales');
            }
        }
        
        // Test 2 : Vérifier chaque serveur individuellement
        for (const guild of client.guilds.cache.values()) {
            console.log(`\n📋 Test 2 : Diagnostic du serveur ${guild.name} (${guild.id})`);
            console.log('─'.repeat(50));
            
            try {
                // Vérifier les permissions du bot
                const botMember = guild.members.cache.get(client.user.id);
                if (!botMember) {
                    console.log('❌ Le bot n\'est pas membre de ce serveur');
                    continue;
                }
                
                console.log('🔑 Permissions du bot :');
                console.log(`  • Administrateur : ${botMember.permissions.has('Administrator') ? '✅' : '❌'}`);
                console.log(`  • Gérer les applications : ${botMember.permissions.has('ManageGuild') ? '✅' : '❌'}`);
                console.log(`  • Utiliser les commandes slash : ${botMember.permissions.has('UseApplicationCommands') ? '✅' : '❌'}`);
                
                // Test d'enregistrement d'une seule commande
                console.log('\n🧪 Test d\'enregistrement d\'une commande simple...');
                
                try {
                    // Supprimer d'abord toutes les commandes
                    await guild.commands.set([]);
                    console.log('✅ Commandes supprimées');
                    
                    // Attendre
                    await new Promise(resolve => setTimeout(resolve, 1000));
                    
                    // Enregistrer une seule commande de test
                    const testCommand = new SlashCommandBuilder()
                        .setName('test')
                        .setDescription('Commande de test');
                    
                    await guild.commands.create(testCommand);
                    console.log('✅ Commande de test enregistrée');
                    
                    // Vérifier
                    const registeredCommands = await guild.commands.fetch();
                    console.log(`✅ ${registeredCommands.size} commandes maintenant enregistrées`);
                    
                    // Nettoyer
                    await guild.commands.set([]);
                    console.log('✅ Nettoyage effectué');
                    
                } catch (error) {
                    console.log(`❌ Erreur lors de l'enregistrement : ${error.message}`);
                    console.log(`   Code d'erreur : ${error.code}`);
                    
                    if (error.code === 50001) {
                        console.log('💡 Le bot n\'a pas les permissions "applications.commands"');
                    } else if (error.code === 50013) {
                        console.log('💡 Le bot n\'a pas les permissions "Manage Guild"');
                    } else if (error.code === 10008) {
                        console.log('💡 Application inconnue - vérifiez l\'ID du bot');
                    }
                }
                
            } catch (error) {
                console.log(`❌ Erreur générale : ${error.message}`);
            }
        }
        
        // Test 3 : Essayer l'enregistrement global
        console.log('\n🧪 Test 3 : Tentative d\'enregistrement global...');
        try {
            await rest.put(Routes.applicationCommands(client.user.id), { body: commands });
            console.log('✅ Commandes globales enregistrées');
            
            // Vérifier
            const globalCommands = await rest.get(Routes.applicationCommands(client.user.id));
            console.log(`✅ ${globalCommands.length} commandes globales vérifiées`);
            
        } catch (error) {
            console.log(`❌ Erreur enregistrement global : ${error.message}`);
        }
        
        console.log('\n' + '='.repeat(60));
        console.log('📋 RÉSUMÉ DU DIAGNOSTIC');
        console.log('='.repeat(60));
        console.log('\n💡 SOLUTIONS POSSIBLES :');
        console.log('1. Réinvitez le bot avec le lien généré précédemment');
        console.log('2. Vérifiez que le bot a le rôle "Administrateur" temporairement');
        console.log('3. Essayez d\'enregistrer les commandes globalement au lieu de par serveur');
        console.log('4. Vérifiez que le token du bot est correct');
        console.log('\n🔗 Lien d\'invitation avec toutes les permissions :');
        console.log(`https://discord.com/api/oauth2/authorize?client_id=${client.user.id}&permissions=8&scope=bot%20applications.commands`);
        
    } catch (error) {
        console.error('❌ Erreur critique :', error);
    }
    
    // Quitter après 20 secondes
    setTimeout(() => {
        console.log('\n👋 Fermeture du diagnostic...\n');
        process.exit(0);
    }, 20000);
});

// Gestion des erreurs
client.on('error', error => {
    console.error('❌ Erreur Discord:', error);
});

process.on('unhandledRejection', error => {
    console.error('❌ Erreur non gérée:', error);
});

// Connexion
console.log('🚀 Démarrage du diagnostic des commandes Discord...');
client.login(TOKEN).catch(error => {
    console.error('❌ Impossible de se connecter:', error.message);
    process.exit(1);
}); 