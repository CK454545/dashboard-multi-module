<?php
// Inclure la validation des tokens
require_once __DIR__ . '/validate_token.php';

// Valider le token et récupérer les infos utilisateur
$user = requireValidToken();

$control = isset($_GET['control']) && $_GET['control'] === 'true';
$token = $_GET['token'] ?? '';
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
        }

        /* ==================== MAIN CONTAINER ==================== */
        .widget-container {
            max-width: 1200px;
            margin: 0 auto;
            height: calc(100vh - 2 * var(--spacing-lg));
            display: flex;
            flex-direction: column;
            overflow: hidden;
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

        /* ==================== SAVE NOTIFICATION ==================== */
        .save-notification {
            position: fixed;
            top: 20px;
            right: 20px;
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
            padding: var(--spacing-md) var(--spacing-lg);
            border-radius: var(--radius-lg);
            font-weight: 500;
            z-index: 1000;
            animation: slideInRight 0.3s ease-out;
            box-shadow: 0 8px 25px rgba(16, 185, 129, 0.4);
        }

        .save-notification i {
            margin-right: var(--spacing-sm);
        }

        @keyframes slideInRight {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        /* ==================== AUTO-SAVE NOTIFICATION ==================== */
        .auto-save-notification {
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            background: linear-gradient(135deg, #f59e0b, #d97706);
            color: white;
            padding: var(--spacing-sm) var(--spacing-md);
            border-radius: var(--radius-md);
            font-size: 0.875rem;
            font-weight: 500;
            z-index: 1000;
            animation: slideInTop 0.3s ease-out;
            box-shadow: 0 4px 12px rgba(245, 158, 11, 0.4);
        }

        .auto-save-notification i {
            margin-right: var(--spacing-sm);
        }

        @keyframes slideInTop {
            from {
                transform: translateX(-50%) translateY(-100%);
                opacity: 0;
            }
            to {
                transform: translateX(-50%) translateY(0);
                opacity: 1;
            }
        }
        
        @keyframes slideOutRight {
            from {
                transform: translateX(0);
                opacity: 1;
            }
            to {
                transform: translateX(100%);
                opacity: 0;
            }
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
        }

        #timer-display {
            font-size: 6rem;
            font-weight: 800;
            color: white;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin: 0;
            font-variant-numeric: tabular-nums;
        }

        /* ==================== TIMER ACTION BAR - NOUVELLE BARRE MODERNE ==================== */
        .timer-action-bar {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: rgba(15, 23, 42, 0.95);
            backdrop-filter: blur(20px);
            border-top: 1px solid rgba(148, 163, 184, 0.1);
            padding: var(--spacing-sm) var(--spacing-md);
            z-index: 1000;
            display: flex;
            flex-direction: column;
            gap: var(--spacing-sm);
        }

        .timer-action-bar-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: var(--spacing-xs);
        }

        .timer-action-bar-title {
            font-size: 0.75rem;
            font-weight: 600;
            color: #3b82f6;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            display: flex;
            align-items: center;
            gap: var(--spacing-xs);
        }

        .timer-action-bar-config {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            background: rgba(59, 130, 246, 0.1);
            border: 1px solid #3b82f6;
            color: #3b82f6;
            font-size: 0.7rem;
            cursor: pointer;
            transition: all var(--transition-fast);
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
        }

        .timer-action-bar-config:hover {
            background: #3b82f6;
            color: white;
            transform: scale(1.05);
        }

        .timer-action-bar-sections {
            display: flex;
            flex-direction: column;
            gap: var(--spacing-sm);
        }

        .timer-action-section {
            background: rgba(30, 41, 59, 0.5);
            border-radius: var(--radius-md);
            padding: var(--spacing-sm);
            border: 1px solid rgba(148, 163, 184, 0.1);
        }

        .timer-action-section-header {
            display: flex;
            align-items: center;
            gap: var(--spacing-xs);
            margin-bottom: var(--spacing-xs);
            font-size: 0.7rem;
            font-weight: 600;
            color: var(--text-secondary);
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .timer-action-buttons {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: var(--spacing-xs);
        }

        .timer-action-buttons.controls {
            grid-template-columns: repeat(3, 1fr);
        }

        .timer-action-btn {
            padding: var(--spacing-xs) var(--spacing-xs);
            border: none;
            border-radius: var(--radius-md);
            font-size: 0.7rem;
            font-weight: 600;
            cursor: pointer;
            transition: all var(--transition-fast);
            text-transform: uppercase;
            letter-spacing: 0.3px;
            min-height: 36px;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
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
            transition: opacity var(--transition-fast);
            z-index: -1;
        }

        .timer-action-btn:hover::before {
            opacity: 0.1;
        }

        .timer-action-btn.add {
            background: rgba(59, 130, 246, 0.1);
            color: #3b82f6;
            border: 1px solid rgba(59, 130, 246, 0.3);
        }

        .timer-action-btn.add:hover {
            background: rgba(59, 130, 246, 0.2);
            border-color: #3b82f6;
            transform: translateY(-1px);
        }

        .timer-action-btn.subtract {
            background: rgba(239, 68, 68, 0.1);
            color: #ef4444;
            border: 1px solid rgba(239, 68, 68, 0.3);
        }

        .timer-action-btn.subtract:hover {
            background: rgba(239, 68, 68, 0.2);
            border-color: #ef4444;
            transform: translateY(-1px);
        }

        .timer-action-btn.reset {
            background: rgba(245, 158, 11, 0.1);
            color: #f59e0b;
            border: 1px solid rgba(245, 158, 11, 0.3);
        }

        .timer-action-btn.reset:hover {
            background: rgba(245, 158, 11, 0.2);
            border-color: #f59e0b;
            transform: translateY(-1px);
        }

        .timer-action-btn.primary {
            background: rgba(59, 130, 246, 0.1);
            color: #3b82f6;
            border: 1px solid rgba(59, 130, 246, 0.3);
        }

        .timer-action-btn.primary:hover {
            background: rgba(59, 130, 246, 0.2);
            border-color: #3b82f6;
            transform: translateY(-1px);
        }

        .timer-action-btn.warning {
            background: rgba(245, 158, 11, 0.1);
            color: #f59e0b;
            border: 1px solid rgba(245, 158, 11, 0.3);
        }

        .timer-action-btn.warning:hover {
            background: rgba(245, 158, 11, 0.2);
            border-color: #f59e0b;
            transform: translateY(-1px);
        }

        .timer-action-btn.large {
            padding: var(--spacing-md) var(--spacing-sm);
            font-size: 0.875rem;
            min-height: 52px;
        }

        .timer-action-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            transform: none !important;
        }

        /* ==================== CONFIG BUTTON INTEGRATED ==================== */
        .config-btn-integrated {
            position: relative;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background: var(--bg-glass);
            backdrop-filter: blur(20px);
            border: 1px solid var(--accent-color);
            color: var(--accent-color);
            font-size: 0.8rem;
            cursor: pointer;
            transition: all var(--transition-fast);
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            z-index: 10;
        }

        .config-btn-integrated:hover {
            background: var(--accent-color);
            color: white;
            transform: scale(1.1);
        }

        /* ==================== STYLES PERSONNALISÉS ==================== */
        /* Default */
        .style-default #timer-display {
            font-family: 'Inter', sans-serif;
        }

        /* Neon */
        .style-neon #timer-display {
            font-family: 'Inter', sans-serif;
            text-shadow: 0 0 10px currentColor, 0 0 20px currentColor, 0 0 30px currentColor, 0 0 40px currentColor;
        }

        /* Gradient */
        .style-gradient #timer-display {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(45deg, #667eea 0%, #764ba2 25%, #f093fb 50%, #4facfe 75%, #667eea 100%);
            background-size: 400% 400%;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            animation: gradientShift 3s ease infinite;
        }

        @keyframes gradientShift {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        /* Retro */
        .style-retro #timer-display {
            font-family: 'Press Start 2P', cursive;
            font-size: 3rem;
            color: #00ff00;
            text-shadow: 2px 2px 0 #008800;
        }

        /* Minimal */
        .style-minimal #timer-display {
            font-family: 'Inter', sans-serif;
            font-weight: 300;
        }

        /* Bold */
        .style-bold #timer-display {
            font-family: 'Inter', sans-serif;
            font-weight: 900;
            font-style: italic;
        }

        /* Tech */
        .style-tech #timer-display {
            font-family: 'Orbitron', monospace;
            font-weight: 900;
            letter-spacing: 0.1em;
        }

        /* Space */
        .style-space #timer-display {
            font-family: 'Audiowide', cursive;
            letter-spacing: 0.2em;
        }

        /* Cyber */
        .style-cyber #timer-display {
            font-family: 'Russo One', sans-serif;
            text-transform: uppercase;
            letter-spacing: 0.1em;
        }

        /* Arcade */
        .style-arcade #timer-display {
            font-family: 'Bungee', cursive;
            text-shadow: 3px 3px 0 rgba(0,0,0,0.5);
        }

        /* Horror */
        .style-horror #timer-display {
            font-family: 'Creepster', cursive;
            color: #ff0000;
            text-shadow: 0 0 10px #ff0000, 2px 2px 5px #000;
        }

        /* Elegant */
        .style-elegant #timer-display {
            font-family: 'Playfair Display', serif;
            font-weight: 700;
            font-style: italic;
        }

        /* Comic */
        .style-comic #timer-display {
            font-family: 'Luckiest Guy', cursive;
            text-shadow: 3px 3px 0 #000;
            -webkit-text-stroke: 2px #000;
        }

        /* Fun */
        .style-fun #timer-display {
            font-family: 'Fredoka One', cursive;
            animation: bounce 2s ease-in-out infinite;
        }

        @keyframes bounce {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }

        /* Sizes */
        .size-small #timer-display { font-size: 3rem; }
        .size-medium #timer-display { font-size: 6rem; }
        .size-large #timer-display { font-size: 8rem; }
        .size-xlarge #timer-display { font-size: 10rem; }

        /* Effects */
        .effect-pulse #timer-display {
            animation: pulse 2s ease-in-out infinite;
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); opacity: 1; }
            50% { transform: scale(1.05); opacity: 0.8; }
        }

        .effect-glow #timer-display {
            filter: drop-shadow(0 0 20px currentColor);
        }

        .effect-shake #timer-display {
            animation: shake 0.5s ease-in-out infinite;
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }

        /* ==================== RESPONSIVE ==================== */
        @media (max-width: 768px) {
            .widget-container {
                padding: var(--spacing-sm);
            }

            #timer-display {
                font-size: 4rem;
            }

            .size-small #timer-display { font-size: 2.5rem; }
            .size-medium #timer-display { font-size: 4rem; }
            .size-large #timer-display { font-size: 5rem; }
            .size-xlarge #timer-display { font-size: 6rem; }

            .style-retro #timer-display {
                font-size: 2rem;
            }

            .timer-action-bar {
                padding: var(--spacing-xs);
            }

            .timer-action-buttons {
                grid-template-columns: repeat(4, 1fr);
            }

            .timer-action-buttons.controls {
                grid-template-columns: repeat(2, 1fr);
            }

            .timer-action-btn {
                font-size: 0.65rem;
                padding: var(--spacing-xs);
                min-height: 32px;
            }
        }

        @media (max-width: 480px) {
            #timer-display {
                font-size: 3rem;
            }

            .size-small #timer-display { font-size: 2rem; }
            .size-medium #timer-display { font-size: 3rem; }
            .size-large #timer-display { font-size: 4rem; }
            .size-xlarge #timer-display { font-size: 5rem; }

            .style-retro #timer-display {
                font-size: 1.5rem;
            }

            .config-btn-small {
                width: 50px;
                height: 50px;
                font-size: 1.2rem;
            }

            .timer-action-buttons {
                grid-template-columns: repeat(3, 1fr);
            }

            .timer-action-buttons.controls {
                grid-template-columns: 1fr;
            }

            .timer-action-btn {
                font-size: 0.65rem;
                padding: var(--spacing-xs);
                min-height: 32px;
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
                        <button class="timer-action-btn subtract" onclick="handleTimeAction(event, 'subtract', 10)">-10s</button>
                        <button class="timer-action-btn reset" onclick="resetTimer()">RESET</button>
                        <button class="timer-action-btn add" onclick="handleTimeAction(event, 'add', 10)">+10s</button>
                        <button class="timer-action-btn add" onclick="handleTimeAction(event, 'add', 60)">+1min</button>
                        <button class="timer-action-btn add" onclick="handleTimeAction(event, 'add', 300)">+5min</button>
                    </div>
                </div>

                <div class="timer-action-section">
                    <div class="timer-action-section-header">
                        <i class="fas fa-play-circle"></i> Contrôles
                    </div>
                    <div class="timer-action-buttons controls">
                        <button class="timer-action-btn primary large" data-action="start" id="startBtn" onclick="toggleTimer()">
                            <i class="fas fa-play"></i> Démarrer
                        </button>
                        <button class="timer-action-btn warning large" data-action="pause" id="pauseBtn" onclick="toggleTimer()" style="display: none;">
                            <i class="fas fa-pause"></i> Pause
                        </button>
                        <button class="timer-action-btn primary large" onclick="handleTimeAction(event, 'add', 30)">
                            <i class="fas fa-plus"></i> +30s
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <script>
        const token = '<?php echo htmlspecialchars($token); ?>';
        const userId = '<?php echo htmlspecialchars($user['userId']); ?>';
        const isRealtime = <?php echo isset($_GET['realtime']) && $_GET['realtime'] === 'true' ? 'true' : 'false'; ?>;
        
        let timerState = {
            seconds: 0,
            isRunning: false,
            lastUpdate: Date.now()
        };
        
        let interval = null;
        let syncInterval = null;

        // Charger la configuration du style
        async function loadConfig() {
            try {
                const response = await fetch(`/api.php?token=${token}&module=timer&action=get`);
                const data = await response.json();
                
                if (data.success && data.data) {
                    // Charger l'état du timer
                    timerState.seconds = data.data.seconds || 0;
                    timerState.isRunning = data.data.isRunning || false;
                    updateDisplay();
                    if (timerState.isRunning) {
                        startTimer(false);
                    }
                }
                
                // Charger la configuration des styles séparément
                loadStyles();
            } catch (error) {
                console.error('Erreur lors du chargement de la configuration:', error);
            }
        }

        // Charger les styles (basé sur Win)
        async function loadStyles() {
            try {
                const response = await fetch(`/api.php?token=${token}&module=timer-style&action=get`);
                const data = await response.json();
                
                if (data.success && data.style) {
                    const styles = JSON.parse(data.style);
                    console.log('📋 Styles chargés depuis l\'API:', styles);
                    applyStyle(styles);
                }
            } catch (error) {
                console.error('Erreur lors du chargement des styles:', error);
            }
        }

        // Appliquer les styles (basé sur la logique du module Win)
        function applyStyle(styles) {
            if (!styles || typeof styles !== 'object') {
                console.warn('Styles invalides reçus:', styles);
                return;
            }
            
            console.log('🎨 Application des styles:', styles);
            
            let css = '';
            
            // 1. STYLES GÉNÉRAUX (Background, Police, Position)
            if (styles.general) {
                const general = styles.general;
                
                // Background avec gestion de la transparence
                if (general.transparent === true || general.transparent === 'true' || general.transparent === 1) {
                    css += 'body { background: transparent !important; } ';
                    css += 'html { background: transparent !important; } ';
                    css += '.widget-container { background: transparent !important; } ';
                    css += '.display { background: transparent !important; } ';
                } else if (general.background) {
                    css += `body { background: ${general.background} !important; } `;
                    css += `html { background: ${general.background} !important; } `;
                }
                
                // Police générale
                if (general['font-family']) {
                    css += `#timer-display { font-family: ${general['font-family']} !important; } `;
                }
                
                // Positionnement du texte
                if (general['text-position']) {
                    const margin = general['text-margin'] || '0';
                    css += generatePositionCSS(general['text-position'], margin);
                }
                
                // Décalage vertical du timer
                if (general['vertical-offset']) {
                    const offset = parseInt(general['vertical-offset']) || 0;
                    if (offset !== 0) {
                        css += `.display { transform: translateY(${offset}px) !important; } `;
                    }
                }
            }
            
            // 2. OPTIONS GLOBALES
            if (styles.options) {
                const options = styles.options;
                
                // Masquer les contrôles si demandé
                if (options['hide-controls'] === true || options['hide-controls'] === 'true' || options['hide-controls'] === 1) {
                    css += '.command-bar { display: none !important; } ';
                    css += '.command-config-btn { display: none !important; } ';
                    css += '.config-btn-small { display: none !important; } ';
                }
            }
            
            // 3. STYLES TIMER
            if (styles.timer) {
                const timer = styles.timer;
                
                // Couleur
                if (timer.color) {
                    css += `#timer-display { color: ${timer.color} !important; } `;
                }
                
                // Taille
                if (timer.size) {
                    css += `#timer-display { font-size: ${timer.size}px !important; } `;
                }
                
                // Contour
                if (timer.stroke) {
                    css += `#timer-display { -webkit-text-stroke: 2px ${timer.stroke} !important; text-stroke: 2px ${timer.stroke} !important; } `;
                }
                
                // Ombre
                if (timer.shadow === true || timer.shadow === 'true' || timer.shadow === 1) {
                    css += '#timer-display { text-shadow: 3px 3px 6px rgba(0,0,0,0.8) !important; } ';
                }
                
                // Background du timer
                if ((timer.showBackground === true || timer.showBackground === 'true' || timer.showBackground === 1) && timer.background) {
                    css += `#timer-display { background: ${timer.background} !important; padding: 20px 40px !important; border-radius: 12px !important; } `;
                }
            }
            
            // Appliquer le CSS
            applyCSS(css);
        }
        
        // Génération du CSS de positionnement
        function generatePositionCSS(position, margin) {
            const positions = {
                'top-left': `.display { 
                    justify-content: flex-start !important; 
                    align-items: flex-start !important; 
                    padding-top: ${margin}px !important;
                    padding-left: ${margin}px !important;
                }`,
                'top-center': `.display { 
                    justify-content: center !important; 
                    align-items: flex-start !important; 
                    padding-top: ${margin}px !important; 
                }`,
                'top-right': `.display { 
                    justify-content: flex-end !important; 
                    align-items: flex-start !important; 
                    padding-top: ${margin}px !important;
                    padding-right: ${margin}px !important;
                }`,
                'center-left': `.display { 
                    justify-content: flex-start !important; 
                    align-items: center !important; 
                    padding-left: ${margin}px !important; 
                }`,
                'center-right': `.display { 
                    justify-content: flex-end !important; 
                    align-items: center !important; 
                    padding-right: ${margin}px !important; 
                }`,
                'bottom-left': `.display { 
                    justify-content: flex-start !important; 
                    align-items: flex-end !important; 
                    padding-bottom: ${margin}px !important;
                    padding-left: ${margin}px !important;
                }`,
                'bottom-center': `.display { 
                    justify-content: center !important; 
                    align-items: flex-end !important; 
                    padding-bottom: ${margin}px !important; 
                }`,
                'bottom-right': `.display { 
                    justify-content: flex-end !important; 
                    align-items: flex-end !important; 
                    padding-bottom: ${margin}px !important;
                    padding-right: ${margin}px !important;
                }`,
                'center': `.display { 
                    justify-content: center !important; 
                    align-items: center !important; 
                }`
            };
            
            return positions[position] || positions['center'];
        }
        
        // Application optimisée du CSS
        function applyCSS(css) {
            const existingStyle = document.getElementById('dynamic-styles');
            if (existingStyle) {
                existingStyle.remove();
            }
            
            if (css.trim()) {
                const styleElement = document.createElement('style');
                styleElement.id = 'dynamic-styles';
                styleElement.innerHTML = css;
                document.head.appendChild(styleElement);
            }
        }
        
        // Génération du CSS de positionnement
        function generatePositionCSS(position, margin) {
            const positions = {
                'top-left': `.display { justify-content: flex-start !important; align-items: flex-start !important; padding: ${margin}px !important; }`,
                'top-center': `.display { justify-content: flex-start !important; align-items: center !important; padding-top: ${margin}px !important; }`,
                'top-right': `.display { justify-content: flex-start !important; align-items: flex-end !important; padding: ${margin}px !important; }`,
                'center-left': `.display { justify-content: center !important; align-items: flex-start !important; padding-left: ${margin}px !important; }`,
                'center-right': `.display { justify-content: center !important; align-items: flex-end !important; padding-right: ${margin}px !important; }`,
                'bottom-left': `.display { justify-content: flex-end !important; align-items: flex-start !important; padding: ${margin}px !important; }`,
                'bottom-center': `.display { justify-content: flex-end !important; align-items: center !important; padding-bottom: ${margin}px !important; }`,
                'bottom-right': `.display { justify-content: flex-end !important; align-items: flex-end !important; padding: ${margin}px !important; }`,
                'center': `.display { justify-content: center !important; align-items: center !important; }`
            };
            
            return positions[position] || positions['center'];
        }
        
        // Application optimisée du CSS
        function applyCSS(css) {
            const existingStyle = document.getElementById('dynamic-styles');
            if (existingStyle) {
                existingStyle.remove();
            }
            
            if (css.trim()) {
                const styleElement = document.createElement('style');
                styleElement.id = 'dynamic-styles';
                styleElement.innerHTML = css;
                document.head.appendChild(styleElement);
            }
        }

        // Formater le temps en HH:MM:SS
        function formatTime(totalSeconds) {
            const hours = Math.floor(totalSeconds / 3600);
            const minutes = Math.floor((totalSeconds % 3600) / 60);
            const seconds = totalSeconds % 60;
            
            return `${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
        }

        // Mettre à jour l'affichage
        function updateDisplay() {
            document.getElementById('timer-display').textContent = formatTime(timerState.seconds);
        }

        // Démarrer/Arrêter le timer
        async function toggleTimer() {
            if (timerState.isRunning) {
                await pauseTimer();
            } else {
                await startTimer(true);
            }
        }

        // Démarrer le timer
        async function startTimer(save = true) {
            timerState.isRunning = true;
            timerState.lastUpdate = Date.now();
            
            const startBtn = document.getElementById('startBtn');
            const pauseBtn = document.getElementById('pauseBtn');
            if (startBtn && pauseBtn) {
                startBtn.style.display = 'none';
                pauseBtn.style.display = 'flex';
            }
            
            // Sauvegarder l'état
            if (save) {
                await saveState();
            }
            
            // Démarrer l'intervalle
            interval = setInterval(() => {
                if (timerState.seconds > 0) {
                    timerState.seconds--;
                    updateDisplay();
                    
                    // Arrêter à 0
                    if (timerState.seconds === 0) {
                        pauseTimer();
                        // Notification ou son de fin si configuré
                    }
                }
            }, 1000);
            
            // Si en temps réel, synchroniser périodiquement
            if (isRealtime) {
                syncInterval = setInterval(syncState, 5000);
            }
        }

        // Mettre en pause le timer
        async function pauseTimer() {
            timerState.isRunning = false;
            
            const startBtn = document.getElementById('startBtn');
            const pauseBtn = document.getElementById('pauseBtn');
            if (startBtn && pauseBtn) {
                startBtn.style.display = 'flex';
                pauseBtn.style.display = 'none';
            }
            
            // Arrêter l'intervalle
            if (interval) {
                clearInterval(interval);
                interval = null;
            }
            
            if (syncInterval) {
                clearInterval(syncInterval);
                syncInterval = null;
            }
            
            // Sauvegarder l'état
            await saveState();
        }

        // Réinitialiser le timer
        async function resetTimer() {
            // Arrêter le timer s'il est en cours
            if (timerState.isRunning) {
                await pauseTimer();
            }
            
            // Réinitialiser l'état
            timerState.seconds = 0;
            updateDisplay();
            
            // Sauvegarder l'état
            await saveState();
            
            // Appel API pour reset
            try {
                await fetch(`/api.php?token=${token}&module=timer&action=reset`, {
                    method: 'GET'
                });
            } catch (error) {
                console.error('Erreur lors du reset:', error);
            }
        }

        // Gérer les actions de temps
        async function handleTimeAction(event, action, seconds) {
            event.preventDefault();
            
            if (action === 'add') {
                timerState.seconds += seconds;
            } else if (action === 'subtract') {
                timerState.seconds = Math.max(0, timerState.seconds - seconds);
            }
            
            updateDisplay();
            await saveState();
        }

        // Sauvegarder l'état
        async function saveState() {
            try {
                const stateData = {
                    seconds: timerState.seconds,
                    isRunning: timerState.isRunning
                };
                await fetch(`/api.php?token=${token}&module=timer&action=set&value=${encodeURIComponent(JSON.stringify(stateData))}`, {
                    method: 'GET'
                });
            } catch (error) {
                console.error('Erreur lors de la sauvegarde:', error);
            }
        }

        // Synchroniser l'état (pour le mode temps réel)
        async function syncState() {
            if (!isRealtime) return;
            
            try {
                const response = await fetch(`/api.php?token=${token}&module=timer&action=get`);
                const data = await response.json();
                
                if (data.success && data.data) {
                    // Mettre à jour seulement si l'état distant est différent
                    if (data.data.seconds !== timerState.seconds || 
                        data.data.isRunning !== timerState.isRunning) {
                        
                        timerState.seconds = data.data.seconds;
                        timerState.isRunning = data.data.isRunning;
                        updateDisplay();
                        
                        // Gérer le changement d'état running
                        if (timerState.isRunning && !interval) {
                            startTimer(false);
                        } else if (!timerState.isRunning && interval) {
                            pauseTimer();
                        }
                    }
                }
            } catch (error) {
                console.error('Erreur lors de la synchronisation:', error);
            }
        }

        // Ajouter le temps manuel
        function addManualTime() {
            const hours = parseInt(document.getElementById('manual-hours').value) || 0;
            const minutes = parseInt(document.getElementById('manual-minutes').value) || 0;
            const seconds = parseInt(document.getElementById('manual-seconds').value) || 0;
            
            const totalSeconds = (hours * 3600) + (minutes * 60) + seconds;
            if (totalSeconds > 0) {
                timerState.seconds += totalSeconds;
                updateDisplay();
                saveState();
            }
        }

        // Soustraire le temps manuel
        function subtractManualTime() {
            const hours = parseInt(document.getElementById('manual-hours').value) || 0;
            const minutes = parseInt(document.getElementById('manual-minutes').value) || 0;
            const seconds = parseInt(document.getElementById('manual-seconds').value) || 0;
            
            const totalSeconds = (hours * 3600) + (minutes * 60) + seconds;
            if (totalSeconds > 0) {
                timerState.seconds = Math.max(0, timerState.seconds - totalSeconds);
                updateDisplay();
                saveState();
            }
        }

        // Auto-refresh rapide (basé sur Win)
        setInterval(() => {
            // Auto-refresh ultra-rapide toutes les 200ms
            syncState();
        }, 200);
        
        // Vérifier localStorage pour les styles temps réel (basé sur Win)
        setInterval(() => {
            const stylesTimestamp = localStorage.getItem('timerStylesTimestamp');
            const lastTimestamp = window.lastTimerStylesTimestamp || 0;
            
            // Vérifier le signal de force update
            const forceUpdate = localStorage.getItem('forceTimerStyleUpdate');
            if (forceUpdate && forceUpdate !== window.lastForceTimerUpdate) {
                console.log('🚀 Force update détecté pour Timer');
                window.lastForceTimerUpdate = forceUpdate;
                const styles = JSON.parse(localStorage.getItem('realtimeTimerStyles') || '{}');
                
                if (Object.keys(styles).length > 0) {
                    applyStyle(styles);
                }
                return;
            }
            
            if (stylesTimestamp && parseInt(stylesTimestamp) > lastTimestamp) {
                const styles = JSON.parse(localStorage.getItem('realtimeTimerStyles') || '{}');
                
                if (Object.keys(styles).length > 0) {
                    applyStyle(styles);
                    window.lastTimerStylesTimestamp = parseInt(stylesTimestamp);
                }
            }
        }, 15); // 15ms pour une réactivité ultra-rapide
        
        // Écouter les messages BroadcastChannel pour les styles
        if (window.BroadcastChannel) {
            const channel = new BroadcastChannel('timer_styles_channel');
            channel.addEventListener('message', (event) => {
                if (event.data && event.data.type === 'timerStylesUpdate') {
                    console.log('📡 Styles reçus via BroadcastChannel');
                    applyStyle(event.data.styles);
                }
            });
        }
        
        // Initialisation
        document.addEventListener('DOMContentLoaded', () => {
            loadConfig();
            
            // Charger les styles après un court délai
            setTimeout(() => {
                loadStyles();
            }, 100);
            
            // Forcer un rechargement des styles après 500ms
            setTimeout(() => {
                loadStyles();
            }, 500);
        });
        
        // Auto-refresh rapide (basé sur Win)
        setInterval(() => {
            syncState();
        }, 200); // 200ms pour une synchronisation quasi-instantanée
        
        // Vérifier localStorage pour les styles temps réel (version ultra-rapide comme Win)
        setInterval(() => {
            const stylesTimestamp = localStorage.getItem('timerStylesTimestamp');
            const lastTimestamp = window.lastTimerStylesTimestamp || 0;
            
            // Vérifier le signal de force update avec réactivité améliorée
            const forceUpdate = localStorage.getItem('forceTimerStyleUpdate');
            if (forceUpdate && forceUpdate !== window.lastForceTimerUpdate) {
                console.log('🚀 Force update détecté pour Timer');
                window.lastForceTimerUpdate = forceUpdate;
                const styles = JSON.parse(localStorage.getItem('realtimeTimerStyles') || '{}');
                
                if (Object.keys(styles).length > 0) {
                    applyStyle(styles);
                }
                return;
            }
            
            if (stylesTimestamp && parseInt(stylesTimestamp) > lastTimestamp) {
                const styles = JSON.parse(localStorage.getItem('realtimeTimerStyles') || '{}');
                
                if (Object.keys(styles).length > 0) {
                    applyStyle(styles);
                    window.lastTimerStylesTimestamp = parseInt(stylesTimestamp);
                }
            }
        }, 15); // 15ms pour une réactivité ultra-rapide comme Win
    </script>
</body>
</html>
