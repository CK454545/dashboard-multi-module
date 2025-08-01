const { Client, GatewayIntentBits } = require('discord.js');
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

client.once('ready', async () => {
    console.log(`✅ Bot connecté en tant que ${client.user.tag}`);
    console.log(`🆔 Bot ID: ${client.user.id}`);
    console.log(`📊 Nombre de serveurs: ${client.guilds.cache.size}`);
    
    // Vérifier chaque serveur
    for (const guild of client.guilds.cache.values()) {
        console.log(`\n🏠 Serveur: ${guild.name} (${guild.id})`);
        
        // Vérifier les permissions du bot
        const botMember = guild.members.cache.get(client.user.id);
        if (botMember) {
            console.log(`👤 Rôle du bot: ${botMember.roles.highest.name}`);
            console.log(`🔑 Permissions du bot:`);
            console.log(`  • Use Slash Commands: ${botMember.permissions.has('UseApplicationCommands') ? '✅' : '❌'}`);
            console.log(`  • Send Messages: ${botMember.permissions.has('SendMessages') ? '✅' : '❌'}`);
            console.log(`  • Embed Links: ${botMember.permissions.has('EmbedLinks') ? '✅' : '❌'}`);
            console.log(`  • Read Message History: ${botMember.permissions.has('ReadMessageHistory') ? '✅' : '❌'}`);
            
            // Vérifier les commandes existantes
            try {
                const commands = await guild.commands.fetch();
                console.log(`📋 Commandes existantes: ${commands.size}`);
                commands.forEach(cmd => {
                    console.log(`  • /${cmd.name} - ${cmd.description}`);
                });
            } catch (error) {
                console.log(`❌ Erreur lors de la vérification des commandes: ${error.message}`);
            }
        } else {
            console.log(`❌ Bot non trouvé dans ce serveur`);
        }
    }
    
    console.log('\n🎯 DIAGNOSTIC TERMINÉ');
    console.log('\n💡 Si les permissions sont ❌ :');
    console.log('  1. Réinvitez le bot avec le bon lien');
    console.log('  2. Vérifiez les permissions dans les paramètres du serveur');
    console.log('  3. Le bot doit avoir "Use Slash Commands"');
    
    // Quitter après 5 secondes
    setTimeout(() => {
        console.log('\n👋 Fermeture du test...');
        process.exit(0);
    }, 5000);
});

client.login(config.discord.token); 