<?php
// Inclure la validation des tokens
require_once __DIR__ . '/validate_token.php';

// Valider le token et récupérer les infos utilisateur
$user = requireValidToken();

$token = $_GET['token'] ?? '';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuration Team VS Team - MyFull Agency</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Luckiest+Guy&family=Orbitron:wght@400;700;900&family=Press+Start+2P&family=Russo+One&family=Audiowide&family=Bungee&family=Creepster&family=Nosifer&family=Walter+Turncoat&family=Fredoka+One&family=Cinzel:wght@400;600&family=Playfair+Display:wght@400;700&family=Dancing+Script:wght@400;700&family=Black+Ops+One&family=Faster+One&family=Jolly+Lodger&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* ==================== CSS VARIABLES ==================== */
        :root {
            /* Colors */
            --primary-color: #8b00ff;
            --primary-hover: #7000dd;
            --secondary-color: #00d4ff;
            --secondary-hover: #00b8e6;
            --accent-color: #ff006e;
            --danger-color: #ff0000;
            --success-color: #44ff00;
            --warning-color: #ff9500;
            --team-green: #10b981;
            --team-red: #ef4444;
            
            /* Backgrounds */
            --bg-primary: #0a0e1b;
            --bg-secondary: rgba(255, 255, 255, 0.05);
            --bg-tertiary: rgba(255, 255, 255, 0.08);
            --bg-card: rgba(255, 255, 255, 0.03);
            --bg-glass: rgba(255, 255, 255, 0.05);
            --bg-input: rgba(0, 0, 0, 0.3);
            
            /* Text Colors */
            --text-primary: #ffffff;
            --text-secondary: #a0a0a0;
            --text-muted: #666666;
            
            /* Borders */
            --border-color: rgba(255, 255, 255, 0.1);
            --border-hover: rgba(255, 255, 255, 0.2);
            --border-focus: rgba(139, 0, 255, 0.5);
            
            /* Shadows */
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            --shadow-xl: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
            --shadow-glow: 0 0 20px rgba(99, 102, 241, 0.3);
            
            /* Spacing */
            --spacing-xs: 0.25rem;
            --spacing-sm: 0.5rem;
            --spacing-md: 1rem;
            --spacing-lg: 1.5rem;
            --spacing-xl: 2rem;
            --spacing-2xl: 3rem;
            
            /* Border Radius */
            --radius-sm: 0.25rem;
            --radius-md: 0.5rem;
            --radius-lg: 0.75rem;
            --radius-xl: 1rem;
            --radius-full: 9999px;
            
            /* Transitions */
            --transition-fast: 0.15s ease;
            --transition-normal: 0.3s ease;
            --transition-slow: 0.5s ease;
        }

        /* ==================== GLOBAL STYLES ==================== */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: var(--bg-primary);
            color: var(--text-primary);
            line-height: 1.6;
            min-height: 100vh;
            padding: 0;
            overflow-x: hidden;
            position: relative;
        }
        
        /* ==================== ANIMATED BACKGROUND ==================== */
        body::before {
            content: '';
            position: fixed;
            width: 200%;
            height: 200%;
            top: -50%;
            left: -50%;
            background: 
                radial-gradient(circle at 20% 50%, #ff006e 0%, transparent 50%),
                radial-gradient(circle at 80% 50%, #8b00ff 0%, transparent 50%),
                radial-gradient(circle at 50% 100%, #00d4ff 0%, transparent 50%);
            animation: backgroundRotate 30s linear infinite;
            opacity: 0.15;
            z-index: -1;
        }

        @keyframes backgroundRotate {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* ==================== CONTAINER ==================== */
        .config-container {
            max-width: 1200px;
            margin: 0 auto;
        }

        /* ==================== HEADER ==================== */
        .config-header {
            text-align: center;
            margin-bottom: var(--spacing-2xl);
        }

        .config-title {
            font-size: 2.5rem;
            font-weight: 800;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: var(--spacing-sm);
        }

        .config-subtitle {
            color: var(--text-secondary);
            font-size: 1.125rem;
        }

        /* ==================== TABS ==================== */
        .tabs {
            display: flex;
            gap: var(--spacing-sm);
            margin-bottom: var(--spacing-xl);
            background: var(--bg-glass);
            padding: var(--spacing-sm);
            border-radius: var(--radius-lg);
            backdrop-filter: blur(10px);
        }

        .tab-btn {
            flex: 1;
            padding: var(--spacing-md) var(--spacing-lg);
            background: transparent;
            border: none;
            color: var(--text-secondary);
            font-weight: 500;
            cursor: pointer;
            border-radius: var(--radius-md);
            transition: all var(--transition-normal);
        }

        .tab-btn:hover {
            background: rgba(255, 255, 255, 0.05);
            color: var(--text-primary);
        }

        .tab-btn.active {
            background: var(--primary-color);
            color: white;
        }

        /* ==================== TAB CONTENT ==================== */
        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
            animation: fadeIn 0.3s ease;
        }

        /* ==================== CONFIG SECTIONS ==================== */
        .config-section {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-xl);
            padding: var(--spacing-xl);
            margin-bottom: var(--spacing-lg);
            backdrop-filter: blur(10px);
        }

        .section-title {
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: var(--spacing-lg);
            display: flex;
            align-items: center;
            gap: var(--spacing-sm);
        }

        /* ==================== FORM GROUPS ==================== */
        .form-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: var(--spacing-lg);
            margin-bottom: var(--spacing-lg);
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: var(--spacing-xs);
        }

        .form-group label {
            font-size: 0.875rem;
            font-weight: 500;
            color: var(--text-secondary);
        }

        /* ==================== INPUT STYLES ==================== */
        .color-input-group {
            display: flex;
            gap: var(--spacing-sm);
            align-items: center;
        }

        input[type="color"] {
            width: 50px;
            height: 50px;
            border: 2px solid var(--border-color);
            border-radius: var(--radius-md);
            cursor: pointer;
            background: var(--bg-input);
        }

        input[type="text"].color-text,
        input[type="number"],
        input[type="text"]:not(.color-text) {
            flex: 1;
            padding: var(--spacing-sm) var(--spacing-md);
            background: var(--bg-input);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-md);
            color: var(--text-primary);
            font-size: 0.875rem;
            transition: all var(--transition-fast);
        }

        input[type="text"].color-text:focus,
        input[type="number"]:focus,
        input[type="text"]:not(.color-text):focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px var(--border-focus);
        }

        /* ==================== RANGE INPUTS ==================== */
        .range-input-group {
            display: flex;
            gap: var(--spacing-sm);
            align-items: center;
        }

        input[type="range"] {
            flex: 1;
            height: 6px;
            background: var(--bg-tertiary);
            border-radius: var(--radius-full);
            outline: none;
            -webkit-appearance: none;
        }

        input[type="range"]::-webkit-slider-thumb {
            -webkit-appearance: none;
            width: 20px;
            height: 20px;
            background: var(--primary-color);
            border-radius: 50%;
            cursor: pointer;
            transition: all var(--transition-fast);
        }

        input[type="range"]::-webkit-slider-thumb:hover {
            background: var(--primary-hover);
            transform: scale(1.2);
        }

        .range-value {
            min-width: 60px;
            text-align: center;
            font-weight: 500;
            color: var(--text-secondary);
        }

        /* ==================== SELECT ==================== */
        select {
            padding: var(--spacing-sm) var(--spacing-md);
            background: var(--bg-input);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-md);
            color: var(--text-primary);
            font-size: 0.875rem;
            cursor: pointer;
        }

        /* ==================== CHECKBOX ==================== */
        .checkbox-group {
            display: flex;
            align-items: center;
            gap: var(--spacing-sm);
        }

        input[type="checkbox"] {
            width: 20px;
            height: 20px;
            cursor: pointer;
        }

        /* ==================== PREVIEW ==================== */
        .preview-container {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-xl);
            padding: var(--spacing-2xl);
            text-align: center;
            backdrop-filter: blur(10px);
            margin-bottom: var(--spacing-xl);
        }

        .preview-title {
            font-size: 1rem;
            font-weight: 600;
            color: var(--text-secondary);
            margin-bottom: var(--spacing-lg);
        }

        .preview-teams {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: var(--spacing-2xl);
        }

        .preview-team {
            text-align: center;
        }

        .preview-team-name {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: var(--spacing-sm);
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .preview-team-score {
            font-size: 3rem;
            font-weight: 800;
            margin: 0;
        }

        .preview-vs {
            font-size: 2rem;
            font-weight: 800;
            color: var(--text-muted);
        }

        /* ==================== BUTTONS ==================== */
        .button-group {
            display: flex;
            gap: var(--spacing-md);
            justify-content: center;
            margin-top: var(--spacing-xl);
        }

        .btn {
            padding: var(--spacing-md) var(--spacing-xl);
            border: none;
            border-radius: var(--radius-lg);
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            transition: all var(--transition-normal);
            display: flex;
            align-items: center;
            gap: var(--spacing-sm);
        }

        .btn-primary {
            background: var(--primary-color);
            color: white;
        }

        .btn-primary:hover {
            background: var(--primary-hover);
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }

        .btn-secondary {
            background: var(--bg-glass);
            color: var(--text-primary);
            border: 1px solid var(--border-color);
        }

        .btn-secondary:hover {
            background: rgba(255, 255, 255, 0.1);
            border-color: var(--border-hover);
        }

        /* ==================== URLS SECTION ==================== */
        .urls-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: var(--spacing-lg);
            margin-bottom: var(--spacing-xl);
        }

        .url-card {
            background: var(--bg-glass);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-lg);
            padding: var(--spacing-xl);
            text-align: center;
            transition: all var(--transition-normal);
        }

        .url-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
            border-color: var(--primary-color);
        }

        .url-icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            border-radius: var(--radius-lg);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto var(--spacing-md);
            font-size: 1.5rem;
            color: white;
        }

        .url-card h3 {
            font-size: 1.125rem;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: var(--spacing-sm);
        }

        .url-card p {
            font-size: 0.875rem;
            color: var(--text-secondary);
            margin-bottom: var(--spacing-lg);
            line-height: 1.5;
        }

        .url-btn {
            width: 100%;
            padding: var(--spacing-md);
            background: var(--primary-color);
            color: white;
            border: none;
            border-radius: var(--radius-md);
            font-weight: 500;
            cursor: pointer;
            transition: all var(--transition-fast);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: var(--spacing-sm);
            position: relative;
        }
        
        .url-btn::after {
            content: "Cliquez pour copier l'URL";
            position: absolute;
            bottom: 100%;
            left: 50%;
            transform: translateX(-50%);
            background: rgba(0, 0, 0, 0.9);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 4px;
            font-size: 0.75rem;
            white-space: nowrap;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.2s;
            margin-bottom: 0.5rem;
        }
        
        .url-btn:hover::after {
            opacity: 1;
        }

        .url-btn:hover {
            background: var(--primary-hover);
            transform: translateY(-1px);
            box-shadow: var(--shadow-md);
        }

        .url-btn.active {
            background: var(--secondary-color);
            cursor: default;
        }

        .url-btn.active:hover {
            transform: none;
            background: var(--secondary-color);
        }

        /* Team specific colors */
        .green-section h2 {
            color: var(--team-green);
        }

        .red-section h2 {
            color: var(--team-red);
        }

        /* ==================== NOTIFICATIONS ==================== */
        .save-notification {
            position: fixed;
            top: 20px;
            right: 20px;
            background: linear-gradient(135deg, var(--secondary-color), var(--secondary-hover));
            color: white;
            padding: var(--spacing-md) var(--spacing-lg);
            border-radius: var(--radius-lg);
            font-weight: 500;
            z-index: 1000;
            animation: slideInRight 0.3s ease-out;
            box-shadow: 0 8px 25px rgba(16, 185, 129, 0.4);
        }

        .auto-save-notification {
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            background: var(--warning-color);
            color: white;
            padding: var(--spacing-sm) var(--spacing-md);
            border-radius: var(--radius-md);
            font-size: 0.875rem;
            z-index: 999;
            animation: pulse 1s ease-in-out infinite;
        }

        /* ==================== ANIMATIONS ==================== */
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
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

        @keyframes pulse {
            0%, 100% {
                opacity: 1;
            }
            50% {
                opacity: 0.8;
            }
        }

        /* ==================== RESPONSIVE ==================== */
        @media (max-width: 768px) {
            .tabs {
                flex-direction: column;
            }
            
            .form-row {
                grid-template-columns: 1fr;
            }
            
            .button-group {
                flex-direction: column;
            }
            
            .btn {
                width: 100%;
                justify-content: center;
            }
            
            .preview-teams {
                flex-direction: column;
                gap: var(--spacing-lg);
            }
            
            .preview-vs {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="config-container">
        <!-- Header -->
        <div class="config-header">
            <h1 class="config-title">⚔️ Configuration Team VS Team</h1>
            <p class="config-subtitle">Personnalisez l'apparence de votre bataille d'équipes</p>
        </div>

        <!-- Preview -->
        <div class="preview-container">
            <div class="preview-title">Aperçu en temps réel</div>
            <div class="preview-teams">
                <div class="preview-team">
                    <div class="preview-team-name" id="preview-green-name" style="color: #10b981;">ÉQUIPE VERTE</div>
                    <div class="preview-team-score" id="preview-green-score" style="color: #10b981;">0</div>
                </div>
                <div class="preview-vs">VS</div>
                <div class="preview-team">
                    <div class="preview-team-name" id="preview-red-name" style="color: #ef4444;">ÉQUIPE ROUGE</div>
                    <div class="preview-team-score" id="preview-red-score" style="color: #ef4444;">0</div>
                </div>
            </div>
        </div>

        <!-- Tabs -->
        <div class="tabs">
            <button class="tab-btn active" data-tab="green">
                <i class="fas fa-users"></i> Équipe Verte
            </button>
            <button class="tab-btn" data-tab="red">
                <i class="fas fa-users"></i> Équipe Rouge
            </button>
            <button class="tab-btn" data-tab="general">
                <i class="fas fa-cog"></i> Général
            </button>
            <button class="tab-btn" data-tab="urls">
                <i class="fas fa-link"></i> URLs
            </button>
        </div>

        <!-- Tab Contents -->
        <!-- Équipe Verte Tab -->
        <div id="tab-green" class="tab-content active">
            <div class="config-section green-section">
                <h2 class="section-title">
                    <i class="fas fa-palette"></i> Configuration Équipe Verte
                </h2>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="green-name">Nom de l'équipe</label>
                        <input type="text" id="green-name" data-style="green-name" value="ÉQUIPE VERTE" placeholder="Nom de l'équipe verte">
                    </div>
                    
                    <div class="form-group">
                        <label for="green-color">Couleur de l'équipe</label>
                        <div class="color-input-group">
                            <input type="color" id="green-color" data-style="green-color" value="#10b981">
                            <input type="text" class="color-text" data-style="green-color" value="#10b981" placeholder="#10b981">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="green-score-color">Couleur du score</label>
                        <div class="color-input-group">
                            <input type="color" id="green-score-color" data-style="green-score-color" value="#10b981">
                            <input type="text" class="color-text" data-style="green-score-color" value="#10b981" placeholder="#10b981">
                        </div>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="green-size">Taille du score</label>
                        <div class="range-input-group">
                            <input type="range" id="green-size" data-style="green-size" min="12" max="200" value="80">
                            <input type="number" class="size-number" data-style="green-size" min="12" max="200" value="80">
                            <span class="range-value" id="green-size-value">80px</span>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="green-stroke">Contour du score</label>
                        <div class="color-input-group">
                            <input type="color" id="green-stroke" data-style="green-stroke" value="#000000">
                            <input type="text" class="color-text" data-style="green-stroke" value="#000000" placeholder="#000000">
                        </div>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Options d'affichage</label>
                        <div class="checkbox-group">
                            <input type="checkbox" id="green-shadow" data-style="green-shadow">
                            <label for="green-shadow">Ombre portée</label>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="green-background-color">Couleur du fond d'équipe</label>
                        <div class="color-input-group">
                            <input type="color" id="green-background-color" data-style="green-background-color" value="#000000">
                            <input type="text" class="color-text" data-style="green-background-color" value="#000000" placeholder="#000000">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Équipe Rouge Tab -->
        <div id="tab-red" class="tab-content">
            <div class="config-section red-section">
                <h2 class="section-title">
                    <i class="fas fa-palette"></i> Configuration Équipe Rouge
                </h2>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="red-name">Nom de l'équipe</label>
                        <input type="text" id="red-name" data-style="red-name" value="ÉQUIPE ROUGE" placeholder="Nom de l'équipe rouge">
                    </div>
                    
                    <div class="form-group">
                        <label for="red-color">Couleur de l'équipe</label>
                        <div class="color-input-group">
                            <input type="color" id="red-color" data-style="red-color" value="#ef4444">
                            <input type="text" class="color-text" data-style="red-color" value="#ef4444" placeholder="#ef4444">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="red-score-color">Couleur du score</label>
                        <div class="color-input-group">
                            <input type="color" id="red-score-color" data-style="red-score-color" value="#ef4444">
                            <input type="text" class="color-text" data-style="red-score-color" value="#ef4444" placeholder="#ef4444">
                        </div>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="red-size">Taille du score</label>
                        <div class="range-input-group">
                            <input type="range" id="red-size" data-style="red-size" min="12" max="200" value="80">
                            <input type="number" class="size-number" data-style="red-size" min="12" max="200" value="80">
                            <span class="range-value" id="red-size-value">80px</span>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="red-stroke">Contour du score</label>
                        <div class="color-input-group">
                            <input type="color" id="red-stroke" data-style="red-stroke" value="#000000">
                            <input type="text" class="color-text" data-style="red-stroke" value="#000000" placeholder="#000000">
                        </div>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Options d'affichage</label>
                        <div class="checkbox-group">
                            <input type="checkbox" id="red-shadow" data-style="red-shadow">
                            <label for="red-shadow">Ombre portée</label>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="red-background-color">Couleur du fond d'équipe</label>
                        <div class="color-input-group">
                            <input type="color" id="red-background-color" data-style="red-background-color" value="#000000">
                            <input type="text" class="color-text" data-style="red-background-color" value="#000000" placeholder="#000000">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Général Tab -->
        <div id="tab-general" class="tab-content">
            <div class="config-section">
                <h2 class="section-title">
                    <i class="fas fa-sliders-h"></i> Configuration Générale
                </h2>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="font-family">Police d'écriture</label>
                        <select id="font-family" data-style="font-family">
                            <option value="Arial, Helvetica, sans-serif">Arial</option>
                            <option value="'Inter', sans-serif">Inter</option>
                            <option value="'Luckiest Guy', cursive">Luckiest Guy</option>
                            <option value="'Orbitron', monospace">Orbitron</option>
                            <option value="'Press Start 2P', cursive">Press Start 2P</option>
                            <option value="'Russo One', sans-serif">Russo One</option>
                            <option value="'Audiowide', cursive">Audiowide</option>
                            <option value="'Bungee', cursive">Bungee</option>
                            <option value="'Black Ops One', cursive">Black Ops One</option>
                            <option value="'Faster One', cursive">Faster One</option>
                            <option value="Impact, sans-serif">Impact</option>
                            <option value="Georgia, serif">Georgia</option>
                            <option value="'Times New Roman', serif">Times New Roman</option>
                            <option value="Verdana, sans-serif">Verdana</option>
                            <option value="'Comic Sans MS', cursive">Comic Sans MS</option>
                            <option value="'Courier New', monospace">Courier New</option>
                            <option value="Helvetica, sans-serif">Helvetica</option>
                            <option value="'Trebuchet MS', sans-serif">Trebuchet MS</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="background">Couleur d'arrière-plan</label>
                        <div class="color-input-group">
                            <input type="color" id="background" data-style="background" value="#1e293b">
                            <input type="text" class="color-text" data-style="background" value="#1e293b" placeholder="#1e293b">
                        </div>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="text-position">Position du texte</label>
                        <select id="text-position" data-style="text-position">
                            <option value="center">Centre</option>
                            <option value="top-left">Haut Gauche</option>
                            <option value="top-center">Haut Centre</option>
                            <option value="top-right">Haut Droite</option>
                            <option value="center-left">Centre Gauche</option>
                            <option value="center-right">Centre Droite</option>
                            <option value="bottom-left">Bas Gauche</option>
                            <option value="bottom-center">Bas Centre</option>
                            <option value="bottom-right">Bas Droite</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="text-margin">Marge du texte</label>
                        <div class="range-input-group">
                            <input type="range" id="text-margin" data-style="text-margin" min="0" max="100" value="20">
                            <input type="number" class="size-number" data-style="text-margin" min="0" max="100" value="20">
                            <span class="range-value" id="text-margin-value">20px</span>
                        </div>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Options d'affichage</label>
                        <div class="checkbox-group">
                            <input type="checkbox" id="transparent" data-style="transparent">
                            <label for="transparent">Fond transparent</label>
                        </div>
                        <div class="checkbox-group">
                            <input type="checkbox" id="team-background" data-style="team-background">
                            <label for="team-background">Activer le fond des équipes</label>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Contrôles</label>
                        <div class="checkbox-group">
                            <input type="checkbox" id="hide-controls" data-style="hide-controls">
                            <label for="hide-controls">Masquer les contrôles</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- URLs Tab -->
        <div id="tab-urls" class="tab-content">
            <div class="config-section">
                <h2 class="section-title">
                    <i class="fas fa-users"></i> URLs API - ÉQUIPE VERTE
                </h2>
                
                <div class="urls-grid">
                    <!-- Score Équipe Verte -->
                    <div class="url-card">
                        <div class="url-icon" style="background: linear-gradient(135deg, #10b981, #059669);">
                            <i class="fas fa-plus"></i>
                        </div>
                        <h3>Ajouter +1 (Vert)</h3>
                        <p>Augmente le score de 1</p>
                        <button class="url-btn" onclick="copyApiUrl('/api.php?token=<?php echo $token; ?>&module=teams&action=add-score&value={\"team\":\"green\",\"value\":\"1\"}')">
                            <i class="fas fa-copy"></i>
                            +1 Vert
                        </button>
                    </div>
                    
                    <div class="url-card">
                        <div class="url-icon" style="background: linear-gradient(135deg, #10b981, #059669);">
                            <i class="fas fa-plus"></i>
                        </div>
                        <h3>Ajouter +5 (Vert)</h3>
                        <p>Augmente le score de 5</p>
                        <button class="url-btn" onclick="copyApiUrl('/api.php?token=<?php echo $token; ?>&module=teams&action=add-score&value={\"team\":\"green\",\"value\":\"5\"}')">
                            <i class="fas fa-copy"></i>
                            +5 Vert
                        </button>
                    </div>
                    
                    <div class="url-card">
                        <div class="url-icon" style="background: linear-gradient(135deg, #10b981, #059669);">
                            <i class="fas fa-plus"></i>
                        </div>
                        <h3>Ajouter +10 (Vert)</h3>
                        <p>Augmente le score de 10</p>
                        <button class="url-btn" onclick="copyApiUrl('/api.php?token=<?php echo $token; ?>&module=teams&action=add-score&value={\"team\":\"green\",\"value\":\"10\"}')">
                            <i class="fas fa-copy"></i>
                            +10 Vert
                        </button>
                    </div>
                    
                    <div class="url-card">
                        <div class="url-icon" style="background: linear-gradient(135deg, #dc2626, #b91c1c);">
                            <i class="fas fa-minus"></i>
                        </div>
                        <h3>Retirer -1 (Vert)</h3>
                        <p>Diminue le score de 1</p>
                        <button class="url-btn" onclick="copyApiUrl('/api.php?token=<?php echo $token; ?>&module=teams&action=add-score&value={\"team\":\"green\",\"value\":\"-1\"}')">
                            <i class="fas fa-copy"></i>
                            -1 Vert
                        </button>
                    </div>
                    
                    <div class="url-card">
                        <div class="url-icon" style="background: linear-gradient(135deg, #dc2626, #b91c1c);">
                            <i class="fas fa-minus"></i>
                        </div>
                        <h3>Retirer -5 (Vert)</h3>
                        <p>Diminue le score de 5</p>
                        <button class="url-btn" onclick="copyApiUrl('/api.php?token=<?php echo $token; ?>&module=teams&action=add-score&value={\"team\":\"green\",\"value\":\"-5\"}')">
                            <i class="fas fa-copy"></i>
                            -5 Vert
                        </button>
                    </div>
                    
                    <div class="url-card">
                        <div class="url-icon" style="background: linear-gradient(135deg, #dc2626, #b91c1c);">
                            <i class="fas fa-minus"></i>
                        </div>
                        <h3>Retirer -10 (Vert)</h3>
                        <p>Diminue le score de 10</p>
                        <button class="url-btn" onclick="copyApiUrl('/api.php?token=<?php echo $token; ?>&module=teams&action=add-score&value={\"team\":\"green\",\"value\":\"-10\"}')">
                            <i class="fas fa-copy"></i>
                            -10 Vert
                        </button>
                    </div>
                    
                    <div class="url-card">
                        <div class="url-icon" style="background: linear-gradient(135deg, #0ea5e9, #0284c7);">
                            <i class="fas fa-sync"></i>
                        </div>
                        <h3>Reset (Vert)</h3>
                        <p>Remet à zéro</p>
                        <button class="url-btn" onclick="copyApiUrl('/api.php?token=<?php echo $token; ?>&module=teams&action=reset-score&value=green')">
                            <i class="fas fa-copy"></i>
                            Reset Vert
                        </button>
                    </div>
                </div>
            </div>

            <div class="config-section" style="margin-top: var(--spacing-xl); background: var(--bg-glass);">
                <h2 class="section-title">
                    <i class="fas fa-users"></i> URLs API - ÉQUIPE ROUGE
                </h2>
                
                <div class="urls-grid">
                    <!-- Score Équipe Rouge -->
                    <div class="url-card">
                        <div class="url-icon" style="background: linear-gradient(135deg, #ef4444, #dc2626);">
                            <i class="fas fa-plus"></i>
                        </div>
                        <h3>Ajouter +1 (Rouge)</h3>
                        <p>Augmente le score de 1</p>
                        <button class="url-btn" onclick="copyApiUrl('/api.php?token=<?php echo $token; ?>&module=teams&action=add-score&value={\"team\":\"red\",\"value\":\"1\"}')">
                            <i class="fas fa-copy"></i>
                            +1 Rouge
                        </button>
                    </div>
                    
                    <div class="url-card">
                        <div class="url-icon" style="background: linear-gradient(135deg, #ef4444, #dc2626);">
                            <i class="fas fa-plus"></i>
                        </div>
                        <h3>Ajouter +5 (Rouge)</h3>
                        <p>Augmente le score de 5</p>
                        <button class="url-btn" onclick="copyApiUrl('/api.php?token=<?php echo $token; ?>&module=teams&action=add-score&value={\"team\":\"red\",\"value\":\"5\"}')">
                            <i class="fas fa-copy"></i>
                            +5 Rouge
                        </button>
                    </div>
                    
                    <div class="url-card">
                        <div class="url-icon" style="background: linear-gradient(135deg, #ef4444, #dc2626);">
                            <i class="fas fa-plus"></i>
                        </div>
                        <h3>Ajouter +10 (Rouge)</h3>
                        <p>Augmente le score de 10</p>
                        <button class="url-btn" onclick="copyApiUrl('/api.php?token=<?php echo $token; ?>&module=teams&action=add-score&value={\"team\":\"red\",\"value\":\"10\"}')">
                            <i class="fas fa-copy"></i>
                            +10 Rouge
                        </button>
                    </div>
                    
                    <div class="url-card">
                        <div class="url-icon" style="background: linear-gradient(135deg, #991b1b, #7f1d1d);">
                            <i class="fas fa-minus"></i>
                        </div>
                        <h3>Retirer -1 (Rouge)</h3>
                        <p>Diminue le score de 1</p>
                        <button class="url-btn" onclick="copyApiUrl('/api.php?token=<?php echo $token; ?>&module=teams&action=add-score&value={\"team\":\"red\",\"value\":\"-1\"}')">
                            <i class="fas fa-copy"></i>
                            -1 Rouge
                        </button>
                    </div>
                    
                    <div class="url-card">
                        <div class="url-icon" style="background: linear-gradient(135deg, #991b1b, #7f1d1d);">
                            <i class="fas fa-minus"></i>
                        </div>
                        <h3>Retirer -5 (Rouge)</h3>
                        <p>Diminue le score de 5</p>
                        <button class="url-btn" onclick="copyApiUrl('/api.php?token=<?php echo $token; ?>&module=teams&action=add-score&value={\"team\":\"red\",\"value\":\"-5\"}')">
                            <i class="fas fa-copy"></i>
                            -5 Rouge
                        </button>
                    </div>
                    
                    <div class="url-card">
                        <div class="url-icon" style="background: linear-gradient(135deg, #991b1b, #7f1d1d);">
                            <i class="fas fa-minus"></i>
                        </div>
                        <h3>Retirer -10 (Rouge)</h3>
                        <p>Diminue le score de 10</p>
                        <button class="url-btn" onclick="copyApiUrl('/api.php?token=<?php echo $token; ?>&module=teams&action=add-score&value={\"team\":\"red\",\"value\":\"-10\"}')">
                            <i class="fas fa-copy"></i>
                            -10 Rouge
                        </button>
                    </div>
                    
                    <div class="url-card">
                        <div class="url-icon" style="background: linear-gradient(135deg, #0ea5e9, #0284c7);">
                            <i class="fas fa-sync"></i>
                        </div>
                        <h3>Reset (Rouge)</h3>
                        <p>Remet à zéro</p>
                        <button class="url-btn" onclick="copyApiUrl('/api.php?token=<?php echo $token; ?>&module=teams&action=reset-score&value=red')">
                            <i class="fas fa-copy"></i>
                            Reset Rouge
                        </button>
                    </div>
                </div>
            </div>

            <div class="config-section" style="margin-top: var(--spacing-xl); background: var(--bg-tertiary);">
                <h2 class="section-title">
                    <i class="fas fa-gamepad"></i> URLs API - ACTIONS GÉNÉRALES
                </h2>
                
                <div class="urls-grid">
                    <div class="url-card">
                        <div class="url-icon" style="background: linear-gradient(135deg, #8b5cf6, #7c3aed);">
                            <i class="fas fa-redo"></i>
                        </div>
                        <h3>Reset Tout</h3>
                        <p>Remet tous les scores à zéro</p>
                        <button class="url-btn" onclick="copyApiUrl('/api.php?token=<?php echo $token; ?>&module=teams&action=reset-all')">
                            <i class="fas fa-copy"></i>
                            Reset Tout
                        </button>
                    </div>
                    
                    <div class="url-card">
                        <div class="url-icon" style="background: linear-gradient(135deg, #f59e0b, #d97706);">
                            <i class="fas fa-exchange-alt"></i>
                        </div>
                        <h3>Échanger Scores</h3>
                        <p>Inverse les scores des équipes</p>
                        <button class="url-btn" onclick="copyApiUrl('/api.php?token=<?php echo $token; ?>&module=teams&action=swap-scores')">
                            <i class="fas fa-copy"></i>
                            Échanger
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Buttons -->
        <div class="button-group">
            <button class="btn btn-primary" id="save-btn">
                <i class="fas fa-save"></i>
                Sauvegarder
            </button>
            <button class="btn btn-secondary" id="reset-btn">
                <i class="fas fa-undo"></i>
                Réinitialiser
            </button>
            <button class="btn btn-secondary" onclick="window.open('/modules/team-battle.php?token=<?php echo $token; ?>&control=true', '_blank')">
                <i class="fas fa-external-link-alt"></i>
                Ouvrir Teams
            </button>
        </div>

        <div class="save-info" style="text-align: center; margin-top: var(--spacing-md); color: var(--text-muted);">
            <i class="fas fa-info-circle"></i> Les modifications sont sauvegardées automatiquement
        </div>
    </div>

    <script>
        // Variables globales
        const token = '<?php echo $token; ?>';
        let autoSaveTimeout = null;

        // Fonction pour collecter les styles
        function collectStyles() {
            const fontSelect = document.getElementById('font-family');
            let selectedFont = 'Arial, Helvetica, sans-serif';
            
            if (fontSelect && fontSelect.selectedIndex >= 0) {
                const selectedOption = fontSelect.options[fontSelect.selectedIndex];
                if (selectedOption && selectedOption.value) {
                    selectedFont = selectedOption.value;
                }
            }
            
            const groupedStyles = {
                green: {
                    name: getInputValue('green-name', 'ÉQUIPE VERTE'),
                    color: getInputValue('green-color', '#10b981'),
                    'score-color': getInputValue('green-score-color', '#10b981'),
                    size: getInputValue('green-size', '80'),
                    stroke: getInputValue('green-stroke', '#000000'),
                    shadow: getInputValue('green-shadow', false),
                    'background-color': getInputValue('green-background-color', '#000000')
                },
                red: {
                    name: getInputValue('red-name', 'ÉQUIPE ROUGE'),
                    color: getInputValue('red-color', '#ef4444'),
                    'score-color': getInputValue('red-score-color', '#ef4444'),
                    size: getInputValue('red-size', '80'),
                    stroke: getInputValue('red-stroke', '#000000'),
                    shadow: getInputValue('red-shadow', false),
                    'background-color': getInputValue('red-background-color', '#000000')
                },
                general: {
                    'font-family': selectedFont,
                    background: getInputValue('background', '#1e293b'),
                    'text-position': getInputValue('text-position', 'center'),
                    'text-margin': getInputValue('text-margin', '0'),
                    transparent: getInputValue('transparent', false),
                    'team-background': getInputValue('team-background', false)
                },
                options: {
                    'hide-controls': getInputValue('hide-controls', false)
                },
                meta: {
                    version: '2.0',
                    'collected-at': Date.now(),
                    'structure': 'grouped'
                }
            };
            
            return groupedStyles;
        }

        // Fonction pour récupérer la valeur d'un input
        function getInputValue(inputId, defaultValue) {
            const mainInput = document.getElementById(inputId) || document.querySelector(`[data-style="${inputId}"]`);
            if (mainInput) {
                if (mainInput.type === 'checkbox') {
                    return mainInput.checked;
                } else if (mainInput.tagName === 'SELECT' && mainInput.id === 'font-family') {
                    const selectedOption = mainInput.options[mainInput.selectedIndex];
                    const value = selectedOption ? selectedOption.value : defaultValue;
                    return value;
                } else {
                    const value = mainInput.value || defaultValue;
                    return value;
                }
            }
            
            const priorityInputs = document.querySelectorAll(`.color-text[data-style="${inputId}"], .size-number[data-style="${inputId}"]`);
            if (priorityInputs.length > 0) {
                for (const priorityInput of priorityInputs) {
                    if (priorityInput.value) {
                        return priorityInput.value;
                    }
                }
            }
            
            return defaultValue;
        }

        // Fonction pour appliquer les styles à l'aperçu
        function applyPreviewStyles(styles) {
            const previewGreenName = document.getElementById('preview-green-name');
            const previewGreenScore = document.getElementById('preview-green-score');
            const previewRedName = document.getElementById('preview-red-name');
            const previewRedScore = document.getElementById('preview-red-score');
            
            // Extraire les styles
            const extracted = extractStylesFromData(styles);
            
            // Styles Équipe Verte
            if (previewGreenName && extracted.greenName) {
                previewGreenName.textContent = extracted.greenName;
            }
            if (previewGreenScore) {
                if (extracted.greenColor) {
                    previewGreenName.style.color = extracted.greenColor;
                }
                if (extracted.greenScoreColor) {
                    previewGreenScore.style.color = extracted.greenScoreColor;
                }
                if (extracted.greenSize) {
                    previewGreenScore.style.fontSize = `${extracted.greenSize}px`;
                }
                if (extracted.greenStroke) {
                    previewGreenScore.style.webkitTextStroke = `2px ${extracted.greenStroke}`;
                }
                if (extracted.greenShadow) {
                    previewGreenScore.style.textShadow = '3px 3px 6px rgba(0,0,0,0.8)';
                } else {
                    previewGreenScore.style.textShadow = 'none';
                }
            }
            
            // Styles Équipe Rouge
            if (previewRedName && extracted.redName) {
                previewRedName.textContent = extracted.redName;
            }
            if (previewRedScore) {
                if (extracted.redColor) {
                    previewRedName.style.color = extracted.redColor;
                }
                if (extracted.redScoreColor) {
                    previewRedScore.style.color = extracted.redScoreColor;
                }
                if (extracted.redSize) {
                    previewRedScore.style.fontSize = `${extracted.redSize}px`;
                }
                if (extracted.redStroke) {
                    previewRedScore.style.webkitTextStroke = `2px ${extracted.redStroke}`;
                }
                if (extracted.redShadow) {
                    previewRedScore.style.textShadow = '3px 3px 6px rgba(0,0,0,0.8)';
                } else {
                    previewRedScore.style.textShadow = 'none';
                }
            }
            
            // Police
            if (extracted.fontFamily) {
                if (previewGreenName) previewGreenName.style.fontFamily = extracted.fontFamily;
                if (previewGreenScore) previewGreenScore.style.fontFamily = extracted.fontFamily;
                if (previewRedName) previewRedName.style.fontFamily = extracted.fontFamily;
                if (previewRedScore) previewRedScore.style.fontFamily = extracted.fontFamily;
            }
            
            // Arrière-plan
            const container = document.querySelector('.preview-container');
            if (extracted.transparent) {
                container.style.background = 'transparent';
                container.style.border = '2px dashed rgba(255, 255, 255, 0.3)';
            } else if (extracted.background) {
                container.style.background = extracted.background;
                container.style.border = '1px solid var(--border-color)';
            }
            
            // Fond des équipes
            const greenTeam = document.querySelector('.preview-team');
            const redTeam = document.querySelector('.preview-team:last-child');
            if (extracted.teamBackground) {
                if (greenTeam) {
                    const greenBgColor = extracted.greenBackgroundColor || '#000000';
                    greenTeam.style.background = greenBgColor;
                    greenTeam.style.padding = '15px';
                    greenTeam.style.borderRadius = '10px';
                }
                if (redTeam) {
                    const redBgColor = extracted.redBackgroundColor || '#000000';
                    redTeam.style.background = redBgColor;
                    redTeam.style.padding = '15px';
                    redTeam.style.borderRadius = '10px';
                }
            } else {
                if (greenTeam) {
                    greenTeam.style.background = 'transparent';
                    greenTeam.style.padding = '0';
                    greenTeam.style.borderRadius = '0';
                }
                if (redTeam) {
                    redTeam.style.background = 'transparent';
                    redTeam.style.padding = '0';
                    redTeam.style.borderRadius = '0';
                }
            }
        }

        // Fonction pour extraire les styles
        function extractStylesFromData(data) {
            const extracted = {};
            
            if (data.green) {
                extracted.greenName = data.green.name;
                extracted.greenColor = data.green.color;
                extracted.greenScoreColor = data.green['score-color'] || data.green.color;
                extracted.greenSize = data.green.size;
                extracted.greenStroke = data.green.stroke;
                extracted.greenShadow = data.green.shadow;
                extracted.greenBackgroundColor = data.green['background-color'];
            }
            if (data.red) {
                extracted.redName = data.red.name;
                extracted.redColor = data.red.color;
                extracted.redScoreColor = data.red['score-color'] || data.red.color;
                extracted.redSize = data.red.size;
                extracted.redStroke = data.red.stroke;
                extracted.redShadow = data.red.shadow;
                extracted.redBackgroundColor = data.red['background-color'];
            }
            if (data.general) {
                extracted.fontFamily = data.general['font-family'];
                extracted.background = data.general.background;
                extracted.textPosition = data.general['text-position'];
                extracted.textMargin = data.general['text-margin'];
                extracted.transparent = data.general.transparent;
                extracted.teamBackground = data.general['team-background'];
            }
            if (data.options) {
                extracted.hideControls = data.options['hide-controls'];
            }
            
            return extracted;
        }

        // Fonction pour sauvegarder automatiquement
        function autoSaveStyles(styles) {
            if (autoSaveTimeout) {
                clearTimeout(autoSaveTimeout);
            }
            
            showAutoSaveNotification();
            
            autoSaveTimeout = setTimeout(async () => {
                try {
                    const response = await fetch(`/api.php?token=${token}&module=teams-style&action=save&value=${encodeURIComponent(JSON.stringify(styles))}`);
                    const result = await response.json();
                    
                    if (result.success) {
                        notifyAllTeamsPages(styles);
                        localStorage.setItem('forceTeamsStyleUpdate', Date.now());
                    } else {
                        showAutoSaveError();
                    }
                } catch (error) {
                    showAutoSaveError();
                }
            }, 50); // Réduit à 50ms pour une sauvegarde instantanée
        }

        // Fonction pour notifier toutes les pages team-battle.php
        function notifyAllTeamsPages(styles) {
            const timestamp = Date.now() + '_' + Math.random();
            localStorage.setItem('realtimeTeamsStyles', JSON.stringify(styles));
            localStorage.setItem('teamsStylesTimestamp', timestamp);
            
            if (window.BroadcastChannel) {
                try {
                    const channel = new BroadcastChannel('teams_styles_channel');
                    // Envoyer immédiatement et répéter pour assurer la réception
                    channel.postMessage({
                        type: 'teamsStylesUpdate',
                        styles: styles,
                        timestamp: timestamp
                    });
                    // Répéter après 5ms pour assurer la réception
                    setTimeout(() => {
                        channel.postMessage({
                            type: 'teamsStylesUpdate',
                            styles: styles,
                            timestamp: timestamp + '_retry'
                        });
                    }, 5);
                } catch (error) {}
            }
            
            try {
                // Envoyer immédiatement via postMessage
                window.postMessage({
                    type: 'teamsStylesUpdate',
                    styles: styles,
                    timestamp: timestamp
                }, '*');
                // Répéter après 5ms
                setTimeout(() => {
                    window.postMessage({
                        type: 'teamsStylesUpdate',
                        styles: styles,
                        timestamp: timestamp + '_post_retry'
                    }, '*');
                }, 5);
            } catch (error) {}
            
            localStorage.setItem('forceTeamsStyleUpdate', timestamp + '_force');
        }

        // Fonction pour afficher la notification d'auto-save
        function showAutoSaveNotification() {
            document.querySelectorAll('.auto-save-notification').forEach(notif => notif.remove());
            
            const notification = document.createElement('div');
            notification.className = 'auto-save-notification';
            notification.innerHTML = '<i class="fas fa-save"></i> Auto save...';
            document.body.appendChild(notification);
            
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.remove();
                }
            }, 1500);
        }

        // Fonction pour afficher une erreur d'auto-save
        function showAutoSaveError() {
            document.querySelectorAll('.auto-save-notification').forEach(notif => notif.remove());
            
            const notification = document.createElement('div');
            notification.className = 'auto-save-notification';
            notification.style.background = 'linear-gradient(135deg, #ef4444, #dc2626)';
            notification.innerHTML = '<i class="fas fa-exclamation-triangle"></i> Erreur auto save';
            document.body.appendChild(notification);
            
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.remove();
                }
            }, 3000);
        }

        // Fonction pour sauvegarder manuellement
        async function saveStyles() {
            const styles = collectStyles();
            const saveBtn = document.getElementById('save-btn');
            
            saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sauvegarde...';
            saveBtn.disabled = true;
            
            try {
                const response = await fetch(`/api.php?token=${token}&module=teams-style&action=save&value=${encodeURIComponent(JSON.stringify(styles))}`);
                const result = await response.json();
                
                if (result.success) {
                    saveBtn.innerHTML = '<i class="fas fa-check"></i> Sauvegardé !';
                    notifyAllTeamsPages(styles);
                    
                    // Afficher notification
                    const notification = document.createElement('div');
                    notification.className = 'save-notification';
                    notification.innerHTML = '<i class="fas fa-check-circle"></i> Styles sauvegardés avec succès !';
                    document.body.appendChild(notification);
                    
                    setTimeout(() => {
                        notification.remove();
                        saveBtn.innerHTML = '<i class="fas fa-save"></i> Sauvegarder';
                        saveBtn.disabled = false;
                    }, 2000);
                } else {
                    throw new Error(result.error || 'Erreur lors de la sauvegarde');
                }
            } catch (error) {
                saveBtn.innerHTML = '<i class="fas fa-exclamation-triangle"></i> Erreur';
                saveBtn.disabled = false;
                showAutoSaveError();
            }
        }

        // Fonction pour réinitialiser les styles
        function resetStyles() {
            document.getElementById('green-name').value = 'ÉQUIPE VERTE';
            document.getElementById('green-color').value = '#10b981';
            document.getElementById('green-score-color').value = '#10b981';
            document.getElementById('green-size').value = '80';
            document.getElementById('green-stroke').value = '#000000';
            document.getElementById('green-shadow').checked = false;
            document.getElementById('green-background-color').value = '#000000';
            
            document.getElementById('red-name').value = 'ÉQUIPE ROUGE';
            document.getElementById('red-color').value = '#ef4444';
            document.getElementById('red-score-color').value = '#ef4444';
            document.getElementById('red-size').value = '80';
            document.getElementById('red-stroke').value = '#000000';
            document.getElementById('red-shadow').checked = false;
            document.getElementById('red-background-color').value = '#000000';
            
            document.getElementById('font-family').value = 'Arial, Helvetica, sans-serif';
            document.getElementById('background').value = '#1e293b';
            document.getElementById('text-position').value = 'center';
            document.getElementById('text-margin').value = '0';
            document.getElementById('transparent').checked = false;
            document.getElementById('team-background').checked = false;
            document.getElementById('hide-controls').checked = false;
            
            // Mettre à jour les inputs secondaires
            document.querySelectorAll('.color-text').forEach(input => {
                const mainInput = input.parentElement.querySelector('input[type="color"]');
                if (mainInput) input.value = mainInput.value;
            });
            
            document.querySelectorAll('.size-number').forEach(input => {
                const mainInput = input.parentElement.querySelector('input[type="range"]');
                if (mainInput) input.value = mainInput.value;
            });
            
            updateRangeValues();
            const styles = collectStyles();
            applyPreviewStyles(styles);
            autoSaveStyles(styles);
        }

        // Fonction pour charger les styles
        async function loadStyles() {
            try {
                const response = await fetch(`/api.php?token=${token}&module=teams-style&action=get`);
                const result = await response.json();
                
                if (result.success && result.data) {
                    let styles = result.data;
                    
                    if (styles.green || styles.red || styles.general || styles.options) {
                        loadGroupedStyles(styles);
                    }
                    
                    applyPreviewStyles(styles);
                }
            } catch (error) {
                console.error('Erreur lors du chargement des styles:', error);
            }
        }

        // Fonction pour charger les styles groupés
        function loadGroupedStyles(styles) {
            if (styles.green) {
                setInputValue('green-name', styles.green.name);
                setInputValue('green-color', styles.green.color);
                setInputValue('green-score-color', styles.green['score-color'] || styles.green.color);
                setInputValue('green-size', styles.green.size);
                setInputValue('green-stroke', styles.green.stroke);
                setInputValue('green-shadow', styles.green.shadow);
                setInputValue('green-background-color', styles.green['background-color'] || '#000000');
            }
            
            if (styles.red) {
                setInputValue('red-name', styles.red.name);
                setInputValue('red-color', styles.red.color);
                setInputValue('red-score-color', styles.red['score-color'] || styles.red.color);
                setInputValue('red-size', styles.red.size);
                setInputValue('red-stroke', styles.red.stroke);
                setInputValue('red-shadow', styles.red.shadow);
                setInputValue('red-background-color', styles.red['background-color'] || '#000000');
            }
            
            if (styles.general) {
                setInputValue('font-family', styles.general['font-family']);
                setInputValue('background', styles.general.background);
                setInputValue('text-position', styles.general['text-position']);
                setInputValue('text-margin', styles.general['text-margin']);
                setInputValue('transparent', styles.general.transparent);
                setInputValue('team-background', styles.general['team-background']);
            }
            
            if (styles.options) {
                setInputValue('hide-controls', styles.options['hide-controls']);
            }
            
            updateRangeValues();
        }

        // Fonction pour définir la valeur d'un input
        function setInputValue(property, value) {
            if (value === undefined || value === null) return;
            
            const mainInput = document.querySelector(`[data-style="${property}"], #${property}`);
            if (mainInput) {
                if (mainInput.type === 'checkbox') {
                    mainInput.checked = (value === true || value === 'true' || value === 1);
                } else if (property === 'font-family' && mainInput.tagName === 'SELECT') {
                    const option = Array.from(mainInput.options).find(opt => opt.value === value);
                    if (option) {
                        mainInput.value = value;
                        option.selected = true;
                    }
                } else {
                    mainInput.value = value;
                }
            }
            
            const priorityInputs = document.querySelectorAll(`.color-text[data-style="${property}"], .size-number[data-style="${property}"]`);
            priorityInputs.forEach(priorityInput => {
                if (priorityInput.type !== 'checkbox') {
                    priorityInput.value = value;
                }
            });
        }

        // Fonction pour copier une URL API
        function copyApiUrl(url) {
            const fullUrl = window.location.origin + url;
            navigator.clipboard.writeText(fullUrl).then(() => {
                // Afficher une notification
                const notification = document.createElement('div');
                notification.className = 'save-notification';
                notification.innerHTML = '<i class="fas fa-check"></i> URL copiée !';
                document.body.appendChild(notification);
                
                setTimeout(() => {
                    notification.remove();
                }, 2000);
            }).catch(err => {
                console.error('Erreur lors de la copie:', err);
            });
        }

        // Fonction pour mettre à jour les valeurs des ranges
        function updateRangeValues() {
            document.getElementById('green-size-value').textContent = document.getElementById('green-size').value + 'px';
            document.getElementById('red-size-value').textContent = document.getElementById('red-size').value + 'px';
            document.getElementById('text-margin-value').textContent = document.getElementById('text-margin').value + 'px';
        }

        // Fonction pour charger les vraies valeurs des équipes
        async function loadTeamsData() {
            try {
                const response = await fetch(`/api.php?token=${token}&module=teams&action=get`);
                const result = await response.json();
                
                if (result.success && result.data) {
                    const greenScore = result.data.green?.score || 0;
                    const redScore = result.data.red?.score || 0;
                    
                    // Mettre à jour l'aperçu
                    const previewGreenScore = document.getElementById('preview-green-score');
                    const previewRedScore = document.getElementById('preview-red-score');
                    
                    if (previewGreenScore) {
                        previewGreenScore.textContent = greenScore;
                    }
                    if (previewRedScore) {
                        previewRedScore.textContent = redScore;
                    }
                    
                    // Réappliquer les styles actuels après la mise à jour des scores
                    const currentStyles = collectStyles();
                    applyPreviewStyles(currentStyles);
                }
            } catch (error) {
                console.error('Erreur lors du chargement des données teams:', error);
            }
        }

        // Gestion des onglets
        function initTabs() {
            const tabBtns = document.querySelectorAll('.tab-btn');
            const tabContents = document.querySelectorAll('.tab-content');
            
            tabBtns.forEach(btn => {
                btn.addEventListener('click', () => {
                    const targetTab = btn.dataset.tab;
                    
                    tabBtns.forEach(b => b.classList.remove('active'));
                    tabContents.forEach(c => c.classList.remove('active'));
                    
                    btn.classList.add('active');
                    document.getElementById(`tab-${targetTab}`).classList.add('active');
                });
            });
        }

        // Synchronisation des inputs
        function initInputSync() {
            // Synchroniser les inputs color et text
            document.querySelectorAll('.color-input-group').forEach(group => {
                const colorInput = group.querySelector('input[type="color"]');
                const textInput = group.querySelector('.color-text');
                
                if (colorInput && textInput) {
                    colorInput.addEventListener('input', () => {
                        textInput.value = colorInput.value;
                        // Déclencher l'événement input sur le text input
                        textInput.dispatchEvent(new Event('input', { bubbles: true }));
                    });
                    
                    textInput.addEventListener('input', () => {
                        if (textInput.value.match(/^#[0-9A-Fa-f]{6}$/)) {
                            colorInput.value = textInput.value;
                        }
                    });
                }
            });
            
            // Synchroniser les inputs range et number
            document.querySelectorAll('.range-input-group').forEach(group => {
                const rangeInput = group.querySelector('input[type="range"]');
                const numberInput = group.querySelector('.size-number');
                const valueSpan = group.querySelector('.range-value');
                
                if (rangeInput && numberInput) {
                    rangeInput.addEventListener('input', () => {
                        numberInput.value = rangeInput.value;
                        if (valueSpan) {
                            const suffix = valueSpan.id.includes('margin') || valueSpan.id.includes('size') || valueSpan.id.includes('offset') ? 'px' : '';
                            valueSpan.textContent = rangeInput.value + suffix;
                        }
                        // Déclencher l'événement input sur le number input
                        numberInput.dispatchEvent(new Event('input', { bubbles: true }));
                    });
                    
                    numberInput.addEventListener('input', () => {
                        const min = parseInt(rangeInput.min);
                        const max = parseInt(rangeInput.max);
                        const value = parseInt(numberInput.value);
                        
                        if (value >= min && value <= max) {
                            rangeInput.value = numberInput.value;
                            if (valueSpan) {
                                const suffix = valueSpan.id.includes('margin') || valueSpan.id.includes('size') || valueSpan.id.includes('offset') ? 'px' : '';
                                valueSpan.textContent = numberInput.value + suffix;
                            }
                        }
                    });
                }
            });
        }

        // Initialisation
        document.addEventListener('DOMContentLoaded', () => {
            initTabs();
            initInputSync();
            
            // Charger les styles et les données en parallèle
            Promise.all([
                loadStyles(),
                loadTeamsData()
            ]).then(() => {
                // Une fois les styles et données chargés, réappliquer les styles
                setTimeout(() => {
                    const styles = collectStyles();
                    applyPreviewStyles(styles);
                    notifyAllTeamsPages(styles);
                }, 20); // Réduit à 20ms pour une application quasi-instantanée
            });
            
            // Recharger les données toutes les 100ms pour une synchronisation ultra-rapide
            setInterval(loadTeamsData, 100);
            
            // Forcer l'envoi initial des styles après un court délai
            setTimeout(() => {
                const initialStyles = collectStyles();
                notifyAllTeamsPages(initialStyles);
            }, 50); // Réduit à 50ms pour une initialisation ultra-rapide
            
            // Écouter les changements
            document.querySelectorAll('[data-style], .color-text, .size-number, input[type="checkbox"], #font-family, #text-position').forEach(input => {
                const handleChange = () => {
                    const styles = collectStyles();
                    applyPreviewStyles(styles);
                    notifyAllTeamsPages(styles);
                    autoSaveStyles(styles);
                    
                    // Forcer la mise à jour de l'aperçu pour refléter les changements
                    setTimeout(() => {
                        loadTeamsData();
                    }, 10);
                };
                
                if (input.tagName === 'SELECT') {
                    input.addEventListener('change', handleChange);
                } else if (input.type === 'checkbox') {
                    input.addEventListener('change', handleChange);
                } else {
                    input.addEventListener('input', handleChange);
                }
            });
            
            // Boutons
            document.getElementById('save-btn').addEventListener('click', saveStyles);
            document.getElementById('reset-btn').addEventListener('click', resetStyles);
        });
    </script>
</body>
</html>