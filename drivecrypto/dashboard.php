<?php
require_once 'includes/auth.php';
require_once 'config/database.php';
require_once 'includes/crypto.php';
require_auth();

// Messages flash
$flash_success = $_SESSION['flash_success'] ?? '';
$flash_error = $_SESSION['flash_error'] ?? '';
unset($_SESSION['flash_success'], $_SESSION['flash_error']);

// Récupérer la clé privée RSA de l'utilisateur
$stmt = $pdo->prepare('SELECT cle_privee_chiffree FROM utilisateurs WHERE id_utilisateur = :id');
$stmt->execute(['id' => $_SESSION['user_id']]);
$cle_privee_chiffree = $stmt->fetchColumn();
$cle_privee = dechiffrer_cle_privee($cle_privee_chiffree, $_SESSION['mdp_clair']);

// Mes fichiers (propriétaire) avec la clé AES pour déchiffrer le nom
$stmt = $pdo->prepare('
    SELECT f.id_fichier, f.nom_original_chiffre, f.taille, f.type_mime_chiffre, f.date_upload, p.cle_aes_chiffree
    FROM fichiers f
    JOIN partager p ON p.id_fichier = f.id_fichier AND p.id_utilisateur = :uid
    WHERE f.id_utilisateur = :uid
    ORDER BY f.date_upload DESC
');
$stmt->execute(['uid' => $_SESSION['user_id']]);
$mes_fichiers_bruts = $stmt->fetchAll();

// Déchiffrer les noms de fichiers
$mes_fichiers = [];
foreach ($mes_fichiers_bruts as $f) {
    $cle_aes = dechiffrer_rsa($f['cle_aes_chiffree'], $cle_privee);
    $f['nom_dechiffre'] = dechiffrer_texte_aes($f['nom_original_chiffre'], $cle_aes);
    $f['mime_dechiffre'] = dechiffrer_texte_aes($f['type_mime_chiffre'], $cle_aes);
    $mes_fichiers[] = $f;
}

// Fichiers partagés avec moi
$stmt = $pdo->prepare('
    SELECT f.id_fichier, f.nom_original_chiffre, f.taille, f.type_mime_chiffre, f.date_upload,
           u.username AS proprietaire, p.cle_aes_chiffree, p.date_partage
    FROM partager p
    JOIN fichiers f ON f.id_fichier = p.id_fichier
    JOIN utilisateurs u ON u.id_utilisateur = f.id_utilisateur
    WHERE p.id_utilisateur = :uid AND f.id_utilisateur != :uid
    ORDER BY p.date_partage DESC
');
$stmt->execute(['uid' => $_SESSION['user_id']]);
$fichiers_partages_bruts = $stmt->fetchAll();

$fichiers_partages = [];
foreach ($fichiers_partages_bruts as $f) {
    $cle_aes = dechiffrer_rsa($f['cle_aes_chiffree'], $cle_privee);
    $f['nom_dechiffre'] = dechiffrer_texte_aes($f['nom_original_chiffre'], $cle_aes);
    $f['mime_dechiffre'] = dechiffrer_texte_aes($f['type_mime_chiffre'], $cle_aes);
    $fichiers_partages[] = $f;
}

// Formater la taille
function format_taille(int $octets): string {
    if ($octets < 1024) return $octets . (current_language() === 'en' ? ' B' : ' o');
    if ($octets < 1024 * 1024) return round($octets / 1024, 1) . (current_language() === 'en' ? ' KB' : ' Ko');
    return round($octets / (1024 * 1024), 1) . (current_language() === 'en' ? ' MB' : ' Mo');
}
?>
<!DOCTYPE html>
<html lang="<?= current_language() ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= translate('my_files') ?> - <?= translate('app_name') ?></title>
    <link rel="stylesheet" href="public/css/style.css">
</head>
<body>
    <div class="container container-wide">
        <?= language_switcher() ?>
        <div class="header">
            <h1><?= translate('my_files') ?></h1>
            <div class="header-right">
                <span><?= translate('hello') ?>, <?= htmlspecialchars($_SESSION['username']) ?></span>
                <a href="delete_account.php" class="btn btn-small btn-danger"><?= translate('delete_account') ?></a>
                <form method="POST" action="logout.php" class="action-form">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-small"><?= translate('logout') ?></button>
                </form>
            </div>
        </div>

        <?php if ($flash_success): ?>
            <div class="alert alert-success"><?= htmlspecialchars($flash_success) ?></div>
        <?php endif; ?>
        <?php if ($flash_error): ?>
            <div class="alert alert-error"><?= htmlspecialchars($flash_error) ?></div>
        <?php endif; ?>

        <!-- Upload -->
        <div class="section">
            <h2><?= translate('upload_file') ?></h2>
            <form method="POST" action="upload.php" enctype="multipart/form-data">
                <?= csrf_field() ?>
                <div class="form-group form-inline">
                    <input type="file" name="fichier" required>
                    <button type="submit" class="btn"><?= translate('upload') ?></button>
                </div>
            </form>
        </div>

        <!-- Mes fichiers -->
        <div class="section">
            <h2><?= translate('my_files') ?> (<?= count($mes_fichiers) ?>)</h2>
            <?php if (empty($mes_fichiers)): ?>
                <p><?= translate('no_files') ?></p>
            <?php else: ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th><?= translate('name') ?></th>
                            <th><?= translate('size') ?></th>
                            <th><?= translate('type') ?></th>
                            <th><?= translate('date') ?></th>
                            <th><?= translate('actions') ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($mes_fichiers as $f): ?>
                            <tr>
                                <td><?= htmlspecialchars($f['nom_dechiffre']) ?></td>
                                <td><?= format_taille($f['taille']) ?></td>
                                <td><?= htmlspecialchars($f['mime_dechiffre'] ?? '-') ?></td>
                                <td><?= htmlspecialchars(format_datetime($f['date_upload'])) ?></td>
                                <td class="actions">
                                    <a href="download.php?id=<?= $f['id_fichier'] ?>" class="btn btn-small"><?= translate('download') ?></a>
                                    <a href="share.php?id=<?= $f['id_fichier'] ?>" class="btn btn-small"><?= translate('share') ?></a>
                                    <form method="POST" action="delete_file.php" class="action-form"
                                          onsubmit="return confirm('<?= htmlspecialchars(translate('confirm_delete_file'), ENT_QUOTES, 'UTF-8') ?>')">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="id_fichier" value="<?= $f['id_fichier'] ?>">
                                        <button type="submit" class="btn btn-small btn-danger"><?= translate('delete') ?></button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <!-- Fichiers partagés avec moi -->
        <div class="section">
            <h2><?= translate('shared_with_me') ?> (<?= count($fichiers_partages) ?>)</h2>
            <?php if (empty($fichiers_partages)): ?>
                <p><?= translate('no_shared_files') ?></p>
            <?php else: ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th><?= translate('name') ?></th>
                            <th><?= translate('size') ?></th>
                            <th><?= translate('shared_by') ?></th>
                            <th><?= translate('shared_on') ?></th>
                            <th><?= translate('actions') ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($fichiers_partages as $f): ?>
                            <tr>
                                <td><?= htmlspecialchars($f['nom_dechiffre']) ?></td>
                                <td><?= format_taille($f['taille']) ?></td>
                                <td><span class="user-badge"><?= htmlspecialchars($f['proprietaire']) ?></span></td>
                                <td><?= htmlspecialchars(format_datetime($f['date_partage'])) ?></td>
                                <td>
                                    <a href="download.php?id=<?= $f['id_fichier'] ?>" class="btn btn-small"><?= translate('download') ?></a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <p class="legal-links">
            <a href="privacy.php"><?= translate('privacy') ?></a>
            <span aria-hidden="true">·</span>
            <a href="terms.php"><?= translate('terms') ?></a>
        </p>
    </div>
</body>
</html>
