<?php
// Inclure la validation des tokens
require_once __DIR__ . '/modules/validate_token.php';

// Valider le token et récupérer les infos utilisateur
$user = requireValidToken();

// Récupération du token depuis les paramètres GET
$token = $_GET['token'] ?? '';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StreamPro Studio - Dashboard</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* ==================== GLOBAL STYLES ==================== */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: #0a0e1b;
            color: #ffffff;
            min-height: 100vh;
            overflow-x: hidden;
            position: relative;
        }

        /* ==================== INTRO VIDEO STYLES ==================== */
        .intro-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background: #000;
            z-index: 10000;
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 1;
            transition: opacity 1s ease-out;
        }

        .intro-overlay.hidden {
            opacity: 0;
            pointer-events: none;
        }

        .intro-video {
            width: 100vw;
            height: 100vh;
            object-fit: cover;
        }

        .skip-button {
            position: absolute;
            top: 30px;
            right: 30px;
            background: rgba(255, 255, 255, 0.1);
            border: 2px solid rgba(255, 255, 255, 0.3);
            color: white;
            padding: 12px 24px;
            border-radius: 25px;
            cursor: pointer;
            font-family: 'Inter', sans-serif;
            font-weight: 500;
            font-size: 14px;
            backdrop-filter: blur(10px);
            transition: all 0.3s ease;
            z-index: 10001;
        }

        .skip-button:hover {
            background: rgba(255, 255, 255, 0.2);
            border-color: rgba(255, 255, 255, 0.5);
            transform: translateY(-2px);
        }

        .dashboard-content {
            opacity: 0;
            transition: opacity 1s ease-in;
        }

        .dashboard-content.visible {
            opacity: 1;
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

        /* ==================== PARTICLES ==================== */
        .particles {
            position: fixed;
            width: 100%;
            height: 100%;
            overflow: hidden;
            z-index: 0;
            pointer-events: none;
        }

        .particle {
            position: absolute;
            width: 4px;
            height: 4px;
            background: linear-gradient(135deg, #00d4ff, #ff006e);
            border-radius: 50%;
            opacity: 0;
            animation: particleFloat 15s linear infinite;
        }

        .particle:nth-child(1) { left: 10%; animation-delay: 0s; }
        .particle:nth-child(2) { left: 25%; animation-delay: 3s; }
        .particle:nth-child(3) { left: 40%; animation-delay: 6s; }
        .particle:nth-child(4) { left: 55%; animation-delay: 9s; }
        .particle:nth-child(5) { left: 70%; animation-delay: 12s; }
        .particle:nth-child(6) { left: 85%; animation-delay: 15s; }

        @keyframes particleFloat {
            0% {
                transform: translateY(100vh) translateX(0) scale(0);
                opacity: 0;
            }
            10% {
                opacity: 1;
                transform: translateY(90vh) translateX(10px) scale(1);
            }
            90% {
                opacity: 1;
                transform: translateY(10vh) translateX(-10px) scale(1);
            }
            100% {
                transform: translateY(0) translateX(0) scale(0);
                opacity: 0;
            }
        }

        /* ==================== MAIN CONTAINER ==================== */
        .dashboard-container {
            position: relative;
            z-index: 1;
            padding: 2rem;
            max-width: 1400px;
            margin: 0 auto;
            animation: fadeIn 1s ease-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* ==================== HEADER (Sans box) ==================== */
        .header {
            text-align: center;
            margin-bottom: 2rem;
            padding: 2rem 0;
        }

        .logo-container {
            margin-bottom: 1.5rem;
            display: inline-block;
            position: relative;
        }

        .logo-img {
            width: 150px;
            height: 150px;
            object-fit: contain;
            border-radius: 25px;
            animation: glow 3s ease-in-out infinite;
            border: none;
            outline: none;
            background: transparent;
            transition: all 0.3s ease;
        }

        .logo-img:hover {
            transform: translateY(-5px) scale(1.1);
        }

        @keyframes glow {
            0%, 100% { 
                filter: brightness(1) drop-shadow(0 0 20px rgba(139, 0, 255, 0.5));
                transform: scale(1);
            }
            50% { 
                filter: brightness(1.2) drop-shadow(0 0 40px rgba(139, 0, 255, 0.8));
                transform: scale(1.05);
            }
        }

        h1 {
            font-size: 3rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            background: linear-gradient(135deg, #ff006e, #8b00ff, #00d4ff);
            background-clip: text;
            -webkit-background-clip: text;
            color: transparent;
            animation: textShimmer 4s ease-in-out infinite;
        }

        @keyframes textShimmer {
            0%, 100% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
        }

        .subtitle {
            font-size: 1.2rem;
            color: #a0a0a0;
            font-weight: 300;
        }

        /* ==================== NAVIGATION TABS ==================== */
        .navigation {
            display: flex;
            justify-content: center;
            gap: 1rem;
            margin-bottom: 3rem;
            flex-wrap: wrap;
        }

        .nav-tab {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: #ffffff;
            padding: 0.8rem 1.5rem;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            backdrop-filter: blur(10px);
            cursor: pointer;
        }

        .nav-tab:hover {
            background: linear-gradient(135deg, rgba(255, 0, 110, 0.2), rgba(139, 0, 255, 0.2));
            border-color: rgba(139, 0, 255, 0.5);
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(139, 0, 255, 0.3);
        }

        .nav-tab.active {
            background: linear-gradient(135deg, #ff006e, #8b00ff);
            border-color: transparent;
            color: white;
        }

        .nav-tab i {
            font-size: 1rem;
        }

        /* ==================== MODULES SECTION ==================== */
        .modules-section {
            margin-bottom: 3rem;
        }

        .section-title {
            font-size: 2rem;
            margin-bottom: 2rem;
            text-align: center;
            color: #00d4ff;
            font-weight: 600;
        }

        .modules-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 2rem;
            margin-bottom: 3rem;
        }

        /* ==================== MODULE CARD ==================== */
        .module-card {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            padding: 2rem;
            backdrop-filter: blur(20px);
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            position: relative;
            overflow: hidden;
        }

        .module-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, #ff006e, #8b00ff, #00d4ff);
            transform: scaleX(0);
            transition: transform 0.4s ease;
        }

        .module-card:hover {
            transform: translateY(-10px) rotateX(5deg);
            box-shadow: 
                0 20px 40px rgba(139, 0, 255, 0.3),
                0 0 60px rgba(255, 0, 110, 0.2) inset;
            border-color: rgba(139, 0, 255, 0.5);
        }

        .module-card:hover::before {
            transform: scaleX(1);
        }

        /* Module Icons avec dégradés */
        .module-icon {
            width: 80px;
            height: 80px;
            margin: 0 auto 1.5rem;
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            position: relative;
            overflow: hidden;
        }

        .module-icon.wins {
            background: linear-gradient(135deg, #ff006e, #ff4081);
        }

        .module-icon.timer {
            background: linear-gradient(135deg, #8b00ff, #b300ff);
        }

        .module-icon.battle {
            background: linear-gradient(135deg, #00d4ff, #00a8cc);
        }

        .module-icon i {
            position: relative;
            z-index: 1;
            filter: drop-shadow(0 2px 10px rgba(0, 0, 0, 0.3));
        }

        /* Effet de brillance animé */
        .module-icon::after {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(
                45deg,
                transparent 30%,
                rgba(255, 255, 255, 0.3) 50%,
                transparent 70%
            );
            transform: rotate(45deg) translateY(100%);
            animation: shine 3s ease-in-out infinite;
        }

        @keyframes shine {
            0% { transform: rotate(45deg) translateY(100%); }
            50%, 100% { transform: rotate(45deg) translateY(-100%); }
        }

        .module-info h3 {
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
            color: #ffffff;
        }

        .module-status {
            display: inline-block;
            padding: 0.3rem 0.8rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            margin-bottom: 1rem;
        }

        .module-status.active {
            background: rgba(16, 185, 129, 0.2);
            color: #10b981;
            border: 1px solid rgba(16, 185, 129, 0.5);
        }

        .module-status.coming-soon {
            background: rgba(245, 158, 11, 0.2);
            color: #f59e0b;
            border: 1px solid rgba(245, 158, 11, 0.5);
        }

        .module-description {
            color: #a0a0a0;
            margin-bottom: 1.5rem;
            line-height: 1.6;
        }

        /* ==================== ACTION BUTTONS ==================== */
        .module-actions {
            display: flex;
            gap: 1rem;
            margin-top: auto;
        }

        .module-btn {
            flex: 1;
            padding: 0.8rem 1.5rem;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            text-align: center;
            position: relative;
            overflow: hidden;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .module-btn.primary {
            background: linear-gradient(135deg, #ff006e, #8b00ff);
            color: white;
            border: none;
        }

        .module-btn.secondary {
            background: rgba(255, 255, 255, 0.1);
            color: white;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .module-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(139, 0, 255, 0.4);
        }

        /* ==================== TOKEN INFO (Sous les modules) ==================== */
        .token-section {
            text-align: center;
            margin-top: 4rem;
            padding-top: 3rem;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }

        .token-container {
            display: inline-block;
            padding: 2rem 3rem;
            background: rgba(139, 0, 255, 0.1);
            border-radius: 20px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(139, 0, 255, 0.3);
            animation: float 3s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }

        .token-title {
            color: #00d4ff;
            margin-bottom: 1rem;
            font-size: 1.5rem;
            font-weight: 600;
        }

        .token-display {
            display: flex;
            align-items: center;
            gap: 1rem;
            justify-content: center;
        }

        .token-value {
            font-family: monospace;
            background: rgba(0, 0, 0, 0.3);
            padding: 0.8rem 1.5rem;
            border-radius: 10px;
            font-size: 0.9rem;
            color: #00d4ff;
            word-break: break-all;
            max-width: 500px;
        }

        .copy-button {
            background: linear-gradient(135deg, #ff006e, #8b00ff);
            border: none;
            color: white;
            padding: 0.8rem 1.5rem;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 600;
        }

        .copy-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(255, 0, 110, 0.4);
        }

        /* ==================== MODAL STYLES ==================== */
        .modal {
            display: none;
            position: fixed;
            z-index: 9999;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.8);
            backdrop-filter: blur(10px);
        }

        .modal.show {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .modal-content {
            background: rgba(20, 25, 40, 0.95);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            padding: 3rem;
            max-width: 700px;
            max-height: 80vh;
            overflow-y: auto;
            position: relative;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
            animation: modalSlideIn 0.3s ease;
        }

        @keyframes modalSlideIn {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .modal-title {
            font-size: 1.8rem;
            font-weight: 600;
            background: linear-gradient(135deg, #ff006e, #8b00ff);
            background-clip: text;
            -webkit-background-clip: text;
            color: transparent;
        }

        .close-btn {
            background: none;
            border: none;
            color: #ffffff;
            font-size: 1.5rem;
            cursor: pointer;
            padding: 0.5rem;
            border-radius: 10px;
            transition: all 0.3s ease;
        }

        .close-btn:hover {
            background: rgba(255, 0, 110, 0.2);
            color: #ff006e;
        }

        .modal-body {
            color: #a0a0a0;
            line-height: 1.8;
        }

        .modal-body h3 {
            color: #00d4ff;
            margin: 2rem 0 1rem 0;
            font-size: 1.3rem;
        }

        .modal-body p {
            margin-bottom: 1rem;
        }

        /* ==================== RESPONSIVE ==================== */
        @media (max-width: 768px) {
            .dashboard-container {
                padding: 1rem;
            }

            h1 {
                font-size: 2rem;
            }

            .logo-img {
                width: 100px;
                height: 100px;
            }

            .navigation {
                gap: 0.5rem;
            }

            .nav-tab {
                padding: 0.6rem 1rem;
                font-size: 0.9rem;
            }

            .modules-grid {
                grid-template-columns: 1fr;
                gap: 1.5rem;
            }

            .module-card {
                padding: 1.5rem;
            }

            .token-value {
                font-size: 0.8rem;
                padding: 0.6rem 1rem;
            }
        }

        /* ==================== LOADING BAR ==================== */
        .loading-bar {
            width: 100%;
            height: 3px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 3px;
            overflow: hidden;
            margin: 1rem 0;
        }

        .loading-progress {
            height: 100%;
            background: linear-gradient(90deg, #ff006e, #8b00ff, #00d4ff);
            width: 75%;
            animation: loading 2s ease-in-out infinite;
        }

        @keyframes loading {
            0% { transform: translateX(-100%); }
            100% { transform: translateX(133%); }
        }
    </style>
</head>
<body>
    <!-- Intro Video Overlay -->
    <div class="intro-overlay" id="introOverlay">
        <video class="intro-video" id="introVideo" muted autoplay playsinline>
            <source src="intro.mp4" type="video/mp4">
            Votre navigateur ne supporte pas la lecture vidéo.
        </video>
        <button class="skip-button" id="skipButton">
            <i class="fas fa-forward"></i> Passer l'intro
        </button>
    </div>

    <!-- Dashboard Content (masqué initialement) -->
    <div class="dashboard-content" id="dashboardContent">
        <!-- Particles Background -->
        <div class="particles">
            <div class="particle"></div>
            <div class="particle"></div>
            <div class="particle"></div>
            <div class="particle"></div>
            <div class="particle"></div>
            <div class="particle"></div>
        </div>

        <div class="dashboard-container">
        <!-- Header (sans box) -->
        <header class="header">
            <div class="logo-container">
                <img src="https://i.goopics.net/g93k7n.png" alt="StreamPro Studio" class="logo-img">
            </div>
            <h1>StreamPro Studio</h1>
            <p class="subtitle">Centre de Contrôle Créateur</p>
        </header>

        <!-- Navigation Tabs -->
        <nav class="navigation">
            <a href="#" class="nav-tab active">
                <i class="fas fa-tachometer-alt"></i>
                Dashboard
            </a>
            <a href="#" class="nav-tab" onclick="openModal('rules'); return false;">
                <i class="fas fa-scroll"></i>
                Règlement
            </a>
            <a href="#" class="nav-tab" onclick="openModal('prereq'); return false;">
                <i class="fas fa-clipboard-check"></i>
                Prérequis
            </a>
            <a href="https://discord.gg/TbXYYsEgqz" target="_blank" class="nav-tab">
                <i class="fab fa-discord"></i>
                Discord
            </a>
        </nav>

        <!-- Active Modules -->
        <section class="modules-section">
            <h2 class="section-title">
                <i class="fas fa-rocket"></i> Modules Actifs
            </h2>
            
            <div class="modules-grid">
                <!-- Wins Counter Module -->
                <div class="module-card">
                    <div class="module-icon wins">
                        <i class="fas fa-trophy"></i>
                    </div>
                    <div class="module-info">
                        <h3>Compteur de Wins</h3>
                        <span class="module-status active">Actif</span>
                    </div>
                    <p class="module-description">
                        Widget interactif pour compter les victoires en temps réel avec système de multiplicateur et effets visuels spectaculaires.
                    </p>
                    <div class="module-actions">
                        <a href="/modules/win.php?token=<?=$token?>&control=true" class="module-btn primary">
                            <i class="fas fa-play"></i>
                            Lancer
                        </a>
                        <a href="/modules/wins-config.php?token=<?=$token?>" class="module-btn secondary">
                            <i class="fas fa-cog"></i>
                            Configurer
                        </a>
                    </div>
                </div>

                <!-- Timer Module -->
                <div class="module-card">
                    <div class="module-icon timer">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="module-info">
                        <h3>Timer Interactif</h3>
                        <span class="module-status active">Actif</span>
                    </div>
                    <p class="module-description">
                        Chronomètre personnalisable avec contrôles avancés, idéal pour gérer vos sessions de stream et créer de l'engagement.
                    </p>
                    <div class="module-actions">
                        <a href="/modules/timer.php?token=<?=$token?>&control=true" class="module-btn primary">
                            <i class="fas fa-play"></i>
                            Lancer
                        </a>
                        <a href="/modules/timer-config.php?token=<?=$token?>" class="module-btn secondary">
                            <i class="fas fa-cog"></i>
                            Configurer
                        </a>
                    </div>
                </div>

                <!-- Team Battle Module -->
                <div class="module-card">
                    <div class="module-icon battle">
                        <i class="fas fa-users-cog"></i>
                    </div>
                    <div class="module-info">
                        <h3>Team VS Team</h3>
                        <span class="module-status active">Actif</span>
                    </div>
                    <p class="module-description">
                        Système de bataille d'équipes avec scores en temps réel, effets visuels personnalisables et contrôles avancés.
                    </p>
                    <div class="module-actions">
                        <a href="/modules/team-battle.php?token=<?=$token?>&control=true" class="module-btn primary">
                            <i class="fas fa-play"></i>
                            Lancer
                        </a>
                        <a href="/modules/teams-config.php?token=<?=$token?>" class="module-btn secondary">
                            <i class="fas fa-cog"></i>
                            Configurer
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <!-- Token Section (sous les modules) -->
        <section class="token-section">
            <div class="token-container">
                <h2 class="token-title">
                    <i class="fas fa-key"></i> Ton Token Personnel
                </h2>
                <div class="token-display">
                    <span class="token-value" id="tokenValue"><?= htmlspecialchars($token) ?></span>
                    <button class="copy-button" onclick="copyToken()">
                        <i class="fas fa-copy"></i> Copier
                    </button>
                </div>
            </div>
        </section>
    </div>

    <!-- Modal Règlement -->
    <div id="rulesModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title">📜 Règlement MyFull Agency</h2>
                <button class="close-btn" onclick="closeModal('rules')">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <p style="text-align: center; color: #00d4ff; font-size: 1.1rem; margin-bottom: 2rem;">
                    Bienvenue à bord du vaisseau MyFull ! 🚀<br>
                    Quelques règles cosmiques avant le décollage...
                </p>

                <h3>😎 Respect intergalactique obligatoire</h3>
                <p>Pas de lasers d'insultes, ni d'attaques orbitales. On garde la vibe positive entre astronautes ✌️</p>

                <h3>🛸 Contenu adapté à la mission</h3>
                <p>Les trous noirs de contenu choquant, NSFW ou illégal sont interdits. On vole clean dans la galaxie ✨</p>

                <h3>🚀 Pas de spam dans l'espace-temps</h3>
                <p>Pas besoin de bombarder les canaux comme des météorites. Un message bien placé vaut mieux qu'un astéroïde de spam.</p>

                <h3>🛰️ Garde tes coordonnées secrètes</h3>
                <p>Ne partage pas tes infos perso (même pas ton mot de passe interstellaire). La confidentialité, c'est sacré dans notre orbite 🔒</p>

                <div style="background: rgba(139, 0, 255, 0.1); border-radius: 15px; padding: 1.5rem; margin-top: 2rem; text-align: center;">
                    <strong style="color: #ff006e; font-size: 1.2rem;">🎙️ Prêt(e) à décoller avec la team MyFull ?</strong><br>
                    <span style="color: #ffffff;">Alors attache ta ceinture, active ta caméra, et que le live commence ! 🌌</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Prérequis -->
    <div id="prereqModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title">📋 Prérequis - Avant Setup</h2>
                <button class="close-btn" onclick="closeModal('prereq')">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <div style="background: linear-gradient(135deg, rgba(255, 0, 110, 0.2), rgba(245, 158, 11, 0.2)); border-radius: 15px; padding: 1.5rem; margin-bottom: 2rem; text-align: center;">
                    <strong style="color: #ff006e; font-size: 1.1rem;">
                        🔒 AVANT DE CONFIGURER UN JEU INTERACTIF, INSTALLEZ CES OUTILS :
                    </strong>
                </div>

                <h3><i class="fab fa-tiktok"></i> TikTok Live Studio</h3>
                <p>Installez l'application officielle :</p>
                <a href="https://www.tiktok.com/studio/download" target="_blank" style="color: #00d4ff; text-decoration: underline;">
                    <i class="fas fa-download"></i> Télécharger TikTok Live Studio
                </a>

                <h3>📱 Tikfinity Connect</h3>
                <p><strong style="color: #f59e0b;">Profitez de notre partenariat pour une réduction !</strong></p>
                <p>Créez votre compte :</p>
                <a href="https://tikfinity.zerody.one/?agency=g_myfullagency" target="_blank" style="color: #00d4ff; text-decoration: underline;">
                    <i class="fas fa-user-plus"></i> S'inscrire sur Tikfinity
                </a>

                <h3>🚗 GTA V</h3>
                <p>Pour jouer à GTA V en interactif :</p>
                <ul style="list-style: none; padding: 0;">
                    <li><a href="https://www.instant-gaming.com/?igr=MyFullAgencyMFA" target="_blank" style="color: #00d4ff;">
                        <i class="fas fa-shopping-cart"></i> Acheter GTA V
                    </a></li>
                    <li><a href="https://www.rockstargames.com/fr/newswire/article/89k8a554534523/Download-The-Rockstar-Games-Launcher" target="_blank" style="color: #00d4ff;">
                        <i class="fas fa-download"></i> Télécharger Rockstar Launcher
                    </a></li>
                </ul>

                <h3>⛏️ Minecraft</h3>
                <p>Pour Minecraft interactif :</p>
                <ul style="list-style: none; padding: 0;">
                    <li><a href="https://www.instant-gaming.com/fr/442-acheter-minecraft-java-bedrock-edition-pc-jeu/" target="_blank" style="color: #00d4ff;">
                        <i class="fas fa-shopping-cart"></i> Acheter Minecraft
                    </a></li>
                    <li><a href="https://www.minecraft.net/fr-fr/download" target="_blank" style="color: #00d4ff;">
                        <i class="fas fa-download"></i> Télécharger Minecraft
                    </a></li>
                </ul>

                <div style="background: rgba(0, 212, 255, 0.1); border-radius: 15px; padding: 1.5rem; margin-top: 2rem; text-align: center;">
                    <i class="fas fa-headset" style="color: #00d4ff; font-size: 2rem;"></i><br>
                    <strong style="color: #ffffff;">Besoin d'aide ? Contactez-nous sur Discord !</strong>
                </div>
                

            </div>
        </div>
    </div>
    </div> <!-- Fin du dashboard-content -->

    <script>
        // ==================== INTRO VIDEO LOGIC ====================
        document.addEventListener('DOMContentLoaded', function() {
            const introOverlay = document.getElementById('introOverlay');
            const introVideo = document.getElementById('introVideo');
            const dashboardContent = document.getElementById('dashboardContent');
            const skipButton = document.getElementById('skipButton');

            // Fonction pour afficher le dashboard
            function showDashboard() {
                introOverlay.classList.add('hidden');
                dashboardContent.classList.add('visible');
                
                // Supprimer complètement l'overlay après la transition
                setTimeout(() => {
                    introOverlay.style.display = 'none';
                }, 1000);
            }

            // Quand la vidéo se termine
            introVideo.addEventListener('ended', showDashboard);

            // Bouton skip
            skipButton.addEventListener('click', showDashboard);

            // Gestion des erreurs vidéo
            introVideo.addEventListener('error', function() {
                console.warn('Erreur de chargement vidéo, affichage direct du dashboard');
                showDashboard();
            });

            // Si la vidéo ne se charge pas après 3 secondes
            setTimeout(() => {
                if (introVideo.readyState === 0) {
                    console.warn('Vidéo non chargée après 3s, affichage direct du dashboard');
                    showDashboard();
                }
            }, 3000);
        });



        // ==================== DASHBOARD LOGIC ====================
        // Copier le token
        function copyToken() {
            const tokenValue = document.getElementById('tokenValue').textContent;
            navigator.clipboard.writeText(tokenValue).then(() => {
                showNotification('Token copié !');
            });
        }

        // Notification
        function showNotification(message) {
            const notification = document.createElement('div');
            notification.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                background: linear-gradient(135deg, #ff006e, #8b00ff);
                color: white;
                padding: 1rem 2rem;
                border-radius: 12px;
                font-weight: 600;
                box-shadow: 0 10px 30px rgba(139, 0, 255, 0.4);
                z-index: 9999;
                animation: slideIn 0.3s ease-out;
            `;
            notification.textContent = message;
            document.body.appendChild(notification);

            setTimeout(() => {
                notification.style.animation = 'slideOut 0.3s ease-out';
                setTimeout(() => notification.remove(), 300);
            }, 2000);
        }

        // Gestion des modals
        function openModal(modalName) {
            document.getElementById(modalName + 'Modal').classList.add('show');
            document.body.style.overflow = 'hidden';
        }

        function closeModal(modalName) {
            document.getElementById(modalName + 'Modal').classList.remove('show');
            document.body.style.overflow = 'auto';
        }

        // Fermer modal en cliquant à l'extérieur
        document.querySelectorAll('.modal').forEach(modal => {
            modal.addEventListener('click', function(e) {
                if (e.target === this) {
                    const modalId = this.id.replace('Modal', '');
                    closeModal(modalId);
                }
            });
        });

        // Fermer avec la touche Échap
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                document.querySelectorAll('.modal.show').forEach(modal => {
                    const modalId = modal.id.replace('Modal', '');
                    closeModal(modalId);
                });
            }
        });

        // Animations CSS pour les notifications
        const style = document.createElement('style');
        style.textContent = `
            @keyframes slideIn {
                from { transform: translateX(100%); opacity: 0; }
                to { transform: translateX(0); opacity: 1; }
            }
            @keyframes slideOut {
                from { transform: translateX(0); opacity: 1; }
                to { transform: translateX(100%); opacity: 0; }
            }
        `;
        document.head.appendChild(style);

        // Effet parallax sur les particules
        document.addEventListener('mousemove', (e) => {
            const particles = document.querySelectorAll('.particle');
            const x = e.clientX / window.innerWidth;
            const y = e.clientY / window.innerHeight;
            
            particles.forEach((particle, index) => {
                const speed = (index + 1) * 0.5;
                particle.style.transform = `translate(${x * speed}px, ${y * speed}px)`;
            });
        });
    </script>
</body>
</html>