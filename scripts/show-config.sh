#!/bin/bash

# Script pour afficher facilement votre configuration
echo "📋 VOTRE CONFIGURATION STREAMPRO STUDIO"
echo "========================================"
echo ""

# Variables
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_DIR="$(dirname "$SCRIPT_DIR")"
CONFIG_FILE="$PROJECT_DIR/config/config.json"

echo "📁 Localisation: $CONFIG_FILE"
echo ""

if [ -f "$CONFIG_FILE" ]; then
    echo "✅ Fichier trouvé !"
    echo ""
    
    # Affichage formaté avec détails
    echo "🌐 CONFIGURATION WEB:"
    echo "├── URL: $(grep -o '"url"[^,]*' "$CONFIG_FILE" | cut -d'"' -f4)"
    echo "└── Port: $(grep -o '"port"[^,}]*' "$CONFIG_FILE" | grep -o '[0-9]*')"
    echo ""
    
    echo "🤖 CONFIGURATION DISCORD:"
    echo "├── Token: $(grep -o '"token"[^,]*' "$CONFIG_FILE" | cut -d'"' -f4 | cut -c1-20)..."
    echo "└── Support: $(grep -o '"support_url"[^,]*' "$CONFIG_FILE" | cut -d'"' -f4)"
    echo ""
    
    echo "🗄️ CONFIGURATION BASE DE DONNÉES:"
    echo "└── Fichier: $(grep -o '"file"[^,]*' "$CONFIG_FILE" | cut -d'"' -f4)"
    echo ""
    
    echo "📱 INFORMATIONS APPLICATION:"
    echo "├── Nom: $(grep -o '"name"[^,]*' "$CONFIG_FILE" | cut -d'"' -f4)"
    echo "├── Description: $(grep -o '"description"[^,]*' "$CONFIG_FILE" | cut -d'"' -f4)"
    echo "├── Version: $(grep -o '"version"[^,]*' "$CONFIG_FILE" | cut -d'"' -f4)"
    echo "└── Copyright: $(grep -o '"copyright"[^}]*' "$CONFIG_FILE" | cut -d'"' -f4)"
    echo ""
    
    echo "📋 CONFIGURATION COMPLÈTE (JSON):"
    echo "=================================="
    if command -v jq >/dev/null 2>&1; then
        cat "$CONFIG_FILE" | jq .
    elif command -v python3 >/dev/null 2>&1; then
        cat "$CONFIG_FILE" | python3 -m json.tool
    else
        cat "$CONFIG_FILE"
    fi
    
else
    echo "❌ Fichier config.json non trouvé !"
    echo ""
    echo "💡 Vérifiez que vous êtes dans le bon répertoire ou que le fichier existe."
    echo "📁 Chemin attendu: $CONFIG_FILE"
fi

echo ""
echo "💡 Pour modifier la configuration:"
echo "   nano $CONFIG_FILE"
echo ""
echo "🔧 Pour valider avec le système:"
echo "   ./scripts/ubuntu-manager.sh (Option 14)"