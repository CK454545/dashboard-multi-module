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
    <title>MY FULL AGENCY - Révolutionnez votre présence TikTok</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=Space+Grotesk:wght@300;400;500;600;700&display=swap" rel="stylesheet">
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
            height: 100vh;
            overflow: hidden;
            position: relative;
        }

        /* ==================== ANIMATED BACKGROUND ==================== */
        .hero-background {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            overflow: hidden;
        }

        .gradient-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: 
                radial-gradient(circle at 20% 50%, rgba(255, 0, 110, 0.3) 0%, transparent 50%),
                radial-gradient(circle at 80% 50%, rgba(139, 0, 255, 0.3) 0%, transparent 50%),
                radial-gradient(circle at 50% 100%, rgba(0, 212, 255, 0.2) 0%, transparent 50%);
            animation: gradientShift 20s ease infinite;
        }

        @keyframes gradientShift {
            0%, 100% { 
                transform: rotate(0deg) scale(1);
                opacity: 0.3;
            }
            50% { 
                transform: rotate(180deg) scale(1.2);
                opacity: 0.5;
            }
        }

        /* Particules 3D */
        .particles-container {
            position: absolute;
            width: 100%;
            height: 100%;
            overflow: hidden;
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
        .particle:nth-child(7) { left: 15%; animation-delay: 2s; }
        .particle:nth-child(8) { left: 30%; animation-delay: 5s; }
        .particle:nth-child(9) { left: 45%; animation-delay: 8s; }
        .particle:nth-child(10) { left: 60%; animation-delay: 11s; }
        .particle:nth-child(11) { left: 75%; animation-delay: 14s; }
        .particle:nth-child(12) { left: 90%; animation-delay: 17s; }

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

        /* ==================== HERO SECTION ==================== */
        .hero-section {
            height: 100vh;
            display: flex;
            align-items: center;
            position: relative;
            z-index: 1;
            overflow: hidden;
        }

        .hero-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 2rem;
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
        }

        .hero-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 4rem;
            align-items: center;
            width: 100%;
            height: 100%;
        }

        /* ==================== CONTENU GAUCHE ==================== */
        .hero-content {
            animation: fadeInUp 1s ease-out;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .coming-soon-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            padding: 0.75rem 1.5rem;
            border-radius: 50px;
            margin-bottom: 2rem;
            animation: pulseGlow 2s ease-in-out infinite;
            position: relative;
            overflow: hidden;
        }

        .coming-soon-badge::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            animation: shimmer 3s infinite;
        }

        @keyframes shimmer {
            0% { left: -100%; }
            100% { left: 100%; }
        }

        @keyframes pulseGlow {
            0%, 100% {
                box-shadow: 0 0 20px rgba(255, 0, 110, 0.3);
                transform: scale(1);
            }
            50% {
                box-shadow: 0 0 40px rgba(255, 0, 110, 0.6);
                transform: scale(1.02);
            }
        }

        .coming-soon-badge .rocket {
            animation: rocketBounce 1.5s ease-in-out infinite;
        }

        @keyframes rocketBounce {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-3px); }
        }

        .hero-title {
            font-family: 'Space Grotesk', sans-serif;
            font-size: clamp(3rem, 6vw, 6rem);
            font-weight: 900;
            line-height: 0.9;
            margin-bottom: 1rem;
            letter-spacing: -0.02em;
        }

        .hero-title .gradient-text {
            background: linear-gradient(135deg, #ff006e, #8b00ff, #00d4ff);
            background-size: 200% 200%;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            animation: textGradient 4s ease infinite;
        }

        @keyframes textGradient {
            0%, 100% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
        }

        .hero-subtitle {
            font-size: clamp(1rem, 2vw, 1.5rem);
            color: #a0a0a0;
            margin-bottom: 1.5rem;
            line-height: 1.4;
            font-weight: 300;
        }

        .hero-subtitle .highlight {
            color: #00d4ff;
            font-weight: 600;
        }

        .hero-description {
            font-size: 1rem;
            color: #888;
            margin-bottom: 2rem;
            line-height: 1.6;
            max-width: 500px;
        }

        .cta-button {
            display: inline-flex;
            align-items: center;
            gap: 0.75rem;
            background: linear-gradient(135deg, #ff006e, #8b00ff);
            color: white;
            padding: 1rem 2rem;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
            font-size: 1.1rem;
            transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            position: relative;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(255, 0, 110, 0.3);
        }

        .cta-button::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s ease;
        }

        .cta-button:hover::before {
            left: 100%;
        }

        .cta-button:hover {
            transform: translateY(-3px) scale(1.02);
            box-shadow: 0 20px 40px rgba(255, 0, 110, 0.4);
        }

                 .cta-button .discord-icon {
             transition: transform 0.3s ease;
             font-size: 1.2rem;
         }
 
         .cta-button:hover .discord-icon {
             transform: translateX(5px) scale(1.1);
         }

        /* ==================== MOCKUP TÉLÉPHONE ==================== */
        .phone-container {
            position: relative;
            display: flex;
            justify-content: center;
            align-items: center;
            animation: fadeInUp 1s ease-out 0.3s both;
        }

        .phone-mockup {
            position: relative;
            width: 260px;
            height: 520px;
            background: linear-gradient(145deg, #1a1a1a, #2a2a2a);
            border-radius: 40px;
            padding: 8px;
            box-shadow: 
                0 0 0 2px rgba(255, 255, 255, 0.1),
                0 20px 40px rgba(0, 0, 0, 0.5),
                0 40px 80px rgba(0, 0, 0, 0.3),
                0 60px 120px rgba(0, 0, 0, 0.2),
                0 80px 160px rgba(0, 0, 0, 0.15),
                0 100px 200px rgba(0, 0, 0, 0.1),
                inset 0 0 20px rgba(255, 255, 255, 0.05);
            animation: phoneFloat 6s ease-in-out infinite;
            transition: all 0.2s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            transform-style: preserve-3d;
            cursor: pointer;
            perspective: 1200px;
            filter: drop-shadow(0 0 30px rgba(255, 0, 110, 0.1));
        }

        /* Effet de profondeur 3D ultra-réaliste */
        .phone-mockup::before {
            content: '';
            position: absolute;
            top: -30px;
            left: -30px;
            right: -30px;
            bottom: -30px;
            background: 
                radial-gradient(ellipse at center, rgba(255, 0, 110, 0.15) 0%, transparent 50%),
                radial-gradient(ellipse at 30% 30%, rgba(139, 0, 255, 0.1) 0%, transparent 60%),
                radial-gradient(ellipse at 70% 70%, rgba(0, 212, 255, 0.08) 0%, transparent 70%);
            border-radius: 60px;
            z-index: -1;
            animation: glowPulse 4s ease-in-out infinite;
            filter: blur(10px);
        }

        .phone-mockup::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, 
                rgba(255, 255, 255, 0.1) 0%, 
                transparent 20%, 
                transparent 80%, 
                rgba(255, 255, 255, 0.05) 100%);
            border-radius: 40px;
            pointer-events: none;
        }

        @keyframes glowPulse {
            0%, 100% { 
                opacity: 0.3;
                transform: scale(1);
            }
            50% { 
                opacity: 0.6;
                transform: scale(1.05);
            }
        }

        @keyframes phoneFloat {
            0%, 100% { 
                transform: translateY(0) rotateY(0deg) translateZ(0px) rotateX(0deg);
                box-shadow: 
                    0 0 0 2px rgba(255, 255, 255, 0.1),
                    0 20px 40px rgba(0, 0, 0, 0.5),
                    0 40px 80px rgba(0, 0, 0, 0.3),
                    0 60px 120px rgba(0, 0, 0, 0.2),
                    0 80px 160px rgba(0, 0, 0, 0.15),
                    0 100px 200px rgba(0, 0, 0, 0.1);
                filter: drop-shadow(0 0 30px rgba(255, 0, 110, 0.1));
            }
            50% { 
                transform: translateY(-25px) rotateY(8deg) translateZ(50px) rotateX(-3deg);
                box-shadow: 
                    0 0 0 2px rgba(255, 255, 255, 0.1),
                    0 35px 70px rgba(0, 0, 0, 0.7),
                    0 70px 140px rgba(0, 0, 0, 0.5),
                    0 105px 210px rgba(0, 0, 0, 0.4),
                    0 140px 280px rgba(0, 0, 0, 0.3),
                    0 175px 350px rgba(0, 0, 0, 0.2);
                filter: drop-shadow(0 0 50px rgba(255, 0, 110, 0.2));
            }
        }

        .phone-screen {
            width: 100%;
            height: 100%;
            background: #000;
            border-radius: 32px;
            overflow: hidden;
            position: relative;
            padding: 0;
        }

        .phone-notch {
            position: absolute;
            top: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 120px;
            height: 30px;
            background: #000;
            border-radius: 0 0 20px 20px;
            z-index: 10;
        }

        .tiktok-interface {
            width: 100%;
            height: 100%;
            background: linear-gradient(180deg, #000 0%, #1a1a1a 100%);
            position: relative;
            overflow: hidden;
            border-radius: 32px;
        }

        .tiktok-header {
            position: absolute;
            top: 20px;
            left: 0;
            right: 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 20px;
            z-index: 5;
        }

        .tiktok-logo {
            color: #fff;
            font-size: 1.2rem;
            font-weight: 700;
        }

        .live-indicator {
            background: #ff006e;
            color: white;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            animation: livePulse 2s ease-in-out infinite;
        }

        @keyframes livePulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.7; }
        }

        .tiktok-content {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 0;
            border-radius: 32px;
            overflow: hidden;
        }

        .video-container {
            width: 100%;
            height: 100%;
            background: linear-gradient(45deg, #ff006e, #8b00ff);
            border-radius: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }

        .video-container video {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 32px;
        }



        .play-button {
            position: relative;
            z-index: 3;
            width: 60px;
            height: 60px;
            background: rgba(255, 255, 255, 0.9);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #ff006e;
            font-size: 1.5rem;
            animation: playPulse 2s ease-in-out infinite;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .play-button:hover {
            transform: scale(1.1);
            background: rgba(255, 255, 255, 1);
        }

        @keyframes playPulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.1); }
        }

        .video-info {
            position: absolute;
            bottom: 100px;
            left: 20px;
            right: 20px;
            text-align: center;
            background: rgba(0, 0, 0, 0.7);
            padding: 10px;
            border-radius: 10px;
            backdrop-filter: blur(10px);
        }

        .username {
            color: #fff;
            font-weight: 600;
            font-size: 0.9rem;
            margin-bottom: 5px;
        }

        .video-description {
            color: #ccc;
            font-size: 0.8rem;
            line-height: 1.3;
        }

        .interaction-bar {
            position: absolute;
            right: 15px;
            bottom: 120px;
            display: flex;
            flex-direction: column;
            gap: 15px;
            z-index: 5;
        }

        .interaction-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 5px;
            color: #fff;
        }

        .interaction-icon {
            width: 40px;
            height: 40px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            backdrop-filter: blur(10px);
        }

        .interaction-count {
            font-size: 0.7rem;
            font-weight: 600;
        }

        .viewer-count {
            position: absolute;
            bottom: 20px;
            left: 20px;
            background: rgba(0, 0, 0, 0.7);
            color: #fff;
            padding: 8px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            backdrop-filter: blur(10px);
            animation: countUpdate 3s ease-in-out infinite;
        }

        @keyframes countUpdate {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.8; }
        }

        /* ==================== ÉLÉMENTS FLOTTANTS ==================== */
        .floating-elements {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: 2;
        }

        .floating-emoji {
            position: absolute;
            font-size: 1.5rem;
            animation: floatUp 4s ease-out infinite;
            opacity: 0;
        }

        .floating-emoji:nth-child(1) { left: 20%; animation-delay: 0s; }
        .floating-emoji:nth-child(2) { left: 40%; animation-delay: 1s; }
        .floating-emoji:nth-child(3) { left: 60%; animation-delay: 2s; }
        .floating-emoji:nth-child(4) { left: 80%; animation-delay: 3s; }

        @keyframes floatUp {
            0% {
                transform: translateY(100vh) scale(0);
                opacity: 0;
            }
            20% {
                opacity: 1;
                transform: translateY(80vh) scale(1);
            }
            80% {
                opacity: 1;
                transform: translateY(20vh) scale(1);
            }
            100% {
                transform: translateY(0) scale(0);
                opacity: 0;
            }
        }

        .notification {
            position: absolute;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            padding: 8px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            color: #fff;
            animation: notificationPop 3s ease-out infinite;
            opacity: 0;
        }

        .notification:nth-child(5) { 
            top: 30%; 
            right: 20%; 
            animation-delay: 0s; 
        }
        .notification:nth-child(6) { 
            top: 50%; 
            right: 10%; 
            animation-delay: 2s; 
        }

        @keyframes notificationPop {
            0% {
                transform: translateX(100px) scale(0);
                opacity: 0;
            }
            20% {
                transform: translateX(0) scale(1);
                opacity: 1;
            }
            80% {
                transform: translateX(0) scale(1);
                opacity: 1;
            }
            100% {
                transform: translateX(100px) scale(0);
                opacity: 0;
            }
        }

        /* ==================== NAVIGATION ==================== */
        .nav-back {
            position: fixed;
            top: 2rem;
            left: 2rem;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
            z-index: 100;
        }

        .nav-back:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateY(-2px);
        }

        /* ==================== RESPONSIVE ==================== */
        @media (max-width: 1024px) {
            .hero-grid {
                grid-template-columns: 1fr;
                gap: 1.5rem;
                text-align: center;
            }

            .phone-mockup {
                width: 220px;
                height: 440px;
            }

            .hero-title {
                font-size: clamp(2.5rem, 5vw, 4rem);
            }
            
            .hero-subtitle {
                font-size: clamp(0.9rem, 1.8vw, 1.3rem);
            }
            
            .hero-description {
                font-size: 0.9rem;
                margin-bottom: 1.5rem;
            }
        }

        @media (max-width: 768px) {
            .hero-container {
                padding: 0 1rem;
            }

            .hero-grid {
                gap: 1rem;
            }

            .phone-mockup {
                width: 180px;
                height: 360px;
            }

            .coming-soon-badge {
                padding: 0.5rem 1rem;
                font-size: 0.8rem;
                margin-bottom: 1.5rem;
            }

            .cta-button {
                padding: 0.75rem 1.5rem;
                font-size: 0.9rem;
            }

            .nav-back {
                top: 1rem;
                left: 1rem;
                padding: 0.5rem 1rem;
                font-size: 0.9rem;
            }
            
            .hero-title {
                font-size: clamp(2rem, 4vw, 3rem);
                margin-bottom: 0.75rem;
            }
            
            .hero-subtitle {
                font-size: clamp(0.8rem, 1.5vw, 1.1rem);
                margin-bottom: 1rem;
            }
            
            .hero-description {
                font-size: 0.85rem;
                margin-bottom: 1.5rem;
            }
        }

        @media (max-width: 480px) {
            .hero-grid {
                gap: 0.75rem;
            }

            .phone-mockup {
                width: 160px;
                height: 320px;
            }

            .hero-title {
                font-size: clamp(1.8rem, 3.5vw, 2.5rem);
                margin-bottom: 0.5rem;
            }

            .hero-subtitle {
                font-size: clamp(0.75rem, 1.3vw, 1rem);
                margin-bottom: 0.75rem;
            }
            
            .hero-description {
                font-size: 0.8rem;
                margin-bottom: 1rem;
            }
            
            .coming-soon-badge {
                padding: 0.4rem 0.8rem;
                font-size: 0.75rem;
                margin-bottom: 1rem;
            }
            
            .cta-button {
                padding: 0.6rem 1.2rem;
                font-size: 0.85rem;
            }
        }

        /* ==================== ANIMATIONS D'ENTRÉE ==================== */
        .fade-in {
            animation: fadeIn 1s ease-out;
        }

        .fade-in-delay-1 {
            animation: fadeIn 1s ease-out 0.2s both;
        }

        .fade-in-delay-2 {
            animation: fadeIn 1s ease-out 0.4s both;
        }

        .fade-in-delay-3 {
            animation: fadeIn 1s ease-out 0.6s both;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>
<body>
    <!-- Background animé -->
    <div class="hero-background">
        <div class="gradient-overlay"></div>
        <div class="particles-container">
            <div class="particle"></div>
            <div class="particle"></div>
            <div class="particle"></div>
            <div class="particle"></div>
            <div class="particle"></div>
            <div class="particle"></div>
            <div class="particle"></div>
            <div class="particle"></div>
            <div class="particle"></div>
            <div class="particle"></div>
            <div class="particle"></div>
            <div class="particle"></div>
        </div>
    </div>

    <!-- Navigation retour -->
    <a href="dashboard.php?token=<?= htmlspecialchars($token) ?>" class="nav-back">
        <i class="fas fa-arrow-left"></i> Retour Dashboard
    </a>

    <!-- Section HERO -->
    <section class="hero-section">
        <div class="hero-container">
            <div class="hero-grid">
                <!-- Contenu gauche -->
                <div class="hero-content">
                    <div class="coming-soon-badge fade-in-delay-1">
                        <span class="rocket">🚀</span>
                        <span>En développement • Lancement imminent</span>
                    </div>

                    <h1 class="hero-title fade-in-delay-2">
                        <span class="gradient-text">MY FULL</span><br>
                        <span style="color: #ffffff;">AGENCY</span>
                    </h1>

                    <p class="hero-subtitle fade-in-delay-2">
                        Révolutionnez votre présence TikTok avec l'agence 
                        <span class="highlight">nouvelle génération</span>
                    </p>

                    <p class="hero-description fade-in-delay-3">
                        Découvrez la première agence TikTok qui combine intelligence artificielle, 
                        créativité disruptive et résultats mesurables pour propulser votre carrière.
                    </p>

                                         <a href="https://discord.gg/FnVQcRY8Ve" target="_blank" class="cta-button fade-in-delay-3">
                         Rejoindre le Discord
                         <span class="discord-icon">🎮</span>
                     </a>
                </div>

                <!-- Mockup téléphone droite -->
                <div class="phone-container">
                    <div class="phone-mockup">
                        <div class="phone-notch"></div>
                        <div class="phone-screen">
                            <div class="tiktok-interface">
                                <!-- Header TikTok -->
                                <div class="tiktok-header">
                                    <div class="tiktok-logo">TikTok</div>
                                    <div class="live-indicator">LIVE</div>
                                </div>

                                <!-- Contenu principal -->
                                <div class="tiktok-content">
                                    <div class="video-container">
                                        <video autoplay muted loop playsinline>
                                            <source src="mfa.mp4" type="video/mp4">
                                            <!-- Fallback si la vidéo ne se charge pas -->
                                        </video>
                                        <div class="play-button" id="playButton">
                                            <i class="fas fa-play" id="playIcon"></i>
                                        </div>
                                    </div>
                                    
                                    
                                </div>

                                <!-- Barre d'interactions -->
                                <div class="interaction-bar">
                                    <div class="interaction-item">
                                        <div class="interaction-icon">❤️</div>
                                        <div class="interaction-count">2.4K</div>
                                    </div>
                                    <div class="interaction-item">
                                        <div class="interaction-icon">💬</div>
                                        <div class="interaction-count">156</div>
                                    </div>
                                    <div class="interaction-item">
                                        <div class="interaction-icon">🎁</div>
                                        <div class="interaction-count">89</div>
                                    </div>
                                </div>

                                <!-- Compteur de viewers -->
                                <div class="viewer-count">
                                    <i class="fas fa-eye"></i> 1.2K viewers
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Éléments flottants -->
                    <div class="floating-elements">
                        <div class="floating-emoji">❤️</div>
                        <div class="floating-emoji">🎁</div>
                        <div class="floating-emoji">🔥</div>
                        <div class="floating-emoji">⭐</div>
                        
                        <div class="notification">Nouveau follower !</div>
                        <div class="notification">Gift reçu !</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <script>
        // ==================== ANIMATIONS INTERACTIVES ====================
        
        // Effet parallax sur les particules
        document.addEventListener('mousemove', (e) => {
            const particles = document.querySelectorAll('.particle');
            const x = e.clientX / window.innerWidth;
            const y = e.clientY / window.innerHeight;
            
            particles.forEach((particle, index) => {
                const speed = (index + 1) * 0.3;
                particle.style.transform = `translate(${x * speed}px, ${y * speed}px)`;
            });
        });

        // Animation du compteur de viewers
        function updateViewerCount() {
            const viewerCount = document.querySelector('.viewer-count');
            let currentCount = 1200;
            
            setInterval(() => {
                const change = Math.floor(Math.random() * 50) - 25;
                currentCount = Math.max(800, Math.min(2000, currentCount + change));
                viewerCount.innerHTML = `<i class="fas fa-eye"></i> ${(currentCount / 1000).toFixed(1)}K viewers`;
            }, 3000);
        }

        // Animation des interactions
        function animateInteractions() {
            const interactions = document.querySelectorAll('.interaction-count');
            
            interactions.forEach((interaction, index) => {
                setInterval(() => {
                    const currentValue = parseInt(interaction.textContent.replace(/[^\d]/g, ''));
                    const newValue = currentValue + Math.floor(Math.random() * 10);
                    
                    if (index === 0) interaction.textContent = `${newValue}K`;
                    else interaction.textContent = newValue;
                    
                    interaction.style.animation = 'none';
                    interaction.offsetHeight; // Trigger reflow
                    interaction.style.animation = 'countUpdate 0.5s ease-in-out';
                }, 5000 + (index * 1000));
            });
        }

        // Effet de suivi de la souris sur le téléphone
        const phoneMockup = document.querySelector('.phone-mockup');
        let isHovering = false;

        // Suivi de la souris sur le téléphone avec effet 3D ultra-réaliste
        document.addEventListener('mousemove', (e) => {
            const rect = phoneMockup.getBoundingClientRect();
            const mouseX = e.clientX;
            const mouseY = e.clientY;
            
            // Vérifier si la souris est dans la zone du téléphone (zone étendue)
            const isInPhoneArea = mouseX >= rect.left - 150 && 
                                mouseX <= rect.right + 150 && 
                                mouseY >= rect.top - 150 && 
                                mouseY <= rect.bottom + 150;
            
            if (isInPhoneArea) {
                if (!isHovering) {
                    isHovering = true;
                    phoneMockup.style.animation = 'none';
                }
                
                // Calculer le centre du téléphone
                const centerX = rect.left + rect.width / 2;
                const centerY = rect.top + rect.height / 2;
                
                // Calculer la distance depuis le centre
                const deltaX = mouseX - centerX;
                const deltaY = mouseY - centerY;
                
                // Calculer les rotations avec effet de profondeur ultra-réaliste
                const maxRotation = 35;
                const maxDepth = 120;
                const rotationY = (deltaX / (rect.width / 2)) * maxRotation;
                const rotationX = (deltaY / (rect.height / 2)) * maxRotation;
                const depthZ = Math.sqrt(deltaX * deltaX + deltaY * deltaY) / Math.sqrt(rect.width * rect.width + rect.height * rect.height) * maxDepth;
                
                // Calculer l'intensité de l'effet basée sur la distance
                const distance = Math.sqrt(deltaX * deltaX + deltaY * deltaY);
                const maxDistance = Math.sqrt(rect.width * rect.width + rect.height * rect.height) / 2;
                const intensity = Math.max(0, 1 - distance / maxDistance);
                
                // Appliquer la transformation 3D ultra-réaliste
                phoneMockup.style.transform = `
                    translateY(${-25 * intensity}px) 
                    rotateY(${rotationY}deg) 
                    rotateX(${-rotationX}deg)
                    translateZ(${depthZ}px)
                    perspective(1200px)
                    scale(${1 + intensity * 0.05})
                `;
                
                // Effet d'ombre dynamique ultra-réaliste
                const shadowIntensity = 0.4 + (intensity * 0.6);
                const shadowBlur = 20 + depthZ * 0.8;
                phoneMockup.style.boxShadow = `
                    0 0 0 2px rgba(255, 255, 255, 0.1),
                    0 ${20 + depthZ * 0.8}px ${40 + depthZ * 0.8}px rgba(0, 0, 0, ${0.6 + shadowIntensity}),
                    0 ${40 + depthZ * 1.2}px ${80 + depthZ * 1.2}px rgba(0, 0, 0, ${0.4 + shadowIntensity * 0.7}),
                    0 ${60 + depthZ * 1.8}px ${120 + depthZ * 1.8}px rgba(0, 0, 0, ${0.3 + shadowIntensity * 0.5}),
                    0 ${80 + depthZ * 2.2}px ${160 + depthZ * 2.2}px rgba(0, 0, 0, ${0.2 + shadowIntensity * 0.3}),
                    0 ${100 + depthZ * 2.8}px ${200 + depthZ * 2.8}px rgba(0, 0, 0, ${0.15 + shadowIntensity * 0.2})
                `;
                
                // Effet de glow dynamique ultra-réaliste
                const glowIntensity = 30 + depthZ * 1.2;
                const glowOpacity = 0.15 + intensity * 0.3;
                phoneMockup.style.filter = `
                    drop-shadow(0 0 ${glowIntensity}px rgba(255, 0, 110, ${glowOpacity}))
                    drop-shadow(0 0 ${glowIntensity * 0.7}px rgba(139, 0, 255, ${glowOpacity * 0.6}))
                    drop-shadow(0 0 ${glowIntensity * 0.5}px rgba(0, 212, 255, ${glowOpacity * 0.4}))
                `;
                
            } else {
                if (isHovering) {
                    isHovering = false;
                    // Retour à l'animation normale
                    phoneMockup.style.animation = 'phoneFloat 6s ease-in-out infinite';
                    phoneMockup.style.transform = 'translateY(0) rotateY(0deg) rotateX(0deg) translateZ(0px) scale(1)';
                    phoneMockup.style.boxShadow = '';
                    phoneMockup.style.filter = 'drop-shadow(0 0 30px rgba(255, 0, 110, 0.1))';
                }
            }
        });

                 // Animation du bouton CTA
         document.querySelector('.cta-button').addEventListener('mouseenter', function() {
             this.querySelector('.discord-icon').style.transform = 'translateX(8px) scale(1.1)';
         });
 
         document.querySelector('.cta-button').addEventListener('mouseleave', function() {
             this.querySelector('.discord-icon').style.transform = 'translateX(0) scale(1)';
         });

        // Scroll reveal pour les éléments
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        }, observerOptions);

        // Observer tous les éléments avec la classe fade-in
        document.querySelectorAll('.fade-in, .fade-in-delay-1, .fade-in-delay-2, .fade-in-delay-3').forEach(el => {
            observer.observe(el);
        });

        // Initialisation des animations
        document.addEventListener('DOMContentLoaded', function() {
            updateViewerCount();
            animateInteractions();
            
            // Gestion de la vidéo dans le téléphone
            const video = document.querySelector('.video-container video');
            const playButton = document.getElementById('playButton');
            const playIcon = document.getElementById('playIcon');
            
            if (video && playButton) {
                // Masquer le bouton play si la vidéo se charge correctement
                video.addEventListener('loadeddata', () => {
                    playButton.style.display = 'none';
                });
                
                // Gestion du clic sur le bouton play
                playButton.addEventListener('click', () => {
                    if (video.paused) {
                        video.play();
                        playIcon.className = 'fas fa-pause';
                    } else {
                        video.pause();
                        playIcon.className = 'fas fa-play';
                    }
                });
                
                // Gestion des erreurs de vidéo
                video.addEventListener('error', () => {
                    console.log('Vidéo non trouvée, utilisation du fallback');
                    playButton.style.display = 'flex';
                });
            }
            
            // Effet de glitch subtil sur le titre
            const title = document.querySelector('.hero-title');
            let glitchInterval;
            
            title.addEventListener('mouseenter', () => {
                glitchInterval = setInterval(() => {
                    title.style.textShadow = '2px 0 #ff006e, -2px 0 #00d4ff';
                    setTimeout(() => {
                        title.style.textShadow = 'none';
                    }, 100);
                }, 2000);
            });
            
            title.addEventListener('mouseleave', () => {
                clearInterval(glitchInterval);
                title.style.textShadow = 'none';
            });
        });

        // ==================== PERFORMANCE OPTIMIZATION ====================
        
        // Throttle pour les événements de souris
        function throttle(func, limit) {
            let inThrottle;
            return function() {
                const args = arguments;
                const context = this;
                if (!inThrottle) {
                    func.apply(context, args);
                    inThrottle = true;
                    setTimeout(() => inThrottle = false, limit);
                }
            }
        }

        // Appliquer le throttle aux événements de souris
        const throttledMouseMove = throttle((e) => {
            const particles = document.querySelectorAll('.particle');
            const x = e.clientX / window.innerWidth;
            const y = e.clientY / window.innerHeight;
            
            particles.forEach((particle, index) => {
                const speed = (index + 1) * 0.3;
                particle.style.transform = `translate(${x * speed}px, ${y * speed}px)`;
            });
        }, 16); // ~60fps

        document.removeEventListener('mousemove', throttledMouseMove);
        document.addEventListener('mousemove', throttledMouseMove);

        // Désactiver les animations complexes sur mobile pour la performance
        if (window.innerWidth <= 768) {
            const particles = document.querySelectorAll('.particle');
            particles.forEach(particle => {
                particle.style.animation = 'none';
            });
        }
    </script>
</body>
</html> 