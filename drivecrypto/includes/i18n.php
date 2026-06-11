<?php

$supported_languages = ['fr', 'en'];
$requested_language = $_GET['lang'] ?? null;

if (is_string($requested_language) && in_array($requested_language, $supported_languages, true)) {
    $_SESSION['language'] = $requested_language;
}

if (!isset($_SESSION['language']) || !in_array($_SESSION['language'], $supported_languages, true)) {
    $_SESSION['language'] = 'fr';
}

$translations = [
    'fr' => [
        'app_name' => 'Stockage Sécurisé',
        'login' => 'Connexion',
        'sign_in' => 'Se connecter',
        'register' => 'Créer un compte',
        'sign_up' => 'S\'inscrire',
        'username' => 'Nom d\'utilisateur',
        'email' => 'Email',
        'password' => 'Mot de passe',
        'password_min' => 'Mot de passe (min. 8 caractères)',
        'confirm_password' => 'Confirmer le mot de passe',
        'show_password' => 'Afficher',
        'hide_password' => 'Masquer',
        'no_account' => 'Pas de compte ?',
        'already_account' => 'Déjà un compte ?',
        'all_fields_required' => 'Tous les champs sont obligatoires.',
        'invalid_credentials' => 'Identifiants incorrects.',
        'terms_required' => 'Vous devez accepter les conditions d\'utilisation.',
        'password_too_short' => 'Le mot de passe doit contenir au moins 8 caractères.',
        'password_mismatch' => 'Les mots de passe ne correspondent pas.',
        'invalid_email' => 'Adresse email invalide.',
        'account_exists' => 'Ce nom d\'utilisateur ou email est déjà utilisé.',
        'accept_terms_prefix' => 'J\'accepte les',
        'terms' => 'conditions d\'utilisation',
        'privacy' => 'Confidentialité',
        'privacy_policy' => 'politique de confidentialité',
        'and_read' => 'et j\'ai lu la',
        'student_warning' => 'Prototype étudiant : n\'envoyez aucune donnée sensible ou confidentielle.',
        'my_files' => 'Mes fichiers',
        'hello' => 'Bonjour',
        'delete_account' => 'Supprimer compte',
        'logout' => 'Déconnexion',
        'upload_file' => 'Envoyer un fichier',
        'upload' => 'Envoyer',
        'no_files' => 'Aucun fichier pour le moment.',
        'name' => 'Nom',
        'size' => 'Taille',
        'type' => 'Type',
        'date' => 'Date',
        'actions' => 'Actions',
        'download' => 'Télécharger',
        'share' => 'Partager',
        'delete' => 'Supprimer',
        'confirm_delete_file' => 'Supprimer ce fichier ?',
        'shared_with_me' => 'Fichiers partagés avec moi',
        'no_shared_files' => 'Aucun fichier partagé avec vous.',
        'shared_by' => 'Partagé par',
        'shared_on' => 'Partagé le',
        'share_file' => 'Partager un fichier',
        'file' => 'Fichier',
        'recipient_username' => 'Nom d\'utilisateur du destinataire',
        'already_shared_with' => 'Déjà partagé avec',
        'user' => 'Utilisateur',
        'revoke_access' => 'Retirer l\'accès',
        'confirm_revoke_access' => 'Retirer l\'accès à ce fichier pour cet utilisateur ?',
        'share_revoked' => 'Accès retiré pour %s.',
        'share_not_found' => 'Partage introuvable.',
        'back' => 'Retour',
        'delete_my_account' => 'Supprimer mon compte',
        'delete_warning' => 'Cette action est irréversible. Tous vos fichiers seront supprimés.',
        'confirm_your_password' => 'Confirmez votre mot de passe',
        'delete_permanently' => 'Supprimer définitivement',
        'wrong_password' => 'Mot de passe incorrect.',
        'method_not_allowed' => 'Méthode non autorisée.',
        'invalid_csrf' => 'Requête refusée : jeton de sécurité invalide.',
        'invalid_file' => 'Fichier invalide.',
        'file_not_found' => 'Accès refusé ou fichier introuvable.',
        'owner_file_not_found' => 'Fichier introuvable ou vous n\'en êtes pas le propriétaire.',
        'recipient_required' => 'Veuillez entrer un nom d\'utilisateur.',
        'cannot_share_self' => 'Vous ne pouvez pas partager un fichier avec vous-même.',
        'user_not_found' => 'Utilisateur introuvable.',
        'already_shared' => 'Ce fichier est déjà partagé avec cet utilisateur.',
        'shared_success' => 'Fichier partagé avec %s !',
        'file_deleted' => 'Fichier supprimé.',
        'upload_error' => 'Erreur lors de l\'upload du fichier.',
        'file_too_large' => 'Le fichier ne doit pas dépasser 10 Mo.',
        'upload_success' => 'Fichier uploadé et chiffré avec succès !',
        'upload_failed' => 'L\'upload a échoué. Aucun fichier n\'a été conservé.',
        'no_file_received' => 'Aucun fichier reçu.',
        'integrity_error' => 'Erreur d\'intégrité : le fichier a été modifié !',
        'decryption_error' => 'Erreur de déchiffrement : %s',
        'generic_error' => 'Erreur : %s',
        'return_register' => 'Retour à l\'inscription',
    ],
    'en' => [
        'app_name' => 'Secure File Storage',
        'login' => 'Sign in',
        'sign_in' => 'Sign in',
        'register' => 'Create an account',
        'sign_up' => 'Sign up',
        'username' => 'Username',
        'email' => 'Email',
        'password' => 'Password',
        'password_min' => 'Password (8 characters minimum)',
        'confirm_password' => 'Confirm password',
        'show_password' => 'Show',
        'hide_password' => 'Hide',
        'no_account' => 'No account yet?',
        'already_account' => 'Already have an account?',
        'all_fields_required' => 'All fields are required.',
        'invalid_credentials' => 'Invalid username or password.',
        'terms_required' => 'You must accept the Terms of Use.',
        'password_too_short' => 'The password must contain at least 8 characters.',
        'password_mismatch' => 'The passwords do not match.',
        'invalid_email' => 'Invalid email address.',
        'account_exists' => 'This username or email address is already in use.',
        'accept_terms_prefix' => 'I agree to the',
        'terms' => 'Terms of Use',
        'privacy' => 'Privacy',
        'privacy_policy' => 'Privacy Policy',
        'and_read' => 'and I have read the',
        'student_warning' => 'Student prototype: do not upload sensitive or confidential data.',
        'my_files' => 'My files',
        'hello' => 'Hello',
        'delete_account' => 'Delete account',
        'logout' => 'Sign out',
        'upload_file' => 'Upload a file',
        'upload' => 'Upload',
        'no_files' => 'No files yet.',
        'name' => 'Name',
        'size' => 'Size',
        'type' => 'Type',
        'date' => 'Date',
        'actions' => 'Actions',
        'download' => 'Download',
        'share' => 'Share',
        'delete' => 'Delete',
        'confirm_delete_file' => 'Delete this file?',
        'shared_with_me' => 'Files shared with me',
        'no_shared_files' => 'No files have been shared with you.',
        'shared_by' => 'Shared by',
        'shared_on' => 'Shared on',
        'share_file' => 'Share a file',
        'file' => 'File',
        'recipient_username' => 'Recipient username',
        'already_shared_with' => 'Already shared with',
        'user' => 'User',
        'revoke_access' => 'Remove access',
        'confirm_revoke_access' => 'Remove this user\'s access to the file?',
        'share_revoked' => 'Access removed for %s.',
        'share_not_found' => 'Share not found.',
        'back' => 'Back',
        'delete_my_account' => 'Delete my account',
        'delete_warning' => 'This action cannot be undone. All your files will be deleted.',
        'confirm_your_password' => 'Confirm your password',
        'delete_permanently' => 'Delete permanently',
        'wrong_password' => 'Incorrect password.',
        'method_not_allowed' => 'Method not allowed.',
        'invalid_csrf' => 'Request denied: invalid security token.',
        'invalid_file' => 'Invalid file.',
        'file_not_found' => 'Access denied or file not found.',
        'owner_file_not_found' => 'File not found or you are not its owner.',
        'recipient_required' => 'Please enter a username.',
        'cannot_share_self' => 'You cannot share a file with yourself.',
        'user_not_found' => 'User not found.',
        'already_shared' => 'This file is already shared with this user.',
        'shared_success' => 'File shared with %s!',
        'file_deleted' => 'File deleted.',
        'upload_error' => 'An error occurred while uploading the file.',
        'file_too_large' => 'The file must not exceed 10 MB.',
        'upload_success' => 'File uploaded and encrypted successfully!',
        'upload_failed' => 'The upload failed. No file was retained.',
        'no_file_received' => 'No file received.',
        'integrity_error' => 'Integrity error: the file has been modified!',
        'decryption_error' => 'Decryption error: %s',
        'generic_error' => 'Error: %s',
        'return_register' => 'Back to registration',
    ],
];

function current_language(): string
{
    return $_SESSION['language'] ?? 'fr';
}

function translate(string $key, mixed ...$values): string
{
    global $translations;

    $text = $translations[current_language()][$key] ?? $translations['fr'][$key] ?? $key;

    return $values === [] ? $text : sprintf($text, ...$values);
}

function language_switcher(): string
{
    $params = $_GET;
    $page = basename($_SERVER['PHP_SELF']);
    $links = [];

    foreach (['fr' => 'FR', 'en' => 'EN'] as $language => $label) {
        $params['lang'] = $language;
        $url = htmlspecialchars($page . '?' . http_build_query($params), ENT_QUOTES, 'UTF-8');
        $active = current_language() === $language ? ' is-active' : '';
        $links[] = '<a class="language-link' . $active . '" href="' . $url . '">' . $label . '</a>';
    }

    return '<nav class="language-switcher" aria-label="Language">' . implode('', $links) . '</nav>';
}

function format_datetime(string $date): string
{
    $date_time = new DateTimeImmutable($date);

    return current_language() === 'en'
        ? $date_time->format('M j, Y \a\t H:i')
        : $date_time->format('d/m/Y à H:i');
}
