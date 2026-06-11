<?php
require_once 'includes/auth.php';
require_once 'config/database.php';
require_once 'includes/crypto.php';

if (is_logged_in()) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_valid_csrf_token();

    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $mdp = $_POST['mot_de_passe'] ?? '';
    $confirm = $_POST['mot_de_passe_confirmation'] ?? '';
    $terms_accepted = isset($_POST['terms_accepted']);

    // Validations
    if (empty($username) || empty($email) || empty($mdp)) {
        $error = translate('all_fields_required');
    } elseif (!$terms_accepted) {
        $error = translate('terms_required');
    } elseif (strlen($mdp) < 8) {
        $error = translate('password_too_short');
    } elseif ($mdp !== $confirm) {
        $error = translate('password_mismatch');
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = translate('invalid_email');
    } else {
        // Vérifier unicité username/email
        $stmt = $pdo->prepare('SELECT id_utilisateur FROM utilisateurs WHERE username = :u OR email = :e');
        $stmt->execute(['u' => $username, 'e' => $email]);

        if ($stmt->fetch()) {
            $error = translate('account_exists');
        } else {
            try {
                // 1. Hacher le mot de passe (Argon2id)
                $hash = hacher_mot_de_passe($mdp);

                // 2. Générer la paire RSA
                $cles = generer_paire_cles_rsa();

                // 3. Chiffrer la clé privée avec le mot de passe (PBKDF2 + AES-256-GCM)
                $cle_privee_chiffree = chiffrer_cle_privee($cles['privee'], $mdp);

                // 4. Insérer en base
                $stmt = $pdo->prepare('
                    INSERT INTO utilisateurs (username, email, mot_de_passe_hash, cle_publique, cle_privee_chiffree)
                    VALUES (:username, :email, :hash, :pub, :priv)
                ');
                $stmt->execute([
                    'username' => $username,
                    'email' => $email,
                    'hash' => $hash,
                    'pub' => $cles['publique'],
                    'priv' => $cle_privee_chiffree,
                ]);

                // 5. Connecter automatiquement
                $id = $pdo->lastInsertId();
                session_regenerate_id(true);
                $_SESSION['user_id'] = $id;
                $_SESSION['username'] = $username;
                $_SESSION['mdp_clair'] = $mdp; // Pour déchiffrer la clé privée RSA

                header('Location: dashboard.php');
                exit;

            } catch (Exception $e) {
                $error = translate('generic_error', $e->getMessage());
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="<?= current_language() ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= translate('register') ?> - <?= translate('app_name') ?></title>
    <link rel="stylesheet" href="public/css/style.css">
</head>
<body class="auth-page">
    <div class="container">
        <?= language_switcher() ?>
        <h1><?= translate('register') ?></h1>

        <?php if ($error): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" action="register.php">
            <?= csrf_field() ?>
            <div class="form-group">
                <label for="username"><?= translate('username') ?></label>
                <input type="text" id="username" name="username" required
                       value="<?= htmlspecialchars($username ?? '') ?>">
            </div>

            <div class="form-group">
                <label for="email"><?= translate('email') ?></label>
                <input type="email" id="email" name="email" required
                       value="<?= htmlspecialchars($email ?? '') ?>">
            </div>

            <div class="form-group">
                <label for="mot_de_passe"><?= translate('password_min') ?></label>
                <input type="password" id="mot_de_passe" name="mot_de_passe" required minlength="8">
            </div>

            <div class="form-group">
                <label for="mot_de_passe_confirmation"><?= translate('confirm_password') ?></label>
                <input type="password" id="mot_de_passe_confirmation" name="mot_de_passe_confirmation" required>
            </div>

            <div class="checkbox-group">
                <input type="checkbox" id="terms_accepted" name="terms_accepted" value="1" required
                       <?= !empty($terms_accepted) ? 'checked' : '' ?>>
                <label for="terms_accepted">
                    <?= translate('accept_terms_prefix') ?>
                    <a href="terms.php" target="_blank" rel="noopener"><?= translate('terms') ?></a>
                    <?= translate('and_read') ?>
                    <a href="privacy.php" target="_blank" rel="noopener"><?= translate('privacy_policy') ?></a>.
                </label>
            </div>

            <p class="form-notice">
                <?= translate('student_warning') ?>
            </p>

            <button type="submit" class="btn"><?= translate('sign_up') ?></button>
        </form>

        <p class="link"><?= translate('already_account') ?> <a href="login.php"><?= translate('sign_in') ?></a></p>
        <p class="legal-links">
            <a href="privacy.php"><?= translate('privacy') ?></a>
            <span aria-hidden="true">·</span>
            <a href="terms.php"><?= translate('terms') ?></a>
        </p>
    </div>
</body>
</html>
