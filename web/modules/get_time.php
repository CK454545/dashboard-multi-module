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

// Récupérer l'état actuel du timer depuis votre API existante
// Utiliser file_get_contents si curl n'est pas disponible
$apiUrl = "http://" . $_SERVER['HTTP_HOST'] . "/api.php?token=" . urlencode($token) . "&module=timer&action=get";

// Essayer d'abord avec curl
if (function_exists('curl_init')) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
} else {
    // Fallback avec file_get_contents
    $context = stream_context_create([
        'http' => [
            'timeout' => 5,
            'method' => 'GET'
        ]
    ]);
    $response = @file_get_contents($apiUrl, false, $context);
    $httpCode = $response !== false ? 200 : 500;
}

if ($httpCode !== 200 || !$response) {
    // Si l'API ne répond pas, retourner un état par défaut
    echo json_encode([
        'success' => true,
        'end_at' => null,
        'paused' => true,
        'duration' => 0
    ]);
    exit;
}

$data = json_decode($response, true);

// Formater la réponse au format Alka
$result = [
    'success' => true,
    'end_at' => null,
    'paused' => true,
    'duration' => 0
];

if ($data && isset($data['success']) && $data['success'] && isset($data['data'])) {
    $timerData = $data['data'];
    
    // Convertir les données de votre format au format Alka
    $result['end_at'] = $timerData['endTime'] ?? null;
    $result['paused'] = !($timerData['isRunning'] ?? false);
    $result['duration'] = $timerData['duration'] ?? 0;
    
    // Si le timer est en pause mais a un endTime, on peut calculer le temps restant
    if ($result['paused'] && $result['end_at']) {
        $now = time();
        $remaining = max(0, $result['end_at'] - $now);
        $result['duration'] = $remaining;
    }
}

// Retourner la réponse JSON
echo json_encode($result);
?>