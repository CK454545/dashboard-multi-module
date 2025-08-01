#!/bin/bash

# Script pour déployer le bot.js corrigé sur le serveur de production
# À exécuter depuis le dossier du projet

echo "🚀 Déploiement du bot.js corrigé..."

# Vérifier que nous sommes dans le bon dossier
if [ ! -f "bot/bot.js" ]; then
    echo "❌ Erreur: Exécutez ce script depuis la racine du projet"
    exit 1
fi

# Sauvegarder l'ancien fichier
echo "📦 Sauvegarde de l'ancien fichier..."
cp bot/bot.js bot/bot.js.backup.$(date +%Y%m%d_%H%M%S)

# Vérifier la syntaxe
echo "✅ Vérification de la syntaxe..."
cd bot && node -c bot.js

if [ $? -eq 0 ]; then
    echo "✅ Syntaxe correcte!"
    
    # Redémarrer le bot avec PM2
    echo "🔄 Redémarrage du bot Discord..."
    pm2 restart discord-bot
    
    # Afficher le statut
    echo "📊 Statut du bot:"
    pm2 status discord-bot
    
    # Afficher les derniers logs
    echo -e "\n📋 Derniers logs (attendez quelques secondes):"
    sleep 3
    pm2 logs discord-bot --lines 20 --nostream
    
    echo -e "\n✅ Bot Discord déployé avec succès!"
    echo "💡 Pour voir les logs en temps réel: pm2 logs discord-bot"
else
    echo "❌ Erreur de syntaxe dans le fichier!"
    exit 1
fi