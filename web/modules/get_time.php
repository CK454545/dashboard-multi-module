<?php
/**
 * get_time.php - Endpoint compatible avec la logique Alka Agency
 * Ce fichier crée un endpoint simple qui retourne l'état du timer
 * au format JSON comme le système Alka
 */

// Inclure la validation des tokens
require_once __DIR__ . '/validate_token.php';

// Headers pour CORS et JSON
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Cache-Control: no-cache, no-store, must-revalidate');

// Récupérer le token depuis l'URL
$token = $_GET['token'] ?? '';

if (empty($token)) {
    echo json_encode([
        'success' => false,
        'error' => 'Token manquant'
    ]);
    exit;
}

// Valider le token et récupérer les infos utilisateur
try {
    $user = requireValidToken();
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => 'Token invalide'
    ]);
    exit;
}

// Accès direct à la base de données SQLite
$dbPath = __DIR__ . '/../../database/database.db';
$db = new SQLite3($dbPath);

// Récupérer l'état actuel du timer directement depuis la base de données
$seconds = getValue($db, $token, 'timer', 'seconds') ?? '0';
$isRunning = getValue($db, $token, 'timer', 'isRunning') ?? 'false';
$isPaused = getValue($db, $token, 'timer', 'isPaused') ?? 'false';
$endTime = getValue($db, $token, 'timer', 'endTime') ?? null;

// Si le timer est en cours et pas d'endTime, le calculer
if ($isRunning === 'true' && !$endTime) {
    $endTime = time() + intval($seconds);
    setValue($db, $token, 'timer', 'endTime', strval($endTime));
}

// Formater la réponse au format Alka
$result = [
    'success' => true,
    'end_at' => $endTime ? intval($endTime) : null,
    'paused' => !($isRunning === 'true'),
    'duration' => intval($seconds)
];

// Si le timer est en pause mais a un endTime, calculer le temps restant
if ($result['paused'] && $result['end_at']) {
    $now = time();
    $remaining = max(0, $result['end_at'] - $now);
    $result['duration'] = $remaining;
}

// Fermer la connexion à la base de données
$db->close();

// Retourner la réponse JSON
echo json_encode($result);

// Fonctions utilitaires pour SQLite
function getValue($db, $token, $module, $key) {
    $stmt = $db->prepare("SELECT value FROM user_data WHERE token = ? AND module = ? AND key = ?");
    $stmt->bindValue(1, $token, SQLITE3_TEXT);
    $stmt->bindValue(2, $module, SQLITE3_TEXT);
    $stmt->bindValue(3, $key, SQLITE3_TEXT);
    $result = $stmt->execute();
    
    if ($result) {
        $row = $result->fetchArray(SQLITE3_ASSOC);
        return $row ? $row['value'] : null;
    }
    return null;
}

function setValue($db, $token, $module, $key, $value) {
    $stmt = $db->prepare("
        INSERT OR REPLACE INTO user_data (token, module, key, value, updated_at) 
        VALUES (?, ?, ?, ?, datetime('now'))
    ");
    $stmt->bindValue(1, $token, SQLITE3_TEXT);
    $stmt->bindValue(2, $module, SQLITE3_TEXT);
    $stmt->bindValue(3, $key, SQLITE3_TEXT);
    $stmt->bindValue(4, $value, SQLITE3_TEXT);
    return $stmt->execute();
}
?>