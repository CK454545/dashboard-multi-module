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
// ==================== CONFIGURATION ====================
const TIMER_CONFIG = {
  token: '<?php echo htmlspecialchars($token); ?>',
  userId: '<?php echo htmlspecialchars($user['discord_id'] ?? ''); ?>',
  isRealtime: <?php echo isset($_GET['realtime']) && $_GET['realtime'] === 'true' ? 'true' : 'false'; ?>,
  useSimpleMode: true, // Mode synchronisation simple activé
  pollingInterval: 1000, // Interval de synchronisation en ms
  animateChanges: false // Désactiver les animations
};

// ==================== ÉTAT DU TIMER ====================
let timerState = {
  endTime: null,        // Timestamp UNIX de fin
  duration: 0,          // Durée initiale en secondes
  isRunning: false,
  isPaused: false,
  lastSyncTime: 0,      // Dernier timestamp de sync
  lastEndTime: null     // Pour détecter les changements
};

let interval = null;
let syncInterval = null;
let pendingStyleApply = null;
let lastStyleTimestamp = 0;
let lastForceUpdate = null;

// ==================== INITIALISATION ====================
document.addEventListener('DOMContentLoaded', () => {
  console.log('🚀 Timer initialisé - Mode synchronisation simple');
  
  // Initialiser selon le mode
  if (TIMER_CONFIG.useSimpleMode) {
    initializeSimpleMode();
  } else {
    initializeStandardMode();
  }
  
  // Toujours démarrer l'affichage
  startDisplayUpdate();
  
  // Charger les styles après un délai
  setTimeout(loadStyles, 100);
  setTimeout(loadStyles, 500);
});

// ==================== MODE SYNCHRONISATION SIMPLE ====================
function initializeSimpleMode() {
  console.log('📡 Mode synchronisation simple activé');
  
  // Démarrer la synchronisation simple
  startSimpleSync();
  
  // Première synchronisation immédiate
  fetchSimpleTime();
}

