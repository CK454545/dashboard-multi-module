<?php
// Headers CORS améliorés pour TikTok Live Studio
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, Accept, Origin');
header('Access-Control-Max-Age: 86400'); // 24 heures
header('Cache-Control: no-cache, must-revalidate');
header('Pragma: no-cache');

// Gérer les requêtes OPTIONS (preflight)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Récupération des paramètres
$token = $_GET['token'] ?? '';
$module = $_GET['module'] ?? '';
$action = $_GET['action'] ?? '';
$value = $_GET['value'] ?? '';

// Vérification du token
if (empty($token)) {
    echo json_encode(['success' => false, 'error' => 'Token manquant']);
    exit;
}

// Connexion SQLite uniquement
$db = null;
$user = null;

$sqlitePath = __DIR__ . '/../database/database.db';
if (file_exists($sqlitePath)) {
    try {
        $db = new SQLite3($sqlitePath);
        $stmt = $db->prepare('SELECT token, discord_id FROM users WHERE token = ?');
        $stmt->bindValue(1, $token, SQLITE3_TEXT);
        $result = $stmt->execute();
        $userRow = $result->fetchArray(SQLITE3_ASSOC);
        
        if ($userRow) {
            $user = [
                'id' => $token,
                'discord_id' => $userRow['discord_id'],
                'pseudo' => $userRow['discord_id'],
                'token' => $userRow['token']
            ];
        }
    } catch (Exception $e) {
        error_log("Erreur SQLite: " . $e->getMessage());
        echo json_encode(['success' => false, 'error' => 'Erreur de base de données']);
        exit;
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Base de données non trouvée']);
    exit;
}

if (!$user) {
    echo json_encode(['success' => false, 'error' => 'Token invalide']);
    exit;
}

// Logger l'action (seulement pour MySQL)
function logAction($pdo, $userId, $module, $action, $details = null) {
    if (!$pdo) return; // Pas de log pour SQLite
    
    try {
        $stmt = $pdo->prepare("
            INSERT INTO action_logs (user_id, module, action, details, ip_address, user_agent) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $userId,
            $module,
            $action,
            $details ? json_encode($details) : null,
            $_SERVER['REMOTE_ADDR'] ?? null,
            $_SERVER['HTTP_USER_AGENT'] ?? null
        ]);
    } catch (PDOException $e) {
        error_log("Erreur lors du log de l'action: " . $e->getMessage());
    }
}

// Fonctions pour SQLite (compatibilité avec l'ancien système)
function getValue($db, $token, $module, $key) {
    try {
        $stmt = $db->prepare('SELECT value FROM user_data WHERE token = ? AND module = ? AND key = ?');
        $stmt->bindValue(1, $token, SQLITE3_TEXT);
        $stmt->bindValue(2, $module, SQLITE3_TEXT);
        $stmt->bindValue(3, $key, SQLITE3_TEXT);
        $result = $stmt->execute();
        $row = $result->fetchArray();
        return $row ? $row['value'] : null;
    } catch (Exception $e) {
        error_log("ERREUR GETVALUE: " . $e->getMessage());
        return null;
    }
}

function setValue($db, $token, $module, $key, $value) {
    try {
        $stmt = $db->prepare('INSERT OR REPLACE INTO user_data (token, module, key, value, updated_at) VALUES (?, ?, ?, ?, CURRENT_TIMESTAMP)');
        $stmt->bindValue(1, $token, SQLITE3_TEXT);
        $stmt->bindValue(2, $module, SQLITE3_TEXT);
        $stmt->bindValue(3, $key, SQLITE3_TEXT);
        $stmt->bindValue(4, $value, SQLITE3_TEXT);
        return $stmt->execute() !== false;
    } catch (Exception $e) {
        error_log("ERREUR SETVALUE: " . $e->getMessage());
        return false;
    }
}

// Traitement selon le type de base de données
if ($db) { // Only process if SQLite is available
    // Utiliser l'ancien système pour SQLite
    switch($module) {
        case 'wins':
            handleWinsSQLite($db, $token, $action, $value);
            break;
        case 'timer':
            handleTimerSQLite($db, $token, $action, $value);
            break;
        case 'teams':
            handleTeamsSQLite($db, $token, $action, $value);
            break;
        case 'style':
            handleStyleSQLite($db, $token, $action, $value);
            break;
        case 'timer-style':
            handleTimerStyleSQLite($db, $token, $action, $value);
            break;
        case 'teams-style':
            handleTeamsStyleSQLite($db, $token, $action, $value);
            break;
        case 'chat':
            handleChatSQLite($db, $token, $action, $value);
            break;
        case 'getStyles':
            handleGetStylesSQLite($db, $token, $action, $value);
            break;
        default:
            echo json_encode(['success' => false, 'error' => 'Module invalide']);
    }
    $db->close();
} else {
    echo json_encode(['success' => false, 'error' => 'Base de données non disponible']);
}

// ========== HANDLERS POUR SQLITE ==========

