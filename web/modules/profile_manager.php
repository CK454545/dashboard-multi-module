<?php
/**
 * Gestionnaire de Profil MFA CONNECT
 * Gère les profils utilisateurs, statistiques et préférences
 */

require_once __DIR__ . '/../modules/validate_token.php';

class ProfileManager {
    private $db;
    private $token;
    private $user;

    public function __construct($token) {
        $this->token = $token;
        $this->db = new SQLite3(__DIR__ . '/../../database/database.db');


        $this->ensureSchema();

        $this->user = requireValidToken();
    }

    /**
     * S’assure que le schéma minimal existe pour éviter les erreurs 500
     */
    private function ensureSchema(): void {
        $this->db->exec('CREATE TABLE IF NOT EXISTS user_profiles (
            token TEXT PRIMARY KEY,
            display_name TEXT,
            avatar_url TEXT,
            bio TEXT,
            theme_preference TEXT DEFAULT "default",
            language TEXT DEFAULT "fr",
            timezone TEXT DEFAULT "Europe/Paris",
            notifications_enabled BOOLEAN DEFAULT 1,
            last_login TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            login_count INTEGER DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )');
        $this->db->exec('CREATE TABLE IF NOT EXISTS user_stats (
            token TEXT PRIMARY KEY,
            total_wins INTEGER DEFAULT 0,
            total_timer_sessions INTEGER DEFAULT 0,
            total_battle_sessions INTEGER DEFAULT 0,
            total_streaming_time INTEGER DEFAULT 0,
            favorite_module TEXT DEFAULT "wins",
            achievements_unlocked INTEGER DEFAULT 0,
            last_activity TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )');
        $this->db->exec('CREATE TABLE IF NOT EXISTS user_preferences (
            token TEXT PRIMARY KEY,
            auto_save BOOLEAN DEFAULT 1,
            auto_backup BOOLEAN DEFAULT 1,
            sound_effects BOOLEAN DEFAULT 1,
            animations_enabled BOOLEAN DEFAULT 1,
            privacy_level TEXT DEFAULT "public",
            dashboard_layout TEXT DEFAULT "default",
            color_scheme TEXT DEFAULT "blue",
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )');
        $this->db->exec('CREATE TABLE IF NOT EXISTS user_activity_log (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            token TEXT NOT NULL,
            action TEXT NOT NULL,
            module TEXT,
            details TEXT,
            ip_address TEXT,
            user_agent TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )');
        $this->db->exec('CREATE INDEX IF NOT EXISTS idx_uact_token ON user_activity_log(token)');
    }

