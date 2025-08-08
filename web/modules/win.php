<?php
// Inclure la validation des tokens
require_once __DIR__ . '/validate_token.php';

// Valider le token et récupérer les infos utilisateur
$user = requireValidToken();

$control = isset($_GET['control']) && $_GET['control'] === 'true';
$token = $_GET['token'] ?? '';

// Tracking ouverture/fermeture du module Wins
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Wins Counter - MyFull Agency</title>
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

        #wins-display {
            font-size: 4rem;
            font-weight: 800;
            color: white;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin: 0;
        }

        #multi-display {
            font-size: 2.5rem;
            font-weight: 600;
            color: white;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin: 0;
        }

        /* ==================== WIN ACTION BAR - NOUVELLE BARRE MODERNE ==================== */
        .win-action-bar {
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

        .win-action-bar-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: var(--spacing-xs);
        }

        .win-action-bar-controls {
            display: flex;
            align-items: center;
            gap: var(--spacing-xs);
        }

        /* Bouton toggle supprimé - panneau toujours visible */

        .win-action-bar-sections {
            display: flex;
            flex-direction: column;
            gap: var(--spacing-sm);
        }

        .win-action-bar-title {
            font-size: 0.75rem;
            font-weight: 600;
            color: #10b981;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            display: flex;
            align-items: center;
            gap: var(--spacing-xs);
        }

        .win-action-bar-config {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            background: rgba(16, 185, 129, 0.1);
            border: 1px solid #10b981;
            color: #10b981;
            font-size: 0.7rem;
            cursor: pointer;
            transition: all var(--transition-fast);
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
        }

        .win-action-bar-config:hover {
            background: #10b981;
            color: white;
            transform: scale(1.05);
        }

        .win-action-bar-sections {
            display: flex;
            flex-direction: column;
            gap: var(--spacing-sm);
        }

        .win-action-section {
            background: rgba(30, 41, 59, 0.5);
            border-radius: var(--radius-md);
            padding: var(--spacing-sm);
            border: 1px solid rgba(148, 163, 184, 0.1);
        }

        .win-action-section-header {
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

        .win-action-buttons {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: var(--spacing-xs);
        }

        /* Anciens styles supprimés - remplacés par le nouveau design ultra compact */
            text-transform: uppercase;
            letter-spacing: 0.5px;
            display: flex;
            align-items: center;
            gap: var(--spacing-xs);
        }

        .win-multiplier-toggle.active {
            background: #10b981;
            color: white;
            border-color: #10b981;
        }

        .win-multiplier-toggle.inactive {
            background: rgba(239, 68, 68, 0.1);
            color: #ef4444;
            border-color: rgba(239, 68, 68, 0.3);
        }

        .win-multiplier-toggle.inactive:hover {
            background: rgba(239, 68, 68, 0.2);
            border-color: #ef4444;
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

        /* ==================== MULTIPLIER STATUS ==================== */
        .multiplier-status {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: var(--spacing-sm);
            margin-bottom: var(--spacing-sm);
            position: relative;
        }

        .multiplier-toggle {
            background: var(--bg-glass);
            border: 1px solid var(--border-color);
            color: var(--text-secondary);
            padding: var(--spacing-xs) var(--spacing-sm);
            border-radius: var(--radius-md);
            font-size: 0.75rem;
            cursor: pointer;
            transition: all var(--transition-fast);
        }

        .multiplier-toggle.active {
            background: var(--secondary-color);
            color: white;
            border-color: var(--secondary-color);
        }

        .multiplier-toggle.inactive {
            background: var(--danger-color);
            color: white;
            border-color: var(--danger-color);
        }

        /* ==================== CONFIGURATION SECTION ==================== */
        .config-section {
            background: var(--bg-glass);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-lg);
            padding: var(--spacing-lg);
            position: relative;
        }

        .config-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 2px;
            background: linear-gradient(90deg, var(--accent-color), var(--secondary-color));
            border-radius: var(--radius-lg) var(--radius-lg) 0 0;
        }

        .config-section h3 {
            font-size: 1.125rem;
            font-weight: 600;
            color: var(--accent-color);
            margin-bottom: var(--spacing-lg);
            text-align: center;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: var(--spacing-sm);
        }

        .config-actions {
            display: flex;
            justify-content: center;
        }

        .config-btn {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-hover));
            color: white;
            padding: var(--spacing-md) var(--spacing-lg);
            border-radius: var(--radius-md);
            text-decoration: none;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: var(--spacing-sm);
            transition: all var(--transition-fast);
        }

        .config-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(99, 102, 241, 0.4);
        }

        /* ==================== TEXT EFFECTS ANIMATIONS ==================== */
        @keyframes zoomInOut {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.1); }
        }

        @keyframes pulse {
            0% { transform: scale(1); opacity: 1; }
            50% { transform: scale(1.05); opacity: 0.8; }
            100% { transform: scale(1); opacity: 1; }
        }

        @keyframes bounce {
            0%, 20%, 53%, 80%, 100% { transform: translateY(0); }
            40%, 43% { transform: translateY(-10px); }
            70% { transform: translateY(-5px); }
            90% { transform: translateY(-2px); }
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }

        @keyframes glow {
            0%, 100% { text-shadow: 0 0 5px currentColor; }
            50% { text-shadow: 0 0 20px currentColor, 0 0 30px currentColor; }
        }

        @keyframes flip {
            0% { transform: rotateY(0); }
            50% { transform: rotateY(180deg); }
            100% { transform: rotateY(360deg); }
        }

        @keyframes neon {
            0%, 100% { 
                text-shadow: 0 0 5px currentColor, 0 0 10px currentColor, 0 0 15px currentColor;
            }
            50% { 
                text-shadow: 0 0 10px currentColor, 0 0 20px currentColor, 0 0 30px currentColor, 0 0 40px currentColor;
            }
        }

                 /* ==================== RESPONSIVE DESIGN ==================== */
         @media (max-width: 1024px) {
             .widget-container {
                 height: auto;
                 gap: var(--spacing-lg);
             }
             
             .win-action-buttons {
                 grid-template-columns: repeat(4, 1fr);
             }
         }

         @media (max-width: 768px) {
             body {
                 padding: var(--spacing-md);
             }
             
             #wins-display {
                 font-size: 3rem;
             }
             
             #multi-display {
                 font-size: 2rem;
             }
             
             .win-action-bar {
                 padding: var(--spacing-xs);
             }
             
             .win-action-buttons {
                 grid-template-columns: repeat(4, 1fr);
                 gap: var(--spacing-xs);
             }

             .win-action-btn {
                 font-size: 0.65rem;
                 padding: var(--spacing-xs);
                 min-height: 32px;
             }
         }

         @media (max-width: 480px) {
             .win-action-buttons {
                 grid-template-columns: repeat(3, 1fr);
             }
             
             .win-action-section {
                 padding: var(--spacing-sm);
             }
         }

        /* ==================== ULTRA COMPACT CONTROL PANELS ==================== */
        .win-action-bar {
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

        .win-action-bar-sections {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
            justify-content: center;
        }

        .win-action-section {
            display: flex;
            align-items: center;
            gap: 6px;
            flex-wrap: wrap;
        }

        .win-action-section-header {
            font-size: 10px;
            font-weight: 600;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            white-space: nowrap;
            margin-right: 4px;
        }

        .win-action-buttons {
            display: flex;
            align-items: center;
            gap: 4px;
            flex-wrap: wrap;
        }

        /* ==================== ULTRA COMPACT BUTTONS ==================== */
        .win-action-btn {
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

        .win-action-btn::before {
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

        .win-action-btn:hover::before {
            opacity: 0.1;
        }

        .win-action-btn.add {
            background: rgba(16, 185, 129, 0.15);
            color: #10b981;
            border: 1px solid rgba(16, 185, 129, 0.3);
        }

        .win-action-btn.add:hover {
            background: rgba(16, 185, 129, 0.25);
            border-color: #10b981;
            transform: translateY(-1px);
        }

        .win-action-btn.subtract {
            background: rgba(239, 68, 68, 0.15);
            color: #ef4444;
            border: 1px solid rgba(239, 68, 68, 0.3);
        }

        .win-action-btn.subtract:hover {
            background: rgba(239, 68, 68, 0.25);
            border-color: #ef4444;
            transform: translateY(-1px);
        }

        .win-action-btn.reset {
            background: rgba(245, 158, 11, 0.15);
            color: #f59e0b;
            border: 1px solid rgba(245, 158, 11, 0.3);
        }

        .win-action-btn.reset:hover {
            background: rgba(245, 158, 11, 0.25);
            border-color: #f59e0b;
            transform: translateY(-1px);
        }

        .win-action-btn:disabled {
            opacity: 0.4;
            cursor: not-allowed;
            transform: none !important;
        }

        /* ==================== ULTRA COMPACT MULTIPLIER STATUS ==================== */
        .win-multiplier-status {
            display: flex;
            align-items: center;
            gap: 6px;
            padding: 4px 8px;
            background: rgba(30, 41, 59, 0.3);
            border-radius: 4px;
            border: 1px solid rgba(148, 163, 184, 0.1);
        }

        .win-multiplier-toggle {
            background: rgba(16, 185, 129, 0.15);
            border: 1px solid rgba(16, 185, 129, 0.3);
            color: #10b981;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.15s ease;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            height: 24px;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .win-multiplier-toggle:hover {
            background: rgba(16, 185, 129, 0.25);
            border-color: #10b981;
            transform: translateY(-1px);
        }

        .win-multiplier-toggle.inactive {
            background: rgba(239, 68, 68, 0.15);
            border-color: rgba(239, 68, 68, 0.3);
            color: #ef4444;
        }

        .win-multiplier-toggle.inactive:hover {
            background: rgba(239, 68, 68, 0.25);
            border-color: #ef4444;
        }

        .win-multiplier-toggle i {
            font-size: 8px;
        }

        /* ==================== RESPONSIVE DESIGN ==================== */
        @media (max-width: 1024px) {
            .win-action-bar {
                min-width: 350px;
                padding: 6px 10px;
            }
            
            .win-action-bar-sections {
                gap: 10px;
            }
        }

        @media (max-width: 768px) {
            .win-action-bar {
                bottom: 10px;
                padding: 6px 8px;
                gap: 6px;
                min-width: 320px;
                max-width: 95vw;
            }

            .win-action-bar-sections {
                gap: 8px;
                flex-direction: column;
            }

            .win-action-section {
                gap: 4px;
                justify-content: center;
            }

            .win-action-buttons {
                gap: 3px;
                justify-content: center;
                flex-wrap: wrap;
            }

            .win-action-btn {
                padding: 3px 6px;
                font-size: 9px;
                height: 22px;
                min-width: 28px;
            }

            .win-action-section-header {
                font-size: 9px;
                margin-right: 3px;
            }
        }

        @media (max-width: 480px) {
            .win-action-bar {
                min-width: 280px;
                padding: 4px 6px;
                bottom: 5px;
            }
            
            .win-action-bar-sections {
                gap: 6px;
            }
            
            .win-action-section {
                gap: 3px;
            }
            
            .win-action-buttons {
                gap: 2px;
                justify-content: center;
            }
            
            .win-action-btn {
                padding: 2px 4px;
                font-size: 8px;
                height: 20px;
                min-width: 24px;
            }
            
            .win-action-section-header {
                font-size: 8px;
                margin-right: 2px;
            }
        }

        @media (max-width: 360px) {
            .win-action-bar {
                min-width: 260px;
                padding: 3px 4px;
            }
            
            .win-action-btn {
                padding: 1px 3px;
                font-size: 7px;
                height: 18px;
                min-width: 20px;
            }
            
            .win-action-section-header {
                font-size: 7px;
                margin-right: 1px;
            }
        }
    </style>
    <style id="custom-styles"></style>
</head>
<body>
        <div class="widget-container" data-module="wins">
        <?php if(isset($_GET['realtime']) && $_GET['realtime'] === 'true'): ?>
        <div class="realtime-indicator">
            <i class="fas fa-broadcast-tower"></i>
            Mode Temps Réel - Les changements s'appliquent instantanément
        </div>
        <?php endif; ?>
        <!-- Affichage principal -->
        <div class="display" id="main-display">
            <div class="text-container">
                <h1 id="wins-display">WINS: 0/20</h1>
                <h2 id="multi-display">X<span>1</span> ACTIF</h2>
            </div>
        </div>
        
        <!-- Si paramètre control=true, afficher les contrôles -->
        <?php if($control): ?>
        <div class="win-action-bar" id="win-action-bar">
            <div class="win-action-bar-header">
                <div class="win-action-bar-title">
                    <i class="fas fa-trophy"></i>
                    Module Wins
                </div>
                <div class="win-action-bar-controls">
                    <a href="/modules/wins-config.php?token=<?=$token?>" class="win-action-bar-config">
                        <i class="fas fa-cog"></i>
                    </a>
                </div>
            </div>
            
            <div class="win-action-bar-sections">
                <!-- Statut du multiplicateur -->
                <div class="win-multiplier-status">
                    <button class="win-multiplier-toggle active" id="multiplier-toggle" data-active="true">
                        <i class="fas fa-check"></i> Multiplicateur ACTIF
                    </button>
                </div>
                
                <div class="win-action-section">
                    <div class="win-action-section-header">
                        <i class="fas fa-trophy"></i> Wins
                    </div>
                    <div class="win-action-buttons">
                        <button class="win-action-btn subtract" data-action="add-wins" data-value="-10">-10</button>
                        <button class="win-action-btn subtract" data-action="add-wins" data-value="-5">-5</button>
                        <button class="win-action-btn subtract" data-action="add-wins" data-value="-1">-1</button>
                        <button class="win-action-btn reset" data-action="reset-wins">RESET</button>
                        <button class="win-action-btn add" data-action="add-wins" data-value="1">+1</button>
                        <button class="win-action-btn add" data-action="add-wins" data-value="5">+5</button>
                        <button class="win-action-btn add" data-action="add-wins" data-value="10">+10</button>
                    </div>
                </div>
                
                <div class="win-action-section">
                    <div class="win-action-section-header">
                        <i class="fas fa-times"></i> Multiplicateur
                    </div>
                    <div class="win-action-buttons">
                        <button class="win-action-btn subtract" data-action="add-multi" data-value="-50">-50</button>
                        <button class="win-action-btn subtract" data-action="add-multi" data-value="-10">-10</button>
                        <button class="win-action-btn subtract" data-action="add-multi" data-value="-1">-1</button>
                        <button class="win-action-btn reset" data-action="reset-multi">RESET</button>
                        <button class="win-action-btn add" data-action="add-multi" data-value="1">+1</button>
                        <button class="win-action-btn add" data-action="add-multi" data-value="10">+10</button>
                        <button class="win-action-btn add" data-action="add-multi" data-value="50">+50</button>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
     </div>
    
    <script>
        // Variables globales
        const token = '<?=$token?>';
        const module = 'wins';
        let multiplierActive = true;
        
        // Variables globales simplifiées
        let currentData = { count: 0, multiplier: 1, multiplier_active: true, timestamp: 0 };
        
        // Fonction pour mettre à jour l'affichage (simplifiée)
        function updateDisplay(data) {
            if (data.data) {
                // Mettre à jour les données directement
                currentData = data.data;
                
                // Charger les styles pour obtenir max-wins et forcer mise à jour des couleurs
                loadStyles().then(() => {
                    updateWinsDisplay();
                    
                    // Forcer la mise à jour des couleurs si option activée
                    forceUpdateColorBasedOnValue();
                    
                    // Vérifier si le statut du multiplicateur a changé
                    if (currentData.multiplier_active !== undefined) {
                        // Convertir la string en booléen (l'API retourne '1' ou '0')
                        multiplierActive = (currentData.multiplier_active === true || currentData.multiplier_active === '1' || currentData.multiplier_active === 1);
                    }
                    updateMultiplierStatus();
                    updateMultiplierDisplay();
                });
            }
        }
        
        // Fonction pour forcer la mise à jour des couleurs basées sur la valeur
        function forceUpdateColorBasedOnValue() {
            // Vérifier dans la structure groupée d'abord
            if (currentStyles && currentStyles.options && (currentStyles.options['color-based-on-value'] === true || currentStyles.options['color-based-on-value'] === 'true' || currentStyles.options['color-based-on-value'] === 1)) {
                applyStyles(currentStyles);
            } else if (currentStyles && (currentStyles['color-based-on-value'] === true || currentStyles['color-based-on-value'] === 'true' || currentStyles['color-based-on-value'] === 1)) {
                // Support de l'ancienne structure
                applyStyles(currentStyles);
            }
        }
        
        // Fonction pour mettre à jour le statut du multiplicateur
        function updateMultiplierStatus() {
            const toggle = document.getElementById('multiplier-toggle');
            if (toggle) {
                if (multiplierActive) {
                    toggle.className = 'win-multiplier-toggle active';
                    toggle.innerHTML = '<i class="fas fa-check"></i> Multiplicateur ACTIF';
                } else {
                    toggle.className = 'win-multiplier-toggle inactive';
                    toggle.innerHTML = '<i class="fas fa-times"></i> Multiplicateur INACTIF';
                }
            }
        }
        
        // Fonction pour mettre à jour l'affichage du multiplicateur principal
        function updateMultiplierDisplay() {
            const multiDisplay = document.querySelector('#multi-display');
            
            // Ne pas modifier si les styles cachent le multiplicateur
            if (currentStyles && currentStyles['hide-multiplier']) {
                return; // Les styles CSS s'en occupent
            }
            
            if (multiplierActive) {
                const multiplierValue = parseInt(currentData.multiplier);
                multiDisplay.innerHTML = `X<span>${multiplierValue}</span> ACTIF`;
                
                // Couleur selon la valeur : rouge si x1, vert si supérieur
                if (multiplierValue === 1) {
                    multiDisplay.style.color = '#ff0000'; // Rouge
                } else {
                    multiDisplay.style.color = '#44ff00'; // Vert
                }
                
                // Utiliser la visibilité plutôt que display pour éviter les conflits avec les styles
                multiDisplay.style.visibility = 'visible';
                multiDisplay.style.opacity = '1';
            } else {
                // Utiliser la visibilité plutôt que display
                multiDisplay.style.visibility = 'hidden';
                multiDisplay.style.opacity = '0';
            }
        }
         
         // Variables globales pour les styles
         let currentStyles = {};
         
         // Fonction pour mettre à jour l'affichage des wins avec max-wins
         function updateWinsDisplay() {
            // Chercher max-wins dans la structure groupée
            let maxWins = 20; // Valeur par défaut
            if (currentStyles.general && currentStyles.general['max-wins']) {
                maxWins = parseInt(currentStyles.general['max-wins']);
            } else if (currentStyles['max-wins']) {
                // Support de l'ancienne structure
                maxWins = parseInt(currentStyles['max-wins']);
            }
            
            const count = currentData.count;
            let displayText = `WINS: ${count}/${maxWins}`;
            
            if (count > maxWins) {
                displayText += ` (+${count - maxWins})`;
            }
            
            document.querySelector('#wins-display').innerHTML = displayText;
        }
        
                         // Fonction pour charger les styles
        async function loadStyles() {
            try {
                // CORRECTION : Utiliser le bon endpoint pour récupérer les styles
                const response = await fetch(`/api.php?token=${token}&module=style&action=get`);
                const data = await response.json();
                
                if (data.success && data.data) {
                    currentStyles = data.data;
                    applyStyles(currentStyles);
                    
                    // Sauvegarder en localStorage comme fallback
                    localStorage.setItem('winsStylesFallback', JSON.stringify(data.data));
                    localStorage.setItem('winsStylesTimestamp', data.timestamp || Date.now());
                } else {
                    // Fallback : essayer localStorage
                    const fallbackStyles = localStorage.getItem('winsStylesFallback');
                    if (fallbackStyles) {
                        currentStyles = JSON.parse(fallbackStyles);
                        applyStyles(currentStyles);
                    }
                }
            } catch (error) {
                console.error('Erreur lors du chargement des styles:', error);
                // Fallback : essayer localStorage
                const fallbackStyles = localStorage.getItem('winsStylesFallback');
                if (fallbackStyles) {
                    currentStyles = JSON.parse(fallbackStyles);
                    applyStyles(currentStyles);
                }
            }
        }
        
        // ===================================================
        // FONCTION D'APPLICATION DES STYLES (NOUVELLE VERSION 2.0)
        // STRUCTURE GROUPÉE POUR ÉVITER LES CONFLITS
        // ===================================================
        function applyStyles(styles) {
            // Ne pas appliquer si on est en mode temps réel
            if (isApplyingRealtimeStyles) {
                return;
            }
            
            // Validation des styles
            if (!styles || typeof styles !== 'object') {
                return;
            }
            
            // Migration automatique si ancienne structure détectée
            if (!styles.wins && styles['wins-color']) {
                styles = migrateStylesStructure(styles);
            }
            
            // Vérifier currentData
            if (!currentData) {
                return;
            }
            
            let css = '';
            
            // ===================================================
            // 🎨 1. STYLES GÉNÉRAUX (Background, Police, Position)
            // ===================================================
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
                    css += `#wins-display, #multi-display { font-family: ${general['font-family']} !important; } `;
                }
                
                // Positionnement du texte
                if (general['text-position']) {
                    const margin = general['text-margin'] || '0';
                    css += generatePositionCSS(general['text-position'], margin);
                }
            }
            
            // ===================================================
            // ⚙️ 2. OPTIONS GLOBALES (Visibilité)
            // ===================================================
            if (styles.options) {
                const options = styles.options;
                
                // Masquer les contrôles si demandé
                if (options['hide-controls'] === true || options['hide-controls'] === 'true' || options['hide-controls'] === 1) {
                    css += '.win-action-bar { display: none !important; } ';
                    css += '.win-action-bar-config { display: none !important; } ';
                } else {
                    css += '.win-action-bar { display: flex !important; } ';
                }
                
                // Masquer le multiplicateur si demandé
                if (options['hide-multiplier'] === true || options['hide-multiplier'] === 'true' || options['hide-multiplier'] === 1) {
                    css += '#multi-display { display: none !important; } ';
                } else if (!multiplierActive) {
                    css += '#multi-display { display: none !important; } ';
                } else {
                    css += '#multi-display { display: block !important; } ';
                }
            }
            
            // ===================================================
            // 🏆 3. STYLES WINS (Couleur, Taille, Effets)
            // ===================================================
            if (styles.wins) {
                const wins = styles.wins;
                
                // Couleur basée sur la valeur VS couleur fixe - LOGIQUE SIMPLIFIÉE
                if (styles.options && (styles.options['color-based-on-value'] === true || styles.options['color-based-on-value'] === 'true' || styles.options['color-based-on-value'] === 1)) {
                    const winsValue = parseInt(currentData.count) || 0;
                    if (winsValue < 0) {
                        css += '#wins-display { color: #ff4444 !important; } '; // Rouge pour négatif
                        css += '#wins-display { text-shadow: 0 0 20px #ff0000, 0 0 40px #ff0000 !important; } '; // Effet glow rouge
                    } else if (winsValue > 0) {
                        css += '#wins-display { color: #44ff44 !important; } '; // Vert pour positif
                        css += '#wins-display { text-shadow: 0 0 20px #00ff00, 0 0 40px #00ff00 !important; } '; // Effet glow vert
                    } else {
                        css += '#wins-display { color: #ffffff !important; } '; // Blanc pour zéro
                        css += '#wins-display { text-shadow: 0 0 20px rgba(255, 255, 255, 0.5) !important; } ';
                    }
                } else if (wins.color) {
                    css += `#wins-display { color: ${wins.color} !important; } `;
                }
                
                // Taille
                if (wins.size) {
                    css += `#wins-display { font-size: ${wins.size}px !important; } `;
                }
                
                // Contour
                if (wins.stroke) {
                    css += `#wins-display { -webkit-text-stroke: 2px ${wins.stroke} !important; text-stroke: 2px ${wins.stroke} !important; } `;
                }
                
                // Ombre
                if (wins.shadow === true || wins.shadow === 'true' || wins.shadow === 1) {
                    css += '#wins-display { text-shadow: 3px 3px 6px rgba(0,0,0,0.8) !important; } ';
                } else {
                    css += '#wins-display { text-shadow: none !important; } ';
                }
                
                // Effets d'animation
                if (wins.effect && wins.effect !== 'none') {
                    css += generateEffectCSS('wins', wins.effect, wins['effect-speed'] || '1', wins['effect-pause'] || false);
                }
            }
            
            // ===================================================
            // 🔥 4. STYLES MULTIPLICATEUR (Couleur, Taille, Position)
            // ===================================================
            if (styles.multi) {
                const multi = styles.multi;
                
                // Couleur
                if (multi.color) {
                    css += `#multi-display { color: ${multi.color} !important; } `;
                }
                
                // Taille
                if (multi.size) {
                    css += `#multi-display { font-size: ${multi.size}px !important; } `;
                }
                
                // Contour
                if (multi.stroke) {
                    css += `#multi-display { -webkit-text-stroke: 2px ${multi.stroke} !important; text-stroke: 2px ${multi.stroke} !important; } `;
                }
                
                // Ombre
                if (multi.shadow === true || multi.shadow === 'true' || multi.shadow === 1) {
                    css += '#multi-display { text-shadow: 3px 3px 6px rgba(0,0,0,0.8) !important; } ';
                } else {
                    css += '#multi-display { text-shadow: none !important; } ';
                }
                
                // Position verticale
                if (multi['vertical-offset']) {
                    const offset = parseInt(multi['vertical-offset']) || 0;
                    css += `#multi-display { transform: translateY(${offset}px) !important; position: relative !important; } `;
                }
                
                // Effets d'animation
                if (multi.effect && multi.effect !== 'none') {
                    css += generateEffectCSS('multi', multi.effect, multi['effect-speed'] || '1', multi['effect-pause'] || false);
                }
            }
            
            // ===================================================
            // 🎯 APPLICATION FINALE DES STYLES
            // ===================================================
            applyCSS(css);
            
        }
        
        // ===================================================
        // FONCTIONS UTILITAIRES POUR LA GESTION DES STYLES
        // ===================================================
        
        // Migration des anciens styles vers la nouvelle structure
        function migrateStylesStructure(oldStyles) {
            const newStyles = {
                wins: {
                    color: oldStyles['wins-color'] || '#ffffff',
                    size: oldStyles['wins-size'] || '64',
                    stroke: oldStyles['wins-stroke'] || '#000000',
                    shadow: oldStyles['wins-shadow'] || false,
                    effect: oldStyles['wins-effect'] || 'none',
                    'effect-speed': oldStyles['wins-effect-speed'] || '1',
                    'effect-pause': oldStyles['wins-effect-pause'] || false
                },
                multi: {
                    color: oldStyles['multi-color'] || '#ffffff',
                    size: oldStyles['multi-size'] || '48',
                    stroke: oldStyles['multi-stroke'] || '#000000',
                    shadow: oldStyles['multi-shadow'] || false,
                    'vertical-offset': oldStyles['multi-vertical-offset'] || '0',
                    effect: oldStyles['multi-effect'] || 'none',
                    'effect-speed': oldStyles['multi-effect-speed'] || '1',
                    'effect-pause': oldStyles['multi-effect-pause'] || false
                },
                general: {
                    'font-family': oldStyles['font-family'] || 'Arial, Helvetica, sans-serif',
                    background: oldStyles['background'] || '#1e293b',
                    'text-position': oldStyles['text-position'] || 'center',
                    'text-margin': oldStyles['text-margin'] || '0',
                    transparent: oldStyles['transparent'] || false,
                    'max-wins': oldStyles['max-wins'] || '20'
                },
                options: {
                    'color-based-on-value': oldStyles['color-based-on-value'] || false,
                    'hide-controls': oldStyles['hide-controls'] || false,
                    'hide-multiplier': oldStyles['hide-multiplier'] || false
                },
                meta: {
                    version: '2.0',
                    'migrated-from': 'flat-structure',
                    'migrated-at': Date.now()
                }
            };
            
            
            return newStyles;
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
        
        // Génération du CSS d'effets d'animation
        function generateEffectCSS(target, effect, speed, pauseOnHover) {
            const targetElement = target === 'wins' ? '#wins-display' : '#multi-display';
            const pauseCSS = pauseOnHover ? `${targetElement}:hover { animation-play-state: paused !important; }` : '';
            
            const effects = {
                'zoom': `${targetElement} { animation: zoomInOut ${2/speed}s ease-in-out infinite; } ${pauseCSS}`,
                'pulse': `${targetElement} { animation: pulse ${1.5/speed}s ease-in-out infinite; } ${pauseCSS}`,
                'bounce': `${targetElement} { animation: bounce ${1/speed}s ease-in-out infinite; } ${pauseCSS}`,
                'glow': `${targetElement} { animation: glow ${2/speed}s ease-in-out infinite; } ${pauseCSS}`,
                'shake': `${targetElement} { animation: shake ${0.5/speed}s ease-in-out infinite; } ${pauseCSS}`,
                'rotate': `${targetElement} { animation: rotate ${3/speed}s linear infinite; } ${pauseCSS}`
            };
            
            return effects[effect] || '';
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
        
        // Fonction pour faire une requête API (version sans restrictions)
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
        
        // NOUVELLE FONCTION : Initialisation robuste
        function initializeRobustly() {
            // Charger les données initiales
            apiCall('get');
            
            // Charger les styles avec retry
            loadStyles().catch(error => {
                console.error('Erreur lors du chargement des styles:', error);
                
                // Retry après 2 secondes
                setTimeout(() => {
                    loadStyles();
                }, 2000);
            });
            
            // Charger les styles temps réel persistés avec priorité
            try {
                const persistedStyles = localStorage.getItem('currentRealtimeStyles');
                const realtimeStyles = localStorage.getItem('realtimeStyles');
                
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
                console.warn('Erreur lors du chargement des styles temps réel:', error);
            }
        }

        // Système de toggle supprimé - panneau toujours visible
        
        // MODIFIER l'initialisation pour être plus robuste
        document.addEventListener('DOMContentLoaded', () => {
            initializeRobustly();
        });
        
        // Initialisation de fallback si DOMContentLoaded n'est pas déclenché
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initializeRobustly);
        } else {
            initializeRobustly();
        }
        
        // Forcer un rechargement des styles après 500ms
        setTimeout(() => {
            loadStyles();
        }, 500);
        
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
        
        // Fonction pour appliquer les styles en temps réel (version 2.0 optimisée)
        function applyRealtimeStyles(styles) {
            // Migration automatique si nécessaire
            if (!styles.wins && styles['wins-color']) {
                styles = migrateStylesStructure(styles);
            }
            
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
            localStorage.setItem('currentRealtimeStyles', JSON.stringify(currentRealtimeStyles));
            localStorage.setItem('stylesStructureVersion', '2.0');
            
            // Appliquer immédiatement les styles avec la nouvelle fonction
            applyStyles(styles);
            
            // Réactiver le flag après un délai très court pour une meilleure réactivité
            setTimeout(() => {
                isApplyingRealtimeStyles = false;
            }, 25); // Encore plus rapide pour une meilleure réactivité
        }
        
        // Écouter postMessage
        window.addEventListener('message', (event) => {
            if (event.data && event.data.type === 'stylesUpdate') {
                applyRealtimeStyles(event.data.styles);
            }
            if (event.data && event.data.type === 'stylesSaved') {
                isRealtimeMode = false;
                lastStylesHash = '';
                currentRealtimeStyles = {}; // Réinitialiser après sauvegarde
                localStorage.removeItem('currentRealtimeStyles'); // Nettoyer localStorage
            }
        });
        
        // Écouter les événements personnalisés pour une réactivité instantanée
        window.addEventListener('styleUpdate', (event) => {
            if (event.detail && event.detail.styles) {
                applyRealtimeStyles(event.detail.styles);
            }
        });
        
        // Écouter BroadcastChannel (version améliorée)
        if (window.BroadcastChannel) {
            const channel = new BroadcastChannel('styles_channel');
            channel.onmessage = (event) => {
                if (event.data && event.data.type === 'stylesUpdate') {
                    applyRealtimeStyles(event.data.styles);
                }
                if (event.data && event.data.type === 'stylesSaved') {
                    isRealtimeMode = false;
                    lastStylesHash = '';
                    currentRealtimeStyles = {}; // Réinitialiser après sauvegarde
                    localStorage.removeItem('currentRealtimeStyles'); // Nettoyer localStorage
                }
            };
        }
        
        // Vérifier localStorage (version 2.0 super réactive et propre)
        setInterval(() => {
            // Ne pas vérifier si on est en train d'appliquer des styles temps réel
            if (isApplyingRealtimeStyles) return;
            
            const stylesTimestamp = localStorage.getItem('stylesTimestamp');
            const lastTimestamp = window.lastStylesTimestamp || 0;
            
            // Vérifier le signal de force update avec réactivité améliorée
            const forceUpdate = localStorage.getItem('forceStyleUpdate');
            if (forceUpdate && forceUpdate !== window.lastForceUpdate) {
                window.lastForceUpdate = forceUpdate;
                const styles = JSON.parse(localStorage.getItem('realtimeStyles') || '{}');
                
                if (Object.keys(styles).length > 0) {
                    // Migration automatique si nécessaire
                    const finalStyles = (!styles.wins && styles['wins-color']) ? 
                        migrateStylesStructure(styles) : styles;
                    applyRealtimeStyles(finalStyles);
                }
                return;
            }
            
            if (stylesTimestamp && parseInt(stylesTimestamp) > lastTimestamp) {
                const styles = JSON.parse(localStorage.getItem('realtimeStyles') || '{}');
                
                if (Object.keys(styles).length > 0) {
                    // Migration automatique pour la cohérence
                    const finalStyles = (!styles.wins && styles['wins-color']) ? 
                        migrateStylesStructure(styles) : styles;
                    applyRealtimeStyles(finalStyles);
                    window.lastStylesTimestamp = parseInt(stylesTimestamp);
                }
            }
        }, 15); // Encore plus rapide : 15ms pour une réactivité ultra-rapide
        
        // Vérification supplémentaire pour les changements critiques (version 2.0)
        setInterval(() => {
            const stylesData = localStorage.getItem('realtimeStyles');
            if (stylesData) {
                try {
                    const styles = JSON.parse(stylesData);
                    
                    // Gérer la nouvelle structure groupée pour color-based-on-value
                    let hasColorBasedOnValue = false;
                    if (styles.options && (styles.options['color-based-on-value'] === true || styles.options['color-based-on-value'] === 'true' || styles.options['color-based-on-value'] === 1)) {
                        hasColorBasedOnValue = true;
                    } else if (styles['color-based-on-value'] === true || styles['color-based-on-value'] === 'true' || styles['color-based-on-value'] === 1) {
                        // Support de l'ancienne structure
                        hasColorBasedOnValue = true;
                    }
                    
                    // Si l'option couleur basée sur valeur est activée, vérifier si on doit mettre à jour
                    if (hasColorBasedOnValue && currentData.count !== undefined) {
                        const currentHash = JSON.stringify(styles) + currentData.count;
                        if (currentHash !== window.lastColorUpdateHash) {
                            // Migration automatique si nécessaire
                            const finalStyles = (!styles.wins && styles['wins-color']) ? 
                                migrateStylesStructure(styles) : styles;
                            applyRealtimeStyles(finalStyles);
                            window.lastColorUpdateHash = currentHash;
                        }
                    }
                } catch (e) {
                    console.warn('Erreur parsing styles pour color-based:', e);
                }
            }
            
            // Vérifier le signal de nettoyage de debug
            const clearDebug = localStorage.getItem('clearDebug');
            if (clearDebug && clearDebug !== window.lastClearDebug) {
                window.lastClearDebug = clearDebug;
                
                // Réinitialiser les variables
                isRealtimeMode = false;
                lastStylesHash = '';
                currentRealtimeStyles = {};
                window.lastStylesTimestamp = 0;
                window.lastForceUpdate = '';
                window.lastColorUpdateHash = '';
            }
        }, 30); // Ultra optimisé à 30ms pour la réactivité couleur-basée-sur-valeur
        
        <?php if($control): ?>
        // Gestion des boutons
        setTimeout(() => {
            const buttons = document.querySelectorAll('.win-action-btn');
            
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
                    const value = button.dataset.value || '';
                    
                    try {
                        await apiCall(action, value);
                    } catch (error) {
                        console.error('Erreur API:', error);
                    }
                    
                    // Réactiver après un court délai
                    setTimeout(() => {
                        button.disabled = false;
                        button.innerHTML = originalText;
                    }, 50); // Réduit à 50ms pour une réactivité maximale
                });
            });
            
            // Gestion du toggle du multiplicateur
            const multiplierToggle = document.getElementById('multiplier-toggle');
            if (multiplierToggle) {
                multiplierToggle.addEventListener('click', async () => {
                    // Basculer l'état local immédiatement pour la réactivité
                    multiplierActive = !multiplierActive;
                    updateMultiplierStatus();
                    updateMultiplierDisplay();
                    
                    // Envoyer la mise à jour au serveur
                    await apiCall('set-multi-active', multiplierActive ? 'true' : 'false');
                });
            }
        }, 100); // Réduit à 100ms pour une initialisation plus rapide
        <?php endif; ?>
    </script>
</body>
</html>