function handleWinsSQLite($db, $token, $action, $value) {
    switch($action) {
        case 'get':
            $count = intval(getValue($db, $token, 'wins', 'count') ?? '0');
            $multiplier = intval(getValue($db, $token, 'wins', 'multiplier') ?? '1');
            $multiplierActive = getValue($db, $token, 'wins', 'multiplier_active') ?? 'true';
            echo json_encode([
                'success' => true, 
                'data' => [
                    'count' => $count,
                    'multiplier' => $multiplier,
                    'multiplier_active' => $multiplierActive === 'true',
                    'timestamp' => time()
                ]
            ]);
            break;
            
        case 'add':
            $current = intval(getValue($db, $token, 'wins', 'count') ?? '0');
            $new = $current + intval($value);
            setValue($db, $token, 'wins', 'count', strval($new));
            echo json_encode(['success' => true, 'wins' => $new]);
            break;
            
        case 'subtract':
            $current = intval(getValue($db, $token, 'wins', 'count') ?? '0');
            $new = max(0, $current - intval($value));
            setValue($db, $token, 'wins', 'count', strval($new));
            echo json_encode(['success' => true, 'wins' => $new]);
            break;
            
        case 'set':
            $new = max(0, intval($value));
            setValue($db, $token, 'wins', 'count', strval($new));
            echo json_encode(['success' => true, 'wins' => $new]);
            break;
            
        case 'reset':
            setValue($db, $token, 'wins', 'count', '0');
            echo json_encode(['success' => true, 'wins' => 0]);
            break;
            
        case 'double':
            $current = intval(getValue($db, $token, 'wins', 'count') ?? '0');
            $new = $current * 2;
            setValue($db, $token, 'wins', 'count', strval($new));
            echo json_encode(['success' => true, 'wins' => $new]);
            break;
            
        case 'half':
            $current = intval(getValue($db, $token, 'wins', 'count') ?? '0');
            $new = intval($current / 2);
            setValue($db, $token, 'wins', 'count', strval($new));
            echo json_encode(['success' => true, 'wins' => $new]);
            break;
            
        // Compatibilité avec l'ancien système
        case 'add-wins':
            $current = intval(getValue($db, $token, 'wins', 'count') ?? '0');
            $multiplier = intval(getValue($db, $token, 'wins', 'multiplier') ?? '1');
            $multiplierActive = getValue($db, $token, 'wins', 'multiplier_active') ?? 'true';
            
            // Appliquer le multiplicateur si actif
            if ($multiplierActive === 'true') {
                $new = $current + (intval($value) * $multiplier);
            } else {
                $new = $current + intval($value);
            }
            
            setValue($db, $token, 'wins', 'count', strval($new));
            echo json_encode([
                'success' => true, 
                'data' => [
                    'count' => $new,
                    'multiplier' => $multiplier,
                    'multiplier_active' => $multiplierActive === 'true',
                    'timestamp' => time()
                ]
            ]);
            break;
            
        case 'reset-wins':
            setValue($db, $token, 'wins', 'count', '0');
            $multiplier = intval(getValue($db, $token, 'wins', 'multiplier') ?? '1');
            $multiplierActive = getValue($db, $token, 'wins', 'multiplier_active') ?? 'true';
            echo json_encode([
                'success' => true, 
                'data' => [
                    'count' => 0,
                    'multiplier' => $multiplier,
                    'multiplier_active' => $multiplierActive === 'true',
                    'timestamp' => time()
                ]
            ]);
            break;
            
        case 'add-multi':
            $current = intval(getValue($db, $token, 'wins', 'multiplier') ?? '1');
            $new = max(1, $current + intval($value));
            setValue($db, $token, 'wins', 'multiplier', strval($new));
            $count = intval(getValue($db, $token, 'wins', 'count') ?? '0');
            $multiplierActive = getValue($db, $token, 'wins', 'multiplier_active') ?? 'true';
            echo json_encode([
                'success' => true, 
                'data' => [
                    'count' => $count,
                    'multiplier' => $new,
                    'multiplier_active' => $multiplierActive === 'true',
                    'timestamp' => time()
                ]
            ]);
            break;
            
        case 'reset-multi':
            setValue($db, $token, 'wins', 'multiplier', '1');
            $count = intval(getValue($db, $token, 'wins', 'count') ?? '0');
            $multiplierActive = getValue($db, $token, 'wins', 'multiplier_active') ?? 'true';
            echo json_encode([
                'success' => true, 
                'data' => [
                    'count' => $count,
                    'multiplier' => 1,
                    'multiplier_active' => $multiplierActive === 'true',
                    'timestamp' => time()
                ]
            ]);
            break;
            
        case 'set-multi-active':
            setValue($db, $token, 'wins', 'multiplier_active', $value);
            $count = intval(getValue($db, $token, 'wins', 'count') ?? '0');
            $multiplier = intval(getValue($db, $token, 'wins', 'multiplier') ?? '1');
            echo json_encode([
                'success' => true, 
                'data' => [
                    'count' => $count,
                    'multiplier' => $multiplier,
                    'multiplier_active' => $value === 'true',
                    'timestamp' => time()
                ]
            ]);
            break;
            
        default:
            echo json_encode(['success' => false, 'error' => 'Action invalide']);
    }
}

