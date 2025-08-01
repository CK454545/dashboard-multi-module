<?php
/**
 * Script d'initialisation de la base de données MySQL
 * Pour les modules Win, Timer et Team VS Team
 */

// Configuration de la base de données
$host = 'localhost';
$dbname = 'myfull_agency';
$username = 'root';
$password = '';

try {
    // Connexion à MySQL sans spécifier de base de données
    $pdo = new PDO("mysql:host=$host", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Créer la base de données si elle n'existe pas
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "Base de données '$dbname' créée ou déjà existante.\n";
    
    // Se connecter à la base de données
    $pdo->exec("USE `$dbname`");
    
    // Créer la table users
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            discord_id VARCHAR(255) UNIQUE NOT NULL,
            pseudo VARCHAR(255) NOT NULL,
            token VARCHAR(255) UNIQUE NOT NULL,
            is_active BOOLEAN DEFAULT TRUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            last_activity TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_token (token),
            INDEX idx_discord_id (discord_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "Table 'users' créée ou déjà existante.\n";
    
    // Créer la table wins_config
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS wins_config (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            config_data JSON NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            UNIQUE KEY unique_user_wins (user_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "Table 'wins_config' créée ou déjà existante.\n";
    
    // Créer la table timer_config
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS timer_config (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            config_data JSON NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            UNIQUE KEY unique_user_timer (user_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "Table 'timer_config' créée ou déjà existante.\n";
    
    // Créer la table teams_config
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS teams_config (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            config_data JSON NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            UNIQUE KEY unique_user_teams (user_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "Table 'teams_config' créée ou déjà existante.\n";
    
    // Créer la table wins_state (pour stocker l'état actuel des victoires)
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS wins_state (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            wins INT DEFAULT 0,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            UNIQUE KEY unique_user_wins_state (user_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "Table 'wins_state' créée ou déjà existante.\n";
    
    // Créer la table timer_state (pour stocker l'état actuel du timer)
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS timer_state (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            state_data JSON NOT NULL,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            UNIQUE KEY unique_user_timer_state (user_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "Table 'timer_state' créée ou déjà existante.\n";
    
    // Créer la table teams_state (pour stocker l'état actuel des équipes)
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS teams_state (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            green_score INT DEFAULT 0,
            red_score INT DEFAULT 0,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            UNIQUE KEY unique_user_teams_state (user_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "Table 'teams_state' créée ou déjà existante.\n";
    
    // Créer la table action_logs (pour logger toutes les actions)
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS action_logs (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            module VARCHAR(50) NOT NULL,
            action VARCHAR(100) NOT NULL,
            details JSON,
            ip_address VARCHAR(45),
            user_agent TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            INDEX idx_user_module (user_id, module),
            INDEX idx_created_at (created_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "Table 'action_logs' créée ou déjà existante.\n";
    
    // Insérer un utilisateur de test si la table est vide
    $stmt = $pdo->query("SELECT COUNT(*) FROM users");
    $count = $stmt->fetchColumn();
    
    if ($count == 0) {
        $testToken = 'TEST-1234-5678-9ABC';
        $pdo->exec("
            INSERT INTO users (discord_id, pseudo, token) 
            VALUES ('123456789', 'Utilisateur Test', '$testToken')
        ");
        echo "\nUtilisateur de test créé avec le token : $testToken\n";
    }
    
    echo "\n✅ Base de données initialisée avec succès !\n";
    
} catch (PDOException $e) {
    echo "❌ Erreur : " . $e->getMessage() . "\n";
    exit(1);
}
?> 