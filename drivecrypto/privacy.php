<?php
require_once 'includes/auth.php';
$english = current_language() === 'en';
?>
<!DOCTYPE html>
<html lang="<?= current_language() ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $english ? 'Privacy Policy' : 'Politique de confidentialité' ?> - Secure File Storage</title>
    <link rel="stylesheet" href="public/css/style.css">
</head>
<body class="legal-page">
    <main class="container legal-container">
        <?= language_switcher() ?>

        <?php if ($english): ?>
            <h1>Privacy Policy</h1>
            <p class="legal-meta">Last updated: June 11, 2026</p>

            <div class="legal-notice">
                Secure File Storage is an educational demonstration. Do not upload
                sensitive, confidential, or irreplaceable data.
            </div>

            <section>
                <h2>Data Controller</h2>
                <p>
                    The service is operated by Fatih Türk as a student project.
                    For questions about your data, use the operator's
                    <a href="https://github.com/fatiht7">GitHub profile</a>.
                </p>
            </section>

            <section>
                <h2>Data We Process</h2>
                <ul>
                    <li>username and email address;</li>
                    <li>password stored as a hash for authentication and temporarily held in the session while signed in to unlock the private key;</li>
                    <li>cryptographic keys required to operate the service;</li>
                    <li>files, file names, and MIME types stored in encrypted form;</li>
                    <li>sharing information and associated dates;</li>
                    <li>a technical session cookie required for authentication.</li>
                </ul>
            </section>

            <section>
                <h2>Purpose and Legal Basis</h2>
                <p>
                    The data is used only to create your account, authenticate you,
                    and encrypt, store, share, and delete your files. Processing is
                    necessary to provide the service you request.
                </p>
            </section>

            <section>
                <h2>Recipients and Hosting</h2>
                <p>
                    The service is hosted by alwaysdata. A shared file becomes
                    accessible to the recipient selected by its owner. Data is not
                    sold or used for advertising.
                </p>
            </section>

            <section>
                <h2>Retention</h2>
                <p>
                    Accounts and files are retained until the user deletes them.
                    Deleting an account removes the files owned by that user.
                    Technical logs may be retained temporarily by the hosting provider.
                </p>
            </section>

            <section>
                <h2>Your Rights</h2>
                <p>
                    You may request access, correction, or deletion of your data.
                    You may also delete your account and files directly in the
                    application. You may lodge a complaint with the CNIL if you
                    believe your rights have not been respected.
                </p>
            </section>

            <section>
                <h2>Cookies</h2>
                <p>
                    The application uses only a session cookie strictly required
                    for authentication. No advertising cookies or analytics tools
                    are used.
                </p>
            </section>
        <?php else: ?>
            <h1>Politique de confidentialité</h1>
            <p class="legal-meta">Dernière mise à jour : 11 juin 2026</p>

            <div class="legal-notice">
                Secure File Storage est une démonstration pédagogique. N'y déposez
                aucune donnée sensible, confidentielle ou indispensable.
            </div>

            <section>
                <h2>Responsable du traitement</h2>
                <p>
                    Le service est exploité par Fatih Türk dans le cadre d'un projet
                    étudiant. Pour toute demande concernant vos données, utilisez le
                    <a href="https://github.com/fatiht7">profil GitHub du responsable</a>.
                </p>
            </section>

            <section>
                <h2>Données traitées</h2>
                <ul>
                    <li>nom d'utilisateur et adresse e-mail ;</li>
                    <li>mot de passe conservé sous forme de hash pour l'authentification et temporairement présent dans la session pendant la connexion afin de déverrouiller la clé privée ;</li>
                    <li>clés cryptographiques nécessaires au fonctionnement du service ;</li>
                    <li>fichiers, noms de fichiers et types MIME stockés sous forme chiffrée ;</li>
                    <li>informations de partage et dates associées ;</li>
                    <li>cookie technique de session nécessaire à la connexion.</li>
                </ul>
            </section>

            <section>
                <h2>Finalités et base légale</h2>
                <p>
                    Ces données sont utilisées uniquement pour créer votre compte,
                    vous authentifier, chiffrer, stocker, partager et supprimer vos
                    fichiers. Leur traitement est nécessaire à la fourniture du
                    service demandé.
                </p>
            </section>

            <section>
                <h2>Destinataires et hébergement</h2>
                <p>
                    Le service est hébergé par alwaysdata. Un fichier partagé devient
                    accessible au destinataire choisi par son propriétaire. Les
                    données ne sont ni vendues ni utilisées à des fins publicitaires.
                </p>
            </section>

            <section>
                <h2>Durée de conservation</h2>
                <p>
                    Le compte et les fichiers sont conservés jusqu'à leur suppression
                    par l'utilisateur. La suppression du compte entraîne la suppression
                    des fichiers appartenant à cet utilisateur. Des journaux techniques
                    peuvent être conservés temporairement par l'hébergeur.
                </p>
            </section>

            <section>
                <h2>Vos droits</h2>
                <p>
                    Vous pouvez demander l'accès, la rectification ou la suppression
                    de vos données. Le compte et ses fichiers peuvent également être
                    supprimés directement depuis l'application. Vous pouvez adresser
                    une réclamation à la CNIL si vous estimez que vos droits ne sont
                    pas respectés.
                </p>
            </section>

            <section>
                <h2>Cookies</h2>
                <p>
                    L'application utilise uniquement un cookie de session strictement
                    nécessaire à l'authentification. Aucun cookie publicitaire ou outil
                    de suivi d'audience n'est utilisé.
                </p>
            </section>
        <?php endif; ?>

        <p class="link"><a href="register.php"><?= translate('back') ?></a></p>
    </main>
</body>
</html>
