const { Client, GatewayIntentBits } = require('discord.js');
const fs = require('fs');
const path = require('path');

// Charger la configuration
const projectRoot = path.resolve(__dirname, '..');
const configPath = path.join(projectRoot, 'config', 'config.json');
const config = JSON.parse(fs.readFileSync(configPath, 'utf8'));

const TOKEN = config.discord.token;

console.log('🔍 TEST DU TOKEN DISCORD');
console.log('='.repeat(40));
console.log(`Token (premiers caractères) : ${TOKEN.substring(0, 20)}...`);
console.log(`Longueur du token : ${TOKEN.length} caractères`);

// Créer le client Discord
const client = new Client({ 
    intents: [
        GatewayIntentBits.Guilds,
        GatewayIntentBits.GuildMessages,
        GatewayIntentBits.MessageContent
    ] 
});

client.once('ready', async () => {
    console.log('\n✅ TOKEN VALIDE !');
    console.log(`Bot connecté : ${client.user.tag}`);
    console.log(`Bot ID : ${client.user.id}`);
    console.log(`Nombre de serveurs : ${client.guilds.cache.size}`);
    
    // Lister les serveurs
    console.log('\n📋 Serveurs connectés :');
    for (const guild of client.guilds.cache.values()) {
        console.log(`  • ${guild.name} (${guild.id})`);
    }
    
    process.exit(0);
});

client.on('error', error => {
    console.error('\n❌ Erreur Discord :', error.message);
    process.exit(1);
});

// Connexion
console.log('\n🔄 Tentative de connexion...');
client.login(TOKEN).catch(error => {
    console.error('\n❌ ERREUR DE CONNEXION :');
    console.error('Message :', error.message);
    console.error('Code :', error.code);
    
    if (error.message.includes('An invalid token was provided')) {
        console.log('\n💡 SOLUTION :');
        console.log('1. Allez sur https://discord.com/developers/applications');
        console.log('2. Sélectionnez votre application');
        console.log('3. Allez dans "Bot"');
        console.log('4. Cliquez sur "Reset Token"');
        console.log('5. Copiez le nouveau token');
        console.log('6. Mettez à jour config/config.json');
    }
    
    process.exit(1);
}); 