<?php
// Fichier index.php pour le serveur local
// Redirection vers le dashboard principal

// Vérifier si un token est fourni
$token = $_GET['token'] ?? '';

if (empty($token)) {
    // Si pas de token, afficher une page d'accueil simple
    ?>
    <!DOCTYPE html>
    <html lang="fr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>StreamPro Studio - Serveur Local</title>
        <style>
            body {
                font-family: 'Inter', sans-serif;
                background: linear-gradient(135deg, #0a0e1b 0%, #1e293b 100%);
                color: white;
                margin: 0;
                padding: 2rem;
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
            }
            .container {
                text-align: center;
                max-width: 600px;
            }
            h1 {
                font-size: 3rem;
                margin-bottom: 1rem;
                background: linear-gradient(135deg, #ff006e, #8b00ff, #00d4ff);
                background-clip: text;
                -webkit-background-clip: text;
                color: transparent;
            }
            .status {
                background: rgba(16, 185, 129, 0.2);
                border: 1px solid #10b981;
                border-radius: 12px;
                padding: 1rem;
                margin: 2rem 0;
            }
            .warning {
                background: rgba(245, 158, 11, 0.2);
                border: 1px solid #f59e0b;
                border-radius: 12px;
                padding: 1rem;
                margin: 1rem 0;
            }
            .btn {
                background: linear-gradient(135deg, #ff006e, #8b00ff);
                color: white;
                padding: 1rem 2rem;
                border-radius: 12px;
                text-decoration: none;
                display: inline-block;
                margin: 0.5rem;
                transition: all 0.3s ease;
            }
            .btn:hover {
                transform: translateY(-2px);
                box-shadow: 0 10px 30px rgba(139, 0, 255, 0.4);
            }
        </style>
    </head>
    <body>
        <div class="container">
            <h1>🚀 StreamPro Studio</h1>
            <p>Serveur de développement local</p>
            
            <div class="status">
                <strong>✅ Serveur local actif</strong><br>
                URL: http://localhost:8000
            </div>
            
            <div class="warning">
                <strong>⚠️ Attention</strong><br>
                Ce serveur est en mode développement local.<br>
                Aucune modification ne sera envoyée sur le serveur officiel.
            </div>
            
            <h3>📋 Modules disponibles :</h3>
            <a href="dashboard.php" class="btn">Dashboard Principal</a>
            <a href="modules/timer.php" class="btn">Module Timer</a>
            <a href="modules/win.php" class="btn">Module Wins</a>
            <a href="modules/team-battle.php" class="btn">Module Teams</a>
            
            <div style="margin-top: 2rem; font-size: 0.9rem; opacity: 0.7;">
                <p>🛡️ Mode développement sécurisé</p>
                <p>Branche: dev-local | Modifications locales uniquement</p>
            </div>
        </div>
    </body>
    </html>
    <?php
} else {
    // Si un token est fourni, rediriger vers le dashboard
    header("Location: dashboard.php?token=" . urlencode($token));
    exit;
}

// Redirection automatique vers le dashboard avec token de dev
header("Location: dev-token.php");
exit;
?> 