async function fetchSimpleTime() {
  try {
    const el = document.getElementById('timer-display');
  if (el) {
    el.textContent = formatTime(remaining);
  }
}

function forceDisplay() {
  let remaining = 0;
  
  if (timerState.isRunning && timerState.endTime) {
    const now = Math.floor(Date.now() / 1000);
    remaining = Math.max(0, timerState.endTime - now);
  } else {
    remaining = timerState.duration;
  }
  
  const el = document.getElementById('timer-display');
  if (el) {
    el.textContent = formatTime(remaining);
  }
}

function formatTime(totalSeconds) {
  totalSeconds = parseInt(totalSeconds) || 0;
  totalSeconds = Math.max(0, totalSeconds);
  
  const hours = Math.floor(totalSeconds / 3600);
  const minutes = Math.floor((totalSeconds % 3600) / 60);
  const seconds = totalSeconds % 60;
  
  return `${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
}

function initializeForTikTok() {
  if (timerState.duration === 0 && !timerState.isRunning) {
    timerState.duration = 0;
    forceDisplay();
    saveState();
  }
}

function startRealtimeSync() {
  setInterval(syncState, 1000);
  syncState();
}

async function syncState() {
  if (!TIMER_CONFIG.isRealtime) return;
  
  try {
    const response = await fetch(`/api.php?token=${encodeURIComponent(TIMER_CONFIG.token)}&module=timer&action=get`, { 
      cache: 'no-store' 
    });
    const data = await response.json();
    
    if (data.success && data.data) {
      timerState.endTime = data.data.endTime || null;
      timerState.duration = data.data.duration || 0;
      timerState.isRunning = !!data.data.isRunning;
      timerState.isPaused = !!data.data.isPaused;
      
      forceDisplay();
      
      if (timerState.isRunning && !interval) {
        interval = setInterval(updateDisplay, 1000);
      } else if (!timerState.isRunning && interval) {
        clearInterval(interval);
        interval = null;
      }
    }
  } catch (err) {
    console.error('❌ Erreur sync:', err);
  }
}

// ==================== ACTIONS TIMER ====================
async function startTimer(save = true) {
  if (timerState.isRunning) return;
  
  const now = Math.floor(Date.now() / 1000);
  
  // S'assurer qu'on a une durée avant de démarrer
  if (timerState.duration <= 0) {
    console.warn('⚠️ Impossible de démarrer: durée = 0');
    return;
  }
  
  if (timerState.isPaused && timerState.endTime) {
    // Reprendre depuis pause : recalculer endTime basé sur le temps restant
    const remaining = timerState.duration;
    timerState.endTime = now + remaining;
    timerState.isRunning = true;
    timerState.isPaused = false;
  } else {
    // Nouveau timer
    timerState.endTime = now + timerState.duration;
    timerState.isRunning = true;
    timerState.isPaused = false;
  }
  
  if (save) await saveState();
  
  if (interval) clearInterval(interval);
  interval = setInterval(updateDisplay, 1000);
  updateDisplay();
}

async function pauseTimer() {
  if (!timerState.isRunning) return;
  
  // Calculer le temps restant et le sauvegarder dans duration
  if (timerState.endTime) {
    const now = Math.floor(Date.now() / 1000);
    timerState.duration = Math.max(0, timerState.endTime - now);
  }
  
  timerState.isRunning = false;
  timerState.isPaused = true;
  
  if (interval) {
    clearInterval(interval);
    interval = null;
  }
  
  updateDisplay();
  await saveState();
}

async function resetTimer() {
  timerState.endTime = null;
  timerState.duration = 0;
  timerState.isRunning = false;
  timerState.isPaused = false;
  
  if (interval) {
    clearInterval(interval);
    interval = null;
  }
  
  updateDisplay();
  await saveState();
}

async function handleTimeAction(event, action, seconds) {
  event?.preventDefault();
  
  if (action === 'add') {
    // Ajouter du temps
    timerState.duration += seconds;
    
    if (timerState.isRunning && timerState.endTime) {
      // Si le timer tourne, ajuster endTime
      timerState.endTime += seconds;
    }
  } else if (action === 'subtract') {
    // Soustraire du temps
    if (timerState.isRunning && timerState.endTime) {
      // Si le timer tourne, ajuster endTime
      const now = Math.floor(Date.now() / 1000);
      const newEndTime = timerState.endTime - seconds;
      
      if (newEndTime > now) {
        timerState.endTime = newEndTime;
        timerState.duration = Math.max(0, newEndTime - now);
      } else {
        // Si on soustrait trop, arrêter le timer
        await pauseTimer();
        timerState.duration = 0;
      }
    } else {
      // Timer pas démarré, ajuster duration directement
      timerState.duration = Math.max(0, timerState.duration - seconds);
    }
  }
  
  updateDisplay();
  await saveState();
}

async function saveState() {
  if (TIMER_CONFIG.useSimpleMode) {
    // En mode simple, on sauvegarde quand même pour les contrôles
    // mais la synchronisation principale vient de get_time.php
  }
  
  try {
    const stateData = {
      endTime: timerState.endTime,
      duration: timerState.duration,
      isRunning: timerState.isRunning,
      isPaused: timerState.isPaused
    };
    
    await fetch(`/api.php?token=${encodeURIComponent(TIMER_CONFIG.token)}&module=timer&action=set&value=${encodeURIComponent(JSON.stringify(stateData))}`, {
      method: 'GET',
      cache: 'no-store'
    });
  } catch (err) {
    console.error('❌ Erreur sauvegarde:', err);
  }
}

// ==================== FONCTIONS GLOBALES ====================
window.startTimerAction = async function() {
  await startTimer(true);
  updateDisplay();
};

window.pauseTimerAction = async function() {
  await pauseTimer();
  updateDisplay();
};

window.handleTimeAction = handleTimeAction;
window.resetTimer = resetTimer;

window.addManualTime = function() {
  const hours = parseInt(document.getElementById('manual-hours')?.value) || 0;
  const minutes = parseInt(document.getElementById('manual-minutes')?.value) || 0;
  const seconds = parseInt(document.getElementById('manual-seconds')?.value) || 0;
  const totalSeconds = hours * 3600 + minutes * 60 + seconds;
  
  if (totalSeconds > 0) {
    timerState.duration += totalSeconds;
    
    if (timerState.isRunning && timerState.endTime) {
      timerState.endTime += totalSeconds;
    }
    
    updateDisplay();
    saveState();
    
    // Réinitialiser les champs
    document.getElementById('manual-hours').value = '';
    document.getElementById('manual-minutes').value = '';
    document.getElementById('manual-seconds').value = '';
  }
};

window.subtractManualTime = function() {
  const hours = parseInt(document.getElementById('manual-hours')?.value) || 0;
  const minutes = parseInt(document.getElementById('manual-minutes')?.value) || 0;
  const seconds = parseInt(document.getElementById('manual-seconds')?.value) || 0;
  const totalSeconds = hours * 3600 + minutes * 60 + seconds;
  
  if (totalSeconds > 0) {
    if (timerState.isRunning && timerState.endTime) {
      const now = Math.floor(Date.now() / 1000);
      const newEndTime = timerState.endTime - totalSeconds;
      
      if (newEndTime > now) {
        timerState.endTime = newEndTime;
        timerState.duration = Math.max(0, newEndTime - now);
      } else {
        pauseTimer();
        timerState.duration = 0;
      }
    } else {
      timerState.duration = Math.max(0, timerState.duration - totalSeconds);
    }
    
    updateDisplay();
    saveState();
    
    // Réinitialiser les champs
    document.getElementById('manual-hours').value = '';
    document.getElementById('manual-minutes').value = '';
    document.getElementById('manual-seconds').value = '';
  }
};

// ==================== DEBUG & DIAGNOSTICS ====================
window.debugTimer = function() {
  console.group('🔍 Timer Debug');
  console.log('Mode:', TIMER_CONFIG.useSimpleMode ? 'Simple' : 'Standard');
  console.log('État:', timerState);
  console.log('Config:', TIMER_CONFIG);
  console.log('Interval actif:', !!interval);
  console.log('Sync actif:', !!syncInterval);
  console.log('Affichage:', document.getElementById('timer-display')?.textContent);
  console.groupEnd();
};

window.switchToSimpleMode = function() {
  console.log('🔄 Passage en mode Simple...');
  TIMER_CONFIG.useSimpleMode = true;
  if (syncInterval) clearInterval(syncInterval);
  initializeSimpleMode();
};

window.switchToStandardMode = function() {
  console.log('🔄 Passage en mode Standard...');
  TIMER_CONFIG.useSimpleMode = false;
  if (syncInterval) clearInterval(syncInterval);
  initializeStandardMode();
};

console.log('✨ Timer chargé - Mode:', TIMER_CONFIG.useSimpleMode ? 'Simple' : 'Standard');
console.log('💡 Commandes debug: debugTimer(), switchToSimpleMode(), switchToStandardMode()'); response = await fetch(`get_time.php?token=${TIMER_CONFIG.token}`, {
      cache: 'no-store',
      headers: {
        'Accept': 'application/json'
      }
    });
    
    const data = await response.json();
    
    if (data.success) {
      // Mettre à jour l'état
      timerState.endTime = data.end_at;
      timerState.isPaused = data.paused;
      timerState.duration = data.duration || 0;
      timerState.isRunning = !data.paused && data.end_at !== null;
      timerState.lastSyncTime = Date.now();
      
      // Forcer la mise à jour de l'affichage
      updateDisplay();
      
      console.log('✅ Sync:', {
        endTime: data.end_at,
        paused: data.paused,
        remaining: data.end_at ? Math.max(0, data.end_at - Math.floor(Date.now() / 1000)) : 0
      });
    }
  } catch (err) {
    console.error('❌ Erreur sync:', err);
  }
}

function startSimpleSync() {
  // Synchronisation toutes les secondes
  if (syncInterval) clearInterval(syncInterval);
  syncInterval = setInterval(fetchSimpleTime, TIMER_CONFIG.pollingInterval);
}

// ==================== MODE STANDARD (SYSTÈME ORIGINAL) ====================
function initializeStandardMode() {
  console.log('⚙️ Mode Standard activé');
  
  // Initialiser à 00:00:00
  initializeTimer();
  
  // Charger la configuration
  loadConfig().then(() => {
    setTimeout(initializeForTikTok, 1000);
  });
  
  // Démarrer la synchronisation si realtime
  if (TIMER_CONFIG.isRealtime) {
    startRealtimeSync();
  }
}

function initializeTimer() {
  timerState.endTime = null;
  timerState.duration = 0;
  timerState.isRunning = false;
  timerState.isPaused = false;
  forceDisplay();
}

async function loadConfig() {
  try {
    const response = await fetch(`/api.php?token=${encodeURIComponent(TIMER_CONFIG.token)}&module=timer&action=get`, { 
      cache: 'no-store' 
    });
    const data = await response.json();
    
    if (data.success && data.data) {
      console.log('📋 Config chargée:', data.data);
      
      timerState.endTime = data.data.endTime || null;
      timerState.duration = parseInt(data.data.duration) || 0;
      timerState.isRunning = !!data.data.isRunning;
      timerState.isPaused = !!data.data.isPaused;
      
      // Support ancien format
      if (!timerState.duration && data.data.seconds) {
        timerState.duration = parseInt(data.data.seconds) || 0;
      }
      
      forceDisplay();
      
      if (timerState.isRunning && timerState.endTime) {
        if (interval) clearInterval(interval);
        interval = setInterval(updateDisplay, 1000);
      }
    }
  } catch (err) {
    console.error('❌ Erreur config:', err);
  }
}

// ==================== STYLES ====================
async function loadStyles() {
  try {
    const response = await fetch(`/api.php?token=${encodeURIComponent(TIMER_CONFIG.token)}&module=timer-style&action=get`, { 
      cache: 'no-store' 
    });
    const data = await response.json();
    console.log('Styles API response:', data);
    
    if (data.success) {
      let styles = null;
      
      // Gérer différents formats de réponse
      if (data.data && typeof data.data === 'object') {
        styles = data.data;
      } else if (data.style && typeof data.style === 'string') {
        try {
          styles = JSON.parse(data.style);
        } catch (e) {
          console.error('Erreur parsing styles:', e);
        }
      } else if (data.style && typeof data.style === 'object') {
        styles = data.style;
      }
      
      if (styles) {
        console.log('Applying styles:', styles);
        scheduleStyleApply(styles, true);
      }
    }
  } catch (err) {
    console.error('Erreur lors du chargement des styles:', err);
  }
}

function scheduleStyleApply(styles, force = false) {
  const now = Date.now();
  if (!force && now - lastStyleTimestamp < 300) {
    return;
  }
  lastStyleTimestamp = now;
  if (pendingStyleApply) clearTimeout(pendingStyleApply);
  pendingStyleApply = setTimeout(() => {
    applyStyle(styles);
    pendingStyleApply = null;
  }, force ? 0 : 100);
}

function applyStyle(styles) {
  if (!styles || typeof styles !== 'object') {
    console.warn('Styles invalides reçus:', styles);
    return;
  }

  let css = '';

  // 1. Général
  if (styles.general) {
    const general = styles.general;
    if (general.transparent === true || general.transparent === 'true' || general.transparent === 1) {
      css += 'body, html, .widget-container, .display { background: transparent !important; } ';
    } else if (general.background) {
      css += `body, html { background: ${general.background} !important; } `;
    }
    if (general['font-family']) {
      css += `#timer-display { font-family: ${general['font-family']} !important; } `;
    }
    if (general['text-position']) {
      const margin = general['text-margin'] || '0';
      css += generatePositionCSS(general['text-position'], margin);
    }
    if (general['vertical-offset']) {
      const offset = parseInt(general['vertical-offset']) || 0;
      if (offset !== 0) {
        css += `.display { transform: translateY(${offset}px) !important; } `;
      }
    }
  }

  // 2. Options
  if (styles.options) {
    const options = styles.options;
    if (options['hide-controls'] === true || options['hide-controls'] === 'true' || options['hide-controls'] === 1) {
      css += '.timer-action-bar, .timer-action-bar-config, .config-btn-small, .config-button-fixed { display: none !important; } ';
    }
  }

  // 3. Timer
  if (styles.timer) {
    const timer = styles.timer;
    if (timer.color) {
      css += `#timer-display { color: ${timer.color} !important; } `;
    }
    if (timer.size) {
      css += `#timer-display { font-size: ${timer.size}px !important; } `;
    }
    if (timer.stroke) {
      css += `#timer-display { -webkit-text-stroke: 2px ${timer.stroke} !important; text-stroke: 2px ${timer.stroke} !important; } `;
    }
    if (timer.shadow === true || timer.shadow === 'true' || timer.shadow === 1) {
      css += '#timer-display { text-shadow: 3px 3px 6px rgba(0,0,0,0.8) !important; } ';
    }
    if ((timer.showBackground === true || timer.showBackground === 'true' || timer.showBackground === 1) && timer.background) {
      css += `#timer-display { background: ${timer.background} !important; padding: 20px 40px !important; border-radius: 12px !important; } `;
    }
  }

  // Ajuster pour la barre de contrôles si elle est visible
  if (!styles.options || !styles.options['hide-controls']) {
    css += '.display { padding-bottom: 250px !important; } ';
  }

  applyCSS(css);
}

