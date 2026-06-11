<?php
require_once 'includes/auth.php';
require_once 'config/database.php';
require_auth();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Méthode non autorisée.');
}

require_valid_csrf_token();

$id_fichier = intval($_POST['id_fichier'] ?? 0);

// Vérifier que le fichier appartient au user connecté
$stmt = $pdo->prepare('SELECT nom_stockage FROM fichiers WHERE id_fichier = :fid AND id_utilisateur = :uid');
$stmt->execute(['fid' => $id_fichier, 'uid' => $_SESSION['user_id']]);
$fichier = $stmt->fetch();

if (!$fichier) {
    $_SESSION['flash_error'] = 'Fichier introuvable ou accès refusé.';
    header('Location: dashboard.php');
    exit;
}

// Supprimer le fichier physique
$chemin = __DIR__ . '/uploads/' . $fichier['nom_stockage'];
if (file_exists($chemin)) {
    unlink($chemin);
}

// Supprimer en base (CASCADE supprime aussi les partages)
$stmt = $pdo->prepare('DELETE FROM fichiers WHERE id_fichier = :fid');
$stmt->execute(['fid' => $id_fichier]);

$_SESSION['flash_success'] = 'Fichier supprimé.';
header('Location: dashboard.php');
exit;
