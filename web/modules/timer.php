<?php
// Inclure la validation des tokens
require_once __DIR__ . '/validate_token.php';

// Valider le token et récupérer les infos utilisateur
$user = requireValidToken();

$control = isset($_GET['control']) && $_GET['control'] === 'true';
$token = $_GET['token'] ?? '';

// Vérifier l'accès au module timer (en cours de réparation)
checkTimerAccess($token);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Timer - MyFull Agency</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Luckiest+Guy&family=Orbitron:wght@400;700;900&family=Press+Start+2P&family=Russo+One&family=Audiowide&family=Bungee&family=Creepster&family=Nosifer&family=Walter+Turncoat&family=Fredoka+One&family=Cinzel:wght@400;600&family=Playfair+Display:wght@400;700&family=Dancing+Script:wght@400;700&family=Black+Ops+One&family=Faster+One&family=Jolly+Lodger&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* ==================== CSS VARIABLES ==================== */
        :root {
            /* Colors */
            --primary-color: #6366f1;
            --primary-hover: #5855eb;
            --secondary-color: #10b981;
            --accent-color: #f59e0b;
            --danger-color: #ef4444;
            
            /* Backgrounds */
            --bg-primary: #0f172a;
            --bg-secondary: #1e293b;
            --bg-card: rgba(30, 41, 59, 0.8);
            --bg-glass: rgba(255, 255, 255, 0.05);
            
            /* Text Colors */
            --text-primary: #f8fafc;
            --text-secondary: #cbd5e1;
            --text-muted: #94a3b8;
            
            /* Borders */
            --border-color: rgba(148, 163, 184, 0.1);
            --border-hover: rgba(148, 163, 184, 0.2);
            
            /* Shadows */
            --shadow-xl: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
            
            /* Spacing */
            --spacing-xs: 0.25rem;
            --spacing-sm: 0.5rem;
            --spacing-md: 1rem;
            --spacing-lg: 1.5rem;
            --spacing-xl: 2rem;
            --spacing-2xl: 3rem;
            
            /* Border Radius */
            --radius-md: 0.5rem;
            --radius-lg: 0.75rem;
            --radius-xl: 1rem;
            
            /* Transitions */
            --transition-fast: 0.15s ease;
            --transition-normal: 0.3s ease;
        }

        /* ==================== GLOBAL STYLES ==================== */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html, body {
            height: 100%;
            overflow: hidden;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: linear-gradient(135deg, var(--bg-primary) 0%, var(--bg-secondary) 100%);
            color: var(--text-primary);
            line-height: 1.6;
            height: 100vh;
            padding: var(--spacing-lg);
            /* Mobile tweak: Better mobile handling */
            overflow-x: hidden;
            min-height: 100vh;
        }

        /* ==================== MAIN CONTAINER ==================== */
        .widget-container {
            max-width: 1200px;
            margin: 0 auto;
            height: calc(100vh - 2 * var(--spacing-lg));
            display: flex;
            flex-direction: column;
            overflow: hidden;
            /* Mobile tweak: Better mobile container */
            width: 100%;
            min-height: 100vh;
        }

        /* ==================== REALTIME INDICATOR ==================== */
        .realtime-indicator {
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
            padding: var(--spacing-sm) var(--spacing-md);
            text-align: center;
            font-size: 0.875rem;
            font-weight: 500;
            border-radius: var(--radius-md);
            margin-bottom: var(--spacing-md);
            animation: pulse 2s ease-in-out infinite;
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
        }

        .realtime-indicator i {
            margin-right: var(--spacing-sm);
            animation: spin 2s linear infinite;
        }

        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        /* ==================== DISPLAY SECTION ==================== */
        .display {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: var(--spacing-lg);
            padding-bottom: 150px; /* Espace par défaut pour éviter la collision avec les contrôles */
            /* Mobile tweak: Better mobile display */
            width: 100%;
            min-height: 100vh;
            overflow: hidden;
        }

        /* ==================== CONFIG BUTTON FIXED ==================== */
        .config-button-fixed {
            position: fixed;
            bottom: 20px;
            left: 20px;
            z-index: 1000;
        }

        .config-btn-small {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: var(--bg-card);
            backdrop-filter: blur(20px);
            border: 2px solid var(--accent-color);
            color: var(--accent-color);
            font-size: 1.4rem;
            cursor: pointer;
            transition: all var(--transition-fast);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3);
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            position: relative;
            overflow: hidden;
        }

        .config-btn-small::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, var(--accent-color), var(--secondary-color));
            opacity: 0;
            transition: opacity var(--transition-fast);
            z-index: -1;
        }

        .config-btn-small:hover {
            transform: translateY(-3px) scale(1.05);
            box-shadow: 0 12px 35px rgba(245, 158, 11, 0.4);
            color: white;
        }

        .config-btn-small:hover::before {
            opacity: 1;
        }

        /* ==================== TEXT CONTAINER ==================== */
        .text-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            /* Mobile tweak: Better mobile text container */
            width: 100%;
            max-width: 100vw;
            padding: 0 10px;
            overflow: hidden;
        }

        #timer-display {
            font-size: 6rem;
            font-weight: 800;
            color: white;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin: 0;
            font-variant-numeric: tabular-nums;
            /* Mobile tweak: Better responsive behavior */
            word-wrap: break-word;
            word-break: break-word;
            overflow-wrap: break-word;
        }

        /* ==================== TIMER ACTION BAR ==================== */
        .timer-action-bar {
            position: fixed;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            background: rgba(15, 23, 42, 0.95);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(148, 163, 184, 0.1);
            border-radius: 8px;
            padding: 8px 12px;
            display: flex;
            align-items: center;
            gap: 8px;
            z-index: 1000;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
            min-width: 400px;
            max-width: 90vw;
            flex-wrap: wrap;
            justify-content: center;
        }

        .timer-action-bar-sections {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
            justify-content: center;
        }

        .timer-action-section {
            display: flex;
            align-items: center;
            gap: 6px;
            flex-wrap: wrap;
        }

        .timer-action-section-header {
            font-size: 10px;
            font-weight: 600;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            white-space: nowrap;
            margin-right: 4px;
        }

        .timer-action-buttons {
            display: flex;
            align-items: center;
            gap: 4px;
            flex-wrap: wrap;
        }

        /* ==================== ULTRA COMPACT BUTTONS ==================== */
        .timer-action-btn {
            padding: 4px 8px;
            border: none;
            border-radius: 4px;
            font-size: 10px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.15s ease;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            height: 24px;
            min-width: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
            background: transparent;
            color: #ffffff;
        }

        .timer-action-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: currentColor;
            opacity: 0;
            transition: opacity 0.15s ease;
            z-index: -1;
        }

        .timer-action-btn:hover::before {
            opacity: 0.1;
        }

        .timer-action-btn.add {
            background: rgba(16, 185, 129, 0.15);
            color: #10b981;
            border: 1px solid rgba(16, 185, 129, 0.3);
        }

        .timer-action-btn.add:hover {
            background: rgba(16, 185, 129, 0.25);
            border-color: #10b981;
            transform: translateY(-1px);
        }

        .timer-action-btn.subtract {
            background: rgba(239, 68, 68, 0.15);
            color: #ef4444;
            border: 1px solid rgba(239, 68, 68, 0.3);
        }

        .timer-action-btn.subtract:hover {
            background: rgba(239, 68, 68, 0.25);
            border-color: #ef4444;
            transform: translateY(-1px);
        }

        .timer-action-btn.reset {
            background: rgba(245, 158, 11, 0.15);
            color: #f59e0b;
            border: 1px solid rgba(245, 158, 11, 0.3);
        }

        .timer-action-btn.reset:hover {
            background: rgba(245, 158, 11, 0.25);
            border-color: #f59e0b;
            transform: translateY(-1px);
        }

        .timer-action-btn.primary {
            background: rgba(99, 102, 241, 0.15);
            color: #6366f1;
            border: 1px solid rgba(99, 102, 241, 0.3);
        }

        .timer-action-btn.primary:hover {
            background: rgba(99, 102, 241, 0.25);
            border-color: #6366f1;
            transform: translateY(-1px);
        }

        .timer-action-btn.warning {
            background: rgba(245, 158, 11, 0.15);
            color: #f59e0b;
            border: 1px solid rgba(245, 158, 11, 0.3);
        }

        .timer-action-btn.warning:hover {
            background: rgba(245, 158, 11, 0.25);
            border-color: #f59e0b;
            transform: translateY(-1px);
        }

        .timer-action-btn.large {
            height: 28px;
            min-width: 40px;
            font-size: 11px;
        }

        .timer-action-btn:disabled {
            opacity: 0.4;
            cursor: not-allowed;
            transform: none !important;
        }

        /* ==================== ULTRA COMPACT MANUAL INPUTS ==================== */
        .timer-manual-inputs {
            display: flex;
            align-items: center;
            gap: 4px;
            flex-wrap: wrap;
        }

        .manual-input-group {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 2px;
        }

        .manual-input-group label {
            font-size: 8px;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .manual-input {
            width: 32px;
            height: 20px;
            padding: 2px 4px;
            border: 1px solid rgba(148, 163, 184, 0.2);
            border-radius: 3px;
            background: rgba(15, 23, 42, 0.8);
            color: #ffffff;
            font-size: 9px;
            text-align: center;
            font-family: 'Inter', sans-serif;
        }

        .manual-input:focus {
            outline: none;
            border-color: #6366f1;
            background: rgba(15, 23, 42, 0.9);
        }

        /* ==================== RESPONSIVE DESIGN ==================== */
        @media (max-width: 1024px) {
            .timer-action-bar {
                min-width: 350px;
                padding: 6px 10px;
            }
            
            .timer-action-bar-sections {
                gap: 10px;
            }
        }

        @media (max-width: 768px) {
            /* Mobile tweak: Better mobile body padding */
            body {
                padding: 10px;
            }

            /* Mobile tweak: Better mobile container */
            .widget-container {
                height: calc(100vh - 20px);
                padding: 0;
            }

            /* Mobile tweak: Better mobile display */
            .display {
                padding: 10px;
                padding-bottom: 120px;
                justify-content: center;
                align-items: center;
            }

            /* Mobile tweak: Better mobile text container */
            .text-container {
                padding: 0 5px;
                max-width: 100%;
            }

            .timer-action-bar {
                bottom: 10px;
                padding: 6px 8px;
                gap: 6px;
                min-width: 320px;
                max-width: 95vw;
            }

            .timer-action-bar-sections {
                gap: 8px;
                flex-direction: column;
            }

            .timer-action-section {
                gap: 4px;
                justify-content: center;
            }

            .timer-action-buttons {
                gap: 3px;
                justify-content: center;
                flex-wrap: wrap;
            }

            .timer-action-btn {
                padding: 3px 6px;
                font-size: 9px;
                height: 22px;
                min-width: 28px;
            }

            .timer-action-section-header {
                font-size: 9px;
                margin-right: 3px;
            }

            .manual-input {
                width: 28px;
                height: 18px;
                font-size: 8px;
            }

            /* Mobile tweak: Timer display responsive */
            #timer-display {
                font-size: 4rem; /* Mobile tweak: smaller font size */
                letter-spacing: 1px; /* Mobile tweak: reduced letter spacing */
            }
        }

        @media (max-width: 480px) {
            /* Mobile tweak: Better mobile body padding */
            body {
                padding: 5px;
            }

            /* Mobile tweak: Better mobile container */
            .widget-container {
                height: calc(100vh - 10px);
                padding: 0;
            }

            /* Mobile tweak: Better mobile display */
            .display {
                padding: 5px;
                padding-bottom: 100px;
                justify-content: center;
                align-items: center;
            }

            /* Mobile tweak: Better mobile text container */
            .text-container {
                padding: 0 2px;
                max-width: 100%;
            }

            .timer-action-bar {
                min-width: 280px;
                padding: 4px 6px;
                bottom: 5px;
            }
            
            .timer-action-bar-sections {
                gap: 6px;
            }
            
            .timer-action-section {
                gap: 3px;
            }
            
            .timer-action-buttons {
                gap: 2px;
                justify-content: center;
            }
            
            .timer-action-btn {
                padding: 2px 4px;
                font-size: 8px;
                height: 20px;
                min-width: 24px;
            }
            
            .timer-action-section-header {
                font-size: 8px;
                margin-right: 2px;
            }
            
            .timer-action-btn.large {
                height: 24px;
                min-width: 36px;
                font-size: 10px;
            }
            
            .manual-input {
                width: 24px;
                height: 16px;
                font-size: 7px;
            }

            /* Mobile tweak: Timer display responsive for small screens */
            #timer-display {
                font-size: 3rem; /* Mobile tweak: much smaller font size */
                letter-spacing: 0.5px; /* Mobile tweak: minimal letter spacing */
            }
        }

        @media (max-width: 360px) {
            /* Mobile tweak: Better mobile body padding */
            body {
                padding: 2px;
            }

            /* Mobile tweak: Better mobile container */
            .widget-container {
                height: calc(100vh - 4px);
                padding: 0;
            }

            /* Mobile tweak: Better mobile display */
            .display {
                padding: 2px;
                padding-bottom: 80px;
                justify-content: center;
                align-items: center;
            }

            /* Mobile tweak: Better mobile text container */
            .text-container {
                padding: 0 1px;
                max-width: 100%;
            }

            .timer-action-bar {
                min-width: 260px;
                padding: 3px 4px;
            }
            
            .timer-action-btn {
                padding: 1px 3px;
                font-size: 7px;
                height: 18px;
                min-width: 20px;
            }
            
            .timer-action-section-header {
                font-size: 7px;
                margin-right: 1px;
            }
            
            .manual-input {
                width: 20px;
                height: 14px;
                font-size: 6px;
            }

            /* Mobile tweak: Timer display responsive for ultra small screens */
            #timer-display {
                font-size: 2.5rem; /* Mobile tweak: ultra small font size */
                letter-spacing: 0.25px; /* Mobile tweak: minimal letter spacing */
            }
        }


    </style>
    <style id="dynamic-styles"></style>
