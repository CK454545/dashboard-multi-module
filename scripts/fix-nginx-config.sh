#!/bin/bash

# ================================================================
# 🌐 Script de Correction de la Configuration Nginx
# ================================================================

# Couleurs pour l'affichage
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
CYAN='\033[0;36m'
NC='\033[0m' # No Color

# Configuration
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_DIR="$(dirname "$SCRIPT_DIR")"
CONFIG_FILE="$PROJECT_DIR/config/config.json"

print_message() {
    echo -e "${2}${1}${NC}"
}

print_message "🌐 CORRECTION DE LA CONFIGURATION NGINX" "$BLUE"
echo "═══════════════════════════════════════════════════════════════"
echo ""

# 1. Extraire le domaine de la configuration
print_message "📋 Lecture de la configuration..." "$CYAN"

if [ -f "$CONFIG_FILE" ]; then
    DOMAIN=$(jq -r '.website.url' "$CONFIG_FILE" | sed 's|https\?://||' | cut -d'/' -f1)
    if [ "$DOMAIN" = "null" ] || [ "$DOMAIN" = "localhost" ]; then
        DOMAIN="localhost"
    fi
    print_message "🌐 Domaine détecté: $DOMAIN" "$GREEN"
else
    print_message "❌ Fichier config.json manquant!" "$RED"
    exit 1
fi

# 2. Créer une configuration Nginx sécurisée
print_message "🔧 Création de la configuration Nginx..." "$CYAN"

# Créer le fichier de configuration Nginx
sudo tee /etc/nginx/sites-available/dashboard-multi-modules > /dev/null <<EOF
server {
    listen 80;
    server_name $DOMAIN www.$DOMAIN;

    # Root directory
    root $PROJECT_DIR/web;
    index index.php index.html;

    # Logs
    access_log /var/log/nginx/dashboard-access.log;
    error_log /var/log/nginx/dashboard-error.log;

    # Security headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header Referrer-Policy "no-referrer-when-downgrade" always;
    add_header Content-Security-Policy "default-src 'self' http: https: data: blob: 'unsafe-inline'" always;

    # Block access to sensitive files
    location ~ /\.(htaccess|htpasswd|git|svn) {
        deny all;
        return 404;
    }

    location ~ /\.(json|db|bat|js|log)$ {
        deny all;
        return 404;
    }

    # Block access to backup and config directories
    location ~ /(backups|config|database|scripts)/ {
        deny all;
        return 404;
    }

    # PHP processing
    location ~ \.php$ {
        try_files \$uri =404;
        fastcgi_split_path_info ^(.+\.php)(/.+)$;
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_index index.php;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME \$document_root\$fastcgi_script_name;
        fastcgi_param PATH_INFO \$fastcgi_path_info;
    }

    # Handle static files
    location ~* \.(css|js|png|jpg|jpeg|gif|ico|svg|woff|woff2|ttf|eot)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
        try_files \$uri =404;
    }

    # Main location block
    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
        
        # Security: Block common attack patterns
        if (\$request_uri ~* "\.(sh|bash|cmd|bat|exe|dll|so|pl|py|rb|php|jsp|asp|aspx|ashx|asmx|axd|config|ini|log|bak|backup|old|tmp|temp|swp|swo|~)$") {
            return 404;
        }
        
        # Block suspicious user agents
        if (\$http_user_agent ~* "(bot|crawler|spider|scraper|wget|curl|python|java|perl|ruby|php|asp|jsp|shell|bash|cmd|powershell)") {
            return 403;
        }
    }

    # Block access to sensitive endpoints
    location ~ ^/(admin|wp-admin|administrator|manage|management|control|console|debug|test|api/admin) {
        deny all;
        return 404;
    }

    # Block access to common attack paths
    location ~ ^/(shell|cmd|exec|system|eval|base64|decode|encode|phpinfo|info|status|health|ping|test) {
        deny all;
        return 404;
    }

    # Error pages
    error_page 404 /404.html;
    error_page 500 502 503 504 /50x.html;
    
    location = /50x.html {
        root /usr/share/nginx/html;
    }
}
EOF

print_message "✅ Configuration Nginx créée" "$GREEN"

# 3. Activer le site
print_message "🔗 Activation du site..." "$CYAN"

# Supprimer la configuration par défaut
sudo rm -f /etc/nginx/sites-enabled/default

# Activer notre configuration
sudo ln -sf /etc/nginx/sites-available/dashboard-multi-modules /etc/nginx/sites-enabled/

print_message "✅ Site activé" "$GREEN"

# 4. Vérifier la configuration
print_message "🔍 Vérification de la configuration..." "$CYAN"

if sudo nginx -t; then
    print_message "✅ Configuration Nginx valide" "$GREEN"
else
    print_message "❌ Erreur dans la configuration Nginx" "$RED"
    print_message "🔧 Tentative de correction..." "$YELLOW"
    
    # Configuration de fallback plus simple
    sudo tee /etc/nginx/sites-available/dashboard-multi-modules > /dev/null <<EOF
server {
    listen 80;
    server_name $DOMAIN www.$DOMAIN;
    
    root $PROJECT_DIR/web;
    index index.php;
    
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        include fastcgi_params;
    }
    
    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }
}
EOF
    
    if sudo nginx -t; then
        print_message "✅ Configuration de fallback valide" "$GREEN"
    else
        print_message "❌ Impossible de corriger la configuration" "$RED"
        exit 1
    fi
fi

# 5. Corriger les permissions du répertoire web
print_message "🔑 Correction des permissions..." "$CYAN"

# S'assurer que www-data peut accéder au répertoire web
sudo chown -R www-data:www-data "$PROJECT_DIR/web" 2>/dev/null
sudo chmod -R 755 "$PROJECT_DIR/web" 2>/dev/null

# Créer un fichier index.php par défaut s'il n'existe pas
if [ ! -f "$PROJECT_DIR/web/index.php" ]; then
    print_message "📄 Création d'un fichier index.php par défaut..." "$YELLOW"
    sudo tee "$PROJECT_DIR/web/index.php" > /dev/null <<EOF
<?php
// Dashboard Multi-Modules
// Fichier d'accueil par défaut

// Rediriger vers le dashboard principal
if (file_exists('dashboard.php')) {
    header('Location: dashboard.php');
    exit;
}

// Page d'accueil simple si dashboard.php n'existe pas
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Multi-Modules</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; background: #f5f5f5; }
        .container { max-width: 600px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #333; text-align: center; }
        .status { padding: 15px; margin: 20px 0; border-radius: 5px; }
        .success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .info { background: #d1ecf1; color: #0c5460; border: 1px solid #bee5eb; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🚀 Dashboard Multi-Modules</h1>
        <div class="status success">
            ✅ Serveur web opérationnel
        </div>
        <div class="status info">
            ℹ️ Le système est en cours de configuration...
        </div>
        <p>Si vous voyez cette page, cela signifie que :</p>
        <ul>
            <li>✅ Nginx fonctionne correctement</li>
            <li>✅ PHP est configuré</li>
            <li>✅ Les permissions sont correctes</li>
        </ul>
        <p><strong>Prochaine étape :</strong> Configurez votre application principale.</p>
    </div>
</body>
</html>
EOF
    print_message "✅ Fichier index.php créé" "$GREEN"
fi

# 6. Redémarrer Nginx
print_message "🔄 Redémarrage de Nginx..." "$CYAN"

sudo systemctl restart nginx

if systemctl is-active --quiet nginx; then
    print_message "✅ Nginx redémarré avec succès" "$GREEN"
else
    print_message "❌ Erreur lors du redémarrage de Nginx" "$RED"
    print_message "🔧 Tentative de diagnostic..." "$YELLOW"
    sudo systemctl status nginx --no-pager
fi

# 7. Vérification finale
print_message "📊 Vérification finale..." "$CYAN"
echo ""

# Vérifier que Nginx fonctionne
if systemctl is-active --quiet nginx; then
    echo -e "${GREEN}✅ Nginx: Actif${NC}"
else
    echo -e "${RED}❌ Nginx: Inactif${NC}"
fi

# Vérifier que PHP-FPM fonctionne
if systemctl is-active --quiet php8.1-fpm; then
    echo -e "${GREEN}✅ PHP-FPM: Actif${NC}"
else
    echo -e "${RED}❌ PHP-FPM: Inactif${NC}"
fi

# Tester l'accès web
print_message "🌐 Test d'accès web..." "$CYAN"
if curl -s -o /dev/null -w "%{http_code}" http://localhost/ | grep -q "200\|301\|302"; then
    print_message "✅ Accès web fonctionnel" "$GREEN"
else
    print_message "⚠️  Problème d'accès web détecté" "$YELLOW"
fi

echo ""
print_message "🎉 Configuration Nginx terminée!" "$GREEN"
print_message "💡 Votre site devrait maintenant être accessible" "$CYAN" 