function generatePositionCSS(position, margin) {
  const positions = {
    'top-left': `.display { justify-content: flex-start !important; align-items: flex-start !important; padding: ${margin}px !important; }`,
    'top-center': `.display { justify-content: flex-start !important; align-items: center !important; padding-top: ${margin}px !important; }`,
    'top-right': `.display { justify-content: flex-start !important; align-items: flex-end !important; padding: ${margin}px !important; }`,
    'center-left': `.display { justify-content: center !important; align-items: flex-start !important; padding-left: ${margin}px !important; }`,
    'center-right': `.display { justify-content: flex-end !important; align-items: flex-end !important; padding-right: ${margin}px !important; }`,
    'bottom-left': `.display { justify-content: flex-end !important; align-items: flex-start !important; padding: ${margin}px !important; }`,
    'bottom-center': `.display { justify-content: flex-end !important; align-items: center !important; padding-bottom: ${margin}px !important; }`,
    'bottom-right': `.display { justify-content: flex-end !important; align-items: flex-end !important; padding: ${margin}px !important; }`,
    'center': `.display { justify-content: center !important; align-items: center !important; }`
  };
  return positions[position] || positions['center'];
}

function applyCSS(css) {
  let existing = document.getElementById('dynamic-styles');
  if (existing) existing.remove();
  if (css.trim()) {
    const styleEl = document.createElement('style');
    styleEl.id = 'dynamic-styles';
    styleEl.innerHTML = css;
    document.head.appendChild(styleEl);
  }
}

// BroadcastChannel pour styles en temps réel
if (window.BroadcastChannel) {
  const channel = new BroadcastChannel('timer_styles_channel');
  channel.addEventListener('message', (event) => {
    if (event.data && event.data.type === 'timerStylesUpdate') {
      scheduleStyleApply(event.data.styles, true);
    }
  });
}

// Polling localStorage pour styles
setInterval(() => {
  try {
    const forceUpdate = localStorage.getItem('forceTimerStyleUpdate');
    if (forceUpdate && forceUpdate !== lastForceUpdate) {
      lastForceUpdate = forceUpdate;
      const stylesStr = localStorage.getItem('realtimeTimerStyles');
      if (stylesStr) {
        try {
          const styles = JSON.parse(stylesStr);
          console.log('Force update styles:', styles);
          scheduleStyleApply(styles, true);
        } catch (e) {
          console.error('Error parsing localStorage styles:', e);
        }
      }
      return;
    }
    
    const stylesTimestamp = localStorage.getItem('timerStylesTimestamp');
    if (stylesTimestamp && parseInt(stylesTimestamp) > (window.lastTimerStylesTimestamp || 0)) {
      const stylesStr = localStorage.getItem('realtimeTimerStyles');
      if (stylesStr) {
        try {
          const styles = JSON.parse(stylesStr);
          console.log('Timestamp update styles:', styles);
          scheduleStyleApply(styles);
          window.lastTimerStylesTimestamp = parseInt(stylesTimestamp);
        } catch (e) {
          console.error('Error parsing localStorage styles:', e);
        }
      }
    }
  } catch (err) {
    console.error('LocalStorage polling error:', err);
  }
}, 500);

// ==================== AFFICHAGE ====================
function startDisplayUpdate() {
  // Mise à jour de l'affichage chaque seconde
  setInterval(updateDisplay, 1000);
  
  // Forcer une mise à jour immédiate
  updateDisplay();
  
  // Assurance de mise à jour pour TikTok
  let forceUpdateCounter = 0;
  setInterval(() => {
    forceUpdateCounter++;
    if (forceUpdateCounter % 5 === 0) {
      forceDisplay();
    }
  }, 200);
}

function updateDisplay() {
  let remaining = 0;
  
  if (timerState.isRunning && timerState.endTime) {
    const now = Math.floor(Date.now() / 1000);
    remaining = Math.max(0, timerState.endTime - now);
    
    if (remaining === 0 && !TIMER_CONFIG.useSimpleMode) {
      pauseTimer();
      return;
    }
  } else {
    remaining = timerState.duration;
  }
  
  const
</script>
</body>
</html> 