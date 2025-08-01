# 🔒 GUIDE DE SÉCURITÉ - STREAMPRO STUDIO

## 🚨 **PROBLÈME RÉSOLU : SECRETS EXPOSÉS**

### **✅ Actions Correctives Effectuées**

1. **🗑️ Suppression du fichier config.json** avec le vrai token Discord
2. **📝 Création d'un fichier d'exemple** `config/config.example.json`
3. **🔧 Nettoyage du .gitignore** avec protection renforcée
4. **🛠️ Script de nettoyage** `scripts/clean-secrets.sh`

---

## 🛡️ **BONNES PRATIQUES DE SÉCURITÉ**

### **📁 Fichiers Sensibles à JAMAIS Commiter**

```bash
# ❌ NE JAMAIS COMMITER
config/config.json          # Configuration avec secrets
*.db                       # Bases de données
*.key                      # Clés privées
*.pem                      # Certificats SSL
.env                       # Variables d'environnement
```

### **✅ Fichiers Sécurisés à Commiter**

```bash
# ✅ PEUT ÊTRE COMMITÉ
config/config.example.json  # Exemple sans secrets
README.md                  # Documentation
*.md                       # Guides et docs
```

---

## 🔧 **CONFIGURATION LOCALE SÉCURISÉE**

### **1️⃣ Créer votre configuration locale**

```bash
# Copier l'exemple
cp config/config.example.json config/config.json

# Éditer avec vos vrais secrets
nano config/config.json
```

### **2️⃣ Exemple de configuration sécurisée**

```json
{
    "website": {
        "url": "https://votre-domaine.com",
        "port": 80
    },
    "discord": {
        "token": "VOTRE_VRAI_TOKEN_DISCORD_ICI",
        "support_url": "https://discord.gg/VOTRE_SERVEUR"
    },
    "database": {
        "file": "database/database.db"
    },
    "app": {
        "name": "StreamPro Studio",
        "description": "Solutions Professionnelles pour Créateurs de Contenu",
        "version": "1.0.0",
        "copyright": "© Copyright 2025/2026 MFA & Développement/Design by CK"
    }
}
```

---

## 🚨 **URGENT : RÉGÉNÉRER VOTRE TOKEN DISCORD**

### **🔑 Étapes pour régénérer le token Discord**

1. **Aller sur Discord Developer Portal**
   ```
   https://discord.com/developers/applications
   ```

2. **Sélectionner votre application**

3. **Section "Bot" → "Reset Token"**

4. **Copier le nouveau token**

5. **Mettre à jour votre config.json local**

---

## 🧹 **NETTOYAGE DE L'HISTORIQUE GIT**

### **Option 1 : Avec BFG Repo-Cleaner (Recommandé)**

```bash
# Installer BFG
wget https://repo1.maven.org/maven2/com/madgag/bfg/1.14.0/bfg-1.14.0.jar
sudo mv bfg-1.14.0.jar /usr/local/bin/bfg.jar
echo '#!/bin/bash' > /usr/local/bin/bfg
echo 'java -jar /usr/local/bin/bfg.jar "$@"' >> /usr/local/bin/bfg
chmod +x /usr/local/bin/bfg

# Utiliser notre script
./scripts/clean-secrets.sh
```

### **Option 2 : Avec git filter-branch**

```bash
git filter-branch --force --index-filter \
'git rm --cached --ignore-unmatch config/config.json' \
--prune-empty --tag-name-filter cat -- --all
```

---

## 📋 **CHECKLIST DE SÉCURITÉ**

### **✅ Avant chaque commit**

- [ ] Vérifier que `config/config.json` n'est pas dans le commit
- [ ] Vérifier qu'aucun token Discord n'est exposé
- [ ] Vérifier qu'aucune base de données n'est commitée
- [ ] Vérifier qu'aucune clé privée n'est exposée

### **✅ Vérifications automatiques**

```bash
# Vérifier les fichiers sensibles
git diff --cached --name-only | grep -E "(config\.json|\.db|\.key|\.pem)"

# Vérifier les secrets dans le code
git diff --cached | grep -E "(token|password|secret|key)"
```

---

## 🆘 **EN CAS DE FUITE DE SECRET**

### **1️⃣ Actions immédiates**

1. **Régénérer immédiatement le token Discord**
2. **Supprimer le fichier du commit** : `git reset HEAD~1`
3. **Nettoyer l'historique** avec le script fourni
4. **Forcer le push** : `git push --force-with-lease`

### **2️⃣ Prévention future**

1. **Utiliser des variables d'environnement**
2. **Toujours commiter `config.example.json`**
3. **Vérifier le `.gitignore` avant chaque commit**
4. **Utiliser des hooks Git pour détecter les secrets**

---

## 📞 **SUPPORT**

En cas de problème de sécurité :

1. **Diagnostic rapide** : `./scripts/clean-secrets.sh`
2. **Vérification** : `git log --oneline`
3. **Nettoyage** : Suivre les étapes ci-dessus

**🔒 Votre projet est maintenant sécurisé !** 