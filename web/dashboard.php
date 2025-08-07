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
    <title>MFA CONNECT - Centre de Contrôle Streaming</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;500;600;700;800;900&family=Rajdhani:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* ==================== MFA CONNECT - VARIABLES CSS ==================== */
        :root {
            /* Couleurs principales */
            --primary-blue: #0080FF;
            --primary-red: #FF0040;
            --dark-blue: #001633;
            --darker-blue: #000A1A;
            --light-blue: #00B4FF;
            --light-red: #FF5570;
            
            /* Gradients */
            --gradient-blue: linear-gradient(135deg, #0080FF, #00B4FF);
            --gradient-red: linear-gradient(135deg, #FF0040, #FF5570);
            --gradient-mixed: linear-gradient(135deg, #0080FF 0%, #FF0040 100%);
            --gradient-dark: linear-gradient(180deg, #000A1A 0%, #001633 100%);
            
            /* Backgrounds et textes */
            --bg-primary: #000A1A;
            --bg-secondary: #001633;
            --bg-card: rgba(0, 22, 51, 0.6);
            --text-primary: #FFFFFF;
            --text-secondary: #94A3B8;
            --text-muted: #64748B;
            
            /* Effets et ombres */
            --glow-blue: 0 0 30px rgba(0, 128, 255, 0.5);
            --glow-red: 0 0 30px rgba(255, 0, 64, 0.5);
            --shadow-card: 0 10px 40px rgba(0, 0, 0, 0.5);
            
            /* Typographie */
            --font-primary: 'Orbitron', sans-serif;
            --font-secondary: 'Rajdhani', sans-serif;
            --font-mono: 'JetBrains Mono', monospace;
        }

        /* ==================== GLOBAL STYLES ==================== */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        html { overflow-x: hidden; }

        body {
            font-family: var(--font-secondary);
            background: var(--bg-primary);
            color: var(--text-primary);
            min-height: 100vh;
            overflow-x: hidden;
            position: relative;
            background: var(--gradient-dark);
            width: 100%;
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

        /* ==================== BACKGROUND FUTURISTE MFA CONNECT ==================== */
        .background-container {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            overflow: hidden;
        }

        /* Grille 3D animée */
        .grid-3d {
            position: absolute;
            width: 200%;
            height: 200%;
            top: -50%;
            left: -50%;
            background-image: 
                linear-gradient(rgba(0, 128, 255, 0.1) 1px, transparent 1px),
                linear-gradient(90deg, rgba(0, 128, 255, 0.1) 1px, transparent 1px);
            background-size: 50px 50px;
            animation: gridMove 20s linear infinite;
            transform: perspective(1000px) rotateX(60deg);
        }

        @keyframes gridMove {
            0% { transform: perspective(1000px) rotateX(60deg) translateY(0); }
            100% { transform: perspective(1000px) rotateX(60deg) translateY(50px); }
        }

        /* Particules futuristes */
        .particles-container {
            position: absolute;
            width: 100%;
            height: 100%;
            overflow: hidden;
        }

        .particle {
            position: absolute;
            width: 3px;
            height: 3px;
            background: var(--gradient-blue);
            border-radius: 50%;
            opacity: 0;
            animation: particleFloat 20s linear infinite;
            box-shadow: 0 0 10px var(--primary-blue);
        }

        .particle:nth-child(odd) {
            background: var(--gradient-red);
            box-shadow: 0 0 10px var(--primary-red);
        }

        .particle:nth-child(1) { left: 10%; animation-delay: 0s; }
        .particle:nth-child(2) { left: 25%; animation-delay: 4s; }
        .particle:nth-child(3) { left: 40%; animation-delay: 8s; }
        .particle:nth-child(4) { left: 55%; animation-delay: 12s; }
        .particle:nth-child(5) { left: 70%; animation-delay: 16s; }
        .particle:nth-child(6) { left: 85%; animation-delay: 20s; }

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

        /* Lignes de connexion néon */
        .neon-lines {
            position: absolute;
            width: 100%;
            height: 100%;
        }

        .neon-line {
            position: absolute;
            height: 1px;
            background: var(--gradient-blue);
            box-shadow: 0 0 10px var(--primary-blue);
            animation: neonFlow 8s linear infinite;
        }

        .neon-line:nth-child(1) {
            top: 20%;
            width: 30%;
            left: 10%;
            animation-delay: 0s;
        }

        .neon-line:nth-child(2) {
            top: 60%;
            width: 40%;
            right: 10%;
            animation-delay: 2s;
        }

        .neon-line:nth-child(3) {
            top: 80%;
            width: 25%;
            left: 50%;
            animation-delay: 4s;
        }

        @keyframes neonFlow {
            0% { opacity: 0; transform: scaleX(0); }
            50% { opacity: 1; transform: scaleX(1); }
            100% { opacity: 0; transform: scaleX(0); }
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

        /* ==================== HEADER MFA CONNECT ==================== */
        .main-header {
            position: relative;
            background: var(--bg-card);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid rgba(0, 128, 255, 0.3);
            padding: 1.5rem 0;
            margin-bottom: 3rem;
            overflow: hidden;
            z-index: 10;
        }

        .header-bg-effect {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, 
                transparent 0%, 
                rgba(0, 128, 255, 0.1) 50%, 
                transparent 100%);
            animation: headerScan 3s ease-in-out infinite;
            overflow: hidden;
            will-change: transform;
        }

        @keyframes headerScan {
            0%, 100% { transform: translateX(-100%); }
            50% { transform: translateX(100%); }
        }

        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 2rem;
            position: relative;
            z-index: 2;
            overflow: hidden;
        }
        .theme-switcher {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-right: 16px;
        }
        .theme-btn {
            width: 28px; height: 28px; border-radius: 50%; border: 1px solid rgba(255,255,255,0.2);
            background: transparent; cursor: pointer; display:inline-flex; align-items:center; justify-content:center;
            transition: transform .2s ease, box-shadow .2s ease; position: relative;
        }
        .theme-btn:hover { transform: translateY(-2px); box-shadow: 0 6px 16px rgba(0,0,0,.3);} 
        .theme-btn .dot{ width:16px; height:16px; border-radius:50%; }
        .theme-btn[data-theme="blue"] .dot{ background: linear-gradient(135deg, #0080FF, #00B4FF); }
        .theme-btn[data-theme="red"] .dot{ background: linear-gradient(135deg, #FF0040, #FF5570); }
        .theme-btn[data-theme="mixed"] .dot{ background: linear-gradient(135deg, #00B4FF, #FF0040); }
        .theme-btn.active{ outline: 2px solid var(--primary-blue); }

        .logo-section {
            display: flex;
            align-items: center;
            gap: 1.5rem;
        }

        .logo-container {
            position: relative;
            display: inline-block;
        }

        .logo-3d {
            width: 80px;
            height: 80px;
            object-fit: contain;
            border-radius: 15px;
            animation: logoBreath 4s ease-in-out infinite;
            transition: all 0.3s ease;
            filter: drop-shadow(0 0 20px var(--primary-blue));
        }

        .logo-3d:hover {
            transform: translateY(-5px) rotateY(15deg);
            filter: drop-shadow(0 0 30px var(--primary-blue));
        }

        @keyframes logoBreath {
            0%, 100% { 
                transform: scale(1); 
                filter: drop-shadow(0 0 20px var(--primary-blue));
            }
            50% { 
                transform: scale(1.05); 
                filter: drop-shadow(0 0 30px var(--primary-blue));
            }
        }

        .brand-text {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .brand-name {
            font-family: var(--font-primary);
            font-size: 2.5rem;
            font-weight: 800;
            background: var(--gradient-mixed);
            background-clip: text;
            -webkit-background-clip: text;
            color: transparent;
            animation: textGlitch 4s ease-in-out infinite;
            letter-spacing: 2px;
        }

        @keyframes textGlitch {
            0%, 100% { 
                background-position: 0% 50%; 
                transform: translateX(0);
            }
            25% { 
                background-position: 100% 50%; 
                transform: translateX(-1px);
            }
            50% { 
                background-position: 0% 50%; 
                transform: translateX(1px);
            }
            75% { 
                background-position: 100% 50%; 
                transform: translateX(0);
            }
        }

        .brand-tagline {
            font-family: var(--font-secondary);
            font-size: 1rem;
            color: var(--text-secondary);
            font-weight: 400;
            letter-spacing: 1px;
        }

        .user-section {
            display: flex;
            align-items: center;
            gap: 1rem;
            position: relative;
            z-index: 20000;
        }

        .user-info {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            gap: 0.2rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .user-info:hover {
            transform: scale(1.05);
        }

        .welcome-text {
            font-size: 0.8rem;
            color: var(--text-muted);
            font-weight: 300;
        }

        .user-name {
            font-family: var(--font-primary);
            font-size: 1.2rem;
            color: var(--primary-blue);
            font-weight: 600;
        }

        /* ==================== PROFILE SYSTEM MFA CONNECT ==================== */
        .profile-dropdown {
            position: fixed;
            top: 80px;
            right: 40px;
            background: var(--bg-card);
            border: 1px solid rgba(0, 128, 255, 0.3);
            border-radius: 15px;
            backdrop-filter: blur(20px);
            padding: 1.5rem;
            min-width: 320px;
            box-shadow: var(--shadow-card);
            opacity: 0;
            visibility: hidden;
            transform: translateY(-10px);
            transition: all 0.3s ease;
            z-index: 10050;
        }

        .profile-dropdown.active {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
            pointer-events: auto;
        }

        .profile-header {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid rgba(0, 128, 255, 0.2);
        }

        .profile-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: var(--gradient-blue);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: white;
            font-weight: 700;
            position: relative;
            overflow: hidden;
        }

        .profile-avatar::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: var(--gradient-red);
            opacity: 0.3;
            animation: avatarGlow 3s ease-in-out infinite;
        }

        @keyframes avatarGlow {
            0%, 100% { opacity: 0.3; }
            50% { opacity: 0.6; }
        }

        .profile-details h3 {
            font-family: var(--font-primary);
            font-size: 1.1rem;
            color: var(--text-primary);
            margin-bottom: 0.2rem;
        }

        .profile-details p {
            font-size: 0.9rem;
            color: var(--text-secondary);
        }

        .profile-stats {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .profile-stat {
            text-align: center;
            padding: 0.8rem;
            background: rgba(0, 0, 0, 0.3);
            border-radius: 10px;
            border: 1px solid rgba(0, 128, 255, 0.2);
        }

        .profile-stat-value {
            font-family: var(--font-primary);
            font-size: 1.2rem;
            font-weight: 700;
            color: var(--primary-blue);
            display: block;
        }

        .profile-stat-label {
            font-size: 0.7rem;
            color: var(--text-secondary);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .profile-actions {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .profile-action {
            display: flex;
            align-items: center;
            gap: 0.8rem;
            padding: 0.8rem 1rem;
            background: rgba(0, 0, 0, 0.3);
            border: 1px solid rgba(0, 128, 255, 0.2);
            border-radius: 10px;
            color: var(--text-primary);
            text-decoration: none;
            transition: all 0.3s ease;
            font-size: 0.9rem;
        }

        .profile-action:hover {
            background: rgba(0, 128, 255, 0.1);
            border-color: var(--primary-blue);
            transform: translateX(5px);
        }

        .profile-action i {
            color: var(--primary-blue);
            width: 20px;
        }

        /* ==================== NAVIGATION TABS MFA CONNECT ==================== */
        .navigation-tabs {
            margin-bottom: 3rem;
            position: relative;
        }

        .nav-container {
            display: flex;
            justify-content: center;
            gap: 1rem;
            flex-wrap: wrap;
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 2rem;
        }

        .nav-tab {
            position: relative;
            background: var(--bg-card);
            border: 1px solid rgba(0, 128, 255, 0.2);
            color: var(--text-primary);
            padding: 1rem 1.5rem;
            border-radius: 15px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            display: flex;
            align-items: center;
            gap: 0.8rem;
            backdrop-filter: blur(20px);
            cursor: pointer;
            overflow: hidden;
            font-family: var(--font-secondary);
            font-size: 0.9rem;
            letter-spacing: 0.5px;
        }

        .nav-tab::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: var(--gradient-blue);
            transition: left 0.4s ease;
            z-index: -1;
        }

        .nav-tab:hover::before {
            left: 0;
        }

        .nav-tab:hover {
            transform: translateY(-3px) scale(1.05);
            box-shadow: var(--glow-blue);
            border-color: var(--primary-blue);
            color: white;
        }

        .nav-tab.active {
            background: var(--gradient-blue);
            border-color: transparent;
            color: white;
            box-shadow: var(--glow-blue);
        }

        .nav-tab.active::before {
            left: 0;
        }

        .tab-icon {
            font-size: 1.1rem;
            transition: transform 0.3s ease;
        }

        .nav-tab:hover .tab-icon {
            transform: rotate(15deg) scale(1.2);
        }

        .tab-text {
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .tab-indicator {
            position: absolute;
            bottom: 0;
            left: 50%;
            width: 0;
            height: 3px;
            background: var(--gradient-red);
            transform: translateX(-50%);
            transition: width 0.3s ease;
        }

        .nav-tab:hover .tab-indicator,
        .nav-tab.active .tab-indicator {
            width: 80%;
        }

        /* ==================== WEBSITE TAB & NEW BADGE ==================== */
        .website-tab {
            position: relative;
        }

        .new-badge {
            position: absolute;
            top: -8px;
            right: -8px;
            background: linear-gradient(135deg, #ff006e, #ff4081);
            color: white;
            font-size: 0.7rem;
            font-weight: 700;
            padding: 4px 8px;
            border-radius: 12px;
            animation: pulse-glow 2s ease-in-out infinite;
            box-shadow: 0 0 20px rgba(255, 0, 110, 0.5);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        @keyframes pulse-glow {
            0%, 100% {
                transform: scale(1);
                box-shadow: 0 0 20px rgba(255, 0, 110, 0.5);
            }
            50% {
                transform: scale(1.1);
                box-shadow: 0 0 30px rgba(255, 0, 110, 0.8);
            }
        }

        .website-tab:hover .new-badge {
            animation: bounce 0.6s ease-in-out;
        }

        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% {
                transform: translateY(0) scale(1);
            }
            40% {
                transform: translateY(-5px) scale(1.1);
            }
            60% {
                transform: translateY(-3px) scale(1.05);
            }
        }



        /* ==================== MODULES SECTION MFA CONNECT ==================== */
        .modules-section {
            margin-bottom: 4rem;
            position: relative;
            z-index: 1;
        }

        .section-header {
            text-align: center;
            margin-bottom: 3rem;
        }

        .section-title {
            font-family: var(--font-primary);
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 1rem;
            position: relative;
            display: inline-block;
        }

        .title-decoration {
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 100px;
            height: 3px;
            background: var(--gradient-mixed);
            border-radius: 2px;
        }

        .modules-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(380px, 1fr));
            gap: 2.5rem;
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 2rem;
        }

        /* ==================== MODULE CARD FUTURISTE ==================== */
        .module-card {
            position: relative;
            background: var(--bg-card);
            border: 1px solid rgba(0, 128, 255, 0.2);
            border-radius: 25px;
            padding: 2.5rem;
            backdrop-filter: blur(25px);
            transition: all 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            overflow: hidden;
            transform-style: preserve-3d;
            z-index: 1;
        }

        .module-bg-pattern {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: 
                radial-gradient(circle at 20% 80%, rgba(0, 128, 255, 0.1) 0%, transparent 50%),
                radial-gradient(circle at 80% 20%, rgba(255, 0, 64, 0.1) 0%, transparent 50%);
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .module-card:hover .module-bg-pattern {
            opacity: 1;
        }

        .module-glow {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: var(--gradient-blue);
            opacity: 0;
            transition: opacity 0.3s ease;
            z-index: -1;
        }

        .module-card:hover .module-glow {
            opacity: 0.1;
        }

        .module-card:hover {
            transform: translateY(-15px) rotateX(10deg) rotateY(5deg);
            box-shadow: 
                0 25px 50px rgba(0, 0, 0, 0.5),
                0 0 80px rgba(0, 128, 255, 0.3);
            border-color: var(--primary-blue);
        }

        /* ==================== MODULE HEADER & ICONS ==================== */
        .module-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 2rem;
        }

        .module-icon-wrapper {
            position: relative;
            width: 90px;
            height: 90px;
            border-radius: 25px;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .icon-bg {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: var(--gradient-blue);
            opacity: 0.8;
            transition: all 0.3s ease;
        }

        .module-card[data-module="wins"] .icon-bg {
            background: var(--gradient-red);
        }

        .module-card[data-module="timer"] .icon-bg {
            background: var(--gradient-blue);
        }

        .module-card[data-module="battle"] .icon-bg {
            background: var(--gradient-mixed);
        }

        .module-icon {
            font-size: 2.5rem;
            color: white;
            position: relative;
            z-index: 2;
            filter: drop-shadow(0 2px 10px rgba(0, 0, 0, 0.5));
            transition: all 0.3s ease;
        }

        .module-card:hover .module-icon {
            transform: scale(1.1) rotate(5deg);
        }

        .icon-particles {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
        }

        .icon-particle {
            position: absolute;
            width: 4px;
            height: 4px;
            background: white;
            border-radius: 50%;
            opacity: 0;
            animation: iconParticle 2s ease-in-out infinite;
        }

        @keyframes iconParticle {
            0% { 
                opacity: 0; 
                transform: translate(0, 0) scale(0);
            }
            50% { 
                opacity: 1; 
                transform: translate(var(--x), var(--y)) scale(1);
            }
            100% { 
                opacity: 0; 
                transform: translate(var(--x), var(--y)) scale(0);
            }
        }

        .module-status {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            background: rgba(16, 185, 129, 0.2);
            color: #10b981;
            border: 1px solid rgba(16, 185, 129, 0.5);
        }

        .status-dot {
            width: 8px;
            height: 8px;
            background: #10b981;
            border-radius: 50%;
            animation: statusPulse 2s ease-in-out infinite;
        }

        @keyframes statusPulse {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.5; transform: scale(1.2); }
        }

        .status-text {
            font-family: var(--font-secondary);
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* ==================== MODULE BODY ==================== */
        .module-body {
            margin-bottom: 2rem;
        }

        .module-title {
            font-family: var(--font-primary);
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 1rem;
            line-height: 1.2;
        }

        .module-description {
            color: var(--text-secondary);
            margin-bottom: 2rem;
            line-height: 1.6;
            font-size: 0.95rem;
        }

        .module-preview {
            background: rgba(0, 0, 0, 0.3);
            border-radius: 15px;
            padding: 1.5rem;
            text-align: center;
            border: 1px solid rgba(0, 128, 255, 0.2);
            margin-bottom: 2rem;
            transition: all 0.3s ease;
        }

        .module-card:hover .module-preview {
            background: rgba(0, 128, 255, 0.1);
            border-color: var(--primary-blue);
            box-shadow: 0 0 20px rgba(0, 128, 255, 0.3);
        }

        .preview-screen {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.5rem;
            position: relative;
            overflow: hidden;
        }

        .preview-value {
            font-family: var(--font-mono);
            font-size: 2rem;
            font-weight: 700;
            color: var(--primary-blue);
            text-shadow: 0 0 10px var(--primary-blue);
            transition: all 0.3s ease;
        }

        .module-card:hover .preview-value {
            transform: scale(1.1);
            text-shadow: 0 0 20px var(--primary-blue);
            animation: valueGlow 0.5s ease-in-out infinite alternate;
        }

        @keyframes valueGlow {
            0% { 
                text-shadow: 0 0 20px var(--primary-blue);
                transform: scale(1.1);
            }
            100% { 
                text-shadow: 0 0 30px var(--primary-blue), 0 0 40px var(--primary-blue);
                transform: scale(1.15);
            }
        }

        .preview-label {
            font-family: var(--font-secondary);
            font-size: 0.8rem;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: all 0.3s ease;
        }

        .module-card:hover .preview-label {
            color: var(--primary-blue);
        }

        /* Animation spécifique pour chaque module */
        .module-card[data-module="wins"]:hover .preview-value {
            animation: countUp 2s ease-in-out infinite;
        }

        .module-card[data-module="timer"]:hover .preview-value {
            animation: timerCount 2s ease-in-out infinite;
        }

        .module-card[data-module="battle"]:hover .preview-value {
            animation: scoreBattle 2s ease-in-out infinite;
        }

        @keyframes countUp {
            0% { content: "0"; }
            25% { content: "5"; }
            50% { content: "12"; }
            75% { content: "23"; }
            100% { content: "42"; }
        }

        @keyframes timerCount {
            0% { content: "00:00"; }
            25% { content: "00:15"; }
            50% { content: "00:30"; }
            75% { content: "00:45"; }
            100% { content: "01:00"; }
        }

        @keyframes scoreBattle {
            0% { content: "0-0"; }
            25% { content: "2-1"; }
            50% { content: "5-3"; }
            75% { content: "8-6"; }
            100% { content: "12-9"; }
        }

        /* ==================== MODULE FOOTER & BUTTONS ==================== */
        .module-footer {
            display: flex;
            gap: 1rem;
            margin-top: auto;
        }

        .btn-module {
            flex: 1;
            padding: 1rem 1.5rem;
            border-radius: 15px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            text-align: center;
            position: relative;
            overflow: hidden;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.8rem;
            font-family: var(--font-secondary);
            font-size: 0.9rem;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }

        .btn-module::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: var(--gradient-blue);
            transition: left 0.4s ease;
            z-index: -1;
        }

        .btn-module:hover::before {
            left: 0;
        }

        .btn-primary {
            background: var(--gradient-blue);
            color: white;
            border: none;
            box-shadow: var(--glow-blue);
        }

        .btn-primary:hover {
            transform: translateY(-3px) scale(1.05);
            box-shadow: 0 15px 40px rgba(0, 128, 255, 0.4);
        }

        .btn-secondary {
            background: var(--bg-card);
            color: var(--text-primary);
            border: 1px solid rgba(0, 128, 255, 0.3);
            backdrop-filter: blur(20px);
        }

        .btn-secondary:hover {
            transform: translateY(-2px);
            border-color: var(--primary-blue);
            box-shadow: 0 10px 30px rgba(0, 128, 255, 0.3);
            color: white;
        }

        .btn-secondary:hover::before {
            left: 0;
        }

        .btn-glow {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: var(--gradient-blue);
            opacity: 0;
            transition: opacity 0.3s ease;
            z-index: -1;
        }

        .btn-module:hover .btn-glow {
            opacity: 0.2;
        }

        .btn-module.disabled {
            opacity: 0.5;
            cursor: not-allowed;
            background: rgba(107, 114, 128, 0.3) !important;
            color: var(--text-muted) !important;
            border-color: rgba(107, 114, 128, 0.3) !important;
        }

        .btn-module.disabled:hover {
            transform: none;
            box-shadow: none;
        }

        .btn-module.disabled::before {
            display: none;
        }

        /* ==================== TOKEN SECTION CYBERPUNK ==================== */
        .token-section {
            margin-top: 4rem;
            padding-top: 3rem;
            border-top: 1px solid rgba(0, 128, 255, 0.2);
            position: relative;
            overflow: hidden;
        }

        .token-bg-circuit {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: 
                linear-gradient(90deg, rgba(0, 128, 255, 0.1) 1px, transparent 1px),
                linear-gradient(rgba(0, 128, 255, 0.1) 1px, transparent 1px);
            background-size: 30px 30px;
            animation: circuitFlow 10s linear infinite;
        }

        @keyframes circuitFlow {
            0% { transform: translateX(0) translateY(0); }
            100% { transform: translateX(30px) translateY(30px); }
        }

        .token-container {
            position: relative;
            max-width: 800px;
            margin: 0 auto;
            padding: 3rem;
            background: var(--bg-card);
            border-radius: 25px;
            backdrop-filter: blur(25px);
            border: 1px solid rgba(0, 128, 255, 0.3);
            box-shadow: var(--shadow-card);
            overflow: hidden;
        }

        .token-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: var(--gradient-blue);
            opacity: 0.05;
            z-index: -1;
        }

        .token-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .token-title {
            font-family: var(--font-primary);
            font-size: 2rem;
            font-weight: 700;
            color: var(--primary-blue);
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 1rem;
        }

        .token-subtitle {
            font-family: var(--font-secondary);
            font-size: 1rem;
            color: var(--text-secondary);
            font-weight: 400;
        }

        .token-display-wrapper {
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 2rem;
        }

        .token-display {
            background: rgba(0, 0, 0, 0.5);
            border-radius: 15px;
            padding: 2rem;
            border: 1px solid rgba(0, 128, 255, 0.3);
            position: relative;
            overflow: hidden;
            max-width: 600px;
            width: 100%;
        }

        .token-label {
            font-family: var(--font-mono);
            font-size: 0.8rem;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 0.5rem;
        }

        .token-value-container {
            position: relative;
            background: rgba(0, 0, 0, 0.8);
            border-radius: 10px;
            padding: 1rem;
            border: 1px solid rgba(0, 128, 255, 0.5);
        }

        .token-value {
            font-family: var(--font-mono);
            font-size: 1rem;
            color: var(--primary-blue);
            word-break: break-all;
            text-shadow: 0 0 10px var(--primary-blue);
        }

        .token-scan-line {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 2px;
            background: var(--gradient-blue);
            animation: scanLine 3s ease-in-out infinite;
            box-shadow: 0 0 10px var(--primary-blue);
        }

        @keyframes scanLine {
            0% { transform: translateY(-100%); }
            100% { transform: translateY(100%); }
        }

        .btn-copy {
            background: var(--gradient-blue);
            border: none;
            color: white;
            padding: 1rem 2rem;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 600;
            font-family: var(--font-secondary);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            box-shadow: var(--glow-blue);
            margin-top: 1rem;
            width: 100%;
            justify-content: center;
        }

        .btn-copy:hover {
            transform: translateY(-2px);
            box-shadow: 0 15px 40px rgba(0, 128, 255, 0.4);
        }



        .token-security {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            color: var(--text-secondary);
            font-size: 0.9rem;
            font-family: var(--font-secondary);
        }

        /* ==================== COPYRIGHT SECTION MFA CONNECT ==================== */
        .copyright-section {
            margin-top: 3rem;
            padding-top: 2rem;
            border-top: 1px solid rgba(0, 128, 255, 0.2);
        }

        .copyright-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 2rem;
            background: var(--bg-card);
            border-radius: 20px;
            backdrop-filter: blur(20px);
            border: 1px solid rgba(0, 128, 255, 0.2);
        }

        .copyright-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .copyright-left {
            display: flex;
            align-items: center;
        }

        .copyright-text {
            color: var(--text-secondary);
            font-size: 0.9rem;
            font-weight: 400;
            font-family: var(--font-secondary);
        }

        .copyright-text i {
            color: var(--primary-blue);
            margin-right: 0.3rem;
        }

        .copyright-right {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .dev-text {
            color: var(--text-secondary);
            font-size: 0.9rem;
            font-weight: 400;
            font-family: var(--font-secondary);
        }

        .dev-link {
            color: var(--primary-blue);
            text-decoration: none;
            font-weight: 600;
            font-size: 0.9rem;
            padding: 0.5rem 1rem;
            border-radius: 10px;
            background: rgba(0, 128, 255, 0.1);
            border: 1px solid rgba(0, 128, 255, 0.3);
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 0.3rem;
            font-family: var(--font-secondary);
        }

        .dev-link:hover {
            background: rgba(0, 128, 255, 0.2);
            border-color: var(--primary-blue);
            transform: translateY(-2px);
            box-shadow: var(--glow-blue);
        }

        .dev-link i {
            font-size: 0.8rem;
        }

        /* ==================== FIRST-TIME PERSONALIZATION MODAL ==================== */
        .first-time-modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.9);
            backdrop-filter: blur(15px);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 10001;
            opacity: 0;
            visibility: hidden;
            transition: all 0.5s ease;
        }

        .first-time-modal.active {
            opacity: 1;
            visibility: visible;
        }

        .first-time-container {
            background: var(--bg-card);
            border: 2px solid var(--primary-blue);
            border-radius: 20px;
            padding: 2.5rem;
            max-width: 500px;
            width: 90%;
            text-align: center;
            position: relative;
            transform: scale(0.8);
            transition: transform 0.5s ease;
            box-shadow: var(--glow-blue);
        }

        .first-time-modal.active .first-time-container {
            transform: scale(1);
        }

        .first-time-header {
            margin-bottom: 2rem;
        }

        .first-time-title {
            font-family: var(--font-primary);
            font-size: 2rem;
            font-weight: 700;
            background: var(--gradient-mixed);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 0.5rem;
        }

        .first-time-subtitle {
            font-size: 1.1rem;
            color: var(--text-secondary);
            line-height: 1.6;
        }

        .first-time-form {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .form-group {
            text-align: left;
        }

        .form-label {
            display: block;
            font-family: var(--font-primary);
            font-size: 0.9rem;
            color: var(--text-primary);
            margin-bottom: 0.5rem;
            font-weight: 600;
        }

        .form-input {
            width: 100%;
            padding: 1rem;
            background: rgba(0, 0, 0, 0.3);
            border: 1px solid rgba(0, 128, 255, 0.3);
            border-radius: 10px;
            color: var(--text-primary);
            font-family: var(--font-secondary);
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .form-input:focus {
            outline: none;
            border-color: var(--primary-blue);
            box-shadow: var(--glow-blue);
        }

        .form-select {
            width: 100%;
            padding: 1rem;
            background: rgba(0, 0, 0, 0.3);
            border: 1px solid rgba(0, 128, 255, 0.3);
            border-radius: 10px;
            color: var(--text-primary);
            font-family: var(--font-secondary);
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .form-select:focus {
            outline: none;
            border-color: var(--primary-blue);
            box-shadow: var(--glow-blue);
        }

        .first-time-actions {
            display: flex;
            gap: 1rem;
            justify-content: center;
        }

        .btn-first-time {
            padding: 1rem 2rem;
            border: none;
            border-radius: 10px;
            font-family: var(--font-primary);
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-primary-first {
            background: var(--gradient-blue);
            color: white;
            box-shadow: var(--glow-blue);
        }

        .btn-primary-first:hover {
            transform: translateY(-2px);
            box-shadow: 0 0 40px rgba(0, 128, 255, 0.7);
        }

        .btn-secondary-first {
            background: rgba(0, 0, 0, 0.3);
            color: var(--text-secondary);
            border: 1px solid rgba(0, 128, 255, 0.3);
        }

        .btn-secondary-first:hover {
            background: rgba(0, 128, 255, 0.1);
            color: var(--text-primary);
        }

        /* Responsive design pour le profile dropdown */
        @media (max-width: 768px) {
            .profile-dropdown {
                position: fixed;
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%) scale(0.8);
                width: 90%;
                max-width: 350px;
                margin-top: 0;
            }

            .profile-dropdown.active {
                transform: translate(-50%, -50%) scale(1);
            }

            .first-time-container {
                padding: 2rem;
                max-width: 400px;
            }

            .first-time-title {
                font-size: 1.5rem;
            }

            .first-time-actions {
                flex-direction: column;
                gap: 0.5rem;
            }

            .btn-first-time {
                width: 100%;
                justify-content: center;
            }
        }

        /* ==================== MODAL STYLES MFA CONNECT ==================== */
        .modal {
            display: none;
            position: fixed;
            z-index: 9999;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 10, 26, 0.9);
            backdrop-filter: blur(20px);
        }

        .modal.show {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .modal-backdrop {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: var(--gradient-dark);
            opacity: 0.8;
        }

        .modal-container {
            position: relative;
            background: var(--bg-card);
            backdrop-filter: blur(30px);
            border: 1px solid rgba(0, 128, 255, 0.3);
            border-radius: 25px;
            padding: 3rem;
            max-width: 800px;
            max-height: 85vh;
            overflow-y: auto;
            box-shadow: 
                0 25px 60px rgba(0, 0, 0, 0.6),
                0 0 100px rgba(0, 128, 255, 0.2);
            animation: modalSlideIn 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            overflow: hidden;
        }

        .modal-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: var(--gradient-blue);
            opacity: 0.05;
            z-index: -1;
        }

        @keyframes modalSlideIn {
            from {
                opacity: 0;
                transform: translateY(50px) scale(0.9);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2.5rem;
            padding-bottom: 1.5rem;
            border-bottom: 1px solid rgba(0, 128, 255, 0.3);
        }

        .modal-title-wrapper {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .modal-icon {
            width: 50px;
            height: 50px;
            background: var(--gradient-blue);
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: white;
            box-shadow: var(--glow-blue);
        }

        .modal-title {
            font-family: var(--font-primary);
            font-size: 2rem;
            font-weight: 700;
            background: var(--gradient-mixed);
            background-clip: text;
            -webkit-background-clip: text;
            color: transparent;
        }

        .modal-close {
            background: none;
            border: none;
            color: var(--text-primary);
            font-size: 1.5rem;
            cursor: pointer;
            padding: 0.8rem;
            border-radius: 12px;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
        }

        .modal-close:hover {
            background: rgba(255, 0, 64, 0.2);
            color: var(--primary-red);
            transform: rotate(90deg);
        }

        .modal-body {
            color: var(--text-secondary);
            line-height: 1.8;
            font-family: var(--font-secondary);
            font-size: 1rem;
        }

        .modal-body h3 {
            color: var(--primary-blue);
            margin: 2rem 0 1rem 0;
            font-size: 1.4rem;
            font-family: var(--font-primary);
            font-weight: 600;
        }

        .modal-body p {
            margin-bottom: 1.2rem;
        }

        .modal-footer {
            margin-top: 2rem;
            padding-top: 1.5rem;
            border-top: 1px solid rgba(0, 128, 255, 0.2);
            text-align: center;
        }

        .btn-modal-action {
            background: var(--gradient-blue);
            border: none;
            color: white;
            padding: 1rem 2rem;
            border-radius: 15px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 600;
            font-family: var(--font-secondary);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            box-shadow: var(--glow-blue);
        }

        .btn-modal-action:hover {
            transform: translateY(-2px);
            box-shadow: 0 15px 40px rgba(0, 128, 255, 0.4);
        }

        /* ==================== RESPONSIVE MFA CONNECT ==================== */
        @media (max-width: 1200px) {
            .modules-grid {
                grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
                gap: 2rem;
            }
            
            .hero-title {
                font-size: 3rem;
            }
        }

        @media (max-width: 992px) {
            .header-content {
                flex-direction: column;
                gap: 1.5rem;
                text-align: center;
            }
            
            .brand-name {
                font-size: 2rem;
            }
            
            .quick-stats {
                gap: 1.5rem;
            }
            
            .stat-card {
                padding: 1rem 1.5rem;
            }
        }

        @media (max-width: 768px) {
            .dashboard-container {
                padding: 1rem;
            }

            .main-header {
                padding: 1rem 0;
            }

            .header-content {
                padding: 0 1rem;
            }

            .brand-name {
                font-size: 1.8rem;
            }

            .logo-3d {
                width: 60px;
                height: 60px;
            }

            .nav-container {
                gap: 0.5rem;
                padding: 0 1rem;
            }

            .nav-tab {
                padding: 0.8rem 1rem;
                font-size: 0.8rem;
            }

            .hero-title {
                font-size: 2.5rem;
            }

            .quick-stats {
                flex-direction: column;
                align-items: center;
            }

            .stat-card {
                width: 100%;
                max-width: 300px;
            }

            .modules-grid {
                grid-template-columns: 1fr;
                gap: 1.5rem;
                padding: 0 1rem;
            }

            .module-card {
                padding: 1.5rem;
            }

            .token-display-wrapper {
                flex-direction: column;
                gap: 1rem;
            }

            .token-display {
                min-width: auto;
            }

            .token-value {
                font-size: 0.9rem;
            }

            .copyright-content {
                flex-direction: column;
                text-align: center;
                gap: 1rem;
            }

            .copyright-container {
                padding: 1rem;
            }

            .copyright-text,
            .dev-text,
            .dev-link {
                font-size: 0.8rem;
            }

            .modal-container {
                margin: 1rem;
                padding: 2rem;
            }
        }

        @media (max-width: 576px) {
            .brand-name {
                font-size: 1.5rem;
            }
            
            .hero-title {
                font-size: 2rem;
            }
            
            .section-title {
                font-size: 2rem;
            }
            
            .module-title {
                font-size: 1.5rem;
            }
            
            .token-title {
                font-size: 1.5rem;
            }
        }

        /* ==================== ANIMATIONS MFA CONNECT ==================== */
        @keyframes logoBreath {
            0%, 100% { 
                transform: scale(1); 
                filter: drop-shadow(0 0 20px var(--primary-blue));
            }
            50% { 
                transform: scale(1.05); 
                filter: drop-shadow(0 0 30px var(--primary-blue));
            }
        }

        @keyframes textGlitch {
            0%, 100% { 
                background-position: 0% 50%; 
                transform: translateX(0);
            }
            25% { 
                background-position: 100% 50%; 
                transform: translateX(-1px);
            }
            50% { 
                background-position: 0% 50%; 
                transform: translateX(1px);
            }
            75% { 
                background-position: 100% 50%; 
                transform: translateX(0);
            }
        }

        @keyframes titleGlow {
            0%, 100% { 
                filter: brightness(1) drop-shadow(0 0 20px var(--primary-blue)); 
            }
            50% { 
                filter: brightness(1.3) drop-shadow(0 0 40px var(--primary-blue)); 
            }
        }

        @keyframes headerScan {
            0%, 100% { transform: translateX(-100%); }
            50% { transform: translateX(100%); }
        }

        @keyframes waveMove {
            0% { transform: translateX(-50%); }
            100% { transform: translateX(0%); }
        }

        @keyframes gridMove {
            0% { transform: perspective(1000px) rotateX(60deg) translateY(0); }
            100% { transform: perspective(1000px) rotateX(60deg) translateY(50px); }
        }

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

        @keyframes neonFlow {
            0% { opacity: 0; transform: scaleX(0); }
            50% { opacity: 1; transform: scaleX(1); }
            100% { opacity: 0; transform: scaleX(0); }
        }

        @keyframes iconParticle {
            0% { 
                opacity: 0; 
                transform: translate(0, 0) scale(0);
            }
            50% { 
                opacity: 1; 
                transform: translate(var(--x), var(--y)) scale(1);
            }
            100% { 
                opacity: 0; 
                transform: translate(var(--x), var(--y)) scale(0);
            }
        }

        @keyframes statusPulse {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.5; transform: scale(1.2); }
        }

        @keyframes scanLine {
            0% { transform: translateY(-100%); }
            100% { transform: translateY(100%); }
        }

        @keyframes qrScan {
            0% { transform: translateY(-100%); }
            100% { transform: translateY(100%); }
        }

        @keyframes circuitFlow {
            0% { transform: translateX(0) translateY(0); }
            100% { transform: translateX(30px) translateY(30px); }
        }

        @keyframes modalSlideIn {
            from {
                opacity: 0;
                transform: translateY(50px) scale(0.9);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        /* ==================== LOADING BAR MFA CONNECT ==================== */
        .loading-bar {
            width: 100%;
            height: 4px;
            background: rgba(0, 128, 255, 0.2);
            border-radius: 4px;
            overflow: hidden;
            margin: 1rem 0;
            position: relative;
        }

        .loading-progress {
            height: 100%;
            background: var(--gradient-blue);
            width: 75%;
            animation: loading 2s ease-in-out infinite;
            box-shadow: 0 0 10px var(--primary-blue);
        }

        @keyframes loading {
            0% { transform: translateX(-100%); }
            100% { transform: translateX(133%); }
        }

        /***** THEMES *****/
        :root { /* valeurs par défaut (bleu) déjà définies plus haut */ }
        body.theme-red {
          --primary-blue: #FF0040;
          --light-blue: #FF5570;
          --gradient-blue: linear-gradient(135deg, #FF0040, #FF5570);
          --gradient-mixed: linear-gradient(135deg, #FF0040 0%, #FF5570 100%);
        }
        body.theme-mixed {
          --primary-blue: #00B4FF;
          --primary-red: #FF0040;
          --gradient-mixed: linear-gradient(135deg, #00B4FF 0%, #FF0040 100%);
        }

        /* Couche de verrouillage pour dashboard quand déconnecté */
        .dashboard-lock-overlay{
          position: fixed; inset: 0; z-index: 30000; backdrop-filter: blur(6px);
          background: rgba(0,10,26,0.8); display: none; align-items: center; justify-content: center;
        }
        .dashboard-lock-overlay.show{ display: flex; }
        .lock-card{ background: var(--bg-card); border:1px solid rgba(255,255,255,.15); padding: 24px; border-radius: 16px; text-align:center; max-width: 420px; }
        .lock-card h3{ font-family: var(--font-primary); margin-bottom: 8px; }
        .lock-card p{ color: var(--text-secondary); margin-bottom: 16px; }
        .lock-card .btn{ display:inline-flex; align-items:center; gap:8px; padding:10px 16px; border-radius:10px; border:1px solid rgba(0,128,255,.3); background: var(--bg-card); color:#fff; text-decoration:none; }
    </style>
</head>
<body>
    <!-- Background Container -->
    <div class="background-container">
        <div class="grid-3d"></div>
        <div class="particles-container">
            <div class="particle"></div>
            <div class="particle"></div>
            <div class="particle"></div>
            <div class="particle"></div>
            <div class="particle"></div>
            <div class="particle"></div>
        </div>
        <div class="neon-lines">
            <div class="neon-line"></div>
            <div class="neon-line"></div>
            <div class="neon-line"></div>
        </div>
    </div>

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
        <div class="dashboard-container">
            <!-- Header MFA CONNECT -->
            <header class="main-header">
                <div class="header-bg-effect"></div>
                <div class="header-content">
                    <div class="logo-section">
                            <div class="theme-switcher" aria-label="Changer de thème">
                                <button class="theme-btn" data-theme="blue" title="Bleu (par défaut)"><span class="dot"></span></button>
                                <button class="theme-btn" data-theme="red" title="Rouge"><span class="dot"></span></button>
                                <button class="theme-btn" data-theme="mixed" title="Mix"><span class="dot"></span></button>
                            </div>
                        <div class="logo-container">
                            <img src="https://i.goopics.net/g93k7n.png" alt="MFA CONNECT" class="logo-3d">
                        </div>
                        <div class="brand-text">
                            <h1 class="brand-name">MFA CONNECT</h1>
                            <p class="brand-tagline">Streaming Control Center</p>
                        </div>
                    </div>
                    
                    <div class="user-section">
                        <div class="user-info" onclick="toggleProfile()">
                            <span class="welcome-text">Bienvenue</span>
                            <span class="user-name"><?php echo htmlspecialchars($user['name'] ?? 'Utilisateur'); ?></span>
                        </div>
                        
                        <!-- Profile Dropdown -->
                        <div class="profile-dropdown" id="profileDropdown">
                            <div class="profile-header">
                                                <div class="profile-avatar" id="profileAvatar">
                    <?php echo strtoupper(substr($user['name'] ?? 'U', 0, 1)); ?>
                </div>
                                <div class="profile-details">
                                    <h3><?php echo htmlspecialchars($user['name'] ?? 'Utilisateur'); ?></h3>
                                    <p>Token: <?php echo substr($token, 0, 8) . '...'; ?></p>
                                </div>
                            </div>
                            
                            <div class="profile-stats">
                                <div class="profile-stat">
                                    <span class="profile-stat-value">3</span>
                                    <span class="profile-stat-label">Modules</span>
                                </div>
                                <div class="profile-stat">
                                    <span class="profile-stat-value">Active</span>
                                    <span class="profile-stat-label">Status</span>
                                </div>
                            </div>
                            
                            <div class="profile-actions">
                                <a href="#" class="profile-action" onclick="openProfileModal()">
                                    <i class="fas fa-user-edit"></i>
                                    <span>Modifier Profil</span>
                                </a>
                                <a href="#" class="profile-action" onclick="openSettings()">
                                    <i class="fas fa-cog"></i>
                                    <span>Paramètres</span>
                                </a>
                                <a href="#" class="profile-action" onclick="openHistoryModal()">
                                    <i class="fas fa-history"></i>
                                    <span>Historique</span>
                                </a>
                                <a href="#" class="profile-action" onclick="logoutUser()">
                                    <i class="fas fa-sign-out-alt"></i>
                                    <span>Déconnexion</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Navigation Tabs MFA CONNECT -->
            <nav class="navigation-tabs">
                <div class="nav-container">
                    <a href="#" class="nav-tab active" data-tab="dashboard">
                        <span class="tab-icon"><i class="fas fa-th-large"></i></span>
                        <span class="tab-text">Dashboard</span>
                        <div class="tab-indicator"></div>
                    </a>
                    <a href="#" class="nav-tab" onclick="openModal('rules'); return false;" data-tab="rules">
                        <span class="tab-icon"><i class="fas fa-scroll"></i></span>
                        <span class="tab-text">Règlement</span>
                        <div class="tab-indicator"></div>
                    </a>
                    <a href="#" class="nav-tab" onclick="openModal('prereq'); return false;" data-tab="prereq">
                        <span class="tab-icon"><i class="fas fa-clipboard-check"></i></span>
                        <span class="tab-text">Prérequis</span>
                        <div class="tab-indicator"></div>
                    </a>
                    <a href="https://discord.gg/TbXYYsEgqz" target="_blank" class="nav-tab" data-tab="discord">
                        <span class="tab-icon"><i class="fab fa-discord"></i></span>
                        <span class="tab-text">Discord</span>
                        <div class="tab-indicator"></div>
                    </a>
                </div>
            </nav>



            <!-- Modules Section MFA CONNECT -->
            <section class="modules-section">
                <div class="section-header">
                    <h2 class="section-title">
                        <span class="title-text">Modules de Streaming</span>
                        <div class="title-decoration"></div>
                    </h2>
                </div>
                
                <div class="modules-grid">
                    <!-- Module Wins Counter -->
                    <div class="module-card" data-module="wins">
                        <div class="module-bg-pattern"></div>
                        <div class="module-glow"></div>
                        
                        <div class="module-header">
                            <div class="module-icon-wrapper">
                                <div class="icon-bg"></div>
                                <i class="fas fa-trophy module-icon"></i>
                                <div class="icon-particles"></div>
                            </div>
                            <div class="module-status">
                                <span class="status-dot"></span>
                                <span class="status-text">Opérationnel</span>
                            </div>
                        </div>
                        
                        <div class="module-body">
                            <h3 class="module-title">Compteur de Wins</h3>
                            <p class="module-description">
                                Système de comptage en temps réel avec effets visuels spectaculaires et multiplicateurs
                            </p>
                            
                            <div class="module-preview">
                                <div class="preview-screen">
                                    <span class="preview-value" id="winsPreviewValue">0</span>
                                    <span class="preview-label">WINS</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="module-footer">
                            <a href="/modules/win.php?token=<?=$token?>&control=true" class="btn-module btn-primary">
                                <i class="fas fa-play"></i>
                                <span>Lancer</span>
                                <div class="btn-glow"></div>
                            </a>
                            <a href="/modules/wins-config.php?token=<?=$token?>" class="btn-module btn-secondary">
                                <i class="fas fa-cog"></i>
                                <span>Config</span>
                            </a>
                        </div>
                    </div>

                    <!-- Module Timer -->
                    <div class="module-card" data-module="timer">
                        <div class="module-bg-pattern"></div>
                        <div class="module-glow"></div>
                        
                        <div class="module-header">
                            <div class="module-icon-wrapper">
                                <div class="icon-bg"></div>
                                <i class="fas fa-clock module-icon"></i>
                                <div class="icon-particles"></div>
                            </div>
                            <div class="module-status">
                                <span class="status-dot"></span>
                                <span class="status-text">Opérationnel</span>
                            </div>
                        </div>
                        
                        <div class="module-body">
                            <h3 class="module-title">Timer Interactif</h3>
                            <p class="module-description">
                                Chronomètre personnalisable avec contrôles avancés pour gérer vos sessions de stream
                            </p>
                            
                            <div class="module-preview">
                                <div class="preview-screen">
                                    <span class="preview-value">00:00</span>
                                    <span class="preview-label">TIMER</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="module-footer">
                            <a href="/modules/timer.php?token=<?=$token?>&control=true" class="btn-module btn-primary">
                                <i class="fas fa-play"></i>
                                <span>Lancer</span>
                                <div class="btn-glow"></div>
                            </a>
                            <a href="/modules/timer-config.php?token=<?=$token?>" class="btn-module btn-secondary">
                                <i class="fas fa-cog"></i>
                                <span>Config</span>
                            </a>
                        </div>
                    </div>

                    <!-- Module Team Battle -->
                    <div class="module-card" data-module="battle">
                        <div class="module-bg-pattern"></div>
                        <div class="module-glow"></div>
                        
                        <div class="module-header">
                            <div class="module-icon-wrapper">
                                <div class="icon-bg"></div>
                                <i class="fas fa-users-cog module-icon"></i>
                                <div class="icon-particles"></div>
                            </div>
                            <div class="module-status">
                                <span class="status-dot"></span>
                                <span class="status-text">Opérationnel</span>
                            </div>
                        </div>
                        
                        <div class="module-body">
                            <h3 class="module-title">Team VS Team</h3>
                            <p class="module-description">
                                Système de bataille d'équipes avec scores en temps réel et effets visuels
                            </p>
                            
                            <div class="module-preview">
                                <div class="preview-screen">
                                    <span class="preview-value">0-0</span>
                                    <span class="preview-label">SCORE</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="module-footer">
                            <a href="/modules/team-battle.php?token=<?=$token?>&control=true" class="btn-module btn-primary">
                                <i class="fas fa-play"></i>
                                <span>Lancer</span>
                                <div class="btn-glow"></div>
                            </a>
                            <a href="/modules/teams-config.php?token=<?=$token?>" class="btn-module btn-secondary">
                                <i class="fas fa-cog"></i>
                                <span>Config</span>
                            </a>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Token Section Cyberpunk MFA CONNECT -->
            <section class="token-section">
                <div class="token-bg-circuit"></div>
                
                <div class="token-container">
                    <div class="token-header">
                        <h2 class="token-title">
                            <i class="fas fa-key"></i>
                            Clé d'Authentification
                        </h2>
                        <p class="token-subtitle">Votre accès sécurisé aux modules</p>
                    </div>
                    
                    <div class="token-display-wrapper">
                        <div class="token-display">
                            <div class="token-label">API TOKEN</div>
                            <div class="token-value-container">
                                <code class="token-value" id="tokenValue"><?= htmlspecialchars($token) ?></code>
                                <div class="token-scan-line"></div>
                            </div>
                            <button class="btn-copy" onclick="copyToken()">
                                <i class="fas fa-copy"></i>
                                <span>Copier</span>
                            </button>
                        </div>
                    </div>
                    
                    <div class="token-security">
                        <i class="fas fa-shield-alt"></i>
                        <span>Token sécurisé - Ne pas partager</span>
                    </div>
                </div>
            </section>

            <!-- Copyright Section MFA CONNECT -->
            <section class="copyright-section">
                <div class="copyright-container">
                    <div class="copyright-content">
                        <div class="copyright-left">
                            <span class="copyright-text">
                                <i class="fas fa-copyright"></i> 2024 MyFull Agency (MFA)
                            </span>
                        </div>
                        <div class="copyright-right">
                            <span class="dev-text">Développement & Design par</span>
                            <a href="mailto:gaetanck.pro@gmail.com" class="dev-link">
                                <i class="fas fa-code"></i> CK
                            </a>
                        </div>
                    </div>
                </div>
            </section>
    </div>

    <!-- First-Time Personalization Modal -->
    <div id="firstTimeModal" class="first-time-modal">
        <div class="first-time-container">
            <div class="first-time-header">
                <h1 class="first-time-title">Bienvenue sur MFA CONNECT</h1>
                <p class="first-time-subtitle">Personnalisez votre expérience pour un contrôle optimal</p>
            </div>
            
            <form class="first-time-form" id="personalizationForm">
                <div class="form-group">
                    <label class="form-label">Nom d'affichage</label>
                    <input type="text" class="form-input" id="displayName" name="displayName" 
                           value="<?php echo htmlspecialchars($user['name'] ?? ''); ?>" 
                           placeholder="Votre nom d'affichage">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Bio</label>
                    <input type="text" class="form-input" id="bio" name="bio" 
                           placeholder="Une courte description de vous">
                </div>
                <div class="form-group">
                    <label class="form-label">URL de l'avatar</label>
                    <input type="url" class="form-input" id="avatarUrl" name="avatarUrl" placeholder="https://...">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Thème préféré</label>
                    <select class="form-select" id="theme" name="theme">
                        <option value="blue">Bleu (Par défaut)</option>
                        <option value="red">Rouge</option>
                        <option value="purple">Violet</option>
                        <option value="green">Vert</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Langue</label>
                    <select class="form-select" id="language" name="language">
                        <option value="fr">Français</option>
                        <option value="en">English</option>
                        <option value="es">Español</option>
                    </select>
                </div>
            </form>
            
            <div class="first-time-actions">
                <button class="btn-first-time btn-secondary-first" onclick="skipPersonalization()">
                    <i class="fas fa-arrow-right"></i>
                    Passer
                </button>
                <button class="btn-first-time btn-primary-first" onclick="savePersonalization()">
                    <i class="fas fa-save"></i>
                    Sauvegarder
                </button>
            </div>
        </div>
    </div>

    <!-- Modal Règlement MFA CONNECT -->
    <div id="rulesModal" class="modal">
        <div class="modal-backdrop"></div>
        <div class="modal-container">
            <div class="modal-header">
                <div class="modal-title-wrapper">
                    <div class="modal-icon">
                        <i class="fas fa-scroll"></i>
                    </div>
                    <h2 class="modal-title">Règlement MFA</h2>
                </div>
                <button class="modal-close" onclick="closeModal('rules')">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <p style="text-align: center; color: var(--primary-blue); font-size: 1.1rem; margin-bottom: 2rem;">
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

                <div style="background: rgba(0, 128, 255, 0.1); border-radius: 15px; padding: 1.5rem; margin-top: 2rem; text-align: center;">
                    <strong style="color: var(--primary-red); font-size: 1.2rem;">🎙️ Prêt(e) à décoller avec la team MyFull ?</strong><br>
                    <span style="color: var(--text-primary);">Alors attache ta ceinture, active ta caméra, et que le live commence ! 🌌</span>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn-modal-action">J'ai compris</button>
            </div>
        </div>
    </div>

    <!-- Modal Prérequis MFA CONNECT -->
    <div id="prereqModal" class="modal">
        <div class="modal-backdrop"></div>
        <div class="modal-container">
            <div class="modal-header">
                <div class="modal-title-wrapper">
                    <div class="modal-icon">
                        <i class="fas fa-clipboard-check"></i>
                    </div>
                    <h2 class="modal-title">Prérequis - Avant Setup</h2>
                </div>
                <button class="modal-close" onclick="closeModal('prereq')">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <div style="background: linear-gradient(135deg, rgba(0, 128, 255, 0.2), rgba(255, 0, 64, 0.2)); border-radius: 15px; padding: 1.5rem; margin-bottom: 2rem; text-align: center;">
                    <strong style="color: var(--primary-red); font-size: 1.1rem;">
                        🔒 AVANT DE CONFIGURER UN JEU INTERACTIF, INSTALLEZ CES OUTILS :
                    </strong>
                </div>

                <h3><i class="fab fa-tiktok"></i> TikTok Live Studio</h3>
                <p>Installez l'application officielle :</p>
                <a href="https://www.tiktok.com/studio/download" target="_blank" style="color: var(--primary-blue); text-decoration: underline;">
                    <i class="fas fa-download"></i> Télécharger TikTok Live Studio
                </a>

                <h3>📱 Tikfinity Connect</h3>
                <p><strong style="color: var(--primary-red);">Profitez de notre partenariat pour une réduction !</strong></p>
                <p>Créez votre compte :</p>
                <a href="https://tikfinity.zerody.one/?agency=g_myfullagency" target="_blank" style="color: var(--primary-blue); text-decoration: underline;">
                    <i class="fas fa-user-plus"></i> S'inscrire sur Tikfinity
                </a>

                <h3>🚗 GTA V</h3>
                <p>Pour jouer à GTA V en interactif :</p>
                <ul style="list-style: none; padding: 0;">
                    <li><a href="https://www.instant-gaming.com/?igr=MyFullAgencyMFA" target="_blank" style="color: var(--primary-blue);">
                        <i class="fas fa-shopping-cart"></i> Acheter GTA V
                    </a></li>
                    <li><a href="https://www.rockstargames.com/fr/newswire/article/89k8a554534523/Download-The-Rockstar-Games-Launcher" target="_blank" style="color: var(--primary-blue);">
                        <i class="fas fa-download"></i> Télécharger Rockstar Launcher
                    </a></li>
                </ul>

                <h3>⛏️ Minecraft</h3>
                <p>Pour Minecraft interactif :</p>
                <ul style="list-style: none; padding: 0;">
                    <li><a href="https://www.instant-gaming.com/fr/442-acheter-minecraft-java-bedrock-edition-pc-jeu/" target="_blank" style="color: var(--primary-blue);">
                        <i class="fas fa-shopping-cart"></i> Acheter Minecraft
                    </a></li>
                    <li><a href="https://www.minecraft.net/fr-fr/download" target="_blank" style="color: var(--primary-blue);">
                        <i class="fas fa-download"></i> Télécharger Minecraft
                    </a></li>
                </ul>

                <div style="background: rgba(0, 128, 255, 0.1); border-radius: 15px; padding: 1.5rem; margin-top: 2rem; text-align: center;">
                    <i class="fas fa-headset" style="color: var(--primary-blue); font-size: 2rem;"></i><br>
                    <strong style="color: var(--text-primary);">Besoin d'aide ? Contactez-nous sur Discord !</strong>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn-modal-action">J'ai compris</button>
            </div>
        </div>
    </div>
    </div> <!-- Fin du dashboard-content -->

    <!-- Modal Profil Utilisateur -->
    <div id="profileModal" class="modal">
        <div class="modal-backdrop"></div>
        <div class="modal-container">
            <div class="modal-header">
                <div class="modal-title-wrapper">
                    <div class="modal-icon">
                        <i class="fas fa-user"></i>
                    </div>
                    <h2 class="modal-title">Modifier le profil</h2>
                </div>
                <button class="modal-close" onclick="closeModal('profile')">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <form id="profileEditForm" class="first-time-form">
                    <div class="form-group">
                        <label class="form-label">Nom d'affichage</label>
                        <input type="text" class="form-input" id="editDisplayName" name="displayName" placeholder="Votre nom d'affichage">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Bio</label>
                        <input type="text" class="form-input" id="editBio" name="bio" placeholder="Une courte description">
                    </div>
                    <div class="form-group">
                        <label class="form-label">URL de l'avatar</label>
                        <input type="url" class="form-input" id="editAvatarUrl" name="avatarUrl" placeholder="https://...">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Thème préféré</label>
                        <select class="form-select" id="editTheme" name="theme">
                            <option value="blue">Bleu (Par défaut)</option>
                            <option value="red">Rouge</option>
                            <option value="mixed">Mix</option>
                        </select>
                    </div>
                    <div class="first-time-actions">
                        <button type="button" class="btn-first-time btn-secondary-first" onclick="closeModal('profile')">Annuler</button>
                        <button type="submit" class="btn-first-time btn-primary-first">Enregistrer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Paramètres (à venir) -->
    <div id="settingsModal" class="modal">
      <div class="modal-backdrop"></div>
      <div class="modal-container">
        <div class="modal-header">
          <div class="modal-title-wrapper">
            <div class="modal-icon"><i class="fas fa-cog"></i></div>
            <h2 class="modal-title">Paramètres</h2>
          </div>
          <button class="modal-close" onclick="closeModal('settings')"><i class="fas fa-times"></i></button>
        </div>
        <div class="modal-body">
          <p style="opacity:.6">Fonctionnalité à venir. Certaines options seront bientôt disponibles.</p>
          <div style="filter:grayscale(1); opacity:.6; pointer-events:none;">
            <div class="form-group"><label class="form-label">Sons</label><input type="checkbox" checked></div>
            <div class="form-group"><label class="form-label">Animations</label><input type="checkbox" checked></div>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal Historique -->
    <div id="historyModal" class="modal">
      <div class="modal-backdrop"></div>
      <div class="modal-container">
        <div class="modal-header">
          <div class="modal-title-wrapper">
            <div class="modal-icon"><i class="fas fa-history"></i></div>
            <h2 class="modal-title">Historique</h2>
          </div>
          <button class="modal-close" onclick="closeModal('history')"><i class="fas fa-times"></i></button>
        </div>
        <div class="modal-body">
          <div id="historyList" style="display:flex; flex-direction:column; gap:10px;"></div>
        </div>
      </div>
    </div>

    <div id="dashboardLock" class="dashboard-lock-overlay">
      <div class="lock-card">
        <h3>Session terminée</h3>
        <p>Vous êtes déconnecté. Les modules sont bloqués.</p>
        <a id="reloginBtn" class="btn" href="#"><i class="fas fa-key"></i>Se reconnecter</a>
      </div>
    </div>

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
                    // Vérifier la première visite après l'affichage du dashboard
                    checkFirstTime();
                }, 1000);
            }

            // Quand la vidéo se termine
            introVideo.addEventListener('ended', showDashboard);

            // Bouton skip
            skipButton.addEventListener('click', showDashboard);

            // Gestion des erreurs vidéo
            introVideo.addEventListener('error', function() {
                showDashboard();
            });

            // Si la vidéo ne se charge pas après 3 secondes
            setTimeout(() => {
                if (introVideo.readyState === 0) {
                    showDashboard();
                }
            }, 3000);
        });



        // ==================== MFA CONNECT LOGIC ====================
        
        // Gestion du dropdown de profil
        function toggleProfile() {
            const dropdown = document.getElementById('profileDropdown');
            dropdown.classList.toggle('active');
            
            // Charger les données du profil si pas encore fait
            if (dropdown.classList.contains('active') && !dropdown.dataset.loaded) {
                loadProfileData();
            }
            
            // Fermer le dropdown en cliquant à l'extérieur
            document.addEventListener('click', function closeProfile(e) {
                if (!e.target.closest('.user-section') && !e.target.closest('#profileDropdown')) {
                    dropdown.classList.remove('active');
                    document.removeEventListener('click', closeProfile);
                }
            });
        }

        // Charger les données du profil
        async function loadProfileData() {
            try {
                const response = await fetch(`/modules/profile_manager.php?action=get_profile&token=${encodeURIComponent('<?= $token ?>')}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    }
                });
                
                if (response.ok) {
                    const profileData = await response.json();
                    updateProfileDisplay(profileData);
                    document.getElementById('profileDropdown').dataset.loaded = 'true';
                }
            } catch (error) {
                console.error('Erreur lors du chargement du profil:', error);
            }
        }

        // Mettre à jour l'affichage du profil
        function updateProfileDisplay(data) {
            // Mettre à jour l'avatar
            const avatar = document.getElementById('profileAvatar');
            if (avatar) {
                avatar.textContent = data.display_name.charAt(0).toUpperCase();
            }

            // Mettre à jour les statistiques
            const stats = document.querySelectorAll('.profile-stat-value');
            if (stats.length >= 2) {
                stats[0].textContent = data.stats.total_wins || 0;
                stats[1].textContent = data.stats.favorite_module || 'wins';
            }

            // Mettre à jour le nom d'affichage
            const displayName = document.querySelector('.profile-details h3');
            if (displayName) {
                displayName.textContent = data.display_name;
            }

            // Mettre à jour la bio
            const bio = document.querySelector('.profile-details p');
            if (bio) {
                bio.textContent = data.bio || `Token: ${data.token ? data.token.substring(0, 8) + '...' : 'N/A'}`;
            }
        }

        // ==================== FIRST-TIME PERSONALIZATION ====================
        
        // Vérifier si c'est la première visite
        function checkFirstTime() {
            const hasVisited = localStorage.getItem('mfa_connect_visited');
            if (!hasVisited) {
                // Afficher le modal de personnalisation
                setTimeout(() => {
                    showFirstTimeModal();
                }, 1000);
            }
        }

        // Afficher le modal de personnalisation
        function showFirstTimeModal() {
            const modal = document.getElementById('firstTimeModal');
            modal.classList.add('active');
        }

        // Sauvegarder la personnalisation
        async function savePersonalization() {
            const form = document.getElementById('personalizationForm');
            const formData = new FormData(form);
            
            try {
                const response = await fetch(`/modules/profile_manager.php?action=update_profile&token=${encodeURIComponent('<?= $token ?>')}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        display_name: formData.get('displayName'),
                        bio: formData.get('bio'),
                        avatar_url: formData.get('avatarUrl'),
                        theme_preference: formData.get('theme'),
                        language: formData.get('language')
                    })
                });
                
                if (response.ok) {
                    showNotification('Profil personnalisé avec succès !', 'success');
                    localStorage.setItem('mfa_connect_visited', 'true');
                    closeFirstTimeModal();
                    
                    // Mettre à jour l'affichage du nom d'utilisateur
                    const userName = document.querySelector('.user-name');
                    if (userName) {
                        userName.textContent = formData.get('displayName');
                    }
                } else {
                    showNotification('Erreur lors de la sauvegarde', 'error');
                }
            } catch (error) {
                console.error('Erreur lors de la personnalisation:', error);
                showNotification('Erreur de connexion', 'error');
            }
        }

        // Passer la personnalisation
        function skipPersonalization() {
            localStorage.setItem('mfa_connect_visited', 'true');
            closeFirstTimeModal();
            showNotification('Personnalisation ignorée', 'info');
        }

        // Fermer le modal de personnalisation
        function closeFirstTimeModal() {
            const modal = document.getElementById('firstTimeModal');
            modal.classList.remove('active');
        }
        
        // Copier le token avec effet futuriste
        function copyToken() {
            const tokenValue = document.getElementById('tokenValue').textContent;
            navigator.clipboard.writeText(tokenValue).then(() => {
                showNotification('Token copié avec succès !', 'success');
                createCopyEffect();
            });
        }

        // Créer un effet de copie Matrix style
        function createCopyEffect() {
            const tokenContainer = document.querySelector('.token-value-container');
            const particles = [];
            
            for (let i = 0; i < 10; i++) {
                const particle = document.createElement('div');
                particle.style.cssText = `
                    position: absolute;
                    width: 4px;
                    height: 4px;
                    background: var(--primary-blue);
                    border-radius: 50%;
                    pointer-events: none;
                    animation: copyParticle 1s ease-out forwards;
                `;
                
                const x = Math.random() * 100;
                const y = Math.random() * 100;
                particle.style.left = x + '%';
                particle.style.top = y + '%';
                particle.style.setProperty('--x', (Math.random() - 0.5) * 200 + 'px');
                particle.style.setProperty('--y', (Math.random() - 0.5) * 200 + 'px');
                
                tokenContainer.appendChild(particle);
                particles.push(particle);
            }
            
            setTimeout(() => {
                particles.forEach(particle => particle.remove());
            }, 1000);
        }

        // Notification améliorée
        function showNotification(message, type = 'info') {
            const notification = document.createElement('div');
            const colors = {
                success: 'var(--primary-blue)',
                error: 'var(--primary-red)',
                info: 'var(--gradient-mixed)'
            };
            
            notification.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                background: ${colors[type]};
                color: white;
                padding: 1rem 2rem;
                border-radius: 15px;
                font-weight: 600;
                font-family: var(--font-secondary);
                box-shadow: 0 10px 30px rgba(0, 128, 255, 0.4);
                z-index: 9999;
                animation: slideIn 0.3s ease-out;
                backdrop-filter: blur(10px);
                border: 1px solid rgba(255, 255, 255, 0.2);
            `;
            notification.textContent = message;
            document.body.appendChild(notification);

            setTimeout(() => {
                notification.style.animation = 'slideOut 0.3s ease-out';
                setTimeout(() => notification.remove(), 300);
            }, 3000);
        }

        // Gestion des modals améliorée
        function openModal(modalName) {
            const modal = document.getElementById(modalName + 'Modal');
            modal.classList.add('show');
            document.body.style.overflow = 'hidden';
            
            // Animation d'entrée
            const container = modal.querySelector('.modal-container');
            container.style.transform = 'translateY(50px) scale(0.9)';
            container.style.opacity = '0';
            
            setTimeout(() => {
                container.style.transform = 'translateY(0) scale(1)';
                container.style.opacity = '1';
            }, 10);
        }

        function closeModal(modalName) {
            const modal = document.getElementById(modalName + 'Modal');
            const container = modal.querySelector('.modal-container');
            
            container.style.transform = 'translateY(50px) scale(0.9)';
            container.style.opacity = '0';
            
            setTimeout(() => {
                modal.classList.remove('show');
                document.body.style.overflow = 'auto';
            }, 300);
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

        // Effet 3D sur les cartes de modules avec animation des valeurs
        document.querySelectorAll('.module-card').forEach(card => {
            const moduleType = card.getAttribute('data-module');
            const previewValue = card.querySelector('.preview-value');
            let animationInterval;
            let particleInterval;
            
            card.addEventListener('mousemove', (e) => {
                const rect = card.getBoundingClientRect();
                const x = e.clientX - rect.left;
                const y = e.clientY - rect.top;
                
                const centerX = rect.width / 2;
                const centerY = rect.height / 2;
                
                const rotateX = (y - centerY) / 10;
                const rotateY = (centerX - x) / 10;
                
                card.style.transform = `translateY(-15px) rotateX(${rotateX}deg) rotateY(${rotateY}deg)`;
            });
            
            card.addEventListener('mouseenter', () => {
                // Démarrer l'animation des valeurs
                let counter = 0;
                const originalValue = previewValue.textContent;
                
                // Créer des particules autour de la preview
                const previewScreen = card.querySelector('.preview-screen');
                particleInterval = setInterval(() => {
                    const particle = document.createElement('div');
                    particle.style.cssText = `
                        position: absolute;
                        width: 3px;
                        height: 3px;
                        background: var(--primary-blue);
                        border-radius: 50%;
                        pointer-events: none;
                        animation: particleFloat 1s ease-out forwards;
                        z-index: 10;
                    `;
                    
                    const x = Math.random() * previewScreen.offsetWidth;
                    const y = Math.random() * previewScreen.offsetHeight;
                    particle.style.left = x + 'px';
                    particle.style.top = y + 'px';
                    
                    previewScreen.appendChild(particle);
                    
                    setTimeout(() => particle.remove(), 1000);
                }, 100);
                
                animationInterval = setInterval(() => {
                    counter++;
                    
                    switch(moduleType) {
                        case 'wins':
                            const winsValues = ['0', '5', '12', '23', '42', '67', '89', '156'];
                            previewValue.textContent = winsValues[counter % winsValues.length];
                            break;
                        case 'timer':
                            const timerValues = ['00:00', '00:15', '00:30', '00:45', '01:00', '01:15', '01:30', '01:45'];
                            previewValue.textContent = timerValues[counter % timerValues.length];
                            break;
                        case 'battle':
                            const battleValues = ['0-0', '2-1', '5-3', '8-6', '12-9', '15-12', '18-15', '21-18'];
                            previewValue.textContent = battleValues[counter % battleValues.length];
                            break;
                    }
                }, 200);
            });
            
            card.addEventListener('mouseleave', () => {
                card.style.transform = 'translateY(0) rotateX(0) rotateY(0)';
                
                // Arrêter toutes les animations
                if (animationInterval) {
                    clearInterval(animationInterval);
                }
                if (particleInterval) {
                    clearInterval(particleInterval);
                }
                
                // Remettre la valeur originale avec un effet de transition
                previewValue.style.transition = 'all 0.3s ease';
                setTimeout(() => {
                    switch(moduleType) {
                        case 'wins':
                            previewValue.textContent = '0';
                            break;
                        case 'timer':
                            previewValue.textContent = '00:00';
                            break;
                        case 'battle':
                            previewValue.textContent = '0-0';
                            break;
                    }
                }, 100);
            });
        });

        // Animation des particules d'icônes
        function createIconParticles() {
            document.querySelectorAll('.module-icon-wrapper').forEach(icon => {
                const particles = icon.querySelector('.icon-particles');
                
                for (let i = 0; i < 5; i++) {
                    const particle = document.createElement('div');
                    particle.className = 'icon-particle';
                    particle.style.setProperty('--x', (Math.random() - 0.5) * 100 + 'px');
                    particle.style.setProperty('--y', (Math.random() - 0.5) * 100 + 'px');
                    particle.style.animationDelay = Math.random() * 2 + 's';
                    particles.appendChild(particle);
                }
            });
        }

        // Effet parallax amélioré
        document.addEventListener('mousemove', (e) => {
            const x = e.clientX / window.innerWidth;
            const y = e.clientY / window.innerHeight;
            
            // Déplacer les particules
            const particles = document.querySelectorAll('.particle');
            particles.forEach((particle, index) => {
                const speed = (index + 1) * 0.3;
                particle.style.transform = `translate(${x * speed}px, ${y * speed}px)`;
            });
            
            // Animer la grille 3D
            const grid = document.querySelector('.grid-3d');
            if (grid) {
                grid.style.transform = `perspective(1000px) rotateX(60deg) translateY(${y * 20}px) translateX(${x * 10}px)`;
            }
        });

        // Initialisation
        document.addEventListener('DOMContentLoaded', function() {
            createIconParticles();
            
            // Animation des stats
            const stats = document.querySelectorAll('.stat-value');
            stats.forEach(stat => {
                const finalValue = stat.textContent;
                if (finalValue === '3' || finalValue === 'Ready' || finalValue === 'Connected') {
                    stat.style.opacity = '0';
                    stat.style.transform = 'translateY(20px)';
                    
                    setTimeout(() => {
                        stat.style.transition = 'all 0.5s ease';
                        stat.style.opacity = '1';
                        stat.style.transform = 'translateY(0)';
                    }, 500);
                }
            });
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
                    @keyframes copyParticle {
            0% { 
                opacity: 1; 
                transform: translate(0, 0) scale(1);
            }
            100% { 
                opacity: 0; 
                transform: translate(var(--x), var(--y)) scale(0);
            }
        }

        @keyframes particleFloat {
            0% { 
                opacity: 1; 
                transform: translateY(0) scale(1);
            }
            100% { 
                opacity: 0; 
                transform: translateY(-20px) scale(0);
            }
        }
        `;
        document.head.appendChild(style);

        function openProfileModal(){
            // Pré-remplir avec les données déjà affichées si dispo
            const nameEl = document.querySelector('.profile-details h3');
            const bioEl = document.querySelector('.profile-details p');
            if (nameEl) document.getElementById('editDisplayName').value = nameEl.textContent.trim();
            if (bioEl) document.getElementById('editBio').value = bioEl.textContent.replace(/^Token:.*/, '').trim();
            openModal('profile');
        }

        // Soumission du formulaire de modification de profil
        const profileEditForm = document.getElementById('profileEditForm');
        if (profileEditForm){
            profileEditForm.addEventListener('submit', async (e)=>{
                e.preventDefault();
                const body = {
                    display_name: document.getElementById('editDisplayName').value,
                    bio: document.getElementById('editBio').value,
                    avatar_url: document.getElementById('editAvatarUrl').value,
                    theme_preference: document.getElementById('editTheme').value
                };
                try {
                    const res = await fetch(`/modules/profile_manager.php?action=update_profile&token=${encodeURIComponent('<?= $token ?>')}`, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify(body)
                    });
                    if (res.ok){
                        showNotification('Profil mis à jour', 'success');
                        closeModal('profile');
                        // rafraîchir l'affichage
                        loadProfileData();
                    } else {
                        showNotification('Erreur lors de la mise à jour', 'error');
                    }
                } catch(err){
                    showNotification('Erreur réseau', 'error');
                }
            });
        }

        async function openSettings(){ openModal('settings'); }

        // Charger l'historique récent depuis user_activity_log
        async function loadHistory(){
          const res = await fetch(`/modules/profile_manager.php?action=get_profile&token=${encodeURIComponent('<?= $token ?>')}`, {method:'POST'});
          // On réutilise l'endpoint existant pour déclencher l'initialisation si besoin, puis on appelle un petit fetch SQL en brut côté profil_manager via log_activity inexistante.
        }

        // Quand on ouvre l'historique, on tente de lire les derniers logs via une route légère
        async function openHistoryModal(){
          try {
            const res = await fetch(`/modules/profile_manager.php?action=wins_today_summary&token=${encodeURIComponent('<?= $token ?>')}`, {method:'POST'});
            const data = await res.json();
          } catch(e) {}
          // Pour afficher quelque chose d'utile, on montre les temps du jour des 3 modules
          const container = document.getElementById('historyList');
          container.innerHTML = '<div>Chargement...</div>';
          const [wins,timer,battle] = await Promise.all([
            fetch(`/modules/profile_manager.php?action=wins_today_summary&token=${encodeURIComponent('<?= $token ?>')}`, {method:'POST'}).then(r=>r.json()).catch(()=>({wins_today_seconds:0})),
            fetch(`/modules/profile_manager.php?action=timer_today_summary&token=${encodeURIComponent('<?= $token ?>')}`, {method:'POST'}).then(r=>r.json()).catch(()=>({timer_today_seconds:0})),
            fetch(`/modules/profile_manager.php?action=battle_today_summary&token=${encodeURIComponent('<?= $token ?>')}`, {method:'POST'}).then(r=>r.json()).catch(()=>({battle_today_seconds:0}))
          ]);
          function fmt(s){const m=Math.floor(s/60),sec=s%60;return `${m}m ${sec}s`;}
          container.innerHTML = `
            <div>Temps aujourd'hui - Wins: <strong>${fmt(wins.wins_today_seconds||0)}</strong></div>
            <div>Temps aujourd'hui - Timer: <strong>${fmt(timer.timer_today_seconds||0)}</strong></div>
            <div>Temps aujourd'hui - Team Battle: <strong>${fmt(battle.battle_today_seconds||0)}</strong></div>
          `;
          openModal('history');
        }

        // Déconnexion: invalide le token côté activité et verrouille l'UI sans recharger
        async function logoutUser(){
          try {
            await fetch(`/modules/profile_manager.php?action=log_activity&token=${encodeURIComponent('<?= $token ?>')}`, {
              method: 'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify({action:'logout', module:'profile'})
            });
          } catch(e){}
          // Lock UI
          document.getElementById('dashboardLock').classList.add('show');
          // Désactiver tous les liens de lancement de module
          document.querySelectorAll('.btn-module').forEach(a=>{ a.classList.add('disabled'); a.setAttribute('tabindex','-1'); a.addEventListener('click', e=>e.preventDefault()); });
          // Option: rediriger vers page d'accueil avec un token vide pour forcer re-login
          const relogin = document.getElementById('reloginBtn');
          const url = new URL(window.location.href); url.searchParams.set('token',''); relogin.href = url.toString();
        }

        // Appliquer thème préféré depuis backend quand dispo
        function applyTheme(theme){
          document.body.classList.remove('theme-red','theme-mixed');
          if (theme === 'red') document.body.classList.add('theme-red');
          if (theme === 'mixed') document.body.classList.add('theme-mixed');
        }

        // Après chargement du profil, appliquer le thème
        const originalUpdateProfileDisplay = window.updateProfileDisplay;
        window.updateProfileDisplay = function(data){
          if (typeof originalUpdateProfileDisplay === 'function') originalUpdateProfileDisplay(data);
          if (data && data.preferences && data.preferences.color_scheme){
            // Si le backend stocke color_scheme, on l'utilise sinon theme_preference si présent
            const theme = (data.preferences.color_scheme === 'blue_red') ? 'mixed' : (data.preferences.color_scheme === 'red' ? 'red' : 'blue');
            applyTheme(theme);
          }
        };

        // Lors de l'enregistrement du profil dans le modal, forcer application thème si choisi
        (function(){
          const themeSelect = document.getElementById('editTheme');
          if (themeSelect){ themeSelect.addEventListener('change', ()=>applyTheme(themeSelect.value)); }
        })();

        (function initThemeSwitcher(){
          const container = document.querySelector('.theme-switcher');
          if (!container) return;
          const btns = container.querySelectorAll('.theme-btn');
          function setActive(theme){ btns.forEach(b=>b.classList.toggle('active', b.dataset.theme===theme)); }
          btns.forEach(btn=>{
            btn.addEventListener('click', async ()=>{
              const theme = btn.dataset.theme;
              applyTheme(theme);
              setActive(theme);
              try{
                await fetch(`/modules/profile_manager.php?action=update_preferences&token=${encodeURIComponent('<?= $token ?>')}`, {
                  method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify({ color_scheme: theme==='mixed' ? 'blue_red' : theme })
                });
                showNotification('Thème appliqué', 'success');
              }catch(e){/*noop*/}
            });
          });
          // Marquer actif au chargement selon classe
          if (document.body.classList.contains('theme-red')) setActive('red');
          else if (document.body.classList.contains('theme-mixed')) setActive('mixed');
          else setActive('blue');
        })();
    </script>
</body>
</html>
</html>