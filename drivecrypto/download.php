<?php
require_once 'includes/auth.php';
require_once 'config/database.php';
require_once 'includes/crypto.php';
require_auth();

$id_fichier = intval($_GET['id'] ?? 0);

if ($id_fichier <= 0) {
    die(translate('invalid_file'));
}

// 1. Vérifier que l'utilisateur a accès au fichier (propriétaire ou partage)
$stmt = $pdo->prepare('
    SELECT f.nom_original_chiffre, f.nom_stockage, f.type_mime_chiffre, f.hash_sha256, p.cle_aes_chiffree
    FROM fichiers f
    JOIN partager p ON p.id_fichier = f.id_fichier
    WHERE f.id_fichier = :fid AND p.id_utilisateur = :uid
');
$stmt->execute(['fid' => $id_fichier, 'uid' => $_SESSION['user_id']]);
$fichier = $stmt->fetch();

if (!$fichier) {
    die(translate('file_not_found'));
}

try {
    // 2. Récupérer et déchiffrer la clé privée RSA de l'utilisateur
    $stmt = $pdo->prepare('SELECT cle_privee_chiffree FROM utilisateurs WHERE id_utilisateur = :id');
    $stmt->execute(['id' => $_SESSION['user_id']]);
    $cle_privee_chiffree = $stmt->fetchColumn();

    $cle_privee = dechiffrer_cle_privee($cle_privee_chiffree, $_SESSION['mdp_clair']);

    // 3. Déchiffrer la clé AES avec la clé privée RSA
    $cle_aes = dechiffrer_rsa($fichier['cle_aes_chiffree'], $cle_privee);

    // 4. Déchiffrer le nom original et le type MIME
    $nom_original = dechiffrer_texte_aes($fichier['nom_original_chiffre'], $cle_aes);
    $type_mime = dechiffrer_texte_aes($fichier['type_mime_chiffre'], $cle_aes);
    if (!preg_match('#^[a-z0-9][a-z0-9.+-]*/[a-z0-9][a-z0-9.+-]*$#i', $type_mime)) {
        $type_mime = 'application/octet-stream';
    }

    // 5. Lire et déchiffrer le fichier
    $chemin = __DIR__ . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . $fichier['nom_stockage'];
    $contenu_chiffre = file_get_contents($chemin);
    $contenu = dechiffrer_fichier_aes($contenu_chiffre, $cle_aes);

    // 6. Vérifier l'intégrité (SHA-256)
    if (hash('sha256', $contenu) !== $fichier['hash_sha256']) {
        die(translate('integrity_error'));
    }

    $nom_telechargement = preg_replace('/[\x00-\x1F\x7F"\\\\\/]+/u', '_', $nom_original);
    if (!is_string($nom_telechargement)) {
        $nom_telechargement = 'download';
    }
    $nom_telechargement = trim($nom_telechargement, " .");
    if ($nom_telechargement === '') {
        $nom_telechargement = 'download';
    }

    $nom_ascii = preg_replace('/[^\x20-\x7E]/', '_', $nom_telechargement);
    $nom_ascii = str_replace(['"', '\\'], '_', $nom_ascii);
    $nom_utf8 = rawurlencode($nom_telechargement);

    // 7. Envoyer le fichier au navigateur
    header('Content-Type: ' . $type_mime);
    header(
        'Content-Disposition: attachment; filename="' . $nom_ascii
        . '"; filename*=UTF-8\'\'' . $nom_utf8
    );
    header('Content-Length: ' . strlen($contenu));
    echo $contenu;
    exit;

} catch (Exception $e) {
    die(translate('decryption_error', $e->getMessage()));
}