function handleTimerSQLite($db, $token, $action, $value) {
    switch($action) {
        case 'get':
            $seconds = getValue($db, $token, 'timer', 'seconds') ?? '0';
            $isRunning = getValue($db, $token, 'timer', 'isRunning') ?? 'false';
            $isPaused = getValue($db, $token, 'timer', 'isPaused') ?? 'false';
            $endTime = getValue($db, $token, 'timer', 'endTime') ?? null;
            
            // Si le timer est en cours et pas d'endTime, le calculer
            if ($isRunning === 'true' && !$endTime) {
                $endTime = time() + intval($seconds);
                setValue($db, $token, 'timer', 'endTime', strval($endTime));
            }
            
            echo json_encode([
                'success' => true,
                'data' => [
                    'duration' => intval($seconds),
                    'endTime' => $endTime ? intval($endTime) : null,
                    'isRunning' => $isRunning === 'true',
                    'isPaused' => $isPaused === 'true'
                ]
            ]);
            break;
            
        case 'start':
            setValue($db, $token, 'timer', 'isRunning', 'true');
            setValue($db, $token, 'timer', 'isPaused', 'false');
            
            if (!empty($value)) {
                setValue($db, $token, 'timer', 'seconds', strval($value));
            }
            
            $seconds = getValue($db, $token, 'timer', 'seconds') ?? '0';
            $endTime = time() + intval($seconds);
            setValue($db, $token, 'timer', 'endTime', strval($endTime));
            
            echo json_encode([
                'success' => true,
                'state' => [
                    'duration' => intval($seconds),
                    'endTime' => $endTime,
                    'isRunning' => true,
                    'isPaused' => false
                ]
            ]);
            break;
            
        case 'pause':
            setValue($db, $token, 'timer', 'isRunning', 'false');
            setValue($db, $token, 'timer', 'isPaused', 'true');
            
            if (!empty($value)) {
                setValue($db, $token, 'timer', 'seconds', strval($value));
            }
            
            $seconds = getValue($db, $token, 'timer', 'seconds') ?? '0';
            echo json_encode([
                'success' => true,
                'state' => [
                    'duration' => intval($seconds),
                    'endTime' => null,
                    'isRunning' => false,
                    'isPaused' => true
                ]
            ]);
            break;
            
        case 'reset':
            setValue($db, $token, 'timer', 'seconds', '0');
            setValue($db, $token, 'timer', 'isRunning', 'false');
            setValue($db, $token, 'timer', 'isPaused', 'false');
            setValue($db, $token, 'timer', 'endTime', '');
            
            echo json_encode([
                'success' => true,
                'state' => [
                    'duration' => 0,
                    'endTime' => null,
                    'isRunning' => false,
                    'isPaused' => false
                ]
            ]);
            break;
            
        case 'add':
            $current = intval(getValue($db, $token, 'timer', 'seconds') ?? '0');
            $new = $current + intval($value);
            setValue($db, $token, 'timer', 'seconds', strval($new));
            
            // Si le timer est en cours, ajuster l'endTime
            $isRunning = getValue($db, $token, 'timer', 'isRunning') === 'true';
            if ($isRunning) {
                $endTime = getValue($db, $token, 'timer', 'endTime');
                if ($endTime) {
                    $newEndTime = intval($endTime) + intval($value);
                    setValue($db, $token, 'timer', 'endTime', strval($newEndTime));
                }
            }
            
            echo json_encode([
                'success' => true,
                'state' => [
                    'duration' => $new,
                    'endTime' => $isRunning ? (getValue($db, $token, 'timer', 'endTime') ? intval(getValue($db, $token, 'timer', 'endTime')) : null) : null,
                    'isRunning' => $isRunning,
                    'isPaused' => getValue($db, $token, 'timer', 'isPaused') === 'true'
                ]
            ]);
            break;
            
        case 'subtract':
            $current = intval(getValue($db, $token, 'timer', 'seconds') ?? '0');
            $new = max(0, $current - intval($value));
            setValue($db, $token, 'timer', 'seconds', strval($new));
            
            // Si le timer est en cours, ajuster l'endTime
            $isRunning = getValue($db, $token, 'timer', 'isRunning') === 'true';
            if ($isRunning) {
                $endTime = getValue($db, $token, 'timer', 'endTime');
                if ($endTime) {
                    $newEndTime = max(time(), intval($endTime) - intval($value));
                    setValue($db, $token, 'timer', 'endTime', strval($newEndTime));
                }
            }
            
            echo json_encode([
                'success' => true,
                'state' => [
                    'duration' => $new,
                    'endTime' => $isRunning ? (getValue($db, $token, 'timer', 'endTime') ? intval(getValue($db, $token, 'timer', 'endTime')) : null) : null,
                    'isRunning' => $isRunning,
                    'isPaused' => getValue($db, $token, 'timer', 'isPaused') === 'true'
                ]
            ]);
            break;
            
        case 'set':
            // Pour l'auto-save, on peut recevoir un objet JSON avec duration, endTime, isRunning, isPaused
            $data = json_decode($value, true);
            if ($data) {
                if (isset($data['duration'])) {
                    setValue($db, $token, 'timer', 'seconds', strval($data['duration']));
                }
                if (isset($data['endTime'])) {
                    setValue($db, $token, 'timer', 'endTime', $data['endTime'] ? strval($data['endTime']) : '');
                }
                if (isset($data['isRunning'])) {
                    setValue($db, $token, 'timer', 'isRunning', $data['isRunning'] ? 'true' : 'false');
                }
                if (isset($data['isPaused'])) {
                    setValue($db, $token, 'timer', 'isPaused', $data['isPaused'] ? 'true' : 'false');
                }
                
                echo json_encode([
                    'success' => true,
                    'data' => [
                        'duration' => isset($data['duration']) ? intval($data['duration']) : intval(getValue($db, $token, 'timer', 'seconds') ?? '0'),
                        'endTime' => isset($data['endTime']) ? ($data['endTime'] ? intval($data['endTime']) : null) : (getValue($db, $token, 'timer', 'endTime') ? intval(getValue($db, $token, 'timer', 'endTime')) : null),
                        'isRunning' => isset($data['isRunning']) ? $data['isRunning'] : (getValue($db, $token, 'timer', 'isRunning') === 'true'),
                        'isPaused' => isset($data['isPaused']) ? $data['isPaused'] : (getValue($db, $token, 'timer', 'isPaused') === 'true')
                    ]
                ]);
            } else {
                echo json_encode(['success' => false, 'error' => 'Données invalides']);
            }
            break;
            
        default:
            echo json_encode(['success' => false, 'error' => 'Action invalide']);
    }
}