    /**
     * Récupère le profil complet de l'utilisateur
     */
    public function getProfile() {
        $stmt = $this->db->prepare('
            SELECT 
                u.token,
                u.pseudo,
                up.display_name,
                up.avatar_url,
                up.bio,
                up.theme_preference,
                up.language,
                up.timezone,
                up.notifications_enabled,
                up.last_login,
                up.login_count,
                up.created_at,
                up.updated_at
            FROM users u
            LEFT JOIN user_profiles up ON u.token = up.token
            WHERE u.token = :token
        ');
        $stmt->bindValue(':token', $this->token, SQLITE3_TEXT);
        $result = $stmt->execute();
        
        return $result->fetchArray(SQLITE3_ASSOC);
    }

    /**
     * Récupère les statistiques de l'utilisateur
     */
    public function getStats() {
        $stmt = $this->db->prepare('
            SELECT 
                total_wins,
                total_timer_sessions,
                total_battle_sessions,
                total_streaming_time,
                favorite_module,
                achievements_unlocked,
                last_activity,
                updated_at
            FROM user_stats
            WHERE token = :token
        ');
        $stmt->bindValue(':token', $this->token, SQLITE3_TEXT);
        $result = $stmt->execute();
        
        $stats = $result->fetchArray(SQLITE3_ASSOC);
        
        // Si pas de stats, créer un profil par défaut
        if (!$stats) {
            $this->createDefaultStats();
            return $this->getStats();
        }
        
        return $stats;
    }

    /**
     * Récupère les préférences de l'utilisateur
     */
    public function getPreferences() {
        $stmt = $this->db->prepare('
            SELECT 
                auto_save,
                auto_backup,
                sound_effects,
                animations_enabled,
                privacy_level,
                dashboard_layout,
                color_scheme,
                updated_at
            FROM user_preferences
            WHERE token = :token
        ');
        $stmt->bindValue(':token', $this->token, SQLITE3_TEXT);
        $result = $stmt->execute();
        
        $preferences = $result->fetchArray(SQLITE3_ASSOC);
        
        // Si pas de préférences, créer un profil par défaut
        if (!$preferences) {
            $this->createDefaultPreferences();
            return $this->getPreferences();
        }
        
        return $preferences;
    }

    /**
     * Met à jour le profil utilisateur
     */
    public function updateProfile($data) {
        $stmt = $this->db->prepare('
            INSERT OR REPLACE INTO user_profiles (
                token, display_name, avatar_url, bio, theme_preference, 
                language, timezone, notifications_enabled, updated_at
            ) VALUES (
                :token, :display_name, :avatar_url, :bio, :theme_preference,
                :language, :timezone, :notifications_enabled, CURRENT_TIMESTAMP
            )
        ');
        
        $stmt->bindValue(':token', $this->token, SQLITE3_TEXT);
        $stmt->bindValue(':display_name', $data['display_name'] ?? '', SQLITE3_TEXT);
        $stmt->bindValue(':avatar_url', $data['avatar_url'] ?? '', SQLITE3_TEXT);
        $stmt->bindValue(':bio', $data['bio'] ?? '', SQLITE3_TEXT);
        $stmt->bindValue(':theme_preference', $data['theme_preference'] ?? 'default', SQLITE3_TEXT);
        $stmt->bindValue(':language', $data['language'] ?? 'fr', SQLITE3_TEXT);
        $stmt->bindValue(':timezone', $data['timezone'] ?? 'Europe/Paris', SQLITE3_TEXT);
        $stmt->bindValue(':notifications_enabled', isset($data['notifications_enabled']) ? (int)$data['notifications_enabled'] : 1, SQLITE3_INTEGER);
        
        return $stmt->execute();
    }

    /**
     * Met à jour les préférences utilisateur
     */
    public function updatePreferences($data) {
        $stmt = $this->db->prepare('
            INSERT OR REPLACE INTO user_preferences (
                token, auto_save, auto_backup, sound_effects, animations_enabled,
                privacy_level, dashboard_layout, color_scheme, updated_at
            ) VALUES (
                :token, :auto_save, :auto_backup, :sound_effects, :animations_enabled,
                :privacy_level, :dashboard_layout, :color_scheme, CURRENT_TIMESTAMP
            )
        ');
        
        $stmt->bindValue(':token', $this->token, SQLITE3_TEXT);
        $stmt->bindValue(':auto_save', isset($data['auto_save']) ? (int)$data['auto_save'] : 1, SQLITE3_INTEGER);
        $stmt->bindValue(':auto_backup', isset($data['auto_backup']) ? (int)$data['auto_backup'] : 1, SQLITE3_INTEGER);
        $stmt->bindValue(':sound_effects', isset($data['sound_effects']) ? (int)$data['sound_effects'] : 1, SQLITE3_INTEGER);
        $stmt->bindValue(':animations_enabled', isset($data['animations_enabled']) ? (int)$data['animations_enabled'] : 1, SQLITE3_INTEGER);
        $stmt->bindValue(':privacy_level', $data['privacy_level'] ?? 'public', SQLITE3_TEXT);
        $stmt->bindValue(':dashboard_layout', $data['dashboard_layout'] ?? 'default', SQLITE3_TEXT);
        $stmt->bindValue(':color_scheme', $data['color_scheme'] ?? 'blue', SQLITE3_TEXT);
        
        return $stmt->execute();
    }

    /**
     * Incrémente une statistique
     */
    public function incrementStat($statName, $value = 1) {
        $stmt = $this->db->prepare('
            INSERT OR REPLACE INTO user_stats (
                token, ' . $statName . ', last_activity, updated_at
            ) VALUES (
                :token, 
                COALESCE((SELECT ' . $statName . ' FROM user_stats WHERE token = :token), 0) + :value,
                CURRENT_TIMESTAMP, CURRENT_TIMESTAMP
            )
        ');
        
        $stmt->bindValue(':token', $this->token, SQLITE3_TEXT);
        $stmt->bindValue(':value', $value, SQLITE3_INTEGER);
        
        return $stmt->execute();
    }

    /**
     * Enregistre une activité
     */
    public function logActivity($action, $module = null, $details = null) {
        $stmt = $this->db->prepare('
            INSERT INTO user_activity_log (
                token, action, module, details, ip_address, user_agent, created_at
            ) VALUES (
                :token, :action, :module, :details, :ip_address, :user_agent, CURRENT_TIMESTAMP
            )
        ');
        
        $stmt->bindValue(':token', $this->token, SQLITE3_TEXT);
        $stmt->bindValue(':action', $action, SQLITE3_TEXT);
        $stmt->bindValue(':module', $module, SQLITE3_TEXT);
        $stmt->bindValue(':details', $details ? json_encode($details) : null, SQLITE3_TEXT);
        $stmt->bindValue(':ip_address', $_SERVER['REMOTE_ADDR'] ?? '', SQLITE3_TEXT);
        $stmt->bindValue(':user_agent', $_SERVER['HTTP_USER_AGENT'] ?? '', SQLITE3_TEXT);
        
        return $stmt->execute();
    }

    /**
     * Crée des statistiques par défaut
     */
    private function createDefaultStats() {
        $stmt = $this->db->prepare('
            INSERT INTO user_stats (
                token, total_wins, total_timer_sessions, total_battle_sessions,
                total_streaming_time, favorite_module, achievements_unlocked,
                last_activity, updated_at
            ) VALUES (
                :token, 0, 0, 0, 0, "wins", 0, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP
            )
        ');
        
        $stmt->bindValue(':token', $this->token, SQLITE3_TEXT);
        return $stmt->execute();
    }

    /**
     * Crée des préférences par défaut
     */
    private function createDefaultPreferences() {
        $stmt = $this->db->prepare('
            INSERT INTO user_preferences (
                token, auto_save, auto_backup, sound_effects, animations_enabled,
                privacy_level, dashboard_layout, color_scheme, updated_at
            ) VALUES (
                :token, 1, 1, 1, 1, "public", "default", "blue_red", CURRENT_TIMESTAMP
            )
        ');
        
        $stmt->bindValue(':token', $this->token, SQLITE3_TEXT);
        return $stmt->execute();
    }

    /**
     * Récupère les notifications non lues
     */
    public function getUnreadNotifications($limit = 10) {
        $stmt = $this->db->prepare('
            SELECT id, type, title, message, action_url, created_at
            FROM user_notifications
            WHERE token = :token AND is_read = 0
            ORDER BY created_at DESC
            LIMIT :limit
        ');
        
        $stmt->bindValue(':token', $this->token, SQLITE3_TEXT);
        $stmt->bindValue(':limit', $limit, SQLITE3_INTEGER);
        $result = $stmt->execute();
        
        $notifications = [];
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            $notifications[] = $row;
        }
        
        return $notifications;
    }

    /**
     * Marque une notification comme lue
     */
    public function markNotificationAsRead($notificationId) {
        $stmt = $this->db->prepare('
            UPDATE user_notifications
            SET is_read = 1
            WHERE id = :id AND token = :token
        ');
        
        $stmt->bindValue(':id', $notificationId, SQLITE3_INTEGER);
        $stmt->bindValue(':token', $this->token, SQLITE3_TEXT);
        
        return $stmt->execute();
    }

    /**
     * Récupère le résumé du profil pour l'affichage
     */
    public function getProfileSummary() {
        $profile = $this->getProfile();
        $stats = $this->getStats();
        $preferences = $this->getPreferences();
        
        return [
            'display_name' => $profile['display_name'] ?? $profile['pseudo'],
            'avatar' => $profile['avatar_url'] ?? null,
            'bio' => $profile['bio'] ?? '',
            'stats' => [
                'total_wins' => $stats['total_wins'] ?? 0,
                'total_timer_sessions' => $stats['total_timer_sessions'] ?? 0,
                'total_battle_sessions' => $stats['total_battle_sessions'] ?? 0,
                'favorite_module' => $stats['favorite_module'] ?? 'wins'
            ],
            'preferences' => [
                'color_scheme' => $preferences['color_scheme'] ?? 'blue_red',
                'dashboard_layout' => $preferences['dashboard_layout'] ?? 'default',
                'animations_enabled' => $preferences['animations_enabled'] ?? 1
            ],
            'last_login' => $profile['last_login'] ?? null,
            'login_count' => $profile['login_count'] ?? 1
        ];
    }
}

// API Endpoint pour les requêtes AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['action'])) {
    header('Content-Type: application/json');
    
    $token = $_GET['token'] ?? '';
    if (!$token) {
        http_response_code(400);
        echo json_encode(['error' => 'Token manquant']);
        exit;
    }
    
    $profileManager = new ProfileManager($token);
    
    switch ($_GET['action']) {
        case 'get_profile':
            echo json_encode($profileManager->getProfileSummary());
            break;
            
        case 'update_profile':
            $data = json_decode(file_get_contents('php://input'), true);
            $result = $profileManager->updateProfile($data);
            echo json_encode(['success' => $result]);
            break;
            
        case 'update_preferences':
            $data = json_decode(file_get_contents('php://input'), true);
            $result = $profileManager->updatePreferences($data);
            echo json_encode(['success' => $result]);
            break;
            
        case 'log_activity':
            $data = json_decode(file_get_contents('php://input'), true);
            $result = $profileManager->logActivity(
                $data['action'] ?? '',
                $data['module'] ?? null,
                $data['details'] ?? null
            );
            echo json_encode(['success' => $result]);
            break;
        
        // --- WINS tracking ---
        case 'wins_session_start': {
            $db = new SQLite3(__DIR__ . '/../../database/database.sqlite');
            $stmt = $db->prepare('INSERT INTO user_activity_log (token, action, module, details, ip_address, user_agent, created_at) VALUES (:token, :action, :module, :details, :ip, :ua, CURRENT_TIMESTAMP)');
            $stmt->bindValue(':token', $token, SQLITE3_TEXT);
            $stmt->bindValue(':action', 'module_open');
            $stmt->bindValue(':module', 'wins');
            $stmt->bindValue(':details', json_encode(['session_id' => session_id()]));
            $stmt->bindValue(':ip', $_SERVER['REMOTE_ADDR'] ?? '');
            $stmt->bindValue(':ua', $_SERVER['HTTP_USER_AGENT'] ?? '');
            $ok = $stmt->execute();
            echo json_encode(['success' => (bool)$ok]);
            break;
        }
        case 'wins_session_end': {
            $db = new SQLite3(__DIR__ . '/../../database/database.sqlite');
            $data = json_decode(file_get_contents('php://input'), true) ?: [];
            $duration = isset($data['duration']) ? (int)$data['duration'] : 0;
            $stmt = $db->prepare('INSERT INTO user_activity_log (token, action, module, details, ip_address, user_agent, created_at) VALUES (:token, :action, :module, :details, :ip, :ua, CURRENT_TIMESTAMP)');
            $stmt->bindValue(':token', $token, SQLITE3_TEXT);
            $stmt->bindValue(':action', 'module_close');
            $stmt->bindValue(':module', 'wins');
            $stmt->bindValue(':details', json_encode(['duration_seconds' => $duration]));
            $stmt->bindValue(':ip', $_SERVER['REMOTE_ADDR'] ?? '');
            $stmt->bindValue(':ua', $_SERVER['HTTP_USER_AGENT'] ?? '');
            $ok = $stmt->execute();
            if ($duration > 0) {
                $db->exec("INSERT OR REPLACE INTO user_stats (token, total_streaming_time, updated_at) VALUES ('$token', COALESCE((SELECT total_streaming_time FROM user_stats WHERE token = '$token'), 0) + $duration, CURRENT_TIMESTAMP)");
            }
            echo json_encode(['success' => (bool)$ok]);
            break;
        }
        case 'wins_today_summary': {
            $db = new SQLite3(__DIR__ . '/../../database/database.sqlite');
            $stmt = $db->prepare("SELECT SUM(CAST(json_extract(details, '$.duration_seconds') AS INTEGER)) AS total FROM user_activity_log WHERE token = :token AND module = 'wins' AND action = 'module_close' AND date(created_at) = date('now','localtime')");
            $stmt->bindValue(':token', $token, SQLITE3_TEXT);
            $res = $stmt->execute()->fetchArray(SQLITE3_ASSOC);
            echo json_encode(['wins_today_seconds' => (int)($res['total'] ?? 0)]);
            break;
        }
        
        // --- TIMER tracking ---
        case 'timer_session_start': {
            $db = new SQLite3(__DIR__ . '/../../database/database.sqlite');
            $stmt = $db->prepare('INSERT INTO user_activity_log (token, action, module, details, ip_address, user_agent, created_at) VALUES (:token, :action, :module, :details, :ip, :ua, CURRENT_TIMESTAMP)');
            $stmt->bindValue(':token', $token, SQLITE3_TEXT);
            $stmt->bindValue(':action', 'module_open');
            $stmt->bindValue(':module', 'timer');
            $stmt->bindValue(':details', json_encode(['session_id' => session_id()]));
            $stmt->bindValue(':ip', $_SERVER['REMOTE_ADDR'] ?? '');
            $stmt->bindValue(':ua', $_SERVER['HTTP_USER_AGENT'] ?? '');
            $ok = $stmt->execute();
            echo json_encode(['success' => (bool)$ok]);
            break;
        }
        case 'timer_session_end': {
            $db = new SQLite3(__DIR__ . '/../../database/database.sqlite');
            $data = json_decode(file_get_contents('php://input'), true) ?: [];
            $duration = isset($data['duration']) ? (int)$data['duration'] : 0;
            $stmt = $db->prepare('INSERT INTO user_activity_log (token, action, module, details, ip_address, user_agent, created_at) VALUES (:token, :action, :module, :details, :ip, :ua, CURRENT_TIMESTAMP)');
            $stmt->bindValue(':token', $token, SQLITE3_TEXT);
            $stmt->bindValue(':action', 'module_close');
            $stmt->bindValue(':module', 'timer');
            $stmt->bindValue(':details', json_encode(['duration_seconds' => $duration]));
            $stmt->bindValue(':ip', $_SERVER['REMOTE_ADDR'] ?? '');
            $stmt->bindValue(':ua', $_SERVER['HTTP_USER_AGENT'] ?? '');
            $ok = $stmt->execute();
            echo json_encode(['success' => (bool)$ok]);
            break;
        }
        case 'timer_today_summary': {
            $db = new SQLite3(__DIR__ . '/../../database/database.sqlite');
            $stmt = $db->prepare("SELECT SUM(CAST(json_extract(details, '$.duration_seconds') AS INTEGER)) AS total FROM user_activity_log WHERE token = :token AND module = 'timer' AND action = 'module_close' AND date(created_at) = date('now','localtime')");
            $stmt->bindValue(':token', $token, SQLITE3_TEXT);
            $res = $stmt->execute()->fetchArray(SQLITE3_ASSOC);
            echo json_encode(['timer_today_seconds' => (int)($res['total'] ?? 0)]);
            break;
        }
        
        // --- BATTLE tracking ---
        case 'battle_session_start': {
            $db = new SQLite3(__DIR__ . '/../../database/database.sqlite');
            $stmt = $db->prepare('INSERT INTO user_activity_log (token, action, module, details, ip_address, user_agent, created_at) VALUES (:token, :action, :module, :details, :ip, :ua, CURRENT_TIMESTAMP)');
            $stmt->bindValue(':token', $token, SQLITE3_TEXT);
            $stmt->bindValue(':action', 'module_open');
            $stmt->bindValue(':module', 'battle');
            $stmt->bindValue(':details', json_encode(['session_id' => session_id()]));
            $stmt->bindValue(':ip', $_SERVER['REMOTE_ADDR'] ?? '');
            $stmt->bindValue(':ua', $_SERVER['HTTP_USER_AGENT'] ?? '');
            $ok = $stmt->execute();
            echo json_encode(['success' => (bool)$ok]);
            break;
        }
        case 'battle_session_end': {
            $db = new SQLite3(__DIR__ . '/../../database/database.sqlite');
            $data = json_decode(file_get_contents('php://input'), true) ?: [];
            $duration = isset($data['duration']) ? (int)$data['duration'] : 0;
            $stmt = $db->prepare('INSERT INTO user_activity_log (token, action, module, details, ip_address, user_agent, created_at) VALUES (:token, :action, :module, :details, :ip, :ua, CURRENT_TIMESTAMP)');
            $stmt->bindValue(':token', $token, SQLITE3_TEXT);
            $stmt->bindValue(':action', 'module_close');
            $stmt->bindValue(':module', 'battle');
            $stmt->bindValue(':details', json_encode(['duration_seconds' => $duration]));
            $stmt->bindValue(':ip', $_SERVER['REMOTE_ADDR'] ?? '');
            $stmt->bindValue(':ua', $_SERVER['HTTP_USER_AGENT'] ?? '');
            $ok = $stmt->execute();
            echo json_encode(['success' => (bool)$ok]);
            break;
        }
        case 'battle_today_summary': {
            $db = new SQLite3(__DIR__ . '/../../database/database.sqlite');
            $stmt = $db->prepare("SELECT SUM(CAST(json_extract(details, '$.duration_seconds') AS INTEGER)) AS total FROM user_activity_log WHERE token = :token AND module = 'battle' AND action = 'module_close' AND date(created_at) = date('now','localtime')");
            $stmt->bindValue(':token', $token, SQLITE3_TEXT);
            $res = $stmt->execute()->fetchArray(SQLITE3_ASSOC);
            echo json_encode(['battle_today_seconds' => (int)($res['total'] ?? 0)]);
            break;
        }
        
        default:
            http_response_code(400);
            echo json_encode(['error' => 'Action non reconnue']);
            break;
    }
}
?> 