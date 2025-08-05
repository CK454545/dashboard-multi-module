<?php
/**
 * Validation des tokens pour les modules
 * Ce fichier doit être inclus dans chaque module pour vérifier la validité du token
 */

function requireValidToken() {
    $token = $_GET['token'] ?? '';
    
    if (empty($token)) {
        http_response_code(401);
        echo json_encode(['error' => 'Token manquant']);
        exit;
    }
    
    // Vérifier si c'est un token de développement local
    $localTokens = [
        'dev_test_1' => [
            'discord_id' => '123456789012345678',
            'pseudo' => 'Testeur Local 1'
        ],
        'dev_test_2' => [
            'discord_id' => '987654321098765432',
            'pseudo' => 'Testeur Local 2'
        ],
        'dev_admin' => [
            'discord_id' => '111111111111111111',
            'pseudo' => 'Admin Local'
        ],
        'dev_local_token_2024' => [
            'discord_id' => '999999999999999999',
            'pseudo' => 'Développeur Local'
        ]
    ];
    
    // Si c'est un token de développement local, le valider directement
    if (isset($localTokens[$token])) {
        return [
            'discord_id' => $localTokens[$token]['discord_id'],
            'pseudo' => $localTokens[$token]['pseudo'],
            'is_local' => true
        ];
    }
    
    // Connexion SQLite pour les tokens de production
    $sqlitePath = __DIR__ . '/../../database/database.db';
    if (file_exists($sqlitePath)) {
        try {
            $db = new SQLite3($sqlitePath);
            $stmt = $db->prepare('SELECT discord_id, pseudo FROM users WHERE token = ?');
            $stmt->bindValue(1, $token, SQLITE3_TEXT);
            $result = $stmt->execute();
            $user = $result->fetchArray(SQLITE3_ASSOC);
            
            if ($user) {
                return [
                    'discord_id' => $user['discord_id'],
                    'pseudo' => $user['pseudo'] ?? $user['discord_id'],
                    'is_local' => false
                ];
            }
            
        } catch (Exception $e) {
            error_log("Erreur SQLite: " . $e->getMessage());
        }
    }
    
    // Token invalide
    http_response_code(401);
    echo json_encode(['error' => 'Token invalide']);
    exit;
}

/**
 * Fonction pour obtenir les données utilisateur depuis la base de données
 * Compatible avec les tokens de développement local
 */
function getUserData($token, $module, $key = null) {
    // Vérifier si c'est un token de développement local
    $localTokens = ['dev_test_1', 'dev_test_2', 'dev_admin', 'dev_local_token_2024'];
    
    if (in_array($token, $localTokens)) {
        // Retourner des données simulées pour les tokens de développement
        return getLocalTestData($token, $module, $key);
    }
    
    // Connexion SQLite pour les tokens de production
    $sqlitePath = __DIR__ . '/../../database/database.db';
    if (file_exists($sqlitePath)) {
        try {
            $db = new SQLite3($sqlitePath);
            
            if ($key === null) {
                // Récupérer toutes les données du module
                $stmt = $db->prepare('SELECT key, value FROM user_data WHERE token = ? AND module = ?');
                $stmt->bindValue(1, $token, SQLITE3_TEXT);
                $stmt->bindValue(2, $module, SQLITE3_TEXT);
                $result = $stmt->execute();
                
                $data = [];
                while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
                    $data[$row['key']] = $row['value'];
                }
                return $data;
            } else {
                // Récupérer une valeur spécifique
                $stmt = $db->prepare('SELECT value FROM user_data WHERE token = ? AND module = ? AND key = ?');
                $stmt->bindValue(1, $token, SQLITE3_TEXT);
                $stmt->bindValue(2, $module, SQLITE3_TEXT);
                $stmt->bindValue(3, $key, SQLITE3_TEXT);
                $result = $stmt->execute();
                $row = $result->fetchArray(SQLITE3_ASSOC);
                
                return $row ? $row['value'] : null;
            }
            
        } catch (Exception $e) {
            error_log("Erreur SQLite getUserData: " . $e->getMessage());
            return null;
        }
    }
    
    return null;
}

/**
 * Fonction pour sauvegarder les données utilisateur
 * Compatible avec les tokens de développement local
 */
function saveUserData($token, $module, $key, $value) {
    // Vérifier si c'est un token de développement local
    $localTokens = ['dev_test_1', 'dev_test_2', 'dev_admin', 'dev_local_token_2024'];
    
    if (in_array($token, $localTokens)) {
        // Pour les tokens de développement, on peut soit ignorer la sauvegarde
        // soit la faire dans un fichier temporaire
        error_log("Sauvegarde simulée pour token local: $token, module: $module, key: $key, value: $value");
        return true;
    }
    
    // Connexion SQLite pour les tokens de production
    $sqlitePath = __DIR__ . '/../../database/database.db';
    if (file_exists($sqlitePath)) {
        try {
            $db = new SQLite3($sqlitePath);
            $stmt = $db->prepare('INSERT OR REPLACE INTO user_data (token, module, key, value, updated_at) VALUES (?, ?, ?, ?, CURRENT_TIMESTAMP)');
            $stmt->bindValue(1, $token, SQLITE3_TEXT);
            $stmt->bindValue(2, $module, SQLITE3_TEXT);
            $stmt->bindValue(3, $key, SQLITE3_TEXT);
            $stmt->bindValue(4, $value, SQLITE3_TEXT);
            $stmt->execute();
            
            return true;
            
        } catch (Exception $e) {
            error_log("Erreur SQLite saveUserData: " . $e->getMessage());
            return false;
        }
    }
    
    return false;
}

/**
 * Fonction pour générer des données de test pour les tokens de développement local
 */
function getLocalTestData($token, $module, $key = null) {
    // Données de test par défaut
    $testData = [
        'wins' => [
            'count' => '42',
            'multiplier' => '1.5',
            'last_win' => date('Y-m-d H:i:s', time() - 3600)
        ],
        'timer' => [
            'duration' => '120',
            'is_running' => '0',
            'start_time' => date('Y-m-d H:i:s', time() - 1800)
        ],
        'teams' => [
            'team1_score' => '15',
            'team2_score' => '12',
            'battle_active' => '0'
        ]
    ];
    
    if ($key === null) {
        return $testData[$module] ?? [];
    } else {
        return $testData[$module][$key] ?? null;
    }
}
?>