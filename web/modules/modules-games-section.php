<?php
/**
 * 🎨 Modules & Jeux Interactifs - Composant Premium
 * 
 * Composant modulaire immersif et évolutif pour MY FULL AGENCY
 * Architecture future-proof avec support JSON/API dynamique
 * 
 * @version 2.0.0
 * @author MY FULL AGENCY
 */

// Configuration des données (future-proof pour API/JSON)
$modulesData = [
    [
        'id' => 'win',
        'title' => 'WIN',
        'description' => 'Module de compétition et récompenses pour stimuler l\'engagement de votre audience',
        'videoSrc' => 'assets/videos/modules/win-demo.mp4',
        'poster' => 'assets/images/modules/win-poster.jpg',
        'link' => '#win-module',
        'features' => ['Compétition', 'Récompenses'],
        'status' => 'active',
        'icon' => 'fas fa-trophy'
    ],
    [
        'id' => 'timer',
        'title' => 'TIMER',
        'description' => 'Système de chronomètre interactif pour créer du suspense et de l\'engagement',
        'videoSrc' => 'assets/videos/modules/timer-demo.mp4',
        'poster' => 'assets/images/modules/timer-poster.jpg',
        'link' => '#timer-module',
        'features' => ['Chronomètre', 'Suspense'],
        'status' => 'active',
        'icon' => 'fas fa-clock'
    ],
    [
        'id' => 'team',
        'title' => 'TEAM vs TEAM',
        'description' => 'Compétition d\'équipes pour créer de l\'engagement communautaire',
        'videoSrc' => 'assets/videos/modules/team-demo.mp4',
        'poster' => 'assets/images/modules/team-poster.jpg',
        'link' => '#team-module',
        'features' => ['Équipes', 'Communauté'],
        'status' => 'active',
        'icon' => 'fas fa-users'
    ]
];

$gamesData = [
    [
        'id' => 'gravie',
        'title' => 'Gravie le Mont Chilliad',
        'description' => 'Aventure d\'escalade interactive inspirée de GTA',
        'image' => 'assets/images/games/gravie-bg.jpg',
        'ctaLink' => '#gravie-game',
        'icon' => 'fas fa-mountain'
    ],
    [
        'id' => 'fortnite',
        'title' => 'Fortnite',
        'description' => 'Module gaming basé sur l\'univers Fortnite',
        'image' => 'assets/images/games/fortnite-bg.jpg',
        'ctaLink' => '#fortnite-game',
        'icon' => 'fas fa-fort-awesome'
    ],
    [
        'id' => 'onlyup',
        'title' => 'Fortnite Only Up',
        'description' => 'Défi d\'escalade verticale dans l\'univers Fortnite',
        'image' => 'assets/images/games/onlyup-bg.jpg',
        'ctaLink' => '#onlyup-game',
        'icon' => 'fas fa-arrow-up'
    ],
    [
        'id' => 'climb',
        'title' => 'Only Climb',
        'description' => 'Jeu d\'escalade minimaliste et addictif',
        'image' => 'assets/images/games/climb-bg.jpg',
        'ctaLink' => '#climb-game',
        'icon' => 'fas fa-climbing'
    ],
    [
        'id' => 'classroom',
        'title' => 'The Class Room',
        'description' => 'Simulation d\'environnement scolaire interactif',
        'image' => 'assets/images/games/classroom-bg.jpg',
        'ctaLink' => '#classroom-game',
        'icon' => 'fas fa-chalkboard'
    ],
    [
        'id' => 'horror',
        'title' => 'Jeux Horreur',
        'description' => 'Collection de jeux d\'horreur pour captiver l\'audience',
        'image' => 'assets/images/games/horror-bg.jpg',
        'ctaLink' => '#horror-game',
        'icon' => 'fas fa-ghost'
    ],
    [
        'id' => 'studio',
        'title' => 'Gametik Studio',
        'description' => 'Studio de création pour développer vos propres modules',
        'image' => 'assets/images/games/studio-bg.jpg',
        'ctaLink' => '#studio-game',
        'icon' => 'fas fa-palette'
    ]
];
?>

