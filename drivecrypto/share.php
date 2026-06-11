<?php
require_once 'includes/auth.php';
require_once 'config/database.php';
require_once 'includes/crypto.php';
require_auth();

$error = '';
$success = '';

$id_fichier = intval($_GET['id'] ?? $_POST['id_fichier'] ?? 0);

// Vérifier que le fichier appartient à l'utilisateur connecté
$stmt = $pdo->prepare('
    SELECT f.id_fichier, f.nom_original_chiffre, p.cle_aes_chiffree
    FROM fichiers f
    JOIN partager p ON p.id_fichier = f.id_fichier AND p.id_utilisateur = :uid
    WHERE f.id_fichier = :fid AND f.id_utilisateur = :uid
');
$stmt->execute(['fid' => $id_fichier, 'uid' => $_SESSION['user_id']]);
$fichier = $stmt->fetch();

if (!$fichier) {
    die(translate('owner_file_not_found'));
}

// Déchiffrer le nom pour l'affichage
$stmt_key = $pdo->prepare('SELECT cle_privee_chiffree FROM utilisateurs WHERE id_utilisateur = :id');
$stmt_key->execute(['id' => $_SESSION['user_id']]);
$cle_privee = dechiffrer_cle_privee($stmt_key->fetchColumn(), $_SESSION['mdp_clair']);
$cle_aes_share = dechiffrer_rsa($fichier['cle_aes_chiffree'], $cle_privee);
$nom_affiche = dechiffrer_texte_aes($fichier['nom_original_chiffre'], $cle_aes_share);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_valid_csrf_token();

    $action = $_POST['action'] ?? 'share';

    if ($action === 'revoke') {
        $destinataire_id = intval($_POST['destinataire_id'] ?? 0);

        $stmt = $pdo->prepare('
            DELETE FROM partager p
            USING utilisateurs u, fichiers f
            WHERE p.id_utilisateur = u.id_utilisateur
              AND p.id_fichier = f.id_fichier
              AND p.id_fichier = :fid
              AND p.id_utilisateur = :dest
              AND f.id_utilisateur = :owner
              AND p.id_utilisateur != :owner
            RETURNING u.username
        ');
        $stmt->execute([
            'fid' => $id_fichier,
            'dest' => $destinataire_id,
            'owner' => $_SESSION['user_id'],
        ]);
        $username_revoque = $stmt->fetchColumn();

        if ($username_revoque === false) {
            $error = translate('share_not_found');
        } else {
            $success = translate('share_revoked', $username_revoque);
        }
    } else {
        $destinataire_username = trim($_POST['destinataire'] ?? '');

        if (empty($destinataire_username)) {
            $error = translate('recipient_required');
        } elseif ($destinataire_username === $_SESSION['username']) {
            $error = translate('cannot_share_self');
        } else {
            // 1. Trouver le destinataire et sa clé publique
            $stmt = $pdo->prepare('SELECT id_utilisateur, cle_publique FROM utilisateurs WHERE username = :u');
            $stmt->execute(['u' => $destinataire_username]);
            $destinataire = $stmt->fetch();

            if (!$destinataire) {
                $error = translate('user_not_found');
            } else {
                // Vérifier que le partage n'existe pas déjà
                $stmt = $pdo->prepare('SELECT 1 FROM partager WHERE id_fichier = :fid AND id_utilisateur = :uid');
                $stmt->execute(['fid' => $id_fichier, 'uid' => $destinataire['id_utilisateur']]);

                if ($stmt->fetch()) {
                    $error = translate('already_shared');
                } else {
                    try {
                        // 2. Récupérer la clé AES chiffrée du propriétaire
                        $stmt = $pdo->prepare('SELECT cle_aes_chiffree FROM partager WHERE id_fichier = :fid AND id_utilisateur = :uid');
                        $stmt->execute(['fid' => $id_fichier, 'uid' => $_SESSION['user_id']]);
                        $cle_aes_chiffree_proprio = $stmt->fetchColumn();

                        // 3. Déchiffrer la clé privée RSA du propriétaire
                        $stmt = $pdo->prepare('SELECT cle_privee_chiffree FROM utilisateurs WHERE id_utilisateur = :id');
                        $stmt->execute(['id' => $_SESSION['user_id']]);
                        $cle_privee_chiffree = $stmt->fetchColumn();

                        $cle_privee = dechiffrer_cle_privee($cle_privee_chiffree, $_SESSION['mdp_clair']);

                        // 4. Déchiffrer la clé AES
                        $cle_aes = dechiffrer_rsa($cle_aes_chiffree_proprio, $cle_privee);

                        // 5. Re-chiffrer la clé AES avec la clé publique du destinataire
                        $cle_aes_pour_dest = chiffrer_rsa($cle_aes, $destinataire['cle_publique']);

                        // 6. Insérer le partage
                        $stmt = $pdo->prepare('
                            INSERT INTO partager (id_utilisateur, id_fichier, cle_aes_chiffree)
                            VALUES (:uid, :fid, :cle)
                        ');
                        $stmt->execute([
                            'uid' => $destinataire['id_utilisateur'],
                            'fid' => $id_fichier,
                            'cle' => $cle_aes_pour_dest,
                        ]);

                        $success = translate('shared_success', $destinataire_username);

                    } catch (Exception $e) {
                        $error = translate('generic_error', $e->getMessage());
                    }
                }
            }
        }
    }
}

// Liste des utilisateurs avec qui le fichier est déjà partagé
$stmt = $pdo->prepare('
    SELECT u.id_utilisateur, u.username, p.date_partage
    FROM partager p
    JOIN utilisateurs u ON u.id_utilisateur = p.id_utilisateur
    WHERE p.id_fichier = :fid AND p.id_utilisateur != :uid
    ORDER BY p.date_partage DESC
');
$stmt->execute(['fid' => $id_fichier, 'uid' => $_SESSION['user_id']]);
$partages = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="<?= current_language() ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= translate('share') ?> - <?= translate('app_name') ?></title>
    <link rel="stylesheet" href="public/css/style.css">
</head>
<body class="auth-page">
    <div class="container">
        <?= language_switcher() ?>
        <h1><?= translate('share_file') ?></h1>
        <p><?= translate('file') ?> : <strong><?= htmlspecialchars($nom_affiche) ?></strong></p>

        <?php if ($error): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <form method="POST" action="share.php">
            <?= csrf_field() ?>
            <input type="hidden" name="id_fichier" value="<?= $id_fichier ?>">
            <input type="hidden" name="action" value="share">
            <div class="form-group">
                <label for="destinataire"><?= translate('recipient_username') ?></label>
                <input type="text" id="destinataire" name="destinataire" required
                       placeholder="Ex: alice">
            </div>
            <button type="submit" class="btn"><?= translate('share') ?></button>
        </form>

        <?php if (!empty($partages)): ?>
            <h2><?= translate('already_shared_with') ?></h2>
            <table class="table">
                <thead>
                    <tr>
                        <th><?= translate('user') ?></th>
                        <th><?= translate('date') ?></th>
                        <th><?= translate('actions') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($partages as $p): ?>
                        <tr>
                            <td><?= htmlspecialchars($p['username']) ?></td>
                            <td><?= htmlspecialchars(format_datetime($p['date_partage'])) ?></td>
                            <td>
                                <form method="POST" action="share.php" class="action-form"
                                      onsubmit="return confirm('<?= htmlspecialchars(translate('confirm_revoke_access'), ENT_QUOTES, 'UTF-8') ?>')">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="id_fichier" value="<?= $id_fichier ?>">
                                    <input type="hidden" name="destinataire_id" value="<?= $p['id_utilisateur'] ?>">
                                    <input type="hidden" name="action" value="revoke">
                                    <button type="submit" class="btn btn-small btn-danger">
                                        <?= translate('revoke_access') ?>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>

        <p class="link"><a href="dashboard.php"><?= translate('back') ?></a></p>
    </div>
</body>
</html>
