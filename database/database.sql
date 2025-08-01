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

-- Index pour améliorer les performances
CREATE INDEX IF NOT EXISTS idx_user_data_token ON user_data(token);
CREATE INDEX IF NOT EXISTS idx_user_data_module ON user_data(token, module);
CREATE INDEX IF NOT EXISTS idx_users_discord_id ON users(discord_id);

-- Données de test (optionnel - à commenter en production)
-- INSERT INTO users (token, discord_id) VALUES ('test_token_12345', '123456789012345678');
-- INSERT INTO user_data (token, module, key, value) VALUES ('test_token_12345', 'wins', 'count', '0');
-- INSERT INTO user_data (token, module, key, value) VALUES ('test_token_12345', 'wins', 'multiplier', '1.00');