<!-- 🎨 Section Modules & Jeux Interactifs - Composant Premium -->
<section id="modules" class="modules-games-section" aria-labelledby="modules-games-title">
    <div class="modules-games-container">
        
        <!-- 📋 Header de section avec design system avancé -->
        <div class="modules-games-header" data-aos="fade-up">
            <h2 id="modules-games-title" class="modules-games-title">
                <span class="gradient-text">Modules & Jeux</span> Interactifs
            </h2>
            <p class="modules-games-subtitle">
                Découvrez nos modules premium et jeux interactifs pour maximiser votre engagement TikTok
            </p>
        </div>

        <!-- 🧩 Composant ModulesGrid - Grille vidéo dynamique -->
        <?php if (!empty($modulesData)): ?>
        <div class="modules-grid-section" data-aos="fade-up" data-aos-delay="100">
            <h3 class="section-subtitle">Modules Principaux</h3>
            <div class="modules-grid">
                <?php foreach ($modulesData as $index => $module): ?>
                <div class="module-card" 
                     data-aos="fade-up" 
                     data-aos-delay="<?php echo 150 + ($index * 50); ?>"
                     role="listitem">
                    
                    <!-- 🎥 Preview vidéo avec lazy loading -->
                    <div class="module-preview">
                        <video class="preview-video" 
                               autoplay muted loop playsinline 
                               preload="none"
                               poster="<?php echo htmlspecialchars($module['poster']); ?>"
                               loading="lazy">
                            <source src="<?php echo htmlspecialchars($module['videoSrc']); ?>" type="video/mp4">
                        </video>
                        
                        <!-- 🎭 Overlay interactif -->
                        <div class="preview-overlay">
                            <i class="fas fa-play"></i>
                        </div>
                    </div>

                    <!-- 🏷️ Contenu du module -->
                    <div class="module-content">
                        <div class="module-icon">
                            <i class="<?php echo htmlspecialchars($module['icon']); ?>"></i>
                        </div>
                        
                        <h4 class="module-title"><?php echo htmlspecialchars($module['title']); ?></h4>
                        
                        <p class="module-description">
                            <?php echo htmlspecialchars($module['description']); ?>
                        </p>
                        
                        <!-- 🏷️ Tags de fonctionnalités -->
                        <div class="module-features">
                            <?php foreach ($module['features'] as $feature): ?>
                            <span class="feature-tag"><?php echo htmlspecialchars($feature); ?></span>
                            <?php endforeach; ?>
                        </div>
                        
                        <!-- 📊 Status badge -->
                        <div class="module-status">
                            <span class="status-badge <?php echo $module['status']; ?>">
                                <?php echo ucfirst($module['status']); ?>
                            </span>
                        </div>
                    </div>

                    <!-- 🔗 CTA avec accessibilité -->
                    <a href="<?php echo htmlspecialchars($module['link']); ?>" 
                       class="module-cta"
                       aria-label="Découvrir le module <?php echo htmlspecialchars($module['title']); ?>">
                        <span>Découvrir</span>
                        <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- 🎮 Composant GamesGrid - Grille de jeux interactifs -->
        <?php if (!empty($gamesData)): ?>
        <div class="games-grid-section" data-aos="fade-up" data-aos-delay="300">
            <h3 class="section-subtitle">Nos Jeux Interactifs</h3>
            <div class="games-grid">
                <?php foreach ($gamesData as $index => $game): ?>
                <div class="game-card" 
                     data-aos="fade-up" 
                     data-aos-delay="<?php echo 350 + ($index * 50); ?>"
                     role="listitem">
                    
                    <!-- 🖼️ Image de fond avec effet flou -->
                    <div class="game-background" 
                         style="background-image: url('<?php echo htmlspecialchars($game['image']); ?>')">
                    </div>
                    
                    <!-- 🎭 Overlay avec contenu -->
                    <div class="game-content">
                        <div class="game-icon">
                            <i class="<?php echo htmlspecialchars($game['icon']); ?>"></i>
                        </div>
                        
                        <h4 class="game-title">
                            <?php echo htmlspecialchars($game['title']); ?>
                        </h4>
                        
                        <p class="game-description">
                            <?php echo htmlspecialchars($game['description']); ?>
                        </p>
                    </div>

                    <!-- 🔗 CTA avec accessibilité -->
                    <a href="<?php echo htmlspecialchars($game['ctaLink']); ?>" 
                       class="game-cta"
                       aria-label="Découvrir le jeu <?php echo htmlspecialchars($game['title']); ?>">
                        <span>Découvrir</span>
                        <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- 🚀 CTA Section avec design premium -->
        <div class="modules-games-cta" data-aos="fade-up" data-aos-delay="700">
            <p class="cta-text">Nouvelles fonctionnalités et jeux ajoutés régulièrement !</p>
            <a href="#join" class="cta-button primary">
                <i class="fas fa-arrow-right"></i>
                Accéder aux modules
            </a>
        </div>

    </div>
</section>

<!-- 🎨 CSS Premium pour le composant -->
<style>
/* ==================== DESIGN SYSTEM VARIABLES ==================== */
:root {
    /* 🎨 Couleurs principales */
    --primary-color: #8b00ff;
    --primary-hover: #7000dd;
    --secondary-color: #00d4ff;
    --secondary-hover: #00b8e6;
    --accent-color: #ff006e;
    --success-color: #44ff00;
    --warning-color: #ff9500;
    --danger-color: #ff0000;
    
    /* 🌑 Backgrounds */
    --bg-primary: #0a0e1b;
    --bg-secondary: rgba(255, 255, 255, 0.05);
    --bg-tertiary: rgba(255, 255, 255, 0.08);
    --bg-card: rgba(255, 255, 255, 0.03);
    --bg-glass: rgba(255, 255, 255, 0.05);
    --bg-gradient: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    
    /* 📝 Text Colors */
    --text-primary: #ffffff;
    --text-secondary: #a0a0a0;
    --text-muted: #666666;
    
    /* 🔲 Borders */
    --border-color: rgba(255, 255, 255, 0.1);
    --border-hover: rgba(255, 255, 255, 0.2);
    --border-focus: rgba(139, 0, 255, 0.5);
    
    /* 🌟 Shadows */
    --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
    --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
    --shadow-xl: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
    --shadow-glow: 0 0 20px rgba(99, 102, 241, 0.3);
    
    /* 📏 Spacing */
    --spacing-xs: 0.25rem;
    --spacing-sm: 0.5rem;
    --spacing-md: 1rem;
    --spacing-lg: 1.5rem;
    --spacing-xl: 2rem;
    --spacing-2xl: 3rem;
    
    /* 🔄 Border Radius */
    --radius-sm: 0.25rem;
    --radius-md: 0.5rem;
    --radius-lg: 0.75rem;
    --radius-xl: 1rem;
    --radius-full: 9999px;
    
    /* ⚡ Transitions */
    --transition-fast: 0.15s ease;
    --transition-normal: 0.3s ease;
    --transition-slow: 0.5s ease;
}

/* ==================== SECTION CONTAINER ==================== */
.modules-games-section {
    position: relative;
    background: var(--bg-primary);
    padding: var(--spacing-2xl) 0;
    overflow: hidden;
}

/* 🌈 Background animé */
.modules-games-section::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: 
        radial-gradient(circle at 20% 50%, rgba(255, 0, 110, 0.1) 0%, transparent 50%),
        radial-gradient(circle at 80% 50%, rgba(139, 0, 255, 0.1) 0%, transparent 50%),
        radial-gradient(circle at 50% 100%, rgba(0, 212, 255, 0.1) 0%, transparent 50%);
    animation: backgroundFloat 20s ease-in-out infinite;
    z-index: 0;
}

@keyframes backgroundFloat {
    0%, 100% { transform: translateY(0px) rotate(0deg); }
    50% { transform: translateY(-20px) rotate(1deg); }
}

.modules-games-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 var(--spacing-xl);
    position: relative;
    z-index: 1;
}

/* ==================== HEADER SECTION ==================== */
.modules-games-header {
    text-align: center;
    margin-bottom: var(--spacing-2xl);
}

.modules-games-title {
    font-size: clamp(2.5rem, 5vw, 4rem);
    font-weight: 900;
    line-height: 1.1;
    margin-bottom: var(--spacing-lg);
    background: var(--bg-gradient);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.modules-games-subtitle {
    font-size: clamp(1rem, 2vw, 1.25rem);
    color: var(--text-secondary);
    max-width: 600px;
    margin: 0 auto;
    line-height: 1.6;
}

/* ==================== MODULES GRID ==================== */
.modules-grid-section {
    margin-bottom: var(--spacing-2xl);
}

.section-subtitle {
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--text-primary);
    margin-bottom: var(--spacing-xl);
    text-align: center;
}

.modules-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
    gap: var(--spacing-xl);
    margin-bottom: var(--spacing-xl);
}

/* 🎨 Module Card */
.module-card {
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-xl);
    overflow: hidden;
    transition: all var(--transition-normal);
    position: relative;
    backdrop-filter: blur(10px);
}

.module-card:hover {
    transform: translateY(-8px);
    box-shadow: var(--shadow-xl);
    border-color: var(--primary-color);
}

/* 🎥 Video Preview */
.module-preview {
    position: relative;
    aspect-ratio: 16/9;
    overflow: hidden;
}

.preview-video {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform var(--transition-normal);
}

.module-card:hover .preview-video {
    transform: scale(1.05);
}

.preview-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.4);
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: opacity var(--transition-normal);
}

.module-card:hover .preview-overlay {
    opacity: 1;
}

.preview-overlay i {
    font-size: 3rem;
    color: white;
    filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.5));
}

/* 📝 Module Content */
.module-content {
    padding: var(--spacing-xl);
}

.module-icon {
    width: 60px;
    height: 60px;
    background: var(--bg-gradient);
    border-radius: var(--radius-lg);
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: var(--spacing-lg);
    font-size: 1.5rem;
    color: white;
}

.module-title {
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--text-primary);
    margin-bottom: var(--spacing-sm);
}

.module-description {
    color: var(--text-secondary);
    line-height: 1.6;
    margin-bottom: var(--spacing-lg);
}

/* 🏷️ Feature Tags */
.module-features {
    display: flex;
    flex-wrap: wrap;
    gap: var(--spacing-sm);
    margin-bottom: var(--spacing-lg);
}

.feature-tag {
    background: var(--bg-glass);
    color: var(--text-secondary);
    padding: var(--spacing-xs) var(--spacing-sm);
    border-radius: var(--radius-full);
    font-size: 0.875rem;
    font-weight: 500;
    border: 1px solid var(--border-color);
}

/* 📊 Status Badge */
.module-status {
    margin-bottom: var(--spacing-lg);
}

.status-badge {
    display: inline-flex;
    align-items: center;
    padding: var(--spacing-xs) var(--spacing-sm);
    border-radius: var(--radius-full);
    font-size: 0.875rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.status-badge.active {
    background: rgba(68, 255, 0, 0.2);
    color: var(--success-color);
    border: 1px solid rgba(68, 255, 0, 0.3);
}

/* 🔗 Module CTA */
.module-cta {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: var(--spacing-md) var(--spacing-xl);
    background: var(--bg-glass);
    color: var(--text-primary);
    text-decoration: none;
    font-weight: 600;
    transition: all var(--transition-fast);
    border-top: 1px solid var(--border-color);
}

.module-cta:hover {
    background: var(--primary-color);
    color: white;
    transform: translateX(4px);
}

/* ==================== GAMES GRID ==================== */
.games-grid-section {
    margin-bottom: var(--spacing-2xl);
}

.games-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: var(--spacing-lg);
}

/* 🎮 Game Card */
.game-card {
    position: relative;
    aspect-ratio: 4/3;
    border-radius: var(--radius-xl);
    overflow: hidden;
    transition: all var(--transition-normal);
    cursor: pointer;
}

.game-card:hover {
    transform: translateY(-4px) scale(1.02);
    box-shadow: var(--shadow-lg);
}

/* 🖼️ Game Background */
.game-background {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-size: cover;
    background-position: center;
    filter: blur(2px) brightness(0.7);
    transition: all var(--transition-normal);
}

.game-card:hover .game-background {
    filter: blur(0px) brightness(1);
    transform: scale(1.1);
}

/* 🎭 Game Content */
.game-content {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    text-align: center;
    padding: var(--spacing-xl);
    background: rgba(0, 0, 0, 0.3);
    transition: background var(--transition-normal);
}

.game-card:hover .game-content {
    background: rgba(0, 0, 0, 0.1);
}

.game-icon {
    width: 80px;
    height: 80px;
    background: var(--bg-gradient);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: var(--spacing-lg);
    font-size: 2rem;
    color: white;
    box-shadow: var(--shadow-lg);
}

.game-title {
    font-size: 1.25rem;
    font-weight: 700;
    color: white;
    margin-bottom: var(--spacing-sm);
    text-shadow: 0 2px 4px rgba(0, 0, 0, 0.5);
}

.game-description {
    color: rgba(255, 255, 255, 0.9);
    font-size: 0.875rem;
    line-height: 1.5;
    text-shadow: 0 1px 2px rgba(0, 0, 0, 0.5);
}

/* 🔗 Game CTA */
.game-cta {
    position: absolute;
    bottom: var(--spacing-md);
    left: 50%;
    transform: translateX(-50%);
    background: var(--primary-color);
    color: white;
    padding: var(--spacing-sm) var(--spacing-lg);
    border-radius: var(--radius-full);
    text-decoration: none;
    font-weight: 600;
    font-size: 0.875rem;
    opacity: 0;
    transition: all var(--transition-normal);
    display: flex;
    align-items: center;
    gap: var(--spacing-xs);
}

.game-card:hover .game-cta {
    opacity: 1;
    transform: translateX(-50%) translateY(-4px);
}

/* ==================== CTA SECTION ==================== */
.modules-games-cta {
    text-align: center;
    margin-top: var(--spacing-2xl);
}

.cta-text {
    color: var(--text-secondary);
    font-size: 1.125rem;
    margin-bottom: var(--spacing-lg);
}

.cta-button {
    display: inline-flex;
    align-items: center;
    gap: var(--spacing-sm);
    background: var(--bg-gradient);
    color: white;
    padding: var(--spacing-md) var(--spacing-xl);
    border-radius: var(--radius-lg);
    text-decoration: none;
    font-weight: 600;
    font-size: 1.125rem;
    transition: all var(--transition-normal);
    box-shadow: var(--shadow-md);
}

.cta-button:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-xl);
    filter: brightness(1.1);
}

/* ==================== RESPONSIVE DESIGN ==================== */
@media (max-width: 768px) {
    .modules-games-container {
        padding: 0 var(--spacing-md);
    }
    
    .modules-grid {
        grid-template-columns: 1fr;
        gap: var(--spacing-lg);
    }
    
    .games-grid {
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: var(--spacing-md);
    }
    
    .module-content {
        padding: var(--spacing-lg);
    }
    
    .game-content {
        padding: var(--spacing-lg);
    }
    
    .game-icon {
        width: 60px;
        height: 60px;
        font-size: 1.5rem;
    }
}

@media (max-width: 480px) {
    .modules-games-title {
        font-size: 2rem;
    }
    
    .modules-games-subtitle {
        font-size: 1rem;
    }
    
    .section-subtitle {
        font-size: 1.25rem;
    }
    
    .games-grid {
        grid-template-columns: 1fr;
    }
}

/* ==================== ACCESSIBILITY ==================== */
@media (prefers-reduced-motion: reduce) {
    .module-card,
    .game-card,
    .preview-video,
    .game-background {
        transition: none;
    }
    
    .modules-games-section::before {
        animation: none;
    }
}

/* Focus styles pour l'accessibilité */
.module-cta:focus-visible,
.game-cta:focus-visible,
.cta-button:focus-visible {
    outline: 2px solid var(--primary-color);
    outline-offset: 2px;
}

/* ==================== PERFORMANCE OPTIMIZATIONS ==================== */
.module-card,
.game-card {
    will-change: transform;
}

.preview-video {
    will-change: transform;
}

.game-background {
    will-change: transform, filter;
}

/* ==================== ANIMATIONS ENTRANCE ==================== */
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

[data-aos="fade-up"] {
    animation: fadeInUp 0.6s ease-out forwards;
}

/* ==================== LOADING STATES ==================== */
.module-card.loading,
.game-card.loading {
    opacity: 0.7;
    pointer-events: none;
}

.loading::after {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 20px;
    height: 20px;
    border: 2px solid var(--border-color);
    border-top-color: var(--primary-color);
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    to { transform: translate(-50%, -50%) rotate(360deg); }
}
</style>

<!-- 🚀 JavaScript pour interactions avancées -->
<script>
/**
 * 🎨 Modules & Games Section - JavaScript Premium
 * 
 * Gestion des interactions, animations et optimisations
 * Architecture modulaire et évolutive
 */

class ModulesGamesSection {
    constructor() {
        this.init();
    }

    init() {
        // ✅ Initialiser les composants
        this.initLazyLoading();
        this.initVideoOptimizations();
        this.initHoverEffects();
        this.initAccessibility();
        this.initPerformanceMonitoring();
    }

    // 📦 Lazy loading pour les vidéos et images
    initLazyLoading() {
        const observerOptions = {
            root: null,
            rootMargin: '50px',
            threshold: 0.1
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const video = entry.target;
                    if (video.tagName === 'VIDEO') {
                        video.load();
                        video.play().catch(() => {
                            // Gestion silencieuse des erreurs de lecture
                        });
                    }
                }
            });
        }, observerOptions);

        // Observer tous les éléments vidéo
        document.querySelectorAll('.preview-video').forEach(video => {
            observer.observe(video);
        });
    }

    // 🎥 Optimisations vidéo
    initVideoOptimizations() {
        document.querySelectorAll('.preview-video').forEach(video => {
            // Pause automatique quand hors de vue
            const videoObserver = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        video.play().catch(() => {});
                    } else {
                        video.pause();
                    }
                });
            }, { threshold: 0.5 });

            videoObserver.observe(video);
        });
    }

    // 🎭 Effets de hover avancés
    initHoverEffects() {
        document.querySelectorAll('.module-card, .game-card').forEach(card => {
            card.addEventListener('mouseenter', this.handleCardHover.bind(this));
            card.addEventListener('mouseleave', this.handleCardLeave.bind(this));
        });
    }

    handleCardHover(event) {
        const card = event.currentTarget;
        card.style.transform = 'translateY(-8px) scale(1.02)';
        
        // Effet de parallaxe subtil
        const video = card.querySelector('.preview-video');
        if (video) {
            video.style.transform = 'scale(1.05)';
        }
    }

    handleCardLeave(event) {
        const card = event.currentTarget;
        card.style.transform = 'translateY(0) scale(1)';
        
        const video = card.querySelector('.preview-video');
        if (video) {
            video.style.transform = 'scale(1)';
        }
    }

    // ♿ Accessibilité avancée
    initAccessibility() {
        // Navigation au clavier
        document.querySelectorAll('.module-card, .game-card').forEach(card => {
            card.addEventListener('keydown', (event) => {
                if (event.key === 'Enter' || event.key === ' ') {
                    event.preventDefault();
                    const link = card.querySelector('a');
                    if (link) {
                        link.click();
                    }
                }
            });
        });

        // Annonces pour lecteurs d'écran
        document.querySelectorAll('.module-cta, .game-cta').forEach(link => {
            link.setAttribute('role', 'button');
            link.setAttribute('tabindex', '0');
        });
    }

    // 📊 Monitoring des performances
    initPerformanceMonitoring() {
        // Mesurer le temps de chargement
        const startTime = performance.now();
        
        window.addEventListener('load', () => {
            const loadTime = performance.now() - startTime;
            console.log(`🎨 Modules & Games Section loaded in ${loadTime.toFixed(2)}ms`);
        });

        // Observer les erreurs de chargement
        document.querySelectorAll('video').forEach(video => {
            video.addEventListener('error', (event) => {
                console.warn('⚠️ Video loading error:', event.target.src);
            });
        });
    }

    // 🔄 Méthode pour recharger dynamiquement les données
    reloadData(newModulesData, newGamesData) {
        // ✅ Implémentation future pour rechargement dynamique
        console.log('🔄 Reloading modules and games data...');
        
        // Ici on pourrait implémenter la logique de rechargement
        // avec les nouvelles données depuis une API
    }

    // 🎯 Méthode pour filtrer les modules/jeux
    filterByCategory(category) {
        const cards = document.querySelectorAll('.module-card, .game-card');
        
        cards.forEach(card => {
            const hasCategory = card.dataset.category === category;
            card.style.display = hasCategory ? 'block' : 'none';
        });
    }
}

// 🚀 Initialisation du composant
document.addEventListener('DOMContentLoaded', () => {
    new ModulesGamesSection();
});

// 📦 Export pour utilisation future
if (typeof module !== 'undefined' && module.exports) {
    module.exports = ModulesGamesSection;
}
</script> 