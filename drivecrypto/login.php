<?php
require_once 'includes/auth.php';
require_once 'config/database.php';
require_once 'includes/crypto.php';

if (is_logged_in()) {
    header('Location: dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_valid_csrf_token();

    $username = trim($_POST['username'] ?? '');
    $mdp = $_POST['mot_de_passe'] ?? '';

    if (empty($username) || empty($mdp)) {
        $error = 'Tous les champs sont obligatoires.';
    } else {
        $stmt = $pdo->prepare('SELECT id_utilisateur, username, mot_de_passe_hash FROM utilisateurs WHERE username = :u');
        $stmt->execute(['u' => $username]);
        $user = $stmt->fetch();

        if ($user && verifier_mot_de_passe($mdp, $user['mot_de_passe_hash'])) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['id_utilisateur'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['mdp_clair'] = $mdp; // Pour déchiffrer la clé privée RSA

            header('Location: dashboard.php');
            exit;
        } else {
            $error = 'Identifiants incorrects.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - Stockage Sécurisé</title>
    <link rel="stylesheet" href="public/css/style.css">
</head>
<body class="auth-page">
    <div class="container">
        <h1>Connexion</h1>

        <?php if ($error): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" action="login.php">
            <?= csrf_field() ?>
            <div class="form-group">
                <label for="username">Nom d'utilisateur</label>
                <input type="text" id="username" name="username" required
                       value="<?= htmlspecialchars($username ?? '') ?>">
            </div>

            <div class="form-group">
                <label for="mot_de_passe">Mot de passe</label>
                <input type="password" id="mot_de_passe" name="mot_de_passe" required>
            </div>

            <button type="submit" class="btn">Se connecter</button>
        </form>

        <p class="link">Pas de compte ? <a href="register.php">S'inscrire</a></p>
        <p class="legal-links">
            <a href="privacy.php">Confidentialité</a>
            <span aria-hidden="true">·</span>
            <a href="terms.php">Conditions d'utilisation</a>
        </p>
    </div>
</body>
</html>
