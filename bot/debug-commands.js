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
        .setDescription('Ping pong')
];

client.once('ready', async () => {
    console.log(`\n${'='.repeat(60)}`);
    console.log('🔍 DIAGNOSTIC DÉTAILLÉ DES COMMANDES DISCORD');
    console.log(`${'='.repeat(60)}\n`);
    
    console.log(`✅ Bot connecté : ${client.user.tag}`);
    console.log(`🆔 Bot ID : ${client.user.id}\n`);
    
    try {
        // Test 1 : Vérifier les permissions du bot
        console.log('📋 TEST 1 : Vérification des permissions...');
        for (const guild of client.guilds.cache.values()) {
            console.log(`\n📋 Serveur : ${guild.name} (${guild.id})`);
            
            const botMember = guild.members.cache.get(client.user.id);
            if (!botMember) {
                console.log('❌ Bot non membre du serveur');
                continue;
            }
            
            const hasManageGuild = botMember.permissions.has('ManageGuild');
            const hasUseApplicationCommands = botMember.permissions.has('UseApplicationCommands');
            
            console.log(`  ✅ ManageGuild : ${hasManageGuild}`);
            console.log(`  ✅ UseApplicationCommands : ${hasUseApplicationCommands}`);
            
            if (!hasManageGuild) {
                console.log('  ⚠️  Le bot n\'a pas la permission "Gérer le serveur"');
                console.log('  💡 Cette permission est nécessaire pour enregistrer des commandes');
            }
        }
        
        // Test 2 : Essayer d'enregistrer une seule commande
        console.log('\n📋 TEST 2 : Test d\'enregistrement d\'une commande...');
        for (const guild of client.guilds.cache.values()) {
            console.log(`\n📋 Test sur : ${guild.name}`);
            
            try {
                // Essayer d'enregistrer une seule commande de test
                console.log('  🔄 Tentative d\'enregistrement...');
                await guild.commands.set([commands[0]]);
                console.log('  ✅ Commande de test enregistrée avec succès !');
                
                // Vérifier
                const testCommands = await guild.commands.fetch();
                console.log(`  📊 Commandes après test : ${testCommands.size}`);
                
                // Nettoyer
                await guild.commands.set([]);
                console.log('  🧹 Nettoyage effectué');
                
            } catch (error) {
                console.error(`  ❌ Erreur lors du test :`, error.message);
                console.error(`  📍 Code d'erreur :`, error.code);
                console.error(`  📍 Status :`, error.status);
                
                if (error.code === 50001) {
                    console.log('  💡 Erreur 50001 : Le bot n\'a pas accès au serveur');
                } else if (error.code === 50013) {
                    console.log('  💡 Erreur 50013 : Permissions manquantes');
                } else if (error.code === 50035) {
                    console.log('  💡 Erreur 50035 : Payload invalide');
                }
            }
        }
        
        // Test 3 : Vérifier l'API Discord
        console.log('\n📋 TEST 3 : Test de l\'API Discord...');
        try {
            const rest = new REST({ version: '10' }).setToken(TOKEN);
            const application = await rest.get(Routes.oauth2CurrentApplication());
            console.log('  ✅ API Discord accessible');
            console.log(`  📊 Application : ${application.name}`);
        } catch (error) {
            console.error('  ❌ Erreur API Discord :', error.message);
        }
        
        // Test 4 : Générer un nouveau lien d'invitation avec toutes les permissions
        console.log('\n📋 TEST 4 : Génération du lien d\'invitation...');
        const permissionValue = BigInt(0);
        permissionValue |= BigInt(0x8); // Administrator
        permissionValue |= BigInt(0x20); // ManageGuild
        permissionValue |= BigInt(0x8000000); // UseApplicationCommands
        
        const inviteLink = `https://discord.com/api/oauth2/authorize?client_id=${client.user.id}&permissions=${permissionValue}&scope=bot%20applications.commands`;
        
        console.log('🔗 LIEN D\'INVITATION AVEC PERMISSIONS ADMIN :');
        console.log(inviteLink);
        
    } catch (error) {
        console.error('❌ Erreur critique :', error);
    }
    
    // Quitter après 10 secondes
    setTimeout(() => {
        console.log('\n👋 Fermeture du diagnostic...\n');
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
console.log('🚀 Démarrage du diagnostic...');
client.login(TOKEN).catch(error => {
    console.error('❌ Impossible de se connecter:', error.message);
    process.exit(1);
}); 