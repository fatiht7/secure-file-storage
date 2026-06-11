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
        $error = translate('all_fields_required');
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
            $error = translate('invalid_credentials');
        }
    }
}
?>
<!DOCTYPE html>
<html lang="<?= current_language() ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= translate('login') ?> - <?= translate('app_name') ?></title>
    <link rel="stylesheet" href="public/css/style.css">
</head>
<body class="auth-page">
    <div class="container">
        <?= language_switcher() ?>
        <h1><?= translate('login') ?></h1>

        <?php if ($error): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" action="login.php">
            <?= csrf_field() ?>
            <div class="form-group">
                <label for="username"><?= translate('username') ?></label>
                <input type="text" id="username" name="username" required
                       value="<?= htmlspecialchars($username ?? '') ?>">
            </div>

            <div class="form-group">
                <label for="mot_de_passe"><?= translate('password') ?></label>
                <div class="password-field">
                    <input type="password" id="mot_de_passe" name="mot_de_passe" required>
                    <button type="button" class="password-toggle"
                            data-password-toggle="mot_de_passe"
                            data-show-label="<?= htmlspecialchars(translate('show_password'), ENT_QUOTES, 'UTF-8') ?>"
                            data-hide-label="<?= htmlspecialchars(translate('hide_password'), ENT_QUOTES, 'UTF-8') ?>"
                            aria-pressed="false">
                        <?= translate('show_password') ?>
                    </button>
                </div>
            </div>

            <button type="submit" class="btn"><?= translate('sign_in') ?></button>
        </form>

        <p class="link"><?= translate('no_account') ?> <a href="register.php"><?= translate('sign_up') ?></a></p>
        <p class="legal-links">
            <a href="privacy.php"><?= translate('privacy') ?></a>
            <span aria-hidden="true">·</span>
            <a href="terms.php"><?= translate('terms') ?></a>
        </p>
    </div>
    <script src="public/js/app.js"></script>
</body>
</html>
