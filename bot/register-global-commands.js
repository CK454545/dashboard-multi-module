const { SlashCommandBuilder, REST, Routes } = require('discord.js');
const fs = require('fs');
const path = require('path');

// Charger la configuration
const projectRoot = path.resolve(__dirname, '..');
const configPath = path.join(projectRoot, 'config', 'config.json');
const config = JSON.parse(fs.readFileSync(configPath, 'utf8'));

const TOKEN = config.discord.token;

// Définir les commandes (exactement comme dans bot.js)
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

async function registerGlobalCommands() {
    console.log('🚀 ENREGISTREMENT GLOBAL DES COMMANDES DISCORD');
    console.log('='.repeat(50));
    
    try {
        // Créer l'instance REST
        const rest = new REST({ version: '10' }).setToken(TOKEN);
        
        // Récupérer l'ID de l'application depuis le token
        const tokenParts = TOKEN.split('.');
        const applicationId = Buffer.from(tokenParts[0], 'base64').toString();
        
        console.log(`📋 Application ID : ${applicationId}`);
        console.log(`📊 Nombre de commandes à enregistrer : ${commands.length}\n`);
        
        // Lister les commandes
        console.log('📝 Commandes à enregistrer :');
        commands.forEach((cmd, index) => {
            console.log(`  ${index + 1}. /${cmd.name} - ${cmd.description}`);
        });
        console.log('');
        
        // Supprimer d'abord toutes les commandes globales existantes
        console.log('🗑️  Suppression des commandes globales existantes...');
        try {
            await rest.put(Routes.applicationCommands(applicationId), { body: [] });
            console.log('✅ Commandes globales supprimées');
        } catch (error) {
            console.log('⚠️  Pas de commandes globales à supprimer');
        }
        
        // Attendre un peu
        await new Promise(resolve => setTimeout(resolve, 2000));
        
        // Enregistrer les nouvelles commandes globalement
        console.log('\n📝 Enregistrement des nouvelles commandes globales...');
        const data = await rest.put(
            Routes.applicationCommands(applicationId),
            { body: commands }
        );
        
        console.log(`✅ ${data.length} commandes enregistrées globalement !`);
        
        // Vérifier l'enregistrement
        console.log('\n🔍 Vérification de l\'enregistrement...');
        const registeredCommands = await rest.get(Routes.applicationCommands(applicationId));
        console.log(`✅ ${registeredCommands.length} commandes vérifiées :`);
        
        registeredCommands.forEach(cmd => {
            console.log(`  • /${cmd.name} - ${cmd.description}`);
        });
        
        console.log('\n' + '='.repeat(50));
        console.log('🎉 ENREGISTREMENT GLOBAL TERMINÉ !');
        console.log('='.repeat(50));
        
        console.log('\n📌 IMPORTANT :');
        console.log('• Les commandes globales peuvent prendre jusqu\'à 1 heure pour apparaître');
        console.log('• Fermez et rouvrez Discord pour forcer le rafraîchissement');
        console.log('• Tapez / dans un salon pour voir les commandes');
        console.log('\n⚠️  Note : Les commandes globales sont visibles sur TOUS les serveurs où le bot est présent');
        
    } catch (error) {
        console.error('❌ Erreur lors de l\'enregistrement global :', error);
        
        if (error.code === 50001) {
            console.log('\n💡 Le bot n\'a pas les permissions pour gérer les commandes globales');
            console.log('   Essayez de réinviter le bot avec les permissions "applications.commands"');
        } else if (error.code === 40001) {
            console.log('\n💡 Token invalide - vérifiez votre token Discord');
        } else if (error.code === 50035) {
            console.log('\n💡 Payload invalide - vérifiez la structure des commandes');
        }
    }
}

// Exécuter l'enregistrement
registerGlobalCommands().then(() => {
    console.log('\n👋 Fermeture du script...\n');
    process.exit(0);
}).catch(error => {
    console.error('❌ Erreur fatale :', error);
    process.exit(1);
}); 