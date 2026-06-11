<?php
require_once 'includes/auth.php';
require_once 'config/database.php';
require_once 'includes/crypto.php';
require_auth();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_valid_csrf_token();

    $mdp = $_POST['mot_de_passe'] ?? '';

    $stmt = $pdo->prepare('SELECT mot_de_passe_hash FROM utilisateurs WHERE id_utilisateur = :id');
    $stmt->execute(['id' => $_SESSION['user_id']]);
    $user = $stmt->fetch();

    if ($user && verifier_mot_de_passe($mdp, $user['mot_de_passe_hash'])) {
        // Supprimer les fichiers physiques
        $stmt = $pdo->prepare('SELECT nom_stockage FROM fichiers WHERE id_utilisateur = :id');
        $stmt->execute(['id' => $_SESSION['user_id']]);
        $fichiers = $stmt->fetchAll();

        foreach ($fichiers as $f) {
            $chemin = __DIR__ . '/uploads/' . $f['nom_stockage'];
            if (file_exists($chemin)) {
                unlink($chemin);
            }
        }

        // CASCADE supprime fichiers et partages
        $stmt = $pdo->prepare('DELETE FROM utilisateurs WHERE id_utilisateur = :id');
        $stmt->execute(['id' => $_SESSION['user_id']]);

        $_SESSION = [];
        session_destroy();

        header('Location: login.php');
        exit;
    } else {
        $error = 'Mot de passe incorrect.';
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supprimer mon compte - Stockage Sécurisé</title>
    <link rel="stylesheet" href="public/css/style.css">
</head>
<body class="auth-page">
    <div class="container">
        <h1>Supprimer mon compte</h1>
        <p class="warning">Cette action est irréversible. Tous vos fichiers seront supprimés.</p>

        <?php if ($error): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" action="delete_account.php">
            <?= csrf_field() ?>
            <div class="form-group">
                <label for="mot_de_passe">Confirmez votre mot de passe</label>
                <input type="password" id="mot_de_passe" name="mot_de_passe" required>
            </div>
            <button type="submit" class="btn btn-danger">Supprimer définitivement</button>
        </form>

        <p class="link"><a href="dashboard.php">Retour</a></p>
    </div>
</body>
</html>
