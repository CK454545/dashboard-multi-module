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
    <title>Configuration Wins Counter - MyFull Agency</title>
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
        input[type="number"] {
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
        input[type="number"]:focus {
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

        #preview-wins {
            font-size: 64px;
            font-weight: 700;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            margin-bottom: -0.25rem;
        }

        #preview-multi {
            font-size: 48px;
            font-weight: 600;
            letter-spacing: 0.03em;
            text-transform: uppercase;
            margin-top: 0.5rem;
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

        /* OBS Guide */
        .obs-guide {
            display: flex;
            flex-direction: column;
            gap: var(--spacing-lg);
        }

        .obs-step {
            display: flex;
            gap: var(--spacing-md);
            align-items: flex-start;
        }

        .step-number {
            width: 32px;
            height: 32px;
            background: var(--primary-color);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            flex-shrink: 0;
        }

        .step-content h4 {
            font-size: 1rem;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: var(--spacing-xs);
        }

        .step-content p {
            font-size: 0.875rem;
            color: var(--text-secondary);
            margin: 0;
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
        }
    </style>
</head>
<body>
    <div class="config-container">
        <!-- Header -->
        <div class="config-header">
            <h1 class="config-title">🏆 Configuration Wins Counter</h1>
            <p class="config-subtitle">Personnalisez l'apparence de votre compteur de victoires</p>
        </div>

        <!-- Preview -->
        <div class="preview-container">
            <div class="preview-title">Aperçu en temps réel</div>
            <div class="text-container">
                <div id="preview-wins" style="color: #ffffff;">WINS: 0/20</div>
                <div id="preview-multi" style="visibility: visible; color: #ffffff;">X1 ACTIF</div>
            </div>
        </div>

        <!-- Tabs -->
        <div class="tabs">
            <button class="tab-btn active" data-tab="wins">
                <i class="fas fa-trophy"></i> Wins Counter
            </button>
            <button class="tab-btn" data-tab="multi">
                <i class="fas fa-times"></i> Multiplicateur
            </button>
            <button class="tab-btn" data-tab="general">
                <i class="fas fa-cog"></i> Général
            </button>
            <button class="tab-btn" data-tab="urls">
                <i class="fas fa-link"></i> URLs
            </button>
        </div>

        <!-- Tab Contents -->
        <!-- Wins Tab -->
        <div id="tab-wins" class="tab-content active">
            <div class="config-section">
                <h2 class="section-title">
                    <i class="fas fa-palette"></i> Styles du Compteur
                </h2>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="wins-color">Couleur des wins</label>
                        <div class="color-input-group">
                            <input type="color" id="wins-color" data-style="wins-color" value="#ffffff">
                            <input type="text" class="color-text" data-style="wins-color" value="#ffffff" placeholder="#ffffff">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="wins-size">Taille des wins</label>
                        <div class="range-input-group">
                            <input type="range" id="wins-size" data-style="wins-size" min="12" max="200" value="64">
                            <input type="number" class="size-number" data-style="wins-size" min="12" max="200" value="64">
                            <span class="range-value" id="wins-size-value">64px</span>
                        </div>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="wins-stroke">Contour des wins</label>
                        <div class="color-input-group">
                            <input type="color" id="wins-stroke" data-style="wins-stroke" value="#000000">
                            <input type="text" class="color-text" data-style="wins-stroke" value="#000000" placeholder="#000000">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Options d'affichage</label>
                        <div class="checkbox-group">
                            <input type="checkbox" id="wins-shadow" data-style="wins-shadow">
                            <label for="wins-shadow">Ombre portée</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Multiplicateur Tab -->
        <div id="tab-multi" class="tab-content">
            <div class="config-section">
                <h2 class="section-title">
                    <i class="fas fa-times-circle"></i> Styles du Multiplicateur
                </h2>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="multi-color">Couleur du multiplicateur</label>
                        <div class="color-input-group">
                            <input type="color" id="multi-color" data-style="multi-color" value="#ffffff">
                            <input type="text" class="color-text" data-style="multi-color" value="#ffffff" placeholder="#ffffff">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="multi-size">Taille du multiplicateur</label>
                        <div class="range-input-group">
                            <input type="range" id="multi-size" data-style="multi-size" min="12" max="150" value="48">
                            <input type="number" class="size-number" data-style="multi-size" min="12" max="150" value="48">
                            <span class="range-value" id="multi-size-value">48px</span>
                        </div>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="multi-stroke">Contour du multiplicateur</label>
                        <div class="color-input-group">
                            <input type="color" id="multi-stroke" data-style="multi-stroke" value="#000000">
                            <input type="text" class="color-text" data-style="multi-stroke" value="#000000" placeholder="#000000">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="multi-vertical-offset">Position verticale</label>
                        <div class="range-input-group">
                            <input type="range" id="multi-vertical-offset" data-style="multi-vertical-offset" min="-200" max="200" value="0">
                            <input type="number" class="size-number" data-style="multi-vertical-offset" min="-200" max="200" value="0">
                            <span class="range-value" id="multi-vertical-offset-value">0px</span>
                        </div>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Options d'affichage</label>
                        <div class="checkbox-group">
                            <input type="checkbox" id="multi-shadow" data-style="multi-shadow">
                            <label for="multi-shadow">Ombre portée</label>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Visibilité</label>
                        <div class="checkbox-group">
                            <input type="checkbox" id="hide-multiplier" data-style="hide-multiplier">
                            <label for="hide-multiplier">Masquer le multiplicateur</label>
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
                        <label for="max-wins">Maximum de wins</label>
                        <div class="range-input-group">
                            <input type="range" id="max-wins" data-style="max-wins" min="1" max="100" value="20">
                            <input type="number" class="size-number" data-style="max-wins" min="1" max="100" value="20">
                            <span class="range-value" id="max-wins-value">20</span>
                        </div>
                    </div>
                    
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
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="text-margin">Marge du texte</label>
                        <div class="range-input-group">
                            <input type="range" id="text-margin" data-style="text-margin" min="0" max="100" value="20">
                            <input type="number" class="size-number" data-style="text-margin" min="0" max="100" value="20">
                            <span class="range-value" id="text-margin-value">20px</span>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Options d'affichage</label>
                        <div class="checkbox-group">
                            <input type="checkbox" id="transparent" data-style="transparent">
                            <label for="transparent">Fond transparent</label>
                        </div>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Couleur dynamique</label>
                        <div class="checkbox-group">
                            <input type="checkbox" id="color-based-on-value" data-style="color-based-on-value">
                            <label for="color-based-on-value">Couleur selon valeur : Rouge (négatif) / Vert (positif)</label>
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
                    <i class="fas fa-trophy"></i> URLs API - WINS
                </h2>
                
                <div class="urls-grid">
                    <!-- WINS Négatifs -->
                    <div class="url-card">
                        <div class="url-icon" style="background: linear-gradient(135deg, #dc2626, #b91c1c);">
                            <i class="fas fa-minus"></i>
                        </div>
                        <h3>Retirer -10 Wins</h3>
                        <p>Diminuer de 10</p>
                        <button class="url-btn" onclick="copyApiUrl('/api.php?token=<?php echo $token; ?>&module=wins&action=add-wins&value=-10')">
                            <i class="fas fa-copy"></i>
                            -10 Wins
                        </button>
                    </div>
                    
                    <div class="url-card">
                        <div class="url-icon" style="background: linear-gradient(135deg, #ef4444, #dc2626);">
                            <i class="fas fa-minus"></i>
                        </div>
                        <h3>Retirer -5 Wins</h3>
                        <p>Diminuer de 5</p>
                        <button class="url-btn" onclick="copyApiUrl('/api.php?token=<?php echo $token; ?>&module=wins&action=add-wins&value=-5')">
                            <i class="fas fa-copy"></i>
                            -5 Wins
                        </button>
                    </div>
                    
                    <div class="url-card">
                        <div class="url-icon" style="background: linear-gradient(135deg, #f97316, #ea580c);">
                            <i class="fas fa-minus"></i>
                        </div>
                        <h3>Retirer -1 Win</h3>
                        <p>Diminuer de 1</p>
                        <button class="url-btn" onclick="copyApiUrl('/api.php?token=<?php echo $token; ?>&module=wins&action=add-wins&value=-1')">
                            <i class="fas fa-copy"></i>
                            -1 Win
                        </button>
                    </div>
                    
                    <!-- RESET WINS -->
                    <div class="url-card">
                        <div class="url-icon" style="background: linear-gradient(135deg, #0ea5e9, #0284c7);">
                            <i class="fas fa-sync"></i>
                        </div>
                        <h3>Reset Wins</h3>
                        <p>Remettre à zéro</p>
                        <button class="url-btn" onclick="copyApiUrl('/api.php?token=<?php echo $token; ?>&module=wins&action=reset-wins')">
                            <i class="fas fa-copy"></i>
                            Reset
                        </button>
                    </div>
                    
                    <!-- WINS Positifs -->
                    <div class="url-card">
                        <div class="url-icon" style="background: linear-gradient(135deg, #10b981, #059669);">
                            <i class="fas fa-plus"></i>
                        </div>
                        <h3>Ajouter +1 Win</h3>
                        <p>Augmenter de 1</p>
                        <button class="url-btn" onclick="copyApiUrl('/api.php?token=<?php echo $token; ?>&module=wins&action=add-wins&value=1')">
                            <i class="fas fa-copy"></i>
                            +1 Win
                        </button>
                    </div>
                    
                    <div class="url-card">
                        <div class="url-icon" style="background: linear-gradient(135deg, #6366f1, #5855eb);">
                            <i class="fas fa-plus"></i>
                        </div>
                        <h3>Ajouter +5 Wins</h3>
                        <p>Augmenter de 5</p>
                        <button class="url-btn" onclick="copyApiUrl('/api.php?token=<?php echo $token; ?>&module=wins&action=add-wins&value=5')">
                            <i class="fas fa-copy"></i>
                            +5 Wins
                        </button>
                    </div>
                    
                    <div class="url-card">
                        <div class="url-icon" style="background: linear-gradient(135deg, #8b5cf6, #7c3aed);">
                            <i class="fas fa-plus"></i>
                        </div>
                        <h3>Ajouter +10 Wins</h3>
                        <p>Augmenter de 10</p>
                        <button class="url-btn" onclick="copyApiUrl('/api.php?token=<?php echo $token; ?>&module=wins&action=add-wins&value=10')">
                            <i class="fas fa-copy"></i>
                            +10 Wins
                        </button>
                    </div>
                </div>
            </div>

            <div class="config-section" style="margin-top: var(--spacing-xl); background: var(--bg-glass);">
                <h2 class="section-title">
                    <i class="fas fa-times-circle"></i> URLs API - MULTIPLICATEUR
                </h2>
                
                <div class="urls-grid">
                    <!-- MULTI Négatifs -->
                    <div class="url-card">
                        <div class="url-icon" style="background: linear-gradient(135deg, #991b1b, #7f1d1d);">
                            <i class="fas fa-arrow-down"></i>
                        </div>
                        <h3>Diminuer -50</h3>
                        <p>-50 au multiplicateur</p>
                        <button class="url-btn" onclick="copyApiUrl('/api.php?token=<?php echo $token; ?>&module=wins&action=add-multi&value=-50')">
                            <i class="fas fa-copy"></i>
                            -50 Multi
                        </button>
                    </div>
                    
                    <div class="url-card">
                        <div class="url-icon" style="background: linear-gradient(135deg, #dc2626, #b91c1c);">
                            <i class="fas fa-arrow-down"></i>
                        </div>
                        <h3>Diminuer -10</h3>
                        <p>-10 au multiplicateur</p>
                        <button class="url-btn" onclick="copyApiUrl('/api.php?token=<?php echo $token; ?>&module=wins&action=add-multi&value=-10')">
                            <i class="fas fa-copy"></i>
                            -10 Multi
                        </button>
                    </div>
                    
                    <div class="url-card">
                        <div class="url-icon" style="background: linear-gradient(135deg, #f97316, #ea580c);">
                            <i class="fas fa-arrow-down"></i>
                        </div>
                        <h3>Diminuer -1</h3>
                        <p>-1 au multiplicateur</p>
                        <button class="url-btn" onclick="copyApiUrl('/api.php?token=<?php echo $token; ?>&module=wins&action=add-multi&value=-1')">
                            <i class="fas fa-copy"></i>
                            -1 Multi
                        </button>
                    </div>
                    
                    <!-- RESET MULTI -->
                    <div class="url-card">
                        <div class="url-icon" style="background: linear-gradient(135deg, #0ea5e9, #0284c7);">
                            <i class="fas fa-sync"></i>
                        </div>
                        <h3>Reset Multi</h3>
                        <p>Remettre à 1</p>
                        <button class="url-btn" onclick="copyApiUrl('/api.php?token=<?php echo $token; ?>&module=wins&action=reset-multi')">
                            <i class="fas fa-copy"></i>
                            Reset
                        </button>
                    </div>
                    
                    <!-- MULTI Positifs -->
                    <div class="url-card">
                        <div class="url-icon" style="background: linear-gradient(135deg, #ec4899, #db2777);">
                            <i class="fas fa-arrow-up"></i>
                        </div>
                        <h3>Augmenter +1</h3>
                        <p>+1 au multiplicateur</p>
                        <button class="url-btn" onclick="copyApiUrl('/api.php?token=<?php echo $token; ?>&module=wins&action=add-multi&value=1')">
                            <i class="fas fa-copy"></i>
                            +1 Multi
                        </button>
                    </div>
                    
                    <div class="url-card">
                        <div class="url-icon" style="background: linear-gradient(135deg, #a855f7, #9333ea);">
                            <i class="fas fa-arrow-up"></i>
                        </div>
                        <h3>Augmenter +10</h3>
                        <p>+10 au multiplicateur</p>
                        <button class="url-btn" onclick="copyApiUrl('/api.php?token=<?php echo $token; ?>&module=wins&action=add-multi&value=10')">
                            <i class="fas fa-copy"></i>
                            +10 Multi
                        </button>
                    </div>
                    
                    <div class="url-card">
                        <div class="url-icon" style="background: linear-gradient(135deg, #d946ef, #c026d3);">
                            <i class="fas fa-arrow-up"></i>
                        </div>
                        <h3>Augmenter +50</h3>
                        <p>+50 au multiplicateur</p>
                        <button class="url-btn" onclick="copyApiUrl('/api.php?token=<?php echo $token; ?>&module=wins&action=add-multi&value=50')">
                            <i class="fas fa-copy"></i>
                            +50 Multi
                        </button>
                    </div>
                    
                    <!-- Contrôles Multi -->
                    <div class="url-card">
                        <div class="url-icon" style="background: linear-gradient(135deg, #10b981, #059669);">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <h3>Activer Multi</h3>
                        <p>Afficher le multiplicateur</p>
                        <button class="url-btn" onclick="copyApiUrl('/api.php?token=<?php echo $token; ?>&module=wins&action=set-multi-active&value=true')">
                            <i class="fas fa-copy"></i>
                            Activer
                        </button>
                    </div>
                    
                    <div class="url-card">
                        <div class="url-icon" style="background: linear-gradient(135deg, #ef4444, #dc2626);">
                            <i class="fas fa-times-circle"></i>
                        </div>
                        <h3>Désactiver Multi</h3>
                        <p>Masquer le multiplicateur</p>
                        <button class="url-btn" onclick="copyApiUrl('/api.php?token=<?php echo $token; ?>&module=wins&action=set-multi-active&value=false')">
                            <i class="fas fa-copy"></i>
                            Désactiver
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
            <button class="btn btn-secondary" onclick="window.open('/modules/win.php?token=<?php echo $token; ?>&control=true', '_blank')">
                <i class="fas fa-external-link-alt"></i>
                Ouvrir Wins
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
                wins: {
                    color: getInputValue('wins-color', '#ffffff'),
                    size: getInputValue('wins-size', '64'),
                    stroke: getInputValue('wins-stroke', '#000000'),
                    shadow: getInputValue('wins-shadow', false)
                },
                multi: {
                    color: getInputValue('multi-color', '#ffffff'),
                    size: getInputValue('multi-size', '48'),
                    stroke: getInputValue('multi-stroke', '#000000'),
                    shadow: getInputValue('multi-shadow', false),
                    'vertical-offset': getInputValue('multi-vertical-offset', '0')
                },
                general: {
                    'font-family': selectedFont,
                    background: getInputValue('background', '#1e293b'),
                    'text-position': getInputValue('text-position', 'center'),
                    'text-margin': getInputValue('text-margin', '20'),
                    transparent: getInputValue('transparent', false),
                    'max-wins': getInputValue('max-wins', '20')
                },
                options: {
                    'color-based-on-value': getInputValue('color-based-on-value', false),
                    'hide-controls': getInputValue('hide-controls', false),
                    'hide-multiplier': getInputValue('hide-multiplier', false)
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
            const previewWins = document.getElementById('preview-wins');
            const previewMulti = document.getElementById('preview-multi');
            
            // Extraire les styles
            const extracted = extractStylesFromData(styles);
            
            // Styles WINS
            if (extracted.winsColor && !extracted.colorBasedOnValue) {
                previewWins.style.color = extracted.winsColor;
            }
            if (extracted.winsSize) {
                previewWins.style.fontSize = `${extracted.winsSize}px`;
            }
            if (extracted.winsStroke) {
                previewWins.style.webkitTextStroke = `2px ${extracted.winsStroke}`;
            }
            if (extracted.winsShadow) {
                previewWins.style.textShadow = '3px 3px 6px rgba(0,0,0,0.8)';
            } else {
                previewWins.style.textShadow = 'none';
            }
            
            // Styles MULTI
            if (extracted.multiColor) {
                previewMulti.style.color = extracted.multiColor;
            }
            if (extracted.multiSize) {
                previewMulti.style.fontSize = `${extracted.multiSize}px`;
            }
            if (extracted.multiStroke) {
                previewMulti.style.webkitTextStroke = `2px ${extracted.multiStroke}`;
            }
            if (extracted.multiShadow) {
                previewMulti.style.textShadow = '3px 3px 6px rgba(0,0,0,0.8)';
            } else {
                previewMulti.style.textShadow = 'none';
            }
            if (extracted.multiVerticalOffset) {
                previewMulti.style.transform = `translateY(${extracted.multiVerticalOffset}px)`;
            }
            
            // Masquer le multiplicateur si nécessaire
            if (extracted.hideMultiplier) {
                previewMulti.style.visibility = 'hidden';
            } else {
                previewMulti.style.visibility = 'visible';
            }
            
            // Police
            if (extracted.fontFamily) {
                previewWins.style.fontFamily = extracted.fontFamily;
                previewMulti.style.fontFamily = extracted.fontFamily;
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
        }

        // Fonction pour extraire les styles
        function extractStylesFromData(data) {
            const extracted = {};
            
            if (data.wins) {
                extracted.winsColor = data.wins.color;
                extracted.winsSize = data.wins.size;
                extracted.winsStroke = data.wins.stroke;
                extracted.winsShadow = data.wins.shadow;
            }
            if (data.multi) {
                extracted.multiColor = data.multi.color;
                extracted.multiSize = data.multi.size;
                extracted.multiStroke = data.multi.stroke;
                extracted.multiShadow = data.multi.shadow;
                extracted.multiVerticalOffset = data.multi['vertical-offset'];
            }
            if (data.general) {
                extracted.fontFamily = data.general['font-family'];
                extracted.background = data.general.background;
                extracted.textPosition = data.general['text-position'];
                extracted.textMargin = data.general['text-margin'];
                extracted.transparent = data.general.transparent;
                extracted.maxWins = data.general['max-wins'];
            }
            if (data.options) {
                extracted.colorBasedOnValue = data.options['color-based-on-value'];
                extracted.hideControls = data.options['hide-controls'];
                extracted.hideMultiplier = data.options['hide-multiplier'];
            }
            
            // Structure plate (compatibilité)
            if (data['wins-color']) {
                extracted.winsColor = data['wins-color'];
                extracted.winsSize = data['wins-size'];
                extracted.winsStroke = data['wins-stroke'];
                extracted.winsShadow = data['wins-shadow'];
                extracted.multiColor = data['multi-color'];
                extracted.multiSize = data['multi-size'];
                extracted.multiStroke = data['multi-stroke'];
                extracted.multiShadow = data['multi-shadow'];
                extracted.multiVerticalOffset = data['multi-vertical-offset'];
                extracted.fontFamily = data['font-family'];
                extracted.background = data['background'];
                extracted.textPosition = data['text-position'];
                extracted.textMargin = data['text-margin'];
                extracted.transparent = data['transparent'];
                extracted.maxWins = data['max-wins'];
                extracted.colorBasedOnValue = data['color-based-on-value'];
                extracted.hideControls = data['hide-controls'];
                extracted.hideMultiplier = data['hide-multiplier'];
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
                    const response = await fetch(`/api.php?token=${token}&module=style&action=save&value=${encodeURIComponent(JSON.stringify(styles))}`);
                    const result = await response.json();
                    
                    if (result.success) {
                        notifyAllWinPages(styles);
                        localStorage.setItem('forceStyleUpdate', Date.now());
                    } else {
                        showAutoSaveError();
                    }
                } catch (error) {
                    showAutoSaveError();
                }
            }, 50); // Réduit à 50ms pour une sauvegarde instantanée
        }

        // Fonction pour notifier toutes les pages win.php
        function notifyAllWinPages(styles) {
            const timestamp = Date.now() + '_' + Math.random();
            localStorage.setItem('realtimeStyles', JSON.stringify(styles));
            localStorage.setItem('stylesTimestamp', timestamp);
            
            if (window.BroadcastChannel) {
                try {
                    const channel = new BroadcastChannel('styles_channel');
                    // Envoyer immédiatement et répéter pour assurer la réception
                    channel.postMessage({
                        type: 'stylesUpdate',
                        styles: styles,
                        timestamp: timestamp
                    });
                    // Répéter après 5ms pour assurer la réception
                    setTimeout(() => {
                        channel.postMessage({
                            type: 'stylesUpdate',
                            styles: styles,
                            timestamp: timestamp + '_retry'
                        });
                    }, 5);
                } catch (error) {}
            }
            
            try {
                // Envoyer immédiatement via postMessage
                window.postMessage({
                    type: 'stylesUpdate',
                    styles: styles,
                    timestamp: timestamp
                }, '*');
                // Répéter après 5ms
                setTimeout(() => {
                    window.postMessage({
                        type: 'stylesUpdate',
                        styles: styles,
                        timestamp: timestamp + '_post_retry'
                    }, '*');
                }, 5);
            } catch (error) {}
            
            localStorage.setItem('forceStyleUpdate', timestamp + '_force');
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
                const response = await fetch(`/api.php?token=${token}&module=style&action=save&value=${encodeURIComponent(JSON.stringify(styles))}`);
                const result = await response.json();
                
                if (result.success) {
                    saveBtn.innerHTML = '<i class="fas fa-check"></i> Sauvegardé !';
                    notifyAllWinPages(styles);
                    
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
            document.getElementById('wins-color').value = '#ffffff';
            document.getElementById('wins-size').value = '64';
            document.getElementById('wins-stroke').value = '#000000';
            document.getElementById('wins-shadow').checked = false;
            document.getElementById('multi-color').value = '#ffffff';
            document.getElementById('multi-size').value = '48';
            document.getElementById('multi-stroke').value = '#000000';
            document.getElementById('multi-vertical-offset').value = '0';
            document.getElementById('multi-shadow').checked = false;
            document.getElementById('hide-multiplier').checked = false;
            document.getElementById('font-family').value = 'Arial, Helvetica, sans-serif';
            document.getElementById('background').value = '#1e293b';
            document.getElementById('max-wins').value = '20';
            document.getElementById('text-position').value = 'center';
            document.getElementById('text-margin').value = '20';
            document.getElementById('transparent').checked = false;
            document.getElementById('color-based-on-value').checked = false;
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
                const response = await fetch(`/api.php?token=${token}&module=style&action=get`);
                const result = await response.json();
                
                if (result.success && result.data) {
                    let styles = result.data;
                    
                    if (!styles.wins && styles['wins-color']) {
                        styles = migrateToGroupedStructure(styles);
                    }
                    
                    if (styles.wins || styles.multi || styles.general || styles.options) {
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
            if (styles.wins) {
                setInputValue('wins-color', styles.wins.color);
                setInputValue('wins-size', styles.wins.size);
                setInputValue('wins-stroke', styles.wins.stroke);
                setInputValue('wins-shadow', styles.wins.shadow);
            }
            
            if (styles.multi) {
                setInputValue('multi-color', styles.multi.color);
                setInputValue('multi-size', styles.multi.size);
                setInputValue('multi-stroke', styles.multi.stroke);
                setInputValue('multi-shadow', styles.multi.shadow);
                setInputValue('multi-vertical-offset', styles.multi['vertical-offset']);
            }
            
            if (styles.general) {
                setInputValue('font-family', styles.general['font-family']);
                setInputValue('background', styles.general.background);
                setInputValue('text-position', styles.general['text-position']);
                setInputValue('text-margin', styles.general['text-margin']);
                setInputValue('transparent', styles.general.transparent);
                setInputValue('max-wins', styles.general['max-wins']);
            }
            
            if (styles.options) {
                setInputValue('color-based-on-value', styles.options['color-based-on-value']);
                setInputValue('hide-controls', styles.options['hide-controls']);
                setInputValue('hide-multiplier', styles.options['hide-multiplier']);
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

        // Fonction pour migrer vers la structure groupée
        function migrateToGroupedStructure(flatStyles) {
            return {
                wins: {
                    color: flatStyles['wins-color'] || '#ffffff',
                    size: flatStyles['wins-size'] || '64',
                    stroke: flatStyles['wins-stroke'] || '#000000',
                    shadow: flatStyles['wins-shadow'] || false
                },
                multi: {
                    color: flatStyles['multi-color'] || '#ffffff',
                    size: flatStyles['multi-size'] || '48',
                    stroke: flatStyles['multi-stroke'] || '#000000',
                    shadow: flatStyles['multi-shadow'] || false,
                    'vertical-offset': flatStyles['multi-vertical-offset'] || '0'
                },
                general: {
                    'font-family': flatStyles['font-family'] || 'Arial, Helvetica, sans-serif',
                    background: flatStyles['background'] || '#1e293b',
                    'text-position': flatStyles['text-position'] || 'center',
                    'text-margin': flatStyles['text-margin'] || '20',
                    transparent: flatStyles['transparent'] || false,
                    'max-wins': flatStyles['max-wins'] || '20'
                },
                options: {
                    'color-based-on-value': flatStyles['color-based-on-value'] || false,
                    'hide-controls': flatStyles['hide-controls'] || false,
                    'hide-multiplier': flatStyles['hide-multiplier'] || false
                },
                meta: {
                    version: '2.0',
                    'migrated-at': Date.now(),
                    'structure': 'grouped',
                    'from': 'flat'
                }
            };
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
            document.getElementById('wins-size-value').textContent = document.getElementById('wins-size').value + 'px';
            document.getElementById('multi-size-value').textContent = document.getElementById('multi-size').value + 'px';
            document.getElementById('multi-vertical-offset-value').textContent = document.getElementById('multi-vertical-offset').value + 'px';
            document.getElementById('max-wins-value').textContent = document.getElementById('max-wins').value;
            document.getElementById('text-margin-value').textContent = document.getElementById('text-margin').value + 'px';
        }

        // Fonction pour charger les vraies valeurs des wins
        async function loadWinsData() {
            try {
                const response = await fetch(`/api.php?token=${token}&module=wins&action=get`);
                const result = await response.json();
                
                if (result.success && result.data) {
                    const count = result.data.count || 0;
                    const multiplier = result.data.multiplier || 1;
                    const multiplierActive = result.data.multiplier_active;
                    
                    // Récupérer les styles actuels pour max-wins et color-based-on-value
                    const currentStyles = collectStyles();
                    const maxWins = parseInt(currentStyles.general['max-wins']) || 20;
                    const colorBasedOnValue = currentStyles.options['color-based-on-value'] || false;
                    
                    // Mettre à jour l'aperçu avec le format complet
                    const winsDisplay = document.getElementById('preview-wins');
                    if (winsDisplay) {
                        // Format identique à win.php
                        let displayText = `WINS: ${count}/${maxWins}`;
                        if (count > maxWins) {
                            displayText += ` (+${count - maxWins})`;
                        }
                        winsDisplay.textContent = displayText;
                        
                        // Appliquer la couleur selon valeur si activée - LOGIQUE SIMPLIFIÉE
                        if (colorBasedOnValue) {
                            if (count < 0) {
                                winsDisplay.style.color = '#ff4444'; // Rouge pour négatif
                                winsDisplay.style.textShadow = '0 0 20px #ff0000, 0 0 40px #ff0000';
                            } else if (count > 0) {
                                winsDisplay.style.color = '#44ff44'; // Vert pour positif
                                winsDisplay.style.textShadow = '0 0 20px #00ff00, 0 0 40px #00ff00';
                            } else {
                                winsDisplay.style.color = '#ffffff'; // Blanc pour zéro
                                winsDisplay.style.textShadow = '0 0 20px rgba(255, 255, 255, 0.5)';
                            }
                        } else {
                            // Réinitialiser la couleur si l'option est désactivée
                            winsDisplay.style.color = '';
                            winsDisplay.style.textShadow = '';
                        }
                    }
                    
                    const multiDisplay = document.getElementById('preview-multi');
                    if (multiDisplay) {
                        if (multiplierActive === '1' || multiplierActive === true || multiplierActive === 1) {
                            multiDisplay.textContent = `X${multiplier} ACTIF`;
                            multiDisplay.style.visibility = 'visible';
                            multiDisplay.style.display = 'block';
                            
                            // Couleur selon la valeur : rouge si x1, vert si supérieur
                            if (multiplier === 1) {
                                multiDisplay.style.color = '#ff0000';
                            } else {
                                multiDisplay.style.color = '#44ff00';
                            }
                        } else {
                            multiDisplay.style.visibility = 'hidden';
                            multiDisplay.style.display = 'none';
                        }
                    }
                    
                    // Réappliquer les styles actuels après la mise à jour du texte
                    applyPreviewStyles(currentStyles);
                }
            } catch (error) {
                console.error('Erreur lors du chargement des données wins:', error);
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
                loadWinsData()
            ]).then(() => {
                // Une fois les styles et données chargés, réappliquer les styles
                setTimeout(() => {
                    const styles = collectStyles();
                    applyPreviewStyles(styles);
                    notifyAllWinPages(styles);
                }, 20); // Réduit à 20ms pour une application quasi-instantanée
            });
            
            // Recharger les données toutes les 100ms pour une synchronisation ultra-rapide
            setInterval(loadWinsData, 100);
            
            // Forcer l'envoi initial des styles après un court délai
            setTimeout(() => {
                const initialStyles = collectStyles();
                notifyAllWinPages(initialStyles);
            }, 50); // Réduit à 50ms pour une initialisation ultra-rapide
            
            // Écouter les changements
            document.querySelectorAll('[data-style], .color-text, .size-number, input[type="checkbox"], #font-family, #text-position').forEach(input => {
                const handleChange = () => {
                    const styles = collectStyles();
                    applyPreviewStyles(styles);
                    notifyAllWinPages(styles);
                    autoSaveStyles(styles);
                    
                    // Forcer la mise à jour de l'aperçu pour refléter les changements
                    setTimeout(() => {
                        loadWinsData();
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