<?php
require_once 'includes/auth.php';
require_once 'config/database.php';
require_once 'includes/crypto.php';
require_auth();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Méthode non autorisée.');
}

require_valid_csrf_token();

if (isset($_FILES['fichier'])) {
    $fichier = $_FILES['fichier'];

    if ($fichier['error'] !== UPLOAD_ERR_OK) {
        $error = 'Erreur lors de l\'upload du fichier.';
    } elseif ($fichier['size'] > 10 * 1024 * 1024) { // 10 Mo max
        $error = 'Le fichier ne doit pas dépasser 10 Mo.';
    } else {
        $chemin = null;

        try {
            $dossier_uploads = __DIR__ . DIRECTORY_SEPARATOR . 'uploads';
            if (!is_dir($dossier_uploads) && !mkdir($dossier_uploads, 0700, true)) {
                throw new RuntimeException('Impossible de créer le dossier de stockage.');
            }

            // 1. Lire le contenu du fichier
            $contenu = file_get_contents($fichier['tmp_name']);
            if ($contenu === false) {
                throw new RuntimeException('Impossible de lire le fichier envoyé.');
            }

            // 2. Générer une clé AES aléatoire pour ce fichier
            $cle_aes = generer_cle_aes();

            // 3. Chiffrer le fichier avec AES-256-GCM
            $resultat = chiffrer_fichier_aes($contenu, $cle_aes);

            // 4. Chiffrer le nom original et le type MIME (jamais en clair en base)
            $nom_chiffre = chiffrer_texte_aes($fichier['name'], $cle_aes);
            $mime_chiffre = chiffrer_texte_aes($fichier['type'], $cle_aes);

            // 5. Nom de stockage aléatoire (pour ne pas révéler le nom original)
            $nom_stockage = bin2hex(random_bytes(32));

            // 6. Sauvegarder le fichier chiffré sur le disque
            $chemin = $dossier_uploads . DIRECTORY_SEPARATOR . $nom_stockage;
            if (file_put_contents($chemin, $resultat['contenu_chiffre'], LOCK_EX) === false) {
                throw new RuntimeException('Impossible d\'enregistrer le fichier chiffré.');
            }

            $pdo->beginTransaction();

            // 7. Insérer les métadonnées en base (nom chiffré)
            $stmt = $pdo->prepare('
                INSERT INTO fichiers (id_utilisateur, nom_original_chiffre, nom_stockage, type_mime_chiffre, taille, hash_sha256)
                VALUES (:uid, :nom, :stockage, :mime, :taille, :hash)
                RETURNING id_fichier
            ');
            $stmt->execute([
                'uid' => $_SESSION['user_id'],
                'nom' => $nom_chiffre,
                'stockage' => $nom_stockage,
                'mime' => $mime_chiffre,
                'taille' => $fichier['size'],
                'hash' => $resultat['hash_original'],
            ]);
            $id_fichier = $stmt->fetchColumn();

            // 7. Chiffrer la clé AES avec la clé publique RSA du propriétaire
            $stmt = $pdo->prepare('SELECT cle_publique FROM utilisateurs WHERE id_utilisateur = :id');
            $stmt->execute(['id' => $_SESSION['user_id']]);
            $cle_publique = $stmt->fetchColumn();

            $cle_aes_chiffree = chiffrer_rsa($cle_aes, $cle_publique);

            // 8. Insérer dans la table partager (accès du propriétaire)
            $stmt = $pdo->prepare('
                INSERT INTO partager (id_utilisateur, id_fichier, cle_aes_chiffree)
                VALUES (:uid, :fid, :cle)
            ');
            $stmt->execute([
                'uid' => $_SESSION['user_id'],
                'fid' => $id_fichier,
                'cle' => $cle_aes_chiffree,
            ]);

            $pdo->commit();
            $success = 'Fichier uploadé et chiffré avec succès !';

        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            if ($chemin !== null && is_file($chemin) && !unlink($chemin)) {
                error_log('Impossible de supprimer le fichier après l\'échec de l\'upload : ' . $chemin);
            }

            error_log($e->getMessage());
            $error = 'L\'upload a échoué. Aucun fichier n\'a été conservé.';
        }
    }
} else {
    $error = 'Aucun fichier reçu.';
}

// Rediriger vers le dashboard avec le message
if ($success) {
    $_SESSION['flash_success'] = $success;
} elseif ($error) {
    $_SESSION['flash_error'] = $error;
}
header('Location: dashboard.php');
exit;
