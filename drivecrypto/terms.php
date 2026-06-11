<?php
require_once 'includes/auth.php';
$english = current_language() === 'en';
?>
<!DOCTYPE html>
<html lang="<?= current_language() ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $english ? 'Terms of Use' : 'Conditions d\'utilisation' ?> - Secure File Storage</title>
    <link rel="stylesheet" href="public/css/style.css">
</head>
<body class="legal-page">
    <main class="container legal-container">
        <?= language_switcher() ?>

        <?php if ($english): ?>
            <h1>Terms of Use</h1>
            <p class="legal-meta">Last updated: June 11, 2026</p>

            <div class="legal-notice">
                This service is a student prototype provided only for demonstration
                and testing purposes.
            </div>

            <section>
                <h2>Purpose</h2>
                <p>
                    Secure File Storage demonstrates file storage, encryption,
                    downloading, and sharing between users. It is not a professional
                    backup service.
                </p>
            </section>

            <section>
                <h2>Acceptable Use</h2>
                <p>You agree to:</p>
                <ul>
                    <li>provide accurate account information;</li>
                    <li>protect your login credentials;</li>
                    <li>upload only files you are authorized to use;</li>
                    <li>not disrupt, bypass, or attack the service.</li>
                </ul>
            </section>

            <section>
                <h2>Prohibited Content</h2>
                <p>
                    You must not upload illegal or malicious content, content that
                    infringes the rights of others, or sensitive personal data. Do
                    not use this prototype as the only backup of an important file.
                </p>
            </section>

            <section>
                <h2>Availability and Liability</h2>
                <p>
                    The service is provided without any guarantee of availability,
                    retention, or error-free operation. It may be changed, suspended,
                    or removed at any time. Users remain responsible for their files
                    and must keep their own backups.
                </p>
            </section>

            <section>
                <h2>Deletion</h2>
                <p>
                    You may delete your files or account from the application. An
                    account used abusively may be suspended or deleted.
                </p>
            </section>

            <section>
                <h2>Personal Data</h2>
                <p>
                    Data processing is described in the
                    <a href="privacy.php">Privacy Policy</a>.
                </p>
            </section>
        <?php else: ?>
            <h1>Conditions d'utilisation</h1>
            <p class="legal-meta">Dernière mise à jour : 11 juin 2026</p>

            <div class="legal-notice">
                Ce service est un prototype étudiant fourni uniquement à des fins de
                démonstration et de test.
            </div>

            <section>
                <h2>Objet du service</h2>
                <p>
                    Secure File Storage permet de tester le stockage, le chiffrement,
                    le téléchargement et le partage de fichiers entre utilisateurs.
                    Il ne constitue pas un service professionnel de sauvegarde.
                </p>
            </section>

            <section>
                <h2>Utilisation autorisée</h2>
                <p>Vous vous engagez à :</p>
                <ul>
                    <li>fournir des informations de compte exactes ;</li>
                    <li>protéger vos identifiants de connexion ;</li>
                    <li>n'envoyer que des fichiers que vous êtes autorisé à utiliser ;</li>
                    <li>ne pas perturber, contourner ou attaquer le service.</li>
                </ul>
            </section>

            <section>
                <h2>Contenus interdits</h2>
                <p>
                    Il est interdit d'envoyer des contenus illégaux, malveillants,
                    portant atteinte aux droits d'autrui ou contenant des données
                    personnelles sensibles. N'utilisez pas ce prototype comme unique
                    sauvegarde d'un fichier important.
                </p>
            </section>

            <section>
                <h2>Disponibilité et responsabilité</h2>
                <p>
                    Le service est fourni sans garantie de disponibilité, de
                    conservation ou d'absence d'erreur. Il peut être modifié, suspendu
                    ou supprimé à tout moment. L'utilisateur reste responsable de ses
                    fichiers et doit conserver ses propres sauvegardes.
                </p>
            </section>

            <section>
                <h2>Suppression</h2>
                <p>
                    Vous pouvez supprimer vos fichiers ou votre compte depuis
                    l'application. Un compte utilisé de manière abusive peut être
                    suspendu ou supprimé.
                </p>
            </section>

            <section>
                <h2>Données personnelles</h2>
                <p>
                    Le traitement des données est décrit dans la
                    <a href="privacy.php">politique de confidentialité</a>.
                </p>
            </section>
        <?php endif; ?>

        <p class="link"><a href="register.php"><?= translate('return_register') ?></a></p>
    </main>
</body>
</html>
