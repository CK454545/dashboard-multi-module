const { Client, GatewayIntentBits, PermissionFlagsBits } = require('discord.js');
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
    console.log(`\n${'='.repeat(60)}`);
    console.log('🤖 VÉRIFICATION DES PERMISSIONS DU BOT');
    console.log(`${'='.repeat(60)}\n`);
    
    console.log(`✅ Bot connecté : ${client.user.tag}`);
    console.log(`🆔 Bot ID : ${client.user.id}\n`);
    
    // Permissions requises pour les commandes slash
    const requiredPermissions = [
        { flag: PermissionFlagsBits.ViewChannel, name: 'Voir les salons' },
        { flag: PermissionFlagsBits.SendMessages, name: 'Envoyer des messages' },
        { flag: PermissionFlagsBits.EmbedLinks, name: 'Intégrer des liens' },
        { flag: PermissionFlagsBits.AttachFiles, name: 'Joindre des fichiers' },
        { flag: PermissionFlagsBits.ReadMessageHistory, name: 'Voir l\'historique des messages' },
        { flag: PermissionFlagsBits.UseApplicationCommands, name: 'Utiliser les commandes slash' },
        { flag: PermissionFlagsBits.ManageMessages, name: 'Gérer les messages' }
    ];
    
    // Vérifier les permissions sur chaque serveur
    for (const guild of client.guilds.cache.values()) {
        console.log(`\n📋 Serveur : ${guild.name} (${guild.id})`);
        console.log('─'.repeat(50));
        
        const botMember = guild.members.cache.get(client.user.id);
        if (!botMember) {
            console.log('❌ Le bot n\'est pas membre de ce serveur !');
            continue;
        }
        
        console.log('🔑 Permissions actuelles :');
        let missingPermissions = [];
        
        for (const perm of requiredPermissions) {
            const hasPermission = botMember.permissions.has(perm.flag);
            console.log(`  ${hasPermission ? '✅' : '❌'} ${perm.name}`);
            if (!hasPermission) {
                missingPermissions.push(perm.name);
            }
        }
        
        if (missingPermissions.length > 0) {
            console.log(`\n⚠️  Permissions manquantes : ${missingPermissions.join(', ')}`);
        } else {
            console.log('\n✅ Toutes les permissions requises sont accordées !');
        }
        
        // Vérifier spécifiquement les commandes
        try {
            const commands = await guild.commands.fetch();
            console.log(`\n📊 Commandes enregistrées : ${commands.size}`);
            if (commands.size > 0) {
                commands.forEach(cmd => {
                    console.log(`   • /${cmd.name}`);
                });
            }
        } catch (error) {
            console.log('\n❌ Impossible de récupérer les commandes');
        }
    }
    
    // Générer le lien d'invitation correct
    const permissions = BigInt(0);
    const scopes = ['bot', 'applications.commands'];
    
    // Calculer les permissions nécessaires
    let permissionValue = BigInt(0);
    permissionValue |= PermissionFlagsBits.ViewChannel;
    permissionValue |= PermissionFlagsBits.SendMessages;
    permissionValue |= PermissionFlagsBits.EmbedLinks;
    permissionValue |= PermissionFlagsBits.AttachFiles;
    permissionValue |= PermissionFlagsBits.ReadMessageHistory;
    permissionValue |= PermissionFlagsBits.UseApplicationCommands;
    permissionValue |= PermissionFlagsBits.ManageMessages;
    
    const inviteLink = `https://discord.com/api/oauth2/authorize?client_id=${client.user.id}&permissions=${permissionValue}&scope=${scopes.join('%20')}`;
    
    console.log(`\n${'='.repeat(60)}`);
    console.log('🔗 LIEN D\'INVITATION AVEC LES BONNES PERMISSIONS :');
    console.log(`${'='.repeat(60)}\n`);
    console.log(inviteLink);
    console.log(`\n${'='.repeat(60)}\n`);
    
    console.log('💡 CONSEILS :');
    console.log('1. Si les permissions sont manquantes, réinvitez le bot avec le lien ci-dessus');
    console.log('2. Assurez-vous que le rôle du bot est placé HAUT dans la hiérarchie');
    console.log('3. Vérifiez que le bot n\'est pas restreint dans les paramètres du serveur\n');
    
    // Quitter après 10 secondes
    setTimeout(() => {
        console.log('👋 Fermeture du script de vérification...\n');
        process.exit(0);
    }, 10000);
});

// Connexion
console.log('🚀 Démarrage de la vérification des permissions...');
client.login(config.discord.token).catch(error => {
    console.error('❌ Impossible de se connecter:', error.message);
    process.exit(1);
});