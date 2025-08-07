-- Création de la base de données SQLite pour le système de widgets TikTok Live

-- Table principale des utilisateurs
CREATE TABLE IF NOT EXISTS users (
    token TEXT PRIMARY KEY,
    discord_id TEXT UNIQUE NOT NULL,
    pseudo TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table flexible pour stocker toutes les données des modules
CREATE TABLE IF NOT EXISTS user_data (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    token TEXT NOT NULL,
    module TEXT NOT NULL,           -- 'wins', 'timer', 'teams'
    key TEXT NOT NULL,              -- 'count', 'multiplier', 'team1_score', etc.
    value TEXT,                     -- Valeur stockée (tout est stocké en texte)
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(token, module, key),
    FOREIGN KEY (token) REFERENCES users(token) ON DELETE CASCADE
);

-- Table pour stocker les styles personnalisés
CREATE TABLE IF NOT EXISTS user_styles (
    token TEXT PRIMARY KEY,
    styles TEXT,                    -- JSON avec tous les styles
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (token) REFERENCES users(token) ON DELETE CASCADE
);

-- ==================== SYSTÈME DE PROFIL AVANCÉ ====================

-- Table des profils utilisateurs avec informations détaillées
CREATE TABLE IF NOT EXISTS user_profiles (
    token TEXT PRIMARY KEY,
    display_name TEXT,
    avatar_url TEXT,
    bio TEXT,
    theme_preference TEXT DEFAULT 'default',  -- 'default', 'dark', 'light', 'custom'
    language TEXT DEFAULT 'fr',
    timezone TEXT DEFAULT 'Europe/Paris',
    notifications_enabled BOOLEAN DEFAULT 1,
    last_login TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    login_count INTEGER DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (token) REFERENCES users(token) ON DELETE CASCADE
);

-- Table des statistiques utilisateur
CREATE TABLE IF NOT EXISTS user_stats (
    token TEXT PRIMARY KEY,
    total_wins INTEGER DEFAULT 0,
    total_timer_sessions INTEGER DEFAULT 0,
    total_battle_sessions INTEGER DEFAULT 0,
    total_streaming_time INTEGER DEFAULT 0,  -- en secondes
    favorite_module TEXT DEFAULT 'wins',
    achievements_unlocked INTEGER DEFAULT 0,
    last_activity TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (token) REFERENCES users(token) ON DELETE CASCADE
);

-- Table des préférences utilisateur
CREATE TABLE IF NOT EXISTS user_preferences (
    token TEXT PRIMARY KEY,
    auto_save BOOLEAN DEFAULT 1,
    auto_backup BOOLEAN DEFAULT 1,
    sound_effects BOOLEAN DEFAULT 1,
    animations_enabled BOOLEAN DEFAULT 1,
    privacy_level TEXT DEFAULT 'public',  -- 'public', 'friends', 'private'
    dashboard_layout TEXT DEFAULT 'default',  -- 'default', 'compact', 'detailed'
    color_scheme TEXT DEFAULT 'blue_red',  -- 'blue_red', 'green_purple', 'orange_cyan'
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (token) REFERENCES users(token) ON DELETE CASCADE
);

-- Table de l'historique des activités
CREATE TABLE IF NOT EXISTS user_activity_log (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    token TEXT NOT NULL,
    action TEXT NOT NULL,  -- 'module_launched', 'settings_changed', 'login', 'logout', etc.
    module TEXT,           -- 'wins', 'timer', 'battle', 'profile', etc.
    details TEXT,          -- JSON avec détails supplémentaires
    ip_address TEXT,
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (token) REFERENCES users(token) ON DELETE CASCADE
);

-- Table des sessions utilisateur
CREATE TABLE IF NOT EXISTS user_sessions (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    token TEXT NOT NULL,
    session_id TEXT UNIQUE NOT NULL,
    ip_address TEXT,
    user_agent TEXT,
    is_active BOOLEAN DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_activity TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP,
    FOREIGN KEY (token) REFERENCES users(token) ON DELETE CASCADE
);

-- Table des notifications utilisateur
CREATE TABLE IF NOT EXISTS user_notifications (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    token TEXT NOT NULL,
    type TEXT NOT NULL,  -- 'info', 'success', 'warning', 'error'
    title TEXT NOT NULL,
    message TEXT NOT NULL,
    is_read BOOLEAN DEFAULT 0,
    action_url TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (token) REFERENCES users(token) ON DELETE CASCADE
);

-- Index pour améliorer les performances
CREATE INDEX IF NOT EXISTS idx_user_data_token ON user_data(token);
CREATE INDEX IF NOT EXISTS idx_user_data_module ON user_data(token, module);
CREATE INDEX IF NOT EXISTS idx_users_discord_id ON users(discord_id);
CREATE INDEX IF NOT EXISTS idx_user_activity_token ON user_activity_log(token);
CREATE INDEX IF NOT EXISTS idx_user_activity_created ON user_activity_log(created_at);
CREATE INDEX IF NOT EXISTS idx_user_sessions_token ON user_sessions(token);
CREATE INDEX IF NOT EXISTS idx_user_sessions_active ON user_sessions(is_active);
CREATE INDEX IF NOT EXISTS idx_user_notifications_token ON user_notifications(token);
CREATE INDEX IF NOT EXISTS idx_user_notifications_read ON user_notifications(is_read);

-- Triggers pour mettre à jour automatiquement les timestamps
CREATE TRIGGER IF NOT EXISTS update_user_profiles_timestamp 
    AFTER UPDATE ON user_profiles
    BEGIN
        UPDATE user_profiles SET updated_at = CURRENT_TIMESTAMP WHERE token = NEW.token;
    END;

CREATE TRIGGER IF NOT EXISTS update_user_stats_timestamp 
    AFTER UPDATE ON user_stats
    BEGIN
        UPDATE user_stats SET updated_at = CURRENT_TIMESTAMP WHERE token = NEW.token;
    END;

CREATE TRIGGER IF NOT EXISTS update_user_preferences_timestamp 
    AFTER UPDATE ON user_preferences
    BEGIN
        UPDATE user_preferences SET updated_at = CURRENT_TIMESTAMP WHERE token = NEW.token;
    END;

-- Données de test (optionnel - à commenter en production)
-- INSERT INTO users (token, discord_id, pseudo) VALUES ('test_token_12345', '123456789012345678', 'TestUser');
-- INSERT INTO user_data (token, module, key, value) VALUES ('test_token_12345', 'wins', 'count', '0');
-- INSERT INTO user_data (token, module, key, value) VALUES ('test_token_12345', 'wins', 'multiplier', '1.00');
-- INSERT INTO user_profiles (token, display_name, bio) VALUES ('test_token_12345', 'Test User', 'Streamer passionné');
-- INSERT INTO user_stats (token, total_wins, favorite_module) VALUES ('test_token_12345', 0, 'wins');
-- INSERT INTO user_preferences (token, color_scheme) VALUES ('test_token_12345', 'blue_red');