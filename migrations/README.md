# 📚 Guide des Migrations de Base de Données

## 🎯 Objectif

Les migrations permettent d'ajouter de nouveaux modules et fonctionnalités sans perdre les données existantes des utilisateurs.

## 📝 Comment créer une migration

### 1. Nommage des fichiers

Format : `XXX_description.sql`

- `XXX` : Numéro séquentiel (001, 002, 003...)
- `description` : Description courte en snake_case

Exemples :
- `001_add_timer_module.sql`
- `002_add_team_vs_team.sql`
- `003_add_webhook_support.sql`

### 2. Structure d'une migration

```sql
-- ================================================================
-- Migration: [Nom du module/feature]
-- Date: [YYYY-MM-DD]
-- Description: [Description détaillée]
-- ================================================================

-- Vos instructions SQL ici
CREATE TABLE IF NOT EXISTS ...

-- Toujours utiliser IF NOT EXISTS pour éviter les erreurs
-- Toujours ajouter des commentaires
```

### 3. Bonnes pratiques

#### ✅ À FAIRE

```sql
-- Utiliser IF NOT EXISTS
CREATE TABLE IF NOT EXISTS timer_settings (...)

-- Ajouter des valeurs par défaut
ALTER TABLE users ADD COLUMN IF NOT EXISTS 
    theme TEXT DEFAULT 'dark';

-- Créer des index pour les performances
CREATE INDEX IF NOT EXISTS idx_user_token 
    ON users(token);

-- Documenter chaque changement
-- Ajout de la colonne theme pour le support multi-thèmes
```

#### ❌ À ÉVITER

```sql
-- Ne pas utiliser DROP TABLE (perte de données)
DROP TABLE users;  -- JAMAIS !

-- Ne pas modifier les colonnes existantes
ALTER TABLE users ALTER COLUMN name TEXT NOT NULL;

-- Ne pas supprimer de colonnes
ALTER TABLE users DROP COLUMN email;
```

## 🔄 Processus de migration

1. **Création** : Créez votre fichier `.sql` dans ce dossier
2. **Test local** : Testez sur une copie de la DB
3. **Push** : Committez sur GitHub
4. **Auto-apply** : Le serveur applique automatiquement

## 📊 Exemples de migrations

### Ajouter un nouveau module

```sql
-- 004_add_poll_module.sql
CREATE TABLE IF NOT EXISTS polls (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id TEXT NOT NULL,
    question TEXT NOT NULL,
    options TEXT NOT NULL, -- JSON array
    votes TEXT DEFAULT '{}', -- JSON object
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    expires_at DATETIME,
    FOREIGN KEY (user_id) REFERENCES users(id)
);
```

### Ajouter une colonne

```sql
-- 005_add_user_preferences.sql
ALTER TABLE users 
ADD COLUMN IF NOT EXISTS preferences TEXT DEFAULT '{}';
```

### Créer une vue

```sql
-- 006_add_statistics_views.sql
CREATE VIEW IF NOT EXISTS user_statistics AS
SELECT 
    u.id,
    u.username,
    COUNT(DISTINCT w.id) as total_wins,
    MAX(w.created_at) as last_win
FROM users u
LEFT JOIN wins w ON u.id = w.user_id
GROUP BY u.id;
```

## 🛡️ Sécurité des migrations

1. **Toujours sauvegarder** avant d'appliquer
2. **Tester localement** d'abord
3. **Éviter les DELETE** et DROP
4. **Utiliser des transactions** si nécessaire

## 🔍 Vérification

Après une migration, vérifiez :

```bash
# Sur le serveur
node scripts/migrate-db.js

# Vérifier l'intégrité
sqlite3 database.db "PRAGMA integrity_check;"

# Voir les migrations appliquées
sqlite3 database.db "SELECT * FROM migrations;"
```

## 💡 Astuce Pro

Créez toujours une migration "rollback" :

```sql
-- 007_add_feature.sql (migration)
CREATE TABLE IF NOT EXISTS new_feature (...);

-- 007_rollback_add_feature.sql (rollback)
-- Instructions pour annuler si nécessaire
-- DROP TABLE IF EXISTS new_feature;
```

---

**Important** : Les migrations sont appliquées dans l'ordre alphabétique/numérique. Respectez la numérotation ! 