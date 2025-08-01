# 🔧 Guide de Correction des Problèmes Système

## 📋 Problèmes Détectés

Votre système a détecté les problèmes suivants :

1. **❌ nginx: problème détecté** - Le serveur web n'est pas actif
2. **❌ Permissions de la base de données** - Problème d'accès en écriture
3. **❌ config.json: fichier manquant** - Configuration absente
4. **⚠️ ufw: non installé** - Pare-feu non configuré
5. **⚠️ Backup: aucune sauvegarde trouvée** - Pas de sauvegarde de sécurité

## 🚀 Solution Rapide

J'ai créé un script qui corrige automatiquement tous ces problèmes !

### Étape 1 : Lancer le script de correction

```bash
cd /chemin/vers/votre/projet
./scripts/fix-system-issues.sh
```

Le script va :
- ✅ Installer et configurer Nginx
- ✅ Créer la base de données avec les bonnes permissions
- ✅ Créer le fichier config.json avec une configuration par défaut
- ✅ Installer UFW (pare-feu) mais sans l'activer
- ✅ Créer un backup initial de votre base de données

### Étape 2 : Configurer votre projet

Après l'exécution du script, vous devez :

1. **Éditer le fichier de configuration** :
   ```bash
   nano config/config.json
   ```
   
   Remplacez les valeurs suivantes :
   - `YOUR_BOT_TOKEN_HERE` : Votre token Discord Bot
   - `YOUR_CLIENT_ID_HERE` : L'ID client de votre bot
   - `YOUR_GUILD_ID_HERE` : L'ID de votre serveur Discord
   - `http://localhost` : L'URL de votre site (ex: `http://votredomaine.com`)

2. **Relancer la vérification système** :
   ```bash
   ./scripts/ubuntu-manager.sh
   ```
   Puis choisissez l'option **14** pour vérifier que tout est corrigé.

### Étape 3 : Démarrer les services

Une fois tout configuré :
```bash
./scripts/ubuntu-manager.sh
```
Choisissez l'option **2** pour démarrer tous les services.

## 🔍 Détails des Corrections

### 1. Nginx
Le script :
- Installe Nginx s'il n'est pas présent
- Crée une configuration par défaut
- Démarre et active le service

### 2. Base de données
Le script :
- Crée le dossier `database/`
- Crée le fichier `database.db` avec le bon schéma
- Configure les permissions pour `www-data`
- Ajoute votre utilisateur au groupe `www-data`

### 3. Configuration
Le script :
- Crée le dossier `config/`
- Génère un `config.json` avec des valeurs par défaut
- Configure les bonnes permissions

### 4. UFW (Pare-feu)
Le script :
- Installe UFW
- Configure les règles (SSH, HTTP, HTTPS)
- **Ne l'active PAS automatiquement** (sécurité)

Pour activer UFW manuellement :
```bash
sudo ufw status         # Vérifier les règles
sudo ufw enable        # Activer le pare-feu
```

⚠️ **ATTENTION** : Assurez-vous que le port SSH (22) est bien ouvert avant d'activer UFW !

### 5. Backups
Le script crée automatiquement un premier backup de votre base de données dans `backups/database/`.

## 🆘 En Cas de Problème

Si le script ne résout pas tous les problèmes :

1. **Vérifier les logs Nginx** :
   ```bash
   sudo journalctl -u nginx -n 50
   ```

2. **Vérifier les permissions manuellement** :
   ```bash
   ls -la database/
   # Devrait afficher : -rw-rw-r-- 1 www-data www-data ... database.db
   ```

3. **Tester la base de données** :
   ```bash
   sqlite3 database/database.db "SELECT 1;"
   ```

4. **Redémarrer les services** :
   ```bash
   sudo systemctl restart nginx
   sudo systemctl restart php8.1-fpm
   ```

## 📞 Support

Si vous avez encore des problèmes après avoir suivi ce guide :

1. Lancez à nouveau la vérification système (option 14)
2. Notez précisément les erreurs qui persistent
3. Vérifiez les logs des services concernés

Bon courage ! 🚀