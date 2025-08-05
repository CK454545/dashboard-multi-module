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
  // ==================== LOGIQUE TIMER ====================
  // Cette logique fonctionne parfaitement sur TikTok Live Studio
  
  const token = '<?php echo htmlspecialchars($token); ?>';
  const userId = '<?php echo htmlspecialchars($user['discord_id'] ?? ''); ?>';
  const isRealtime = <?php echo isset($_GET['realtime']) && $_GET['realtime'] === 'true' ? 'true' : 'false'; ?>;

  // Variables Timer
  let timerState = {
    endTime: null,        // Timestamp UNIX de fin
    duration: 0,          // Durée initiale en secondes
    isRunning: false,
    isPaused: false
  };

  let interval = null;
  
  // Initialiser le timer à 00:00:00
  function initializeTimer() {
    timerState.endTime = null;
    timerState.duration = 0;
    timerState.isRunning = false;
    timerState.isPaused = false;
    forceDisplay();
  }

  // Debounce pour styles
  let pendingStyleApply = null;
  let lastStyleTimestamp = 0;
  let lastForceUpdate = null;

  // Chargement initial
  document.addEventListener('DOMContentLoaded', () => {
    // Initialiser le timer à 00:00:00
    initializeTimer();
    
    loadConfig().then(() => {
          // Initialiser le timer après le chargement
    setTimeout(initializeForTikTok, 1000);
    });
    setTimeout(loadStyles, 100);
    setTimeout(loadStyles, 500);
    startRealtimeSyncs();
    
    // Toujours mettre à jour l'affichage chaque seconde
    setInterval(updateDisplay, 1000);
    
    // Forcer une mise à jour immédiate
    forceDisplay();
    
    // S'assurer que le timer se met à jour même dans TikTok
    let forceUpdateCounter = 0;
    setInterval(() => {
        forceUpdateCounter++;
        if (forceUpdateCounter % 5 === 0) {
            // Toutes les 5 secondes, forcer une mise à jour complète
            forceDisplay(); // Forcer l'affichage même en pause
        }
    }, 200); // Vérifier 5 fois par seconde
    
    // Debug : vérifier que les fonctions sont bien exposées
    console.log('Timer functions loaded:', {
        startTimerAction: typeof window.startTimerAction,
        pauseTimerAction: typeof window.pauseTimerAction,
        handleTimeAction: typeof window.handleTimeAction,
        resetTimer: typeof window.resetTimer
    });
  });

  // ---------- CONFIGURATION & STYLES ----------

  async function loadConfig() {
    try {
      const response = await fetch(`/api.php?token=${encodeURIComponent(token)}&module=timer&action=get`, { cache: 'no-store' });
      const data = await response.json();
      if (data.success && data.data) {
        console.log('Timer config loaded:', data.data);
        
        // Charger les données avec valeurs par défaut
        timerState.endTime = data.data.endTime || null;
        timerState.duration = parseInt(data.data.duration) || 0;
        timerState.isRunning = !!data.data.isRunning;
        timerState.isPaused = !!data.data.isPaused;
        
        // Si pas de duration mais ancien format avec seconds
        if (!timerState.duration && data.data.seconds) {
            timerState.duration = parseInt(data.data.seconds) || 0;
        }
        
        forceDisplay(); // Forcer l'affichage initial
        
        if (timerState.isRunning && timerState.endTime) {
            // Redémarrer l'interval pour l'affichage
            if (interval) clearInterval(interval);
            interval = setInterval(updateDisplay, 1000);
        }
      }
    } catch (err) {
      console.error('Erreur lors du chargement de la config:', err);
    }
  }

  async function loadStyles() {
    try {
      const response = await fetch(`/api.php?token=${encodeURIComponent(token)}&module=timer-style&action=get`, { cache: 'no-store' });
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

  async function debugTimerState() {
    console.group('🔍 Timer Debug Info');
    console.log('Current state:', timerState);
    console.log('Token:', token);
    console.log('Is Realtime:', isRealtime);
    
    // Tester l'API
    try {
        const response = await fetch(`/api.php?token=${encodeURIComponent(token)}&module=timer&action=get`, { cache: 'no-store' });
        const data = await response.json();
        console.log('API Response:', data);
    } catch (err) {
        console.error('API Error:', err);
    }
    
    // Vérifier les styles
    const dynamicStyles = document.getElementById('dynamic-styles');
    if (dynamicStyles) {
        console.log('Applied CSS:', dynamicStyles.innerHTML);
    }
    
    console.groupEnd();
  }

  // Exposer la fonction pour debug depuis la console
  window.debugTimer = debugTimerState;

  // Fonction de test pour vérifier l'API
  async function testAPI() {
    console.group('🧪 API Test');
    
    try {
      // Test timer API
      console.log('Testing timer API...');
      const timerResponse = await fetch(`/api.php?token=${encodeURIComponent(token)}&module=timer&action=get`, { cache: 'no-store' });
      const timerData = await timerResponse.json();
      console.log('Timer API response:', timerData);
      
      // Test styles API
      console.log('Testing styles API...');
      const stylesResponse = await fetch(`/api.php?token=${encodeURIComponent(token)}&module=timer-style&action=get`, { cache: 'no-store' });
      const stylesData = await stylesResponse.json();
      console.log('Styles API response:', stylesData);
      
      // Test localStorage
      console.log('LocalStorage timerStyles:', localStorage.getItem('realtimeTimerStyles'));
      console.log('LocalStorage forceUpdate:', localStorage.getItem('forceTimerStyleUpdate'));
      
    } catch (err) {
      console.error('API Test Error:', err);
    }
    
    console.groupEnd();
  }

  // Exposer la fonction de test
  window.testAPI = testAPI;

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

  // La logique de calcul du temps restant est maintenant directement dans updateDisplay() et forceDisplay()

  // Polling modéré pour styles via localStorage (tous les 500ms)
  setInterval(() => {
    try {
        // Vérifier le forceUpdate
        const forceUpdate = localStorage.getItem('forceTimerStyleUpdate');
        if (forceUpdate && forceUpdate !== lastForceUpdate) {
            lastForceUpdate = forceUpdate;
            const stylesStr = localStorage.getItem('realtimeTimerStyles');
            if (stylesStr) {
                try {
                    const styles = JSON.parse(stylesStr);
                    console.log('Force update styles:', styles);
                    scheduleStyleApply(styles, true);
                    // Le style MFA Premium est maintenant géré automatiquement dans updateDisplay()
                    // Pas besoin de mise à jour manuelle - tout est synchronisé
                } catch (e) {
                    console.error('Error parsing localStorage styles:', e);
                }
            }
            return;
        }
        
        // Vérifier le timestamp normal
        const stylesTimestamp = localStorage.getItem('timerStylesTimestamp');
        if (stylesTimestamp && parseInt(stylesTimestamp) > (window.lastTimerStylesTimestamp || 0)) {
            const stylesStr = localStorage.getItem('realtimeTimerStyles');
            if (stylesStr) {
                try {
                    const styles = JSON.parse(stylesStr);
                    console.log('Timestamp update styles:', styles);
                    scheduleStyleApply(styles);
                    // Le style MFA Premium est maintenant géré automatiquement dans updateDisplay()
                    // Pas besoin de mise à jour manuelle - tout est synchronisé
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

  // ---------- LOGIQUE TIMER ----------

  function formatTime(totalSeconds) {
    // S'assurer qu'on a un nombre valide
    totalSeconds = parseInt(totalSeconds) || 0;
    totalSeconds = Math.max(0, totalSeconds); // Jamais négatif
    
    const hours = Math.floor(totalSeconds / 3600);
    const minutes = Math.floor((totalSeconds % 3600) / 60);
    const seconds = totalSeconds % 60;
    
    return `${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
  }

  function formatParts(num) {
    return num.toString().padStart(2, '0').split('');
  }

  function initializeForTikTok() {
    // Forcer une valeur initiale si tout est à 0
    if (timerState.duration === 0 && !timerState.isRunning) {
        timerState.duration = 0; // 00:00:00 par défaut
        forceDisplay(); // Forcer l'affichage
        saveState();
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
    
    // Mettre à jour l'affichage normal
    const el = document.getElementById('timer-display');
    if (el) {
        el.textContent = formatTime(remaining);
    }
  }



  function debugTikTok() {
    console.log('=== TikTok Debug ===');
    console.log('Timer State:', timerState);
    console.log('Current Time:', new Date().toLocaleTimeString());
    console.log('Date.now():', Date.now());
    console.log('Math.floor(Date.now() / 1000):', Math.floor(Date.now() / 1000));
    console.log('Display Element:', document.getElementById('timer-display'));
    console.log('Display Text:', document.getElementById('timer-display')?.textContent);
    
    // Tester manuellement
    const testTime = formatTime(123);
    console.log('Test formatTime(123):', testTime);
    
    // Forcer une mise à jour
    timerState.duration = 180; // 3 minutes
    forceDisplay(); // Forcer l'affichage
    console.log('After force update:', document.getElementById('timer-display')?.textContent);
  }

  window.debugTikTok = debugTikTok;







  // Fonction de debug pour vérifier l'interval
  function debugInterval() {
    console.log('=== Interval Debug ===');
    console.log('Interval exists:', !!interval);
    console.log('Timer state:', timerState);
    console.log('Current time:', new Date().toLocaleTimeString());
    console.log('End time:', timerState.endTime ? new Date(timerState.endTime * 1000).toLocaleTimeString() : 'null');
    
    if (timerState.isRunning && timerState.endTime) {
      const now = Math.floor(Date.now() / 1000);
      const remaining = Math.max(0, timerState.endTime - now);
      console.log('Remaining time:', remaining, 'seconds');
      console.log('Formatted:', formatTime(remaining));
    }
  }

  window.debugInterval = debugInterval;

  // Fonction de test pour le timer
  function testTimer() {
    console.log('=== Test Timer ===');
    console.log('Current state:', timerState);
    console.log('Interval exists:', !!interval);
    
    // Tester le démarrage manuel
    if (!timerState.isRunning) {
      console.log('Starting timer manually...');
      timerState.duration = 10; // 10 secondes
      timerState.isRunning = true;
      timerState.isPaused = false;
      timerState.endTime = Math.floor(Date.now() / 1000) + 10;
      
      // Démarrer l'interval
      if (interval) clearInterval(interval);
      interval = setInterval(updateDisplay, 1000);
      updateDisplay();
      
      console.log('Timer started manually - endTime:', timerState.endTime);
    } else {
      console.log('Timer already running');
    }
  }

  window.testTimer = testTimer;



  function updateDisplay() {
    let remaining = 0;
    
    if (timerState.isRunning && timerState.endTime) {
        // Timer en cours : calculer le temps restant
        const now = Math.floor(Date.now() / 1000);
        remaining = Math.max(0, timerState.endTime - now);
        
        // Si on arrive à 0, arrêter le timer
        if (remaining === 0) {
            pauseTimer();
            return;
        }
    } else {
        // Timer pas démarré : afficher la durée
        remaining = timerState.duration;
    }
    
    // Mettre à jour l'affichage normal
    const el = document.getElementById('timer-display');
    if (el) {
        el.textContent = formatTime(remaining);
    }
  }

  async function startTimer(save = true) {
    if (timerState.isRunning) return;
    
    const now = Math.floor(Date.now() / 1000);
    
    if (timerState.isPaused && timerState.endTime) {
        // Reprendre depuis l'état pause : garder l'endTime existant
        timerState.isRunning = true;
        timerState.isPaused = false;
    } else {
        // Démarrer un nouveau timer : calculer le nouveau endTime
        timerState.endTime = now + timerState.duration;
        timerState.isRunning = true;
        timerState.isPaused = false;
    }
    
    if (save) await saveState();
    
    // Un seul interval simple pour l'affichage
    if (interval) clearInterval(interval);
    interval = setInterval(updateDisplay, 1000);
    updateDisplay(); // Utiliser updateDisplay (même logique que le timer normal)
  }

  async function pauseTimer() {
    if (!timerState.isRunning) return;
    
    // Ne pas modifier endTime ni duration, juste mettre en pause
    timerState.isRunning = false;
    timerState.isPaused = true;
    
    if (interval) {
      clearInterval(interval);
      interval = null;
    }
    
    updateDisplay(); // Utiliser updateDisplay (même logique que le timer normal)
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
    
    // Utiliser updateDisplay pour la cohérence
    updateDisplay();
    
    await saveState();
  }

  async function handleTimeAction(event, action, seconds) {
    event?.preventDefault();
    
    // Toujours ajuster la duration (même logique que le timer normal)
    if (action === 'add') {
        timerState.duration += seconds;
        if (timerState.isRunning && timerState.endTime) {
            timerState.endTime += seconds;
        }
    } else if (action === 'subtract') {
        timerState.duration = Math.max(0, timerState.duration - seconds);
        if (timerState.isRunning && timerState.endTime) {
            const now = Math.floor(Date.now() / 1000);
            const newEndTime = timerState.endTime - seconds;
            if (newEndTime > now) {
                timerState.endTime = newEndTime;
            } else {
                // Si on soustrait trop, arrêter le timer
                await pauseTimer();
            }
        }
    }
    
    // Utiliser updateDisplay pour la cohérence
    updateDisplay();
    
    await saveState();
  }

  function addManualTime() {
    const hours = parseInt(document.getElementById('manual-hours').value) || 0;
    const minutes = parseInt(document.getElementById('manual-minutes').value) || 0;
    const seconds = parseInt(document.getElementById('manual-seconds').value) || 0;
    const totalSeconds = hours * 3600 + minutes * 60 + seconds;
    
    if (totalSeconds > 0) {
      if (timerState.isRunning) {
        timerState.endTime += totalSeconds;
      } else {
        timerState.duration += totalSeconds;
      }
      // Utiliser updateDisplay pour la cohérence
      updateDisplay();
      
      saveState();
    }
  }

  function subtractManualTime() {
    const hours = parseInt(document.getElementById('manual-hours').value) || 0;
    const minutes = parseInt(document.getElementById('manual-minutes').value) || 0;
    const seconds = parseInt(document.getElementById('manual-seconds').value) || 0;
    const totalSeconds = hours * 3600 + minutes * 60 + seconds;
    
    if (totalSeconds > 0) {
      if (timerState.isRunning) {
        timerState.endTime -= totalSeconds;
      } else {
        timerState.duration = Math.max(0, timerState.duration - totalSeconds);
      }
      // Utiliser updateDisplay pour la cohérence
      updateDisplay();
      
      saveState();
    }
  }

  // Sauvegarde de l'état
  async function saveState() {
    try {
      const stateData = {
        endTime: timerState.endTime,
        duration: timerState.duration,
        isRunning: timerState.isRunning,
        isPaused: timerState.isPaused
      };
      await fetch(`/api.php?token=${encodeURIComponent(token)}&module=timer&action=set&value=${encodeURIComponent(JSON.stringify(stateData))}`, {
        method: 'GET',
        cache: 'no-store'
      });
    } catch (err) {
      console.error('Erreur lors de la sauvegarde:', err);
    }
  }

  // Synchronisation (mode realtime)
  async function syncState() {
    if (!isRealtime) return;
    
    try {
      const response = await fetch(`/api.php?token=${encodeURIComponent(token)}&module=timer&action=get`, { 
        cache: 'no-store' 
      });
      const data = await response.json();
      
      if (data.success && data.data) {
        timerState.endTime = data.data.endTime || null;
        timerState.duration = data.data.duration || 0;
        timerState.isRunning = !!data.data.isRunning;
        timerState.isPaused = !!data.data.isPaused;
        
        forceDisplay(); // Forcer l'affichage
        
        // Gérer le démarrage/arrêt du timer
        if (timerState.isRunning && !interval) {
          interval = setInterval(updateDisplay, 1000);
        } else if (!timerState.isRunning && interval) {
          clearInterval(interval);
          interval = null;
        }
      }
    } catch (err) {
      console.error('Erreur sync:', err);
    }
  }

  // Synchronisation simple toutes les secondes
  function startRealtimeSyncs() {
    if (isRealtime) {
      setInterval(syncState, 1000);
      syncState();
    }
  }

  // Actions liées aux boutons (exposées globalement comme avant)
  window.startTimerAction = async function () {
    if (!timerState.isRunning) {
      await startTimer(true);
      
      // Forcer la mise à jour immédiate de l'affichage
      updateDisplay();
      
      // Vérifier que l'état a bien été mis à jour
      if (!timerState.isRunning) {
        forceStartTimer();
      }
    }
  };
  
  window.pauseTimerAction = async function () {
    if (timerState.isRunning) {
      await pauseTimer();
      
      // Forcer la mise à jour immédiate de l'affichage
      updateDisplay();
    }
  };
  
  // Exposer les fonctions globalement
  window.handleTimeAction = handleTimeAction;
  window.addManualTime = addManualTime;
  window.subtractManualTime = subtractManualTime;
  window.resetTimer = resetTimer;

  // Affichage initial
  forceDisplay();
  


  // Fonction de diagnostic spécifique pour TikTok Live Studio
  async function diagnoseTikTokLive() {
    console.group('🔍 TikTok Live Studio Diagnostic');
    
    // 1. Vérifier l'environnement
    console.log('Environment:', {
      userAgent: navigator.userAgent,
      platform: navigator.platform,
      language: navigator.language,
      cookieEnabled: navigator.cookieEnabled,
      onLine: navigator.onLine
    });
    
    // 2. Vérifier les éléments DOM
    console.log('DOM Elements:', {
      timerDisplay: !!document.getElementById('timer-display'),
      actionBar: !!document.querySelector('.timer-action-bar'),
      control: <?= $control ? 'true' : 'false' ?>
    });
    
    // 3. Tester l'API
    try {
      console.log('Testing API endpoints...');
      
      // Test API timer
      const timerResponse = await fetch(`/api.php?token=${encodeURIComponent(token)}&module=timer&action=get`, { 
        cache: 'no-store',
        headers: {
          'Accept': 'application/json',
          'X-Requested-With': 'XMLHttpRequest'
        }
      });
      
      console.log('Timer API Status:', timerResponse.status);
      console.log('Timer API Headers:', Object.fromEntries(timerResponse.headers.entries()));
      
      const timerData = await timerResponse.json();
      console.log('Timer API Response:', timerData);
      
      // Test API styles
      const stylesResponse = await fetch(`/api.php?token=${encodeURIComponent(token)}&module=timer-style&action=get`, { 
        cache: 'no-store',
        headers: {
          'Accept': 'application/json',
          'X-Requested-With': 'XMLHttpRequest'
        }
      });
      
      console.log('Styles API Status:', stylesResponse.status);
      const stylesData = await stylesResponse.json();
      console.log('Styles API Response:', stylesData);
      
    } catch (err) {
      console.error('API Test Error:', err);
    }
    
    // 4. Vérifier l'état du timer
    console.log('Timer State:', timerState);
    console.log('Interval Status:', !!interval);
    
    // 5. Test de performance
    const startTime = performance.now();
    updateDisplay();
    const endTime = performance.now();
    console.log('Display Update Performance:', `${(endTime - startTime).toFixed(2)}ms`);
    
    // 6. Vérifier les styles appliqués
    const dynamicStyles = document.getElementById('dynamic-styles');
    if (dynamicStyles) {
      console.log('Applied CSS Length:', dynamicStyles.innerHTML.length);
      console.log('Applied CSS Preview:', dynamicStyles.innerHTML.substring(0, 200) + '...');
    }
    
    // 7. Test de compatibilité TikTok
    console.log('TikTok Compatibility:', {
      fetchSupported: typeof fetch !== 'undefined',
      setIntervalSupported: typeof setInterval !== 'undefined',
      localStorageSupported: typeof localStorage !== 'undefined',
      broadcastChannelSupported: typeof BroadcastChannel !== 'undefined'
    });
    
    console.groupEnd();
  }
  
  // Exposer la fonction de diagnostic
  window.diagnoseTikTokLive = diagnoseTikTokLive;
  
  // Fonction pour forcer la compatibilité TikTok
  function forceTikTokCompatibility() {
    console.log('🔧 Forcing TikTok Live Studio compatibility...');
    
    // 1. Forcer les headers CORS
    const originalFetch = window.fetch;
    window.fetch = function(url, options = {}) {
      const newOptions = {
        ...options,
        headers: {
          'Accept': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
          ...options.headers
        },
        cache: 'no-store'
      };
      return originalFetch(url, newOptions);
    };
    
    // 2. Améliorer la gestion des erreurs
    window.addEventListener('error', (event) => {
      console.error('Global error caught:', event.error);
    });
    
    // 3. Forcer l'affichage initial
    setTimeout(() => {
      forceDisplay();
      console.log('Forced initial display');
    }, 100);
    
    // 4. Polling de sécurité pour TikTok
    setInterval(() => {
      const timerDisplay = document.getElementById('timer-display');
      if (timerDisplay && !timerDisplay.textContent) {
        console.log('Timer display empty, forcing update...');
        forceDisplay();
      }
    }, 5000);
    
    console.log('TikTok compatibility mode activated');
  }
  
  // Activer la compatibilité TikTok automatiquement
  forceTikTokCompatibility();
</script>
</body>
</html> 