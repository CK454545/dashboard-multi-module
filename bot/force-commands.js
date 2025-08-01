const { Client, GatewayIntentBits, Collection } = require('discord.js');
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

// Commandes à enregistrer
const commands = [
    {
        name: 'wait',
        description: 'Attendre son tour',
        type: 1
    },
    {
        name: 'mfa',
        description: 'Accéder au dashboard MFA',
        type: 1
    },
    {
        name: 'start-mfa',
        description: 'Démarrer une session MFA',
        type: 1
    },
    {
        name: 'mfa-list',
        description: 'Voir la liste des sessions MFA',
        type: 1
    },
    {
        name: 'voc',
        description: 'Accéder au dashboard VOC',
        type: 1
    },
    {
        name: 'end',
        description: 'Terminer une session',
        type: 1
    },
    {
        name: 'supptoken',
        description: 'Supprimer un token',
        type: 1
    },
    {
        name: 'carte',
        description: 'Voir sa carte utilisateur',
        type: 1
    },
    {
        name: 'infos',
        description: 'Voir les informations utilisateur',
        type: 1
    }
];

client.once('ready', async () => {
    console.log(`✅ Bot connecté en tant que ${client.user.tag}`);
    console.log(`🆔 Bot ID: ${client.user.id}`);
    
    try {
        // 1. Supprimer toutes les commandes globales
        console.log('🗑️ Suppression des commandes globales...');
        await client.application.commands.set([]);
        console.log('✅ Commandes globales supprimées');
        
        // 2. Enregistrer les nouvelles commandes globalement
        console.log('📝 Enregistrement des commandes globales...');
        await client.application.commands.set(commands);
        console.log('✅ Commandes globales enregistrées');
        
        // 3. Enregistrer aussi sur chaque serveur pour accélérer
        console.log('🏠 Enregistrement sur les serveurs...');
        for (const guild of client.guilds.cache.values()) {
            try {
                console.log(`📋 Enregistrement sur: ${guild.name} (${guild.id})`);
                await guild.commands.set(commands);
                console.log(`✅ Commandes enregistrées sur ${guild.name}`);
            } catch (error) {
                console.log(`❌ Erreur sur ${guild.name}: ${error.message}`);
            }
        }
        
        console.log('');
        console.log('🎉 ENREGISTREMENT TERMINÉ !');
        console.log('');
        console.log('📋 Commandes enregistrées :');
        commands.forEach(cmd => {
            console.log(`  • /${cmd.name} - ${cmd.description}`);
        });
        console.log('');
        console.log('💡 Les commandes devraient apparaître dans Discord dans 1-2 minutes');
        console.log('   Si elles n\'apparaissent pas, essayez de :');
        console.log('   1. Rafraîchir Discord (Ctrl+R)');
        console.log('   2. Taper / dans un canal');
        console.log('   3. Attendre 2-3 minutes supplémentaires');
        
    } catch (error) {
        console.error('❌ Erreur lors de l\'enregistrement:', error);
    }
    
    // Quitter après 5 secondes
    setTimeout(() => {
        console.log('👋 Fermeture du script...');
        process.exit(0);
    }, 5000);
});

client.login(config.discord.token); 