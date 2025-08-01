<?php
/**
 * Validation des tokens pour les modules
 * Ce fichier doit être inclus dans chaque module pour vérifier la validité du token
 */

// Configuration de la base de données SQLite
$sqlitePath = __DIR__ . '/../../database/database.db';

// Fonction pour valider un token
function validateToken($token) {
    global $sqlitePath;
    
    if (empty($token)) {
        return false;
    }
    
    // Essayer d'abord SQLite
    if (file_exists($sqlitePath)) {
        try {
            $db = new SQLite3($sqlitePath);
            $stmt = $db->prepare('SELECT discord_id FROM users WHERE token = ?');
            $stmt->bindValue(1, $token, SQLITE3_TEXT);
            $result = $stmt->execute();
            $user = $result->fetchArray(SQLITE3_ASSOC);
            
            if ($user) {
                $db->close();
                return $user;
            }
            $db->close();
        } catch (Exception $e) {
            error_log("Erreur SQLite: " . $e->getMessage());
        }
    }
    
    // Essayer MySQL si SQLite échoue
    try {
        $host = 'localhost';
        $dbname = 'myfullagency_connect';
        $username = 'myfullagency_connect';
        $password = 'myfullagency_connect';
        
        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        $stmt = $pdo->prepare('SELECT discord_id, pseudo FROM users WHERE token = ?');
        $stmt->execute([$token]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user) {
            return $user;
        }
        
    } catch (Exception $e) {
        error_log("Erreur MySQL: " . $e->getMessage());
    }
    
    return false;
}

// Fonction pour rediriger si le token est invalide
function requireValidToken() {
    $token = $_GET['token'] ?? '';
    $user = validateToken($token);
    
    if (!$user) {
        // Rediriger vers une page d'erreur ou afficher un message
        header('HTTP/1.1 403 Forbidden');
        die('
        <!DOCTYPE html>
        <html lang="fr">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Accès Refusé</title>
            <style>
                body {
                    margin: 0;
                    padding: 0;
                    font-family: Arial, sans-serif;
                    background: #0f172a;
                    color: #f8fafc;
                    display: flex;
                    justify-content: center;
                    align-items: center;
                    min-height: 100vh;
                }
                .error-container {
                    text-align: center;
                    padding: 2rem;
                    background: rgba(239, 68, 68, 0.1);
                    border: 1px solid rgba(239, 68, 68, 0.3);
                    border-radius: 1rem;
                    max-width: 500px;
                }
                h1 {
                    color: #ef4444;
                    margin-bottom: 1rem;
                }
                p {
                    color: #cbd5e1;
                    line-height: 1.6;
                }
                .icon {
                    font-size: 4rem;
                    margin-bottom: 1rem;
                }
                .debug {
                    margin-top: 2rem;
                    padding: 1rem;
                    background: rgba(0, 0, 0, 0.3);
                    border-radius: 0.5rem;
                    font-family: monospace;
                    font-size: 0.8rem;
                    text-align: left;
                }
            </style>
        </head>
        <body>
            <div class="error-container">
                <div class="icon">🚫</div>
                <h1>Accès Refusé</h1>
                <p>
                    Le token fourni est invalide ou manquant.<br>
                    Veuillez utiliser un lien valide depuis votre bot Discord.
                </p>
                ' . (isset($_GET['debug']) ? '<div class="debug">Token reçu: ' . htmlspecialchars($token) . '<br>Longueur: ' . strlen($token) . '</div>' : '') . '
            </div>
        </body>
        </html>
        ');
    }
    
    return $user;
}
?>