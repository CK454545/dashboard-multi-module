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
    
    // Connexion SQLite uniquement
    $sqlitePath = __DIR__ . '/../../database/database.db';
    if (file_exists($sqlitePath)) {
        try {
            $db = new SQLite3($sqlitePath);
            $stmt = $db->prepare('SELECT discord_id FROM users WHERE token = ?');
            $stmt->bindValue(1, $token, SQLITE3_TEXT);
            $result = $stmt->execute();
            $user = $result->fetchArray(SQLITE3_ASSOC);
            
            if ($user) {
                return [
                    'discord_id' => $user['discord_id'],
                    'pseudo' => $user['discord_id']
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
?>