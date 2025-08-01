#!/bin/bash

echo "🧹 Nettoyage des conflits Git dans bot.js..."

# Chemin du fichier
BOT_FILE="/var/www/dashboard-multi-modules/bot/bot.js"
BACKUP_FILE="/var/www/dashboard-multi-modules/bot/bot.js.backup-$(date +%Y%m%d-%H%M%S)"

# Vérifier si le fichier existe
if [ ! -f "$BOT_FILE" ]; then
    echo "❌ Erreur : $BOT_FILE n'existe pas"
    exit 1
fi

# Créer une sauvegarde
echo "📦 Création d'une sauvegarde : $BACKUP_FILE"
sudo cp "$BOT_FILE" "$BACKUP_FILE"

# Compter les conflits
CONFLICTS=$(grep -c "<<<<<<< " "$BOT_FILE" 2>/dev/null || echo "0")
echo "🔍 Nombre de conflits trouvés : $CONFLICTS"

if [ "$CONFLICTS" -eq 0 ]; then
    echo "✅ Aucun conflit trouvé dans le fichier"
    exit 0
fi

# Créer un fichier temporaire
TEMP_FILE="/tmp/bot_cleaned.js"

echo "🔧 Nettoyage des conflits en cours..."

# Nettoyer les conflits en gardant la version "incoming" (après =======)
awk '
    /^<<<<<<< / { in_conflict = 1; in_ours = 1; next }
    /^=======/ { if (in_conflict) { in_ours = 0; in_theirs = 1 } next }
    /^>>>>>>> / { in_conflict = 0; in_ours = 0; in_theirs = 0; next }
    !in_conflict || in_theirs { print }
' "$BOT_FILE" > "$TEMP_FILE"

# Vérifier si le nettoyage a réussi
if [ -s "$TEMP_FILE" ]; then
    echo "✅ Nettoyage réussi"
    
    # Remplacer le fichier original
    sudo mv "$TEMP_FILE" "$BOT_FILE"
    
    # Corriger les permissions
    sudo chown ubuntu:ubuntu "$BOT_FILE"
    sudo chmod 644 "$BOT_FILE"
    
    echo "📝 Fichier nettoyé et permissions corrigées"
    
    # Vérifier la syntaxe
    echo "🔍 Vérification de la syntaxe..."
    if node -c "$BOT_FILE" 2>/dev/null; then
        echo "✅ Syntaxe JavaScript valide"
        
        # Redémarrer le bot
        echo "🚀 Redémarrage du bot..."
        cd /var/www/dashboard-multi-modules/bot
        pm2 restart discord-bot
        
        echo "✅ Bot redémarré avec succès"
    else
        echo "❌ Erreur de syntaxe détectée"
        echo "💡 Restauration de la sauvegarde..."
        sudo cp "$BACKUP_FILE" "$BOT_FILE"
        echo "⚠️  Veuillez vérifier manuellement le fichier"
    fi
else
    echo "❌ Erreur lors du nettoyage"
    rm -f "$TEMP_FILE"
    exit 1
fi

echo "✅ Processus terminé"