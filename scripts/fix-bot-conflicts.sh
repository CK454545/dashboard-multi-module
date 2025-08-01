#!/bin/bash

# Script pour corriger les conflits de merge dans bot.js sur le serveur de production

echo "🔧 Correction des conflits de merge dans bot.js..."

# Chemin du fichier sur le serveur de production
PROD_FILE="/var/www/dashboard-multi-modules/bot/bot.js"
LOCAL_FILE="/workspace/bot/bot.js"

# Vérifier si le fichier local existe et est propre
if [ ! -f "$LOCAL_FILE" ]; then
    echo "❌ Erreur: Le fichier local bot.js n'existe pas"
    exit 1
fi

# Vérifier qu'il n'y a pas de conflits dans le fichier local
if grep -q "<<<<<<\|>>>>>>>" "$LOCAL_FILE"; then
    echo "❌ Erreur: Le fichier local contient encore des conflits de merge!"
    exit 1
fi

# Sauvegarder l'ancien fichier
echo "📦 Sauvegarde de l'ancien fichier..."
sudo cp "$PROD_FILE" "$PROD_FILE.backup.$(date +%Y%m%d_%H%M%S)"

# Copier le nouveau fichier
echo "📄 Copie du fichier corrigé..."
sudo cp "$LOCAL_FILE" "$PROD_FILE"

# Corriger les permissions
echo "🔐 Correction des permissions..."
sudo chown ubuntu:ubuntu "$PROD_FILE"
sudo chmod 644 "$PROD_FILE"

# Vérifier la syntaxe
echo "✅ Vérification de la syntaxe..."
cd /var/www/dashboard-multi-modules/bot
node -c bot.js

if [ $? -eq 0 ]; then
    echo "✅ Syntaxe correcte!"
    
    # Redémarrer le bot avec PM2
    echo "🔄 Redémarrage du bot Discord..."
    pm2 restart discord-bot
    
    # Afficher le statut
    echo "📊 Statut du bot:"
    pm2 status discord-bot
    
    # Afficher les derniers logs
    echo -e "\n📋 Derniers logs:"
    pm2 logs discord-bot --lines 10 --nostream
    
    echo -e "\n✅ Bot Discord corrigé et redémarré avec succès!"
else
    echo "❌ Erreur de syntaxe dans le fichier! Restauration du backup..."
    sudo cp "$PROD_FILE.backup.$(date +%Y%m%d_%H%M%S)" "$PROD_FILE"
    exit 1
fi