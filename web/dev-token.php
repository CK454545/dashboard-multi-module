<?php
// Token provisoire pour développement local
// Usage: http://localhost:8000/dev-token.php

// Token de développement (à utiliser uniquement en local)
$devToken = "dev_local_token_2024";

// Rediriger vers le dashboard avec le token
header("Location: dashboard.php?token=" . urlencode($devToken));
exit;
?> 