function handleTeamsSQLite($db, $token, $action, $value) {
    // Pour SQLite, on stocke les scores dans user_data
    $greenScore = intval(getValue($db, $token, 'teams', 'green_score') ?? '0');
    $redScore = intval(getValue($db, $token, 'teams', 'red_score') ?? '0');
    
    switch($action) {
        case 'get':
            $greenName = getValue($db, $token, 'teams', 'green_name') ?? 'ÉQUIPE VERTE';
            $redName = getValue($db, $token, 'teams', 'red_name') ?? 'ÉQUIPE ROUGE';
            echo json_encode([
                'success' => true,
                'data' => [
                    'green' => [
                        'name' => $greenName,
                        'score' => $greenScore
                    ],
                    'red' => [
                        'name' => $redName,
                        'score' => $redScore
                    ],
                    'timestamp' => time()
                ]
            ]);
            break;
            
        case 'reset-all':
            setValue($db, $token, 'teams', 'green_score', '0');
            setValue($db, $token, 'teams', 'red_score', '0');
            echo json_encode([
                'success' => true,
                'data' => [
                    'green' => ['score' => 0],
                    'red' => ['score' => 0]
                ]
            ]);
            break;
            
        case 'add-score':
            $data = json_decode($value, true);
            if ($data && isset($data['team']) && isset($data['value'])) {
                if ($data['team'] === 'green') {
                    $newScore = $greenScore + intval($data['value']);
                    setValue($db, $token, 'teams', 'green_score', strval($newScore));
                    echo json_encode([
                        'success' => true,
                        'data' => [
                            'green' => ['score' => $newScore],
                            'red' => ['score' => $redScore]
                        ]
                    ]);
                } elseif ($data['team'] === 'red') {
                    $newScore = $redScore + intval($data['value']);
                    setValue($db, $token, 'teams', 'red_score', strval($newScore));
                    echo json_encode([
                        'success' => true,
                        'data' => [
                            'green' => ['score' => $greenScore],
                            'red' => ['score' => $newScore]
                        ]
                    ]);
                }
            } else {
                echo json_encode(['success' => false, 'error' => 'Données invalides']);
            }
            break;
            
        case 'reset-score':
            if ($value === 'green') {
                setValue($db, $token, 'teams', 'green_score', '0');
                echo json_encode([
                    'success' => true,
                    'data' => [
                        'green' => ['score' => 0],
                        'red' => ['score' => $redScore]
                    ]
                ]);
            } elseif ($value === 'red') {
                setValue($db, $token, 'teams', 'red_score', '0');
                echo json_encode([
                    'success' => true,
                    'data' => [
                        'green' => ['score' => $greenScore],
                        'red' => ['score' => 0]
                    ]
                ]);
            }
            break;
            
        case 'swap-scores':
            setValue($db, $token, 'teams', 'green_score', strval($redScore));
            setValue($db, $token, 'teams', 'red_score', strval($greenScore));
            echo json_encode([
                'success' => true,
                'data' => [
                    'green' => ['score' => $redScore],
                    'red' => ['score' => $greenScore]
                ]
            ]);
            break;
            
        case 'set-score':
            $data = json_decode($value, true);
            if ($data && isset($data['team']) && isset($data['value'])) {
                $newScore = max(0, intval($data['value']));
                if ($data['team'] === 'green') {
                    setValue($db, $token, 'teams', 'green_score', strval($newScore));
                    echo json_encode([
                        'success' => true,
                        'data' => [
                            'green' => ['score' => $newScore],
                            'red' => ['score' => $redScore]
                        ]
                    ]);
                } elseif ($data['team'] === 'red') {
                    setValue($db, $token, 'teams', 'red_score', strval($newScore));
                    echo json_encode([
                        'success' => true,
                        'data' => [
                            'green' => ['score' => $greenScore],
                            'red' => ['score' => $newScore]
                        ]
                    ]);
                }
            } else {
                echo json_encode(['success' => false, 'error' => 'Données invalides']);
            }
            break;
            
        case 'double-score':
            if ($value === 'green') {
                $newScore = $greenScore * 2;
                setValue($db, $token, 'teams', 'green_score', strval($newScore));
                echo json_encode([
                    'success' => true,
                    'data' => [
                        'green' => ['score' => $newScore],
                        'red' => ['score' => $redScore]
                    ]
                ]);
            } elseif ($value === 'red') {
                $newScore = $redScore * 2;
                setValue($db, $token, 'teams', 'red_score', strval($newScore));
                echo json_encode([
                    'success' => true,
                    'data' => [
                        'green' => ['score' => $greenScore],
                        'red' => ['score' => $newScore]
                    ]
                ]);
            }
            break;
            
        case 'half-score':
            if ($value === 'green') {
                $newScore = intval($greenScore / 2);
                setValue($db, $token, 'teams', 'green_score', strval($newScore));
                echo json_encode([
                    'success' => true,
                    'data' => [
                        'green' => ['score' => $newScore],
                        'red' => ['score' => $redScore]
                    ]
                ]);
            } elseif ($value === 'red') {
                $newScore = intval($redScore / 2);
                setValue($db, $token, 'teams', 'red_score', strval($newScore));
                echo json_encode([
                    'success' => true,
                    'data' => [
                        'green' => ['score' => $greenScore],
                        'red' => ['score' => $newScore]
                    ]
                ]);
            }
            break;
            
        default:
            echo json_encode(['success' => false, 'error' => 'Action invalide']);
    }
}

function handleStyleSQLite($db, $token, $action, $value) {
    switch($action) {
        case 'get':
            $stmt = $db->prepare('SELECT styles FROM user_styles WHERE token = ?');
            $stmt->bindValue(1, $token, SQLITE3_TEXT);
            $result = $stmt->execute();
            $row = $result->fetchArray();
            
            if ($row && $row['styles']) {
                $styles = json_decode($row['styles'], true);
                echo json_encode(['success' => true, 'data' => $styles]);
            } else {
                echo json_encode(['success' => true, 'data' => []]);
            }
            break;
            
        case 'save':
            $stmt = $db->prepare('INSERT OR REPLACE INTO user_styles (token, styles, updated_at) VALUES (?, ?, CURRENT_TIMESTAMP)');
            $stmt->bindValue(1, $token, SQLITE3_TEXT);
            $stmt->bindValue(2, $value, SQLITE3_TEXT);
            $result = $stmt->execute();
            
            echo json_encode(['success' => $result !== false]);
            break;
            
        default:
            echo json_encode(['success' => false, 'error' => 'Action invalide']);
    }
}

function handleTimerStyleSQLite($db, $token, $action, $value) {
    switch($action) {
        case 'get':
            $styles = getValue($db, $token, 'timer-style', 'config') ?? '{}';
            echo json_encode(['success' => true, 'data' => json_decode($styles, true)]);
            break;
            
        case 'save':
            setValue($db, $token, 'timer-style', 'config', $value);
            echo json_encode(['success' => true]);
            break;
            
        default:
            echo json_encode(['success' => false, 'error' => 'Action invalide']);
    }
}

function handleTeamsStyleSQLite($db, $token, $action, $value) {
    switch($action) {
        case 'get':
            $styles = getValue($db, $token, 'teams-styles', 'config') ?? '{}';
            echo json_encode(['success' => true, 'data' => json_decode($styles, true)]);
            break;
            
        case 'save':
            setValue($db, $token, 'teams-styles', 'config', $value);
            echo json_encode(['success' => true]);
            break;
            
        default:
            echo json_encode(['success' => false, 'error' => 'Action invalide']);
    }
}

// === Chat simple via SQLite (bridge Dashboard <-> Discord) ===
function handleChatSQLite($db, $token, $action, $value) {
    // Structure de table attendue:
    // CREATE TABLE IF NOT EXISTS chat_messages (
    //   id INTEGER PRIMARY KEY AUTOINCREMENT,
    //   token TEXT NOT NULL,
    //   source TEXT NOT NULL, -- 'dashboard' | 'discord'
    //   message TEXT NOT NULL,
    //   created_at INTEGER NOT NULL
    // );

    try {
        // Garantir la table (idempotent)
        $db->exec('CREATE TABLE IF NOT EXISTS chat_messages (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            token TEXT NOT NULL,
            source TEXT NOT NULL,
            message TEXT NOT NULL,
            created_at INTEGER NOT NULL
        )');

        switch ($action) {
            case 'send':
                $payload = json_decode($value, true);
                $text = is_array($payload) && isset($payload['message']) ? strval($payload['message']) : strval($value);
                if ($text === '') { echo json_encode(['success' => false, 'error' => 'Message vide']); return; }
                $stmt = $db->prepare('INSERT INTO chat_messages (token, source, message, created_at) VALUES (?, ?, ?, ?)');
                $stmt->bindValue(1, $token, SQLITE3_TEXT);
                $stmt->bindValue(2, 'dashboard', SQLITE3_TEXT);
                $stmt->bindValue(3, $text, SQLITE3_TEXT);
                $stmt->bindValue(4, time(), SQLITE3_INTEGER);
                $ok = $stmt->execute();
                // Garder uniquement les 20 derniers messages pour ce token
                try {
                    $db->exec("DELETE FROM chat_messages WHERE token = '" . SQLite3::escapeString($token) . "' AND id NOT IN (SELECT id FROM chat_messages WHERE token = '" . SQLite3::escapeString($token) . "' ORDER BY id DESC LIMIT 20)");
                } catch (Exception $e) {}
                echo json_encode(['success' => $ok !== false]);
                break;

            case 'list':
                $since = intval($_GET['since'] ?? 0);
                if ($since > 0) {
                    $stmt = $db->prepare('SELECT id, source, message, created_at FROM chat_messages WHERE token = ? AND created_at > ? ORDER BY id ASC LIMIT 200');
                    $stmt->bindValue(1, $token, SQLITE3_TEXT);
                    $stmt->bindValue(2, $since, SQLITE3_INTEGER);
                } else {
                    $stmt = $db->prepare('SELECT id, source, message, created_at FROM chat_messages WHERE token = ? ORDER BY id DESC LIMIT 50');
                    $stmt->bindValue(1, $token, SQLITE3_TEXT);
                }
                $result = $stmt->execute();
                $rows = [];
                while ($row = $result->fetchArray(SQLITE3_ASSOC)) { $rows[] = $row; }
                // Si pas de since, on renvoie trié croissant pour l'affichage
                if ($since === 0) { $rows = array_reverse($rows); }
                echo json_encode(['success' => true, 'messages' => $rows, 'now' => time()]);
                break;

            case 'close':
                // Insérer un message système de fermeture pour informer le staff côté Discord
                $stmt = $db->prepare('INSERT INTO chat_messages (token, source, message, created_at) VALUES (?, ?, ?, ?)');
                $stmt->bindValue(1, $token, SQLITE3_TEXT);
                $stmt->bindValue(2, 'dashboard', SQLITE3_TEXT);
                $stmt->bindValue(3, '[Système] Discussion close par l’utilisateur', SQLITE3_TEXT);
                $stmt->bindValue(4, time(), SQLITE3_INTEGER);
                $ok = $stmt->execute();
                // Nettoyer tous les messages de ce token pour repartir à zéro
                try {
                    $db->exec("DELETE FROM chat_messages WHERE token = '" . SQLite3::escapeString($token) . "'");
                } catch (Exception $e) {}
                echo json_encode(['success' => $ok !== false, 'cleared' => true]);
                break;

            default:
                echo json_encode(['success' => false, 'error' => 'Action invalide']);
        }
    } catch (Exception $e) {
        error_log('Erreur handleChatSQLite: ' . $e->getMessage());
        echo json_encode(['success' => false, 'error' => 'Erreur chat']);
    }
}

// ========== HANDLERS POUR MYSQL (déjà définis dans le code original) ==========

// Fonction pour obtenir la configuration d'un module
function getModuleConfig($pdo, $userId, $module) {
    $table = $module . '_config';
    try {
        $stmt = $pdo->prepare("SELECT config_data FROM $table WHERE user_id = ?");
        $stmt->execute([$userId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result) {
            return json_decode($result['config_data'], true);
        }
        
        // Retourner la configuration par défaut
        return getDefaultConfig($module);
    } catch (PDOException $e) {
        error_log("Erreur lors de la récupération de la config: " . $e->getMessage());
        return getDefaultConfig($module);
    }
}

// Fonction pour sauvegarder la configuration d'un module
function saveModuleConfig($pdo, $userId, $module, $config) {
    $table = $module . '_config';
    try {
        $stmt = $pdo->prepare("
            INSERT INTO $table (user_id, config_data) 
            VALUES (?, ?) 
            ON DUPLICATE KEY UPDATE 
            config_data = VALUES(config_data),
            updated_at = CURRENT_TIMESTAMP
        ");
        $stmt->execute([$userId, json_encode($config)]);
        return true;
    } catch (PDOException $e) {
        error_log("Erreur lors de la sauvegarde de la config: " . $e->getMessage());
        return false;
    }
}

// Fonction pour obtenir la configuration par défaut
function getDefaultConfig($module) {
    switch ($module) {
        case 'wins':
            return [
                'title' => 'WINS',
                'titleColor' => '#FFFFFF',
                'titleFont' => 'Orbitron',
                'titleSize' => '72',
                'counterColor' => '#4ADE80',
                'counterFont' => 'Orbitron',
                'counterSize' => '144',
                'backgroundColor' => '#0F172A',
                'animationEnabled' => true,
                'soundEnabled' => true,
                'buttons' => [
                    'add1' => true,
                    'add5' => true,
                    'add10' => true,
                    'sub1' => true,
                    'sub5' => true,
                    'sub10' => true,
                    'reset' => true,
                    'set' => true,
                    'double' => true,
                    'half' => true
                ]
            ];
            
        case 'timer':
            return [
                'mode' => 'timer',
                'format' => 'mm:ss',
                'targetTime' => 300,
                'backgroundColor' => '#0F172A',
                'timerColor' => '#4ADE80',
                'timerFont' => 'Orbitron',
                'timerSize' => '96',
                'titleText' => 'TIMER',
                'titleColor' => '#FFFFFF',
                'titleFont' => 'Orbitron',
                'titleSize' => '48',
                'showTitle' => true,
                'showProgressBar' => true,
                'progressBarColor' => '#4ADE80',
                'soundEnabled' => true,
                'autoStart' => false,
                'buttons' => [
                    'start' => true,
                    'pause' => true,
                    'reset' => true,
                    'add30' => true,
                    'add60' => true,
                    'sub30' => true,
                    'sub60' => true,
                    'set' => true
                ]
            ];
            
        case 'teams':
            return [
                'greenTeam' => [
                    'name' => 'Green Team',
                    'color' => '#4ADE80',
                    'font' => 'Orbitron',
                    'nameSize' => '48',
                    'scoreSize' => '96',
                    'scoreColor' => '#4ADE80'
                ],
                'redTeam' => [
                    'name' => 'Red Team',
                    'color' => '#EF4444',
                    'font' => 'Orbitron',
                    'nameSize' => '48',
                    'scoreSize' => '96',
                    'scoreColor' => '#EF4444'
                ],
                'backgroundColor' => '#0F172A',
                'separatorColor' => '#FFFFFF',
                'showSeparator' => true,
                'animationEnabled' => true,
                'soundEnabled' => true,
                'buttons' => [
                    'add1' => true,
                    'add5' => true,
                    'add10' => true,
                    'sub1' => true,
                    'sub5' => true,
                    'sub10' => true,
                    'reset' => true,
                    'set' => true,
                    'double' => true,
                    'half' => true
                ]
            ];
            
        default:
            return [];
    }
}

// Gestionnaire pour le module Wins
function handleWins($pdo, $userId, $action, $value) {
    try {
        // Récupérer l'état actuel
        $stmt = $pdo->prepare("SELECT wins FROM wins_state WHERE user_id = ?");
        $stmt->execute([$userId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $currentWins = $result ? $result['wins'] : 0;
        $newWins = $currentWins;
        
        // Traiter l'action
        switch ($action) {
            case 'get':
                echo json_encode(['success' => true, 'wins' => $currentWins]);
                return;
                
            case 'add':
                $newWins = $currentWins + intval($value);
                break;
                
            case 'subtract':
                $newWins = max(0, $currentWins - intval($value));
                break;
                
            case 'set':
                $newWins = max(0, intval($value));
                break;
                
            case 'reset':
                $newWins = 0;
                break;
                
            case 'double':
                $newWins = $currentWins * 2;
                break;
                
            case 'half':
                $newWins = intval($currentWins / 2);
                break;
                
            default:
                echo json_encode(['success' => false, 'error' => 'Action invalide']);
                return;
        }
        
        // Sauvegarder le nouvel état
        $stmt = $pdo->prepare("
            INSERT INTO wins_state (user_id, wins) 
            VALUES (?, ?) 
            ON DUPLICATE KEY UPDATE 
            wins = VALUES(wins),
            updated_at = CURRENT_TIMESTAMP
        ");
        $stmt->execute([$userId, $newWins]);
        
        // Logger l'action
        logAction($pdo, $userId, 'wins', $action, [
            'old_value' => $currentWins,
            'new_value' => $newWins,
            'change' => $newWins - $currentWins
        ]);
        
        echo json_encode(['success' => true, 'wins' => $newWins]);
        
    } catch (PDOException $e) {
        error_log("Erreur dans handleWins: " . $e->getMessage());
        echo json_encode(['success' => false, 'error' => 'Erreur lors du traitement']);
    }
}

// Gestionnaire pour le module Timer
function handleTimer($pdo, $userId, $action, $value) {
    try {
        // Récupérer l'état actuel
        $stmt = $pdo->prepare("SELECT state_data FROM timer_state WHERE user_id = ?");
        $stmt->execute([$userId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $state = $result ? json_decode($result['state_data'], true) : [
            'time' => 0,
            'isRunning' => false,
            'startTime' => null,
            'pausedTime' => 0
        ];
        
        // Traiter l'action
        switch ($action) {
            case 'get':
                echo json_encode(['success' => true, 'state' => $state]);
                return;
                
            case 'start':
                $state['isRunning'] = true;
                $state['startTime'] = time();
                break;
                
            case 'pause':
                if ($state['isRunning']) {
                    $state['pausedTime'] += time() - $state['startTime'];
                    $state['isRunning'] = false;
                    $state['startTime'] = null;
                }
                break;
                
            case 'reset':
                $state = [
                    'time' => 0,
                    'isRunning' => false,
                    'startTime' => null,
                    'pausedTime' => 0
                ];
                break;
                
            case 'set':
                $state['time'] = intval($value);
                $state['pausedTime'] = intval($value);
                $state['isRunning'] = false;
                $state['startTime'] = null;
                break;
                
            case 'add':
                $state['pausedTime'] += intval($value);
                break;
                
            case 'subtract':
                $state['pausedTime'] = max(0, $state['pausedTime'] - intval($value));
                break;
                
            default:
                echo json_encode(['success' => false, 'error' => 'Action invalide']);
                return;
        }
        
        // Calculer le temps actuel si le timer est en cours
        if ($state['isRunning'] && $state['startTime']) {
            $state['time'] = $state['pausedTime'] + (time() - $state['startTime']);
        } else {
            $state['time'] = $state['pausedTime'];
        }
        
        // Sauvegarder le nouvel état
        $stmt = $pdo->prepare("
            INSERT INTO timer_state (user_id, state_data) 
            VALUES (?, ?) 
            ON DUPLICATE KEY UPDATE 
            state_data = VALUES(state_data),
            updated_at = CURRENT_TIMESTAMP
        ");
        $stmt->execute([$userId, json_encode($state)]);
        
        // Logger l'action
        logAction($pdo, $userId, 'timer', $action, $state);
        
        echo json_encode(['success' => true, 'state' => $state]);
        
    } catch (PDOException $e) {
        error_log("Erreur dans handleTimer: " . $e->getMessage());
        echo json_encode(['success' => false, 'error' => 'Erreur lors du traitement']);
    }
}

// Gestionnaire pour le module Teams
function handleTeams($pdo, $userId, $action, $value) {
    try {
        // Récupérer l'état actuel
        $stmt = $pdo->prepare("SELECT green_score, red_score FROM teams_state WHERE user_id = ?");
        $stmt->execute([$userId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $greenScore = $result ? $result['green_score'] : 0;
        $redScore = $result ? $result['red_score'] : 0;
        
        // Parser l'action pour déterminer l'équipe et l'opération
        $parts = explode('_', $action);
        if (count($parts) < 2) {
            if ($action === 'get') {
                echo json_encode([
                    'success' => true,
                    'green' => $greenScore,
                    'red' => $redScore
                ]);
                return;
            } else if ($action === 'reset') {
                $greenScore = 0;
                $redScore = 0;
            } else {
                echo json_encode(['success' => false, 'error' => 'Action invalide']);
                return;
            }
        } else {
            $team = $parts[0]; // 'green' ou 'red'
            $operation = $parts[1]; // 'add', 'subtract', 'set', etc.
            
            if (!in_array($team, ['green', 'red'])) {
                echo json_encode(['success' => false, 'error' => 'Équipe invalide']);
                return;
            }
            
            $currentScore = $team === 'green' ? $greenScore : $redScore;
            $newScore = $currentScore;
            
            switch ($operation) {
                case 'add':
                    $newScore = $currentScore + intval($value);
                    break;
                    
                case 'subtract':
                    $newScore = max(0, $currentScore - intval($value));
                    break;
                    
                case 'set':
                    $newScore = max(0, intval($value));
                    break;
                    
                case 'double':
                    $newScore = $currentScore * 2;
                    break;
                    
                case 'half':
                    $newScore = intval($currentScore / 2);
                    break;
                    
                case 'reset':
                    $newScore = 0;
                    break;
                    
                default:
                    echo json_encode(['success' => false, 'error' => 'Opération invalide']);
                    return;
            }
            
            if ($team === 'green') {
                $greenScore = $newScore;
            } else {
                $redScore = $newScore;
            }
        }
        
        // Sauvegarder le nouvel état
        $stmt = $pdo->prepare("
            INSERT INTO teams_state (user_id, green_score, red_score) 
            VALUES (?, ?, ?) 
            ON DUPLICATE KEY UPDATE 
            green_score = VALUES(green_score),
            red_score = VALUES(red_score),
            updated_at = CURRENT_TIMESTAMP
        ");
        $stmt->execute([$userId, $greenScore, $redScore]);
        
        // Logger l'action
        logAction($pdo, $userId, 'teams', $action, [
            'green_score' => $greenScore,
            'red_score' => $redScore,
            'value' => $value
        ]);
        
        echo json_encode([
            'success' => true,
            'green' => $greenScore,
            'red' => $redScore
        ]);
        
    } catch (PDOException $e) {
        error_log("Erreur dans handleTeams: " . $e->getMessage());
        echo json_encode(['success' => false, 'error' => 'Erreur lors du traitement']);
    }
}

// Gestionnaire pour les configurations
function handleConfig($pdo, $userId, $action, $value) {
    try {
        $moduleType = $_GET['type'] ?? '';
        
        if (!in_array($moduleType, ['wins', 'timer', 'teams'])) {
            echo json_encode(['success' => false, 'error' => 'Type de module invalide']);
            return;
        }
        
        switch ($action) {
            case 'get':
                $config = getModuleConfig($pdo, $userId, $moduleType);
                echo json_encode(['success' => true, 'config' => $config]);
                break;
                
            case 'save':
                $config = json_decode($value, true);
                if (!$config) {
                    echo json_encode(['success' => false, 'error' => 'Configuration invalide']);
                    return;
                }
                
                $success = saveModuleConfig($pdo, $userId, $moduleType, $config);
                
                if ($success) {
                    logAction($pdo, $userId, $moduleType, 'config_update', $config);
                    echo json_encode(['success' => true]);
                } else {
                    echo json_encode(['success' => false, 'error' => 'Erreur lors de la sauvegarde']);
                }
                break;
                
            default:
                echo json_encode(['success' => false, 'error' => 'Action de configuration invalide']);
        }
        
    } catch (Exception $e) {
        error_log("Erreur dans handleConfig: " . $e->getMessage());
        echo json_encode(['success' => false, 'error' => 'Erreur lors du traitement de la configuration']);
    }
}

// ========== NOUVELLE FONCTION POUR RÉCUPÉRER LES STYLES VIA API ==========

function handleGetStylesSQLite($db, $token, $action, $value) {
    $moduleType = $_GET['type'] ?? '';
    
    if (!in_array($moduleType, ['wins', 'timer', 'teams'])) {
        echo json_encode(['success' => false, 'error' => 'Type de module invalide']);
        return;
    }
    
    switch($action) {
        case 'get':
            $styles = getValue($db, $token, $moduleType . '-styles', 'config') ?? '{}';
            $stylesData = json_decode($styles, true);
            
            // Ajouter un timestamp pour la synchronisation
            $response = [
                'success' => true,
                'data' => $stylesData,
                'timestamp' => time(),
                'version' => '2.0'
            ];
            
            echo json_encode($response);
            break;
            
        default:
            echo json_encode(['success' => false, 'error' => 'Action invalide']);
    }
}

function handleGetStyles($pdo, $userId, $action, $value) {
    $moduleType = $_GET['type'] ?? '';
    
    if (!in_array($moduleType, ['wins', 'timer', 'teams'])) {
        echo json_encode(['success' => false, 'error' => 'Type de module invalide']);
        return;
    }
    
    switch($action) {
        case 'get':
            try {
                $table = $moduleType . '_config';
                $stmt = $pdo->prepare("SELECT config_data FROM $table WHERE user_id = ?");
                $stmt->execute([$userId]);
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                
                $stylesData = $result ? json_decode($result['config_data'], true) : getDefaultConfig($moduleType);
                
                // Ajouter un timestamp pour la synchronisation
                $response = [
                    'success' => true,
                    'data' => $stylesData,
                    'timestamp' => time(),
                    'version' => '2.0'
                ];
                
                echo json_encode($response);
                
            } catch (PDOException $e) {
                error_log("Erreur lors de la récupération des styles: " . $e->getMessage());
                echo json_encode(['success' => false, 'error' => 'Erreur lors de la récupération des styles']);
            }
            break;
            
        default:
            echo json_encode(['success' => false, 'error' => 'Action invalide']);
    }
}
?>