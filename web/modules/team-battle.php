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
    <title>Team VS Team - MyFull Agency</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Luckiest+Guy&family=Orbitron:wght@400;700;900&family=Press+Start+2P&family=Russo+One&family=Audiowide&family=Bungee&family=Creepster&family=Nosifer&family=Walter+Turncoat&family=Fredoka+One&family=Cinzel:wght@400;600&family=Playfair+Display:wght@400;700&family=Dancing+Script:wght@400;700&family=Black+Ops+One&family=Faster+One&family=Jolly+Lodger&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="command-bar.css">
    <style>
        /* ==================== CSS VARIABLES ==================== */
        :root {
            /* Colors */
            --primary-color: #6366f1;
            --primary-hover: #5855eb;
            --secondary-color: #10b981;
            --accent-color: #f59e0b;
            --danger-color: #ef4444;
            --team-green: #10b981;
            --team-red: #ef4444;
            
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

        /* ==================== TEAMS CONTAINER ==================== */
        .teams-container {
            display: flex;
            gap: var(--spacing-2xl);
            align-items: center;
            justify-content: center;
            width: 100%;
            max-width: 1000px;
        }

        .team {
            flex: 1;
            text-align: center;
            padding: var(--spacing-xl);
            background: var(--bg-card);
            border-radius: var(--radius-xl);
            transition: all var(--transition-normal);
        }

        .team.green {
            /* box-shadow supprimé */
        }

        .team.red {
            /* box-shadow supprimé */
        }

        .team-name {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: var(--spacing-md);
            text-transform: uppercase;
            letter-spacing: 2px;
        }

        .score-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0;
        }
        
        .score-label {
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: var(--text-muted);
            margin-bottom: -5px;
        }
        
        .team-score {
            font-size: 5rem;
            font-weight: 800;
            margin: 0;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
            line-height: 1;
        }

        .vs-separator {
            font-size: 3rem;
            font-weight: 800;
            color: var(--text-muted);
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
        }

        /* ==================== CONTROLS SECTION - Styles supprimés, utilisation de command-bar.css ==================== */

        /* ==================== CONFIG BUTTON INTEGRATED ==================== */
        .config-btn-integrated {
            position: absolute;
            top: var(--spacing-md);
            right: var(--spacing-md);
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

        /* ==================== GENERAL CONTROLS ==================== */
        .general-controls {
            display: flex;
            justify-content: center;
            gap: var(--spacing-md);
            margin-top: var(--spacing-sm);
        }

        .btn-large {
            padding: var(--spacing-md) var(--spacing-xl);
            font-size: 0.875rem;
        }

        /* ==================== TEXT EFFECTS ANIMATIONS - DÉSACTIVÉES ==================== */
        /* Animations désactivées pour éviter les distractions */

        /* ==================== RESPONSIVE DESIGN ==================== */
        @media (max-width: 1024px) {
            .widget-container {
                height: auto;
                gap: var(--spacing-lg);
            }
            
            .controls {
                margin-top: var(--spacing-lg);
                max-width: 100%;
            }

            .buttons {
                grid-template-columns: repeat(3, 1fr);
            }

            .teams-container {
                flex-direction: column;
                gap: var(--spacing-lg);
            }

            .vs-separator {
                display: none;
            }
        }

        @media (max-width: 768px) {
            body {
                padding: var(--spacing-md);
            }
            
            .team-name {
                font-size: 1.5rem;
            }
            
            .team-score {
                font-size: 3rem;
            }
            
            .controls {
                max-width: 100%;
            }
            
            .team-controls {
                grid-template-columns: 1fr;
                gap: var(--spacing-md);
            }
            
            .buttons {
                grid-template-columns: repeat(3, 1fr);
                gap: var(--spacing-xs);
            }

            .btn {
                font-size: 0.7rem;
                padding: var(--spacing-xs);
                min-height: 32px;
            }
        }
    </style>
    <style id="custom-styles"></style>
</head>
<body>
    <div class="widget-container" data-module="teams">
        <?php if(isset($_GET['realtime']) && $_GET['realtime'] === 'true'): ?>
        <div class="realtime-indicator">
            <i class="fas fa-broadcast-tower"></i>
            Mode Temps Réel - Les changements s'appliquent instantanément
        </div>
        <?php endif; ?>
        
        <!-- Affichage principal -->
        <div class="display" id="main-display">
            <div class="teams-container">
                <div class="team green" id="team-green">
                    <h2 class="team-name" id="green-name">ÉQUIPE VERTE</h2>
                    <div class="score-container">
                        <span class="score-label">SCORE</span>
                        <div class="team-score" id="green-score">0</div>
                    </div>
                </div>
                
                <div class="vs-separator">VS</div>
                
                <div class="team red" id="team-red">
                    <h2 class="team-name" id="red-name">ÉQUIPE ROUGE</h2>
                    <div class="score-container">
                        <span class="score-label">SCORE</span>
                        <div class="team-score" id="red-score">0</div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Si paramètre control=true, afficher les contrôles -->
        <?php if($control): ?>
        <div class="command-bar" style="max-width: 1000px;">
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                <!-- Contrôles Équipe Verte -->
                <div class="command-section success">
                    <h3><i class="fas fa-users"></i> Équipe Verte</h3>
                    <div class="command-buttons">
                        <button class="command-btn subtract" data-action="add-score" data-team="green" data-value="-10">-10</button>
                        <button class="command-btn subtract" data-action="add-score" data-team="green" data-value="-5">-5</button>
                        <button class="command-btn subtract" data-action="add-score" data-team="green" data-value="-1">-1</button>
                        <button class="command-btn reset" data-action="reset-score" data-team="green">Reset</button>
                        <button class="command-btn add" data-action="add-score" data-team="green" data-value="1">+1</button>
                        <button class="command-btn add" data-action="add-score" data-team="green" data-value="5">+5</button>
                        <button class="command-btn add" data-action="add-score" data-team="green" data-value="10">+10</button>
<<<<<<< HEAD
                        <button class="command-btn reset" data-action="reset-score" data-team="green">Reset</button>
=======
>>>>>>> 5b1251e14b228fd39bb0cbe55b28e46be0cd2da9
                    </div>
                </div>
                
                <!-- Contrôles Équipe Rouge -->
                <div class="command-section danger">
                    <h3><i class="fas fa-users"></i> Équipe Rouge</h3>
<<<<<<< HEAD
                    <div class="command-buttons grid-4">
=======
                    <div class="command-buttons">
>>>>>>> 5b1251e14b228fd39bb0cbe55b28e46be0cd2da9
                        <button class="command-btn subtract" data-action="add-score" data-team="red" data-value="-10">-10</button>
                        <button class="command-btn subtract" data-action="add-score" data-team="red" data-value="-5">-5</button>
                        <button class="command-btn subtract" data-action="add-score" data-team="red" data-value="-1">-1</button>
                        <button class="command-btn reset" data-action="reset-score" data-team="red">Reset</button>
                        <button class="command-btn add" data-action="add-score" data-team="red" data-value="1">+1</button>
                        <button class="command-btn add" data-action="add-score" data-team="red" data-value="5">+5</button>
                        <button class="command-btn add" data-action="add-score" data-team="red" data-value="10">+10</button>
<<<<<<< HEAD
                        <button class="command-btn reset" data-action="reset-score" data-team="red">Reset</button>
=======
>>>>>>> 5b1251e14b228fd39bb0cbe55b28e46be0cd2da9
                    </div>
                </div>
            </div>
            
            <!-- Contrôles généraux -->
            <div style="display: flex; justify-content: center; gap: 10px; margin-top: 10px;">
                <button class="command-btn reset large" data-action="reset-all">
                    <i class="fas fa-redo"></i> Reset Tout
                </button>
                <button class="command-btn primary large" data-action="swap-scores">
                    <i class="fas fa-exchange-alt"></i> Échanger Scores
                </button>
                <a href="/modules/teams-config.php?token=<?=$token?>" class="command-config-btn" style="position: static; width: 32px; height: 32px; font-size: 0.8rem;">
                    <i class="fas fa-cog"></i>
                </a>
            </div>
        </div>
        <?php endif; ?>
    </div>
    
    <script>
        // Variables globales
        const token = '<?=$token?>';
        const module = 'teams';
        
        // Variables globales simplifiées
        let currentData = { 
            green: { 
                name: 'ÉQUIPE VERTE', 
                score: 0 
            }, 
            red: { 
                name: 'ÉQUIPE ROUGE', 
                score: 0 
            },
            timestamp: 0 
        };
        
        // Fonction pour mettre à jour l'affichage
        function updateDisplay(data) {
            if (data.data) {
                // Mettre à jour les données directement
                currentData = data.data;
                
                // Charger les styles pour obtenir les personnalisations
                loadStyles().then(() => {
                    updateTeamsDisplay();
                });
            }
        }
        
        // Fonction pour mettre à jour l'affichage des équipes
        function updateTeamsDisplay() {
            // Équipe verte
            const greenName = document.getElementById('green-name');
            const greenScore = document.getElementById('green-score');
            if (greenName && currentData.green) {
                greenName.textContent = currentData.green.name || 'ÉQUIPE VERTE';
            }
            if (greenScore && currentData.green) {
                greenScore.textContent = currentData.green.score || 0;
            }
            
            // Équipe rouge
            const redName = document.getElementById('red-name');
            const redScore = document.getElementById('red-score');
            if (redName && currentData.red) {
                redName.textContent = currentData.red.name || 'ÉQUIPE ROUGE';
            }
            if (redScore && currentData.red) {
                redScore.textContent = currentData.red.score || 0;
            }
        }
        
        // Variables globales pour les styles
        let currentStyles = {};
        
        // Fonction pour charger les styles
        async function loadStyles() {
            try {
                // Utiliser l'URL complète depuis la configuration
                const baseUrl = window.location.origin;
                const response = await fetch(`${baseUrl}/api.php?token=${token}&module=teams-style&action=get`);
                const result = await response.json();
                if (result.success && result.data) {
                    
                    // Sauvegarder les styles de base (sans écraser les styles temps réel)
                    const baseStyles = result.data;
                    
                    // Vérifier s'il y a des styles temps réel actifs
                    const realtimeStyles = localStorage.getItem('realtimeTeamsStyles');
                    if (realtimeStyles && Object.keys(JSON.parse(realtimeStyles)).length > 0) {
                        currentStyles = JSON.parse(realtimeStyles);
                        // Ne pas appliquer les styles de base, utiliser les temps réel
                        applyStyles(currentStyles);
                    } else {
                        currentStyles = baseStyles;
                        applyStyles(baseStyles);
                    }
                } else {
                }
            } catch (error) {
            }
        }
        
        // Fonction d'application des styles
        function applyStyles(styles) {
            // Ne pas appliquer si on est en mode temps réel
            if (isApplyingRealtimeStyles) {
                return;
            }
            
            // Validation des styles
            if (!styles || typeof styles !== 'object') {
                console.warn('Styles invalides reçus:', styles);
                return;
            }
            
            console.log('🎨 Application des styles groupés:', styles);
            
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
                    css += `.team-name, .team-score { font-family: ${general['font-family']} !important; } `;
                }
                
                // Positionnement du texte
                if (general['text-position']) {
                    const margin = general['text-margin'] || '0';
                    css += generatePositionCSS(general['text-position'], margin);
                }
                
                // Fond des équipes
                if (general['team-background'] === true || general['team-background'] === 'true' || general['team-background'] === 1) {
                    css += `.team { 
                        background: rgba(0, 0, 0, 0.5) !important; 
                        padding: 20px !important;
                        border-radius: 10px !important;
                        backdrop-filter: blur(10px) !important;
                    } `;
                }
            }
            
            // 2. OPTIONS GLOBALES (Visibilité)
            if (styles.options) {
                const options = styles.options;
                
                // Masquer les contrôles si demandé
                if (options['hide-controls'] === true || options['hide-controls'] === 'true' || options['hide-controls'] === 1) {
                    css += '.command-bar { display: none !important; } ';
<<<<<<< HEAD
                    css += '.config-button-fixed { display: none !important; } ';
                } else {
                    css += '.command-bar { display: block !important; } ';
=======
                    css += '.command-config-btn { display: none !important; } ';
                    css += '.config-btn-small { display: none !important; } ';
                } else {
                    css += '.command-bar { display: flex !important; } ';
>>>>>>> 5b1251e14b228fd39bb0cbe55b28e46be0cd2da9
                }
            }
            
            // 3. STYLES ÉQUIPE VERTE
            if (styles.green) {
                const green = styles.green;
                
                // Nom de l'équipe - géré par applyTeamNames()
                // if (green.name) {
                //     const greenNameElement = document.getElementById('green-name');
                //     if (greenNameElement) {
                //         greenNameElement.textContent = green.name;
                //         // Forcer la mise à jour du DOM
                //         greenNameElement.style.display = 'none';
                //         greenNameElement.offsetHeight; // Force reflow
                //         greenNameElement.style.display = '';
                //     }
                // }
                
<<<<<<< HEAD
                // Couleur du score (séparée du nom)
                if (green.scoreColor) {
                    css += `#green-score { color: ${green.scoreColor} !important; } `;
                } else if (green.color) {
                    css += `#green-score { color: ${green.color} !important; } `;
                }
                
                // Couleur du nom
                if (green.nameColor) {
                    css += `#green-name { color: ${green.nameColor} !important; } `;
                } else if (green.color) {
=======
                // Couleur
                if (green.color) {
>>>>>>> 5b1251e14b228fd39bb0cbe55b28e46be0cd2da9
                    css += `#green-name { color: ${green.color} !important; } `;
                }
                
                // Couleur du score (peut être différente)
                if (green['score-color']) {
                    css += `#green-score { color: ${green['score-color']} !important; } `;
                } else if (green.color) {
                    // Fallback sur la couleur de l'équipe
                    css += `#green-score { color: ${green.color} !important; } `;
                }
                
                // Taille
                if (green.size) {
                    css += `#green-score { font-size: ${green.size}px !important; } `;
                }
                
                // Contour
                if (green.stroke) {
                    css += `#green-score { -webkit-text-stroke: 2px ${green.stroke} !important; text-stroke: 2px ${green.stroke} !important; } `;
                }
                
                // Ombre
                if (green.shadow === true || green.shadow === 'true' || green.shadow === 1) {
                    css += '#green-score { text-shadow: 3px 3px 6px rgba(0,0,0,0.8) !important; } ';
                } else {
                    css += '#green-score { text-shadow: none !important; } ';
                }
                
                // Fond des équipes
                if (green.background) {
                    css += `#team-green { background: ${green.background} !important; } `;
                }
                
                // Effets d'animation
                if (green.effect && green.effect !== 'none') {
                    css += generateEffectCSS('green', green.effect, green['effect-speed'] || '1', green['effect-pause'] || false);
                }
            }
            
            // 4. STYLES ÉQUIPE ROUGE
            if (styles.red) {
                const red = styles.red;
                
                // Nom de l'équipe - géré par applyTeamNames()
                // if (red.name) {
                //     const redNameElement = document.getElementById('red-name');
                //     if (redNameElement) {
                //         redNameElement.textContent = red.name;
                //         // Forcer la mise à jour du DOM
                //         redNameElement.style.display = 'none';
                //         redNameElement.offsetHeight; // Force reflow
                //         redNameElement.style.display = '';
                //     }
                // }
                
<<<<<<< HEAD
                // Couleur du score (séparée du nom)
                if (red.scoreColor) {
                    css += `#red-score { color: ${red.scoreColor} !important; } `;
                } else if (red.color) {
                    css += `#red-score { color: ${red.color} !important; } `;
                }
                
                // Couleur du nom
                if (red.nameColor) {
                    css += `#red-name { color: ${red.nameColor} !important; } `;
                } else if (red.color) {
=======
                // Couleur
                if (red.color) {
>>>>>>> 5b1251e14b228fd39bb0cbe55b28e46be0cd2da9
                    css += `#red-name { color: ${red.color} !important; } `;
                }
                
                // Couleur du score (peut être différente)
                if (red['score-color']) {
                    css += `#red-score { color: ${red['score-color']} !important; } `;
                } else if (red.color) {
                    // Fallback sur la couleur de l'équipe
                    css += `#red-score { color: ${red.color} !important; } `;
                }
                
                // Taille
                if (red.size) {
                    css += `#red-score { font-size: ${red.size}px !important; } `;
                }
                
                // Contour
                if (red.stroke) {
                    css += `#red-score { -webkit-text-stroke: 2px ${red.stroke} !important; text-stroke: 2px ${red.stroke} !important; } `;
                }
                
                // Ombre
                if (red.shadow === true || red.shadow === 'true' || red.shadow === 1) {
                    css += '#red-score { text-shadow: 3px 3px 6px rgba(0,0,0,0.8) !important; } ';
                } else {
                    css += '#red-score { text-shadow: none !important; } ';
                }
                
                // Fond des équipes
                if (red.background) {
                    css += `#team-red { background: ${red.background} !important; } `;
                }
                
                // Effets d'animation
                if (red.effect && red.effect !== 'none') {
                    css += generateEffectCSS('red', red.effect, red['effect-speed'] || '1', red['effect-pause'] || false);
                }
            }
            
            // Application finale des styles
            applyCSS(css);
            
            // Appliquer les noms d'équipes séparément pour éviter les conflits
            applyTeamNames(styles);
            
            console.log('✅ Styles appliqués avec succès (structure groupée)');
        }
        
        // Génération du CSS de positionnement
        function generatePositionCSS(position, margin) {
            const positions = {
                'top-left': `#main-display { justify-content: flex-start; align-items: flex-start; padding-top: ${margin}px; padding-left: ${margin}px; }`,
                'top-center': `#main-display { justify-content: flex-start; align-items: center; padding-top: ${margin}px; }`,
                'top-right': `#main-display { justify-content: flex-start; align-items: flex-end; padding-top: ${margin}px; padding-right: ${margin}px; }`,
                'center-left': `#main-display { justify-content: center; align-items: flex-start; padding-left: ${margin}px; }`,
                'center-right': `#main-display { justify-content: center; align-items: flex-end; padding-right: ${margin}px; }`,
                'bottom-left': `#main-display { justify-content: flex-end; align-items: flex-start; padding-bottom: ${margin}px; padding-left: ${margin}px; }`,
                'bottom-center': `#main-display { justify-content: flex-end; align-items: center; padding-bottom: ${margin}px; }`,
                'bottom-right': `#main-display { justify-content: flex-end; align-items: flex-end; padding-bottom: ${margin}px; padding-right: ${margin}px; }`,
                'center': `#main-display { justify-content: center; align-items: center; }`
            };
            
            return positions[position] || positions['center'];
        }
        
        // Génération du CSS d'effets d'animation (désactivée)
        function generateEffectCSS(team, effect, speed, pauseOnHover) {
            // Effets d'animation désactivés pour simplifier l'interface
            return '';
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

        // Fonction pour appliquer les noms d'équipes avec retry
        function applyTeamNames(styles) {
            if (styles.green && styles.green.name) {
                const greenNameElement = document.getElementById('green-name');
                if (greenNameElement) {
                    greenNameElement.textContent = styles.green.name;
                    // Forcer la mise à jour du DOM
                    greenNameElement.style.display = 'none';
                    greenNameElement.offsetHeight; // Force reflow
                    greenNameElement.style.display = '';
                } else {
                    // Retry après 50ms si l'élément n'est pas trouvé
                    setTimeout(() => {
                        const retryElement = document.getElementById('green-name');
                        if (retryElement) {
                            retryElement.textContent = styles.green.name;
                            retryElement.style.display = 'none';
                            retryElement.offsetHeight; // Force reflow
                            retryElement.style.display = '';
                        }
                    }, 50);
                }
            }
            
            if (styles.red && styles.red.name) {
                const redNameElement = document.getElementById('red-name');
                if (redNameElement) {
                    redNameElement.textContent = styles.red.name;
                    // Forcer la mise à jour du DOM
                    redNameElement.style.display = 'none';
                    redNameElement.offsetHeight; // Force reflow
                    redNameElement.style.display = '';
                } else {
                    // Retry après 50ms si l'élément n'est pas trouvé
                    setTimeout(() => {
                        const retryElement = document.getElementById('red-name');
                        if (retryElement) {
                            retryElement.textContent = styles.red.name;
                            retryElement.style.display = 'none';
                            retryElement.offsetHeight; // Force reflow
                            retryElement.style.display = '';
                        }
                    }, 50);
                }
            }
        }
        
        // Fonction pour faire une requête API
        async function apiCall(action, value = '') {
            try {
                // Utiliser l'URL complète depuis la configuration
                const baseUrl = window.location.origin;
                const url = `${baseUrl}/api.php?token=${token}&module=${module}&action=${action}${value ? '&value=' + value : ''}`;
                
                const response = await fetch(url);
                const result = await response.json();
                
                if (result.success) {
                    // Pour les actions, mettre à jour les données locales immédiatement
                    if (action !== 'get' && result.data) {
                        // Sauvegarder l'ancien état
                        const oldData = { ...currentData };
                        currentData = result.data;
                        
                        // S'assurer que le timestamp est mis à jour
                        if (result.data.timestamp) {
                            currentData.timestamp = result.data.timestamp;
                        } else {
                            currentData.timestamp = Date.now();
                        }
                        
                        // Forcer le rechargement des styles après une action
                        setTimeout(() => {
                            loadStyles();
                        }, 20);
                    }
                    updateDisplay(result);
                }
                
            } catch (error) {
                // Ignorer les erreurs pour maintenir la fluidité
            }
        }
        
        // Auto-refresh simplifié (sans restrictions)
        setInterval(() => {
            // Auto-refresh ultra-rapide toutes les 200ms
            apiCall('get');
        }, 200); // Réduit à 200ms pour une synchronisation quasi-instantanée
        
        // Charger les données initiales
        apiCall('get');
        
        // Initialisation propre avec gestion du conflit styles sauvegardés vs temps réel
        setTimeout(() => {
            loadStyles();
        }, 100); // Optimisé à 100ms
        
        // Forcer un rechargement des styles après 500ms
        setTimeout(() => {
            loadStyles();
        }, 500);
        
        // Charger les styles temps réel persistés avec priorité
        try {
            const persistedStyles = localStorage.getItem('currentRealtimeTeamsStyles');
            const realtimeStyles = localStorage.getItem('realtimeTeamsStyles');
            
            if (realtimeStyles && Object.keys(JSON.parse(realtimeStyles)).length > 0) {
                const styles = JSON.parse(realtimeStyles);
                currentRealtimeStyles = styles;
                setTimeout(() => {
                    applyRealtimeStyles(styles);
                }, 100); // Application rapide des styles temps réel
            } else if (persistedStyles) {
                const styles = JSON.parse(persistedStyles);
                if (Object.keys(styles).length > 0) {
                    currentRealtimeStyles = styles;
                    setTimeout(() => {
                        applyRealtimeStyles(styles);
                    }, 200);
                }
            }
        } catch (error) {
            // Ignorer les erreurs de chargement
        }
        
        // SYSTÈME DE TEMPS RÉEL SIMPLIFIÉ AVEC AUTO-SAVE
        let isRealtimeMode = false;
        let lastStylesHash = '';
        let currentRealtimeStyles = {}; // Pour persister les styles temps réel
        let isApplyingRealtimeStyles = false; // Flag pour éviter les conflits
        let autoSaveTimeout = null; // Pour l'auto-save
        
        // Fonction pour calculer un hash des styles
        function getStylesHash(styles) {
            return JSON.stringify(styles);
        }
        
        // Fonction pour appliquer les styles en temps réel
        function applyRealtimeStyles(styles) {
            const stylesHash = getStylesHash(styles);
            
            // Éviter les boucles infinies - ne pas appliquer si c'est le même hash
            if (stylesHash === lastStylesHash) {
                return;
            }
            
            lastStylesHash = stylesHash;
            isRealtimeMode = true;
            isApplyingRealtimeStyles = true;
            currentRealtimeStyles = { ...styles };
            
            // Persister les styles temps réel dans localStorage avec structure groupée
            localStorage.setItem('currentRealtimeTeamsStyles', JSON.stringify(currentRealtimeStyles));
            localStorage.setItem('teamsStylesStructureVersion', '2.0');
            
            // Appliquer immédiatement les styles avec la nouvelle fonction
            applyStyles(styles);
            
            // Réactiver le flag après un délai très court pour une meilleure réactivité
            setTimeout(() => {
                isApplyingRealtimeStyles = false;
            }, 25); // Encore plus rapide pour une meilleure réactivité
        }
        
        // Écouter postMessage
        window.addEventListener('message', (event) => {
            if (event.data && event.data.type === 'teamsStylesUpdate') {
                applyRealtimeStyles(event.data.styles);
            }
            if (event.data && event.data.type === 'teamsStylesSaved') {
                isRealtimeMode = false;
                lastStylesHash = '';
                currentRealtimeStyles = {}; // Réinitialiser après sauvegarde
                localStorage.removeItem('currentRealtimeTeamsStyles'); // Nettoyer localStorage
            }
        });
        
        // Écouter les événements personnalisés pour une réactivité instantanée
        window.addEventListener('teamsStyleUpdate', (event) => {
            if (event.detail && event.detail.styles) {
                applyRealtimeStyles(event.detail.styles);
            }
        });
        
        // Écouter BroadcastChannel (version améliorée)
        if (window.BroadcastChannel) {
            const channel = new BroadcastChannel('teams_styles_channel');
            channel.onmessage = (event) => {
                if (event.data && event.data.type === 'teamsStylesUpdate') {
                    applyRealtimeStyles(event.data.styles);
                }
                if (event.data && event.data.type === 'teamsStylesSaved') {
                    isRealtimeMode = false;
                    lastStylesHash = '';
                    currentRealtimeStyles = {}; // Réinitialiser après sauvegarde
                    localStorage.removeItem('currentRealtimeTeamsStyles'); // Nettoyer localStorage
                }
            };
        }
        
        // Vérifier localStorage (version 2.0 super réactive et propre)
        setInterval(() => {
            // Ne pas vérifier si on est en train d'appliquer des styles temps réel
            if (isApplyingRealtimeStyles) return;
            
            const stylesTimestamp = localStorage.getItem('teamsStylesTimestamp');
            const lastTimestamp = window.lastTeamsStylesTimestamp || 0;
            
            // Vérifier le signal de force update avec réactivité améliorée
            const forceUpdate = localStorage.getItem('forceTeamsStyleUpdate');
            if (forceUpdate && forceUpdate !== window.lastForceTeamsUpdate) {
                console.log('🚀 Force update détecté, application immédiate');
                window.lastForceTeamsUpdate = forceUpdate;
                const styles = JSON.parse(localStorage.getItem('realtimeTeamsStyles') || '{}');
                
                if (Object.keys(styles).length > 0) {
                    applyRealtimeStyles(styles);
                }
                return;
            }
            
            if (stylesTimestamp && parseInt(stylesTimestamp) > lastTimestamp) {
                const styles = JSON.parse(localStorage.getItem('realtimeTeamsStyles') || '{}');
                
                if (Object.keys(styles).length > 0) {
                    applyRealtimeStyles(styles);
                    window.lastTeamsStylesTimestamp = parseInt(stylesTimestamp);
                }
            }
        }, 15); // Encore plus rapide : 15ms pour une réactivité ultra-rapide
        
        <?php if($control): ?>
        // Gestion des boutons
        setTimeout(() => {
            const buttons = document.querySelectorAll('.command-btn');
            
            if (buttons.length === 0) {
                return;
            }
            
            buttons.forEach(button => {
                button.addEventListener('click', async (e) => {
                    e.preventDefault();
                    
                    // Éviter les clics multiples
                    if (button.disabled) return;
                    
                    // Désactiver temporairement le bouton
                    button.disabled = true;
                    const originalText = button.innerHTML;
                    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
                    
                    // Appeler l'API
                    const action = button.dataset.action;
                    const team = button.dataset.team || '';
                    const value = button.dataset.value || '';
                    
                    // Gestion des actions spéciales
                    if (action === 'reset-score') {
                        // Action de reset spécifique pour une équipe
                        await apiCall(action, JSON.stringify({ team }));
                    } else if (action === 'reset-all' || action === 'swap-scores') {
                        await apiCall(action);
                    } else {
                        // Actions normales avec team et value
                        await apiCall(action, JSON.stringify({ team, value }));
                    }
                    
                    // Réactiver après un court délai
                    setTimeout(() => {
                        button.disabled = false;
                        button.innerHTML = originalText;
                    }, 50); // Réduit à 50ms pour une réactivité maximale
                });
            });
        }, 100); // Réduit à 100ms pour une initialisation plus rapide
        <?php endif; ?>
    </script>
</body>
</html>