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
           u.username AS proprietaire, p.cle_aes_chiffree
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
    if ($octets < 1024) return $octets . ' o';
    if ($octets < 1024 * 1024) return round($octets / 1024, 1) . ' Ko';
    return round($octets / (1024 * 1024), 1) . ' Mo';
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes fichiers - Stockage Sécurisé</title>
    <link rel="stylesheet" href="public/css/style.css">
</head>
<body>
    <div class="container container-wide">
        <div class="header">
            <h1>Mes fichiers</h1>
            <div class="header-right">
                <span>Bonjour, <?= htmlspecialchars($_SESSION['username']) ?></span>
                <a href="delete_account.php" class="btn btn-small btn-danger">Supprimer compte</a>
                <form method="POST" action="logout.php" class="action-form">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-small">Déconnexion</button>
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
            <h2>Envoyer un fichier</h2>
            <form method="POST" action="upload.php" enctype="multipart/form-data">
                <?= csrf_field() ?>
                <div class="form-group form-inline">
                    <input type="file" name="fichier" required>
                    <button type="submit" class="btn">Envoyer</button>
                </div>
            </form>
        </div>

        <!-- Mes fichiers -->
        <div class="section">
            <h2>Mes fichiers (<?= count($mes_fichiers) ?>)</h2>
            <?php if (empty($mes_fichiers)): ?>
                <p>Aucun fichier pour le moment.</p>
            <?php else: ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Nom</th>
                            <th>Taille</th>
                            <th>Type</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($mes_fichiers as $f): ?>
                            <tr>
                                <td><?= htmlspecialchars($f['nom_dechiffre']) ?></td>
                                <td><?= format_taille($f['taille']) ?></td>
                                <td><?= htmlspecialchars($f['mime_dechiffre'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($f['date_upload']) ?></td>
                                <td class="actions">
                                    <a href="download.php?id=<?= $f['id_fichier'] ?>" class="btn btn-small">Télécharger</a>
                                    <a href="share.php?id=<?= $f['id_fichier'] ?>" class="btn btn-small">Partager</a>
                                    <form method="POST" action="delete_file.php" class="action-form"
                                          onsubmit="return confirm('Supprimer ce fichier ?')">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="id_fichier" value="<?= $f['id_fichier'] ?>">
                                        <button type="submit" class="btn btn-small btn-danger">Supprimer</button>
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
            <h2>Fichiers partagés avec moi (<?= count($fichiers_partages) ?>)</h2>
            <?php if (empty($fichiers_partages)): ?>
                <p>Aucun fichier partagé avec vous.</p>
            <?php else: ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Nom</th>
                            <th>Taille</th>
                            <th>Propriétaire</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($fichiers_partages as $f): ?>
                            <tr>
                                <td><?= htmlspecialchars($f['nom_dechiffre']) ?></td>
                                <td><?= format_taille($f['taille']) ?></td>
                                <td><?= htmlspecialchars($f['proprietaire']) ?></td>
                                <td><?= htmlspecialchars($f['date_upload']) ?></td>
                                <td>
                                    <a href="download.php?id=<?= $f['id_fichier'] ?>" class="btn btn-small">Télécharger</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