</head>
<body class="style-default size-medium">
    <div class="widget-container">
        <?php if (isset($_GET['realtime']) && $_GET['realtime'] === 'true'): ?>
        <div class="realtime-indicator">
            <i class="fas fa-sync-alt"></i> Mode temps réel activé
        </div>
        <?php endif; ?>

        <div class="display">
            <div class="text-container">
                <h1 id="timer-display">00:00:00</h1>
            </div>
        </div>

        <?php if (!$control): ?>
        <div class="config-button-fixed">
            <a href="/?module=timer-config&token=<?php echo htmlspecialchars($token); ?>" class="config-btn-small">
                <i class="fas fa-cog"></i>
            </a>
        </div>
        <?php endif; ?>

        <?php if ($control): ?>
        <div class="timer-action-bar">
            <div class="timer-action-bar-header">
                <div class="timer-action-bar-title">
                    <i class="fas fa-clock"></i>
                    Module Timer
                </div>
                <a href="/modules/timer-config.php?token=<?php echo htmlspecialchars($token); ?>" class="timer-action-bar-config">
                    <i class="fas fa-cog"></i>
                </a>
            </div>

            <div class="timer-action-bar-sections">
                <div class="timer-action-section">
                    <div class="timer-action-section-header">
                        <i class="fas fa-clock"></i> Ajuster le temps
                    </div>
                    <div class="timer-action-buttons">
                        <button class="timer-action-btn subtract" onclick="handleTimeAction(event, 'subtract', 300)">-5min</button>
                        <button class="timer-action-btn subtract" onclick="handleTimeAction(event, 'subtract', 60)">-1min</button>
                        <button class="timer-action-btn subtract" onclick="handleTimeAction(event, 'subtract', 30)">-30s</button>
                        <button class="timer-action-btn subtract" onclick="handleTimeAction(event, 'subtract', 10)">-10s</button>
                        <button class="timer-action-btn reset" onclick="resetTimer()">RESET</button>
                        <button class="timer-action-btn add" onclick="handleTimeAction(event, 'add', 10)">+10s</button>
                        <button class="timer-action-btn add" onclick="handleTimeAction(event, 'add', 30)">+30s</button>
                        <button class="timer-action-btn add" onclick="handleTimeAction(event, 'add', 60)">+1min</button>
                        <button class="timer-action-btn add" onclick="handleTimeAction(event, 'add', 300)">+5min</button>
                    </div>
                </div>

                <div class="timer-action-section">
                    <div class="timer-action-section-header">
                        <i class="fas fa-edit"></i> Saisie manuelle
                    </div>
                    <div class="timer-manual-inputs">
                        <div class="manual-input-group">
                            <label for="manual-hours">Heures</label>
                            <input type="number" id="manual-hours" min="0" max="99" placeholder="0" class="manual-input">
                        </div>
                        <div class="manual-input-group">
                            <label for="manual-minutes">Minutes</label>
                            <input type="number" id="manual-minutes" min="0" max="59" placeholder="0" class="manual-input">
                        </div>
                        <div class="manual-input-group">
                            <label for="manual-seconds">Secondes</label>
                            <input type="number" id="manual-seconds" min="0" max="59" placeholder="0" class="manual-input">
                        </div>
                    </div>
                    <div class="timer-action-buttons manual">
                        <button class="timer-action-btn add" onclick="addManualTime()">
                            <i class="fas fa-plus"></i> Ajouter
                        </button>
                        <button class="timer-action-btn subtract" onclick="subtractManualTime()">
                            <i class="fas fa-minus"></i> Soustraire
                        </button>
                    </div>
                </div>

                <div class="timer-action-section">
                    <div class="timer-action-section-header">
                        <i class="fas fa-play-circle"></i> Contrôles
                    </div>
                    <div class="timer-action-buttons controls">
                        <button class="timer-action-btn primary large" data-action="start" id="startBtn" onclick="startTimerAction()">
                            <i class="fas fa-play"></i> Démarrer
                        </button>
                        <button class="timer-action-btn warning large" data-action="pause" id="pauseBtn" onclick="pauseTimerAction()">
                            <i class="fas fa-pause"></i> Pause
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>

<script>
// Variables globales du timer
let timerState = {
    endTime: null,
    paused: true,
    remaining: 0,
    duration: 0
};

let syncInterval;
let isRealtimeMode = false;
let lastSyncTime = 0;

// Configuration
const SYNC_INTERVAL = 1000; // 1 seconde
const REALTIME_SYNC_INTERVAL = 500; // 500ms pour le mode temps réel

// Initialisation du timer
document.addEventListener('DOMContentLoaded', function() {
            console.log('✨ Timer chargé - Mode: Simple');
            console.log('💡 Commandes debug: debugTimer(), switchToSimpleMode(), switchToStandardMode()');
            
            initializeTimer();
            startSync();
            // Charger et appliquer les styles (dont fond transparent)
            try { loadTimerStyles(); } catch (e) { console.warn('Styles timer non chargés:', e); }
        });

// Fonction d'initialisation du timer
function initializeTimer() {
    console.log('🚀 Timer initialisé - Mode synchronisation simple');
    
    // Charger l'état initial depuis l'API
    loadTimerState();
    
    // Démarrer la synchronisation
    startSync();
}

// Fonction de chargement de l'état du timer
function loadTimerState() {
    const token = getTokenFromUrl();
    if (!token) {
        console.error('❌ Token manquant dans l\'URL');
        return;
    }
    
    fetch(`/modules/get_time.php?token=${token}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                console.log('📡 Données reçues de get_time.php:', data);
                timerState = {
                    endTime: data.end_at,
                    paused: data.paused,
                    remaining: data.duration || 0,
                    duration: data.duration || 0
                };
                updateDisplay();
                console.log('✅ Sync:', timerState);
            } else {
                console.error('❌ Erreur lors du chargement:', data.error);
            }
        })
        .catch(error => {
            console.error('❌ Erreur réseau:', error);
        });
}

// Fonction de synchronisation
function startSync() {
    console.log('📡 Mode synchronisation simple activé');
    
    // Arrêter l'intervalle existant s'il y en a un
    if (syncInterval) {
        clearInterval(syncInterval);
    }
    
    // Démarrer la synchronisation
    syncInterval = setInterval(() => {
        const now = Date.now();
        if (now - lastSyncTime >= SYNC_INTERVAL) {
            loadTimerState();
            lastSyncTime = now;
        }
    }, SYNC_INTERVAL);
}

// Fonction de mise à jour de l'affichage
function updateDisplay() {
    const display = document.getElementById('timer-display');
    if (!display) return;
    
    let timeToShow = 0;
    
    if (timerState.endTime && !timerState.paused) {
        // Timer en cours
        const now = Math.floor(Date.now() / 1000);
        timeToShow = Math.max(0, timerState.endTime - now);
    } else if (timerState.paused) {
        // Timer en pause - utiliser duration directement
        timeToShow = timerState.duration;
    }
    
    display.textContent = formatTime(timeToShow);
    console.log('🔄 Affichage mis à jour:', formatTime(timeToShow), 'État:', timerState);
}

// Fonction de formatage du temps
function formatTime(seconds) {
    const hours = Math.floor(seconds / 3600);
    const minutes = Math.floor((seconds % 3600) / 60);
    const secs = seconds % 60;
    
    return `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
}

// Fonction pour récupérer le token depuis l'URL
function getTokenFromUrl() {
    const urlParams = new URLSearchParams(window.location.search);
    return urlParams.get('token');
}

// Fonction de gestion des actions de temps
function handleTimeAction(event, action, seconds) {
    event.preventDefault();
    
    const token = getTokenFromUrl();
    if (!token) {
        console.error('❌ Token manquant');
        return;
    }
    
    console.log(`🔄 Action: ${action} ${seconds} secondes`);
    
    // Appeler l'API pour modifier le timer
    fetch(`/api.php?token=${token}&module=timer&action=${action}&value=${seconds}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                console.log('✅ Action réussie:', data);
                // Recharger l'état après l'action
                setTimeout(() => {
                    loadTimerState();
                }, 100);
            } else {
                console.error('❌ Erreur lors de l\'action:', data.error);
            }
        })
        .catch(error => {
            console.error('❌ Erreur réseau:', error);
        });
}

// Fonction de réinitialisation du timer
function resetTimer() {
    handleTimeAction(new Event('click'), 'reset', 0);
}

// Fonction de démarrage du timer
function startTimerAction() {
    const token = getTokenFromUrl();
    if (!token) {
        console.error('❌ Token manquant');
        return;
    }
    
    if (timerState.remaining <= 0) {
        console.warn('⚠️ Impossible de démarrer: durée = 0');
        return;
    }
    
    console.log('▶️ Démarrage du timer');
    
    fetch(`/api.php?token=${token}&module=timer&action=start`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                console.log('✅ Timer démarré:', data);
                setTimeout(() => {
                    loadTimerState();
                }, 100);
            } else {
                console.error('❌ Erreur lors du démarrage:', data.error);
            }
        })
        .catch(error => {
            console.error('❌ Erreur réseau:', error);
        });
}

// Fonction de pause du timer
function pauseTimerAction() {
    const token = getTokenFromUrl();
    if (!token) {
        console.error('❌ Token manquant');
        return;
    }
    
    console.log('⏸️ Pause du timer');
    
    fetch(`/api.php?token=${token}&module=timer&action=pause`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                console.log('✅ Timer en pause:', data);
                setTimeout(() => {
                    loadTimerState();
                }, 100);
            } else {
                console.error('❌ Erreur lors de la pause:', data.error);
            }
        })
        .catch(error => {
            console.error('❌ Erreur réseau:', error);
        });
}

// Fonction d'ajout de temps manuel
function addManualTime() {
    const hours = parseInt(document.getElementById('manual-hours').value) || 0;
    const minutes = parseInt(document.getElementById('manual-minutes').value) || 0;
    const seconds = parseInt(document.getElementById('manual-seconds').value) || 0;
    
    const totalSeconds = hours * 3600 + minutes * 60 + seconds;
    
    if (totalSeconds > 0) {
        handleTimeAction(new Event('click'), 'add', totalSeconds);
        
        // Vider les champs après l'ajout
        document.getElementById('manual-hours').value = '';
        document.getElementById('manual-minutes').value = '';
        document.getElementById('manual-seconds').value = '';
    }
}

// Fonction de soustraction de temps manuel
function subtractManualTime() {
    const hours = parseInt(document.getElementById('manual-hours').value) || 0;
    const minutes = parseInt(document.getElementById('manual-minutes').value) || 0;
    const seconds = parseInt(document.getElementById('manual-seconds').value) || 0;
    
    const totalSeconds = hours * 3600 + minutes * 60 + seconds;
    
    if (totalSeconds > 0) {
        handleTimeAction(new Event('click'), 'subtract', totalSeconds);
        
        // Vider les champs après la soustraction
        document.getElementById('manual-hours').value = '';
        document.getElementById('manual-minutes').value = '';
        document.getElementById('manual-seconds').value = '';
    }
}

// Fonctions de debug (pour la console)
function debugTimer() {
    console.log('🔍 État actuel du timer:', timerState);
    console.log('🔍 URL actuelle:', window.location.href);
    console.log('🔍 Token:', getTokenFromUrl());
}

function switchToSimpleMode() {
    console.log('🔄 Passage en mode simple');
    isRealtimeMode = false;
    startSync();
}

function switchToStandardMode() {
    console.log('🔄 Passage en mode standard');
    isRealtimeMode = true;
    if (syncInterval) {
        clearInterval(syncInterval);
    }
    syncInterval = setInterval(() => {
        const now = Date.now();
        if (now - lastSyncTime >= REALTIME_SYNC_INTERVAL) {
            loadTimerState();
            lastSyncTime = now;
        }
    }, REALTIME_SYNC_INTERVAL);
}

// Nettoyage à la fermeture de la page
window.addEventListener('beforeunload', function() {
    if (syncInterval) {
        clearInterval(syncInterval);
    }
});
</script>
<script>
// Styles dynamiques du Timer (transparent, couleurs, taille, etc.)
let timerStyles = {};

function applyTimerStyles(styles){
    if (!styles || typeof styles !== 'object') return;
    let css = '';

    // Fond transparent et arrières-plans
    if (styles.general && (styles.general.transparent === true || styles.general.transparent === 'true' || styles.general.transparent === 1)) {
        css += 'html, body { background: transparent !important; } ';
        css += '.widget-container, .display { background: transparent !important; } ';
    } else if (styles.general && styles.general.background) {
        css += `html, body { background: ${styles.general.background} !important; } `;
    }

    // Police
    if (styles.general && styles.general['font-family']) {
        css += `#timer-display { font-family: ${styles.general['font-family']} !important; } `;
    }

    // Styles du timer
    if (styles.timer) {
        if (styles.timer.color) css += `#timer-display { color: ${styles.timer.color} !important; } `;
        if (styles.timer.size) css += `#timer-display { font-size: ${styles.timer.size}px !important; } `;
        if (styles.timer.stroke) css += `#timer-display { -webkit-text-stroke: 2px ${styles.timer.stroke} !important; text-stroke: 2px ${styles.timer.stroke} !important; } `;
        if (styles.timer.shadow === true || styles.timer.shadow === 'true' || styles.timer.shadow === 1) {
            css += '#timer-display { text-shadow: 3px 3px 6px rgba(0,0,0,0.8) !important; } ';
        } else {
            css += '#timer-display { text-shadow: none !important; } ';
        }
    }

    // Position du texte
    if (styles.general && styles.general['text-position']) {
        const margin = styles.general['text-margin'] || '0';
        const map = {
            'top-left': `.display { justify-content: flex-start; align-items: flex-start; padding-top: ${margin}px; padding-left: ${margin}px; }`,
            'top-center': `.display { justify-content: flex-start; align-items: center; padding-top: ${margin}px; }`,
            'top-right': `.display { justify-content: flex-start; align-items: flex-end; padding-top: ${margin}px; padding-right: ${margin}px; }`,
            'center-left': `.display { justify-content: center; align-items: flex-start; padding-left: ${margin}px; }`,
            'center-right': `.display { justify-content: center; align-items: flex-end; padding-right: ${margin}px; }`,
            'bottom-left': `.display { justify-content: flex-end; align-items: flex-start; padding-bottom: ${margin}px; padding-left: ${margin}px; }`,
            'bottom-center': `.display { justify-content: flex-end; align-items: center; padding-bottom: ${margin}px; }`,
            'bottom-right': `.display { justify-content: flex-end; align-items: flex-end; padding-bottom: ${margin}px; padding-right: ${margin}px; }`,
            'center': `.display { justify-content: center; align-items: center; }`
        };
        css += map[styles.general['text-position']] || '';
    }

    const styleEl = document.getElementById('dynamic-styles');
    if (styleEl) styleEl.innerHTML = css;
}

async function loadTimerStyles(){
    const token = getTokenFromUrl();
    if (!token) return;
    try {
        const res = await fetch(`/api.php?token=${token}&module=timer-style&action=get`);
        const data = await res.json();
        if (data.success && data.data) {
            timerStyles = data.data;
            applyTimerStyles(timerStyles);
        }
    } catch (e) {
        console.warn('Erreur chargement styles timer:', e);
    }
}

// Mise à jour temps réel via BroadcastChannel (si dispo)
try {
    const ch = new BroadcastChannel('timer_styles_channel');
    ch.onmessage = (event) => {
        if (event.data && event.data.type === 'timerStylesUpdate' && event.data.styles) {
            applyTimerStyles(event.data.styles);
        }
    };
} catch(e) {}

// Fallback via localStorage
window.addEventListener('storage', () => {
    try {
        const s = localStorage.getItem('realtimeTimerStyles');
        if (s) applyTimerStyles(JSON.parse(s));
    } catch(e) {}
});
</script>
</body>
</html> 