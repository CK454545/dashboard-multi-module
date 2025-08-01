<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Configuration de la base de données MySQL
$mysql_host = 'localhost';
$mysql_dbname = 'myfullagency_connect';
$mysql_username = 'myfullagency_connect';
$mysql_password = 'myfullagency_connect';

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

// Variables globales pour les connexions
$db = null; // SQLite
$pdo = null; // MySQL
$user = null;
$dbType = null; // 'sqlite' ou 'mysql'

// D'abord, essayer avec SQLite (ancienne base)
$sqlitePath = __DIR__ . '/../database/database.db'; // Chemin correct depuis web/
if (file_exists($sqlitePath)) {
    try {
        $db = new SQLite3($sqlitePath);
        $stmt = $db->prepare('SELECT token, discord_id FROM users WHERE token = ?');
        $stmt->bindValue(1, $token, SQLITE3_TEXT);
        $result = $stmt->execute();
        $userRow = $result->fetchArray(SQLITE3_ASSOC);
        
        if ($userRow) {
            $user = [
                'id' => $token, // Utiliser le token comme ID pour SQLite
                'discord_id' => $userRow['discord_id'],
                'pseudo' => $userRow['discord_id'], // Utiliser discord_id comme pseudo
                'token' => $userRow['token']
            ];
            $dbType = 'sqlite';
        }
    } catch (Exception $e) {
        error_log("Erreur SQLite: " . $e->getMessage());
    }
}

// Si pas trouvé dans SQLite, essayer MySQL
if (!$user) {
    try {
        $pdo = new PDO("mysql:host=$mysql_host;dbname=$mysql_dbname;charset=utf8mb4", $mysql_username, $mysql_password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        $stmt = $pdo->prepare("SELECT id, discord_id, pseudo FROM users WHERE token = ? AND is_active = 1");
        $stmt->execute([$token]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user) {
            $dbType = 'mysql';
            // Mettre à jour la dernière activité
            $updateStmt = $pdo->prepare("UPDATE users SET last_activity = NOW() WHERE id = ?");
            $updateStmt->execute([$user['id']]);
        }
        
    } catch (PDOException $e) {
        error_log("Erreur MySQL: " . $e->getMessage());
    }
}

// Si aucun utilisateur trouvé
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
if ($dbType === 'sqlite') {
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
        default:
            echo json_encode(['success' => false, 'error' => 'Module invalide']);
    }
    $db->close();
} else {
    // Utiliser le nouveau système pour MySQL
    switch ($module) {
        case 'wins':
            handleWins($pdo, $user['id'], $action, $value);
            break;
        case 'timer':
            handleTimer($pdo, $user['id'], $action, $value);
            break;
        case 'teams':
            handleTeams($pdo, $user['id'], $action, $value);
            break;
        case 'config':
            handleConfig($pdo, $user['id'], $action, $value);
            break;
        default:
            echo json_encode(['success' => false, 'error' => 'Module invalide']);
    }
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
            $new = $current + intval($value);
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
            echo json_encode([
                'success' => true,
                'data' => [
                    'seconds' => intval($seconds),
                    'isRunning' => $isRunning === 'true'
                ]
            ]);
            break;
            
        case 'start':
            setValue($db, $token, 'timer', 'isRunning', 'true');
            if (!empty($value)) {
                setValue($db, $token, 'timer', 'seconds', strval($value));
            }
            $seconds = getValue($db, $token, 'timer', 'seconds') ?? '0';
            echo json_encode([
                'success' => true,
                'state' => [
                    'time' => intval($seconds),
                    'isRunning' => true,
                    'startTime' => time()
                ]
            ]);
            break;
            
        case 'pause':
            setValue($db, $token, 'timer', 'isRunning', 'false');
            if (!empty($value)) {
                setValue($db, $token, 'timer', 'seconds', strval($value));
            }
            $seconds = getValue($db, $token, 'timer', 'seconds') ?? '0';
            echo json_encode([
                'success' => true,
                'state' => [
                    'time' => intval($seconds),
                    'isRunning' => false,
                    'pausedTime' => intval($seconds)
                ]
            ]);
            break;
            
        case 'reset':
            setValue($db, $token, 'timer', 'seconds', '0');
            setValue($db, $token, 'timer', 'isRunning', 'false');
            echo json_encode([
                'success' => true,
                'state' => [
                    'time' => 0,
                    'isRunning' => false,
                    'pausedTime' => 0
                ]
            ]);
            break;
            
        case 'add':
            $current = intval(getValue($db, $token, 'timer', 'seconds') ?? '0');
            $new = $current + intval($value);
            setValue($db, $token, 'timer', 'seconds', strval($new));
            echo json_encode([
                'success' => true,
                'state' => [
                    'time' => $new,
                    'isRunning' => getValue($db, $token, 'timer', 'isRunning') === 'true',
                    'pausedTime' => $new
                ]
            ]);
            break;
            
        case 'subtract':
            $current = intval(getValue($db, $token, 'timer', 'seconds') ?? '0');
            $new = max(0, $current - intval($value));
            setValue($db, $token, 'timer', 'seconds', strval($new));
            echo json_encode([
                'success' => true,
                'state' => [
                    'time' => $new,
                    'isRunning' => getValue($db, $token, 'timer', 'isRunning') === 'true',
                    'pausedTime' => $new
                ]
            ]);
            break;
            
        case 'set':
            // Pour l'auto-save, on peut recevoir un objet JSON avec seconds et isRunning
            $data = json_decode($value, true);
            if ($data && isset($data['seconds'])) {
                setValue($db, $token, 'timer', 'seconds', strval($data['seconds']));
                if (isset($data['isRunning'])) {
                    setValue($db, $token, 'timer', 'isRunning', $data['isRunning'] ? 'true' : 'false');
                }
                echo json_encode([
                    'success' => true,
                    'data' => [
                        'seconds' => intval($data['seconds']),
                        'isRunning' => isset($data['isRunning']) ? $data['isRunning'] : (getValue($db, $token, 'timer', 'isRunning') === 'true')
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
            $styles = getValue($db, $token, 'timer-styles', 'config') ?? '{}';
            echo json_encode(['success' => true, 'data' => json_decode($styles, true)]);
            break;
            
        case 'save':
            setValue($db, $token, 'timer-styles', 'config', $value);
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
?>