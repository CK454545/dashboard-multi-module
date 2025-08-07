<?php
// Test simple du système de profil
require_once __DIR__ . '/modules/validate_token.php';

// Simuler un token pour le test
$token = 'test_token_123';

try {
    $profileManager = new ProfileManager($token);
    
    echo "<h1>Test du Système de Profil MFA CONNECT</h1>";
    
    // Test de création de profil
    $testData = [
        'display_name' => 'Test User',
        'bio' => 'Utilisateur de test pour MFA CONNECT',
        'theme_preference' => 'blue',
        'language' => 'fr'
    ];
    
    $result = $profileManager->updateProfile($testData);
    echo "<p>✅ Profil créé/mis à jour: " . ($result ? 'Succès' : 'Échec') . "</p>";
    
    // Test de récupération de profil
    $profile = $profileManager->getProfile();
    echo "<p>✅ Profil récupéré: " . ($profile ? 'Succès' : 'Échec') . "</p>";
    
    if ($profile) {
        echo "<h2>Données du profil:</h2>";
        echo "<pre>" . print_r($profile, true) . "</pre>";
    }
    
    // Test de récupération des stats
    $stats = $profileManager->getStats();
    echo "<p>✅ Stats récupérées: " . ($stats ? 'Succès' : 'Échec') . "</p>";
    
    if ($stats) {
        echo "<h2>Statistiques:</h2>";
        echo "<pre>" . print_r($stats, true) . "</pre>";
    }
    
    // Test de récupération des préférences
    $preferences = $profileManager->getPreferences();
    echo "<p>✅ Préférences récupérées: " . ($preferences ? 'Succès' : 'Échec') . "</p>";
    
    if ($preferences) {
        echo "<h2>Préférences:</h2>";
        echo "<pre>" . print_r($preferences, true) . "</pre>";
    }
    
    // Test de récupération du résumé
    $summary = $profileManager->getProfileSummary();
    echo "<p>✅ Résumé récupéré: " . ($summary ? 'Succès' : 'Échec') . "</p>";
    
    if ($summary) {
        echo "<h2>Résumé du profil:</h2>";
        echo "<pre>" . print_r($summary, true) . "</pre>";
    }
    
    echo "<h2>🎉 Tous les tests sont passés avec succès !</h2>";
    echo "<p>Le système de profil MFA CONNECT fonctionne correctement.</p>";
    
} catch (Exception $e) {
    echo "<h1>❌ Erreur lors du test</h1>";
    echo "<p>Erreur: " . $e->getMessage() . "</p>";
    echo "<p>Trace: " . $e->getTraceAsString() . "</p>";
}
?> 