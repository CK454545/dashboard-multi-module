# 🚨 Guide de Correction des Problèmes - Dashboard Multi-Modules

## 📋 Problèmes Détectés

D'après votre vérification système, les problèmes suivants ont été identifiés :

### ❌ Problèmes Critiques
1. **Nginx** : Problème de configuration (erreur 403)
2. **Base de données** : Problème de permissions (R:true W:false)
3. **config.json** : Chemin de base de données incorrect

### ⚠️ Problèmes Mineurs
1. **UFW** : Non installé ou non accessible
2. **Backup** : Aucune sauvegarde trouvée
3. **Domaine** : localhost ou non configuré

## 🔧 Solutions Automatiques

### Option 1 : Correction Complète (Recommandée)
```bash
# Sur votre VPS Ubuntu, exécutez :
cd /var/www/dashboard-multi-modules
chmod +x scripts/fix-all-issues.sh
sudo ./scripts/fix-all-issues.sh
```

### Option 2 : Corrections Individuelles

#### A. Corriger les Permissions de la Base de Données
```bash
chmod +x scripts/fix-permissions.sh
sudo ./scripts/fix-permissions.sh
```

#### B. Corriger la Configuration Nginx
```bash
chmod +x scripts/fix-nginx-config.sh
sudo ./scripts/fix-nginx-config.sh
```

#### C. Utiliser le Script Principal
```bash
chmod +x scripts/ubuntu-manager.sh
sudo ./scripts/ubuntu-manager.sh
```
Puis choisir l'option **14) 🛡️ Vérification système complète**

## 📝 Corrections Manuelles (si nécessaire)

### 1. Corriger le Fichier config.json
Le fichier `config/config.json` a été corrigé automatiquement :
```json
{
    "database": {
        "file": "database/database.db"  // ✅ Corrigé
    }
}
```

### 2. Permissions de la Base de Données
```bash
# Corriger les permissions
sudo chown www-data:www-data /var/www/dashboard-multi-modules/database/database.db
sudo chmod 664 /var/www/dashboard-multi-modules/database/database.db

# Ajouter l'utilisateur au groupe www-data
sudo usermod -a -G www-data ubuntu
```

### 3. Configuration Nginx
```bash
# Redémarrer Nginx
sudo systemctl restart nginx

# Vérifier le statut
sudo systemctl status nginx
```

### 4. Redémarrer les Services
```bash
# Redémarrer PHP-FPM
sudo systemctl restart php8.1-fpm

# Redémarrer les services PM2
pm2 restart all
```

## 🧪 Tests de Vérification

### Test de la Base de Données
```bash
# Vérifier l'accès
sqlite3 /var/www/dashboard-multi-modules/database/database.db "SELECT 1;"

# Vérifier l'intégrité
sqlite3 /var/www/dashboard-multi-modules/database/database.db "PRAGMA integrity_check;"
```

### Test de l'Accès Web
```bash
# Tester localement
curl -I http://localhost/

# Tester avec le domaine
curl -I http://myfullagency-connect.fr/
```

### Test des Services
```bash
# Vérifier Nginx
sudo systemctl status nginx

# Vérifier PHP-FPM
sudo systemctl status php8.1-fpm

# Vérifier PM2
pm2 status
```

## 🔍 Diagnostic Avancé

### Vérifier les Logs
```bash
# Logs Nginx
sudo tail -f /var/log/nginx/dashboard-error.log
sudo tail -f /var/log/nginx/dashboard-access.log

# Logs PM2
pm2 logs discord-bot
```

### Vérifier les Permissions
```bash
# Vérifier les permissions du projet
ls -la /var/www/dashboard-multi-modules/

# Vérifier les permissions de la base de données
ls -la /var/www/dashboard-multi-modules/database/
```

## 🚀 Après Correction

Une fois les corrections appliquées :

1. **Vérifiez l'accès web** : `http://myfullagency-connect.fr/`
2. **Testez le bot Discord** : Vérifiez les logs PM2
3. **Relancez la vérification** : Option 14 du script principal

## 📞 Support

Si des problèmes persistent après application des corrections :

1. Vérifiez les logs d'erreur
2. Relancez le script de correction
3. Consultez les logs PM2 pour le bot Discord

## 🔒 Sécurité

Les scripts incluent des mesures de sécurité :
- Blocage des accès aux fichiers sensibles
- Headers de sécurité HTTP
- Protection contre les attaques communes
- Permissions restrictives par défaut

---

**💡 Conseil** : Exécutez d'abord le script de correction complète (`fix-all-issues.sh`) pour résoudre tous les problèmes en une seule fois. 