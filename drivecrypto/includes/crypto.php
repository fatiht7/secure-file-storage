<?php

function crypto_error(string $french, string $english): string
{
    return function_exists('current_language') && current_language() === 'en'
        ? $english
        : $french;
}

/**
 * Fonctions de cryptographie pour le projet
 * - Hachage des mots de passe : Argon2id
 * - Chiffrement des fichiers : AES-256-GCM
 * - Enveloppe numérique : RSA-2048 + PBKDF2
 */

function hacher_mot_de_passe($mdp)
{
    return password_hash($mdp, PASSWORD_ARGON2ID);
}

function verifier_mot_de_passe($mdp, $hash)
{
    return password_verify($mdp, $hash);
}

// GÉNÉRATION CLÉS RSA (2048 bits)

function generer_paire_cles_rsa()
{
    $config = [
        'digest_alg' => 'sha256',
        'private_key_bits' => 2048,
        'private_key_type' => OPENSSL_KEYTYPE_RSA,
    ];

    $opensslConfig = getenv('OPENSSL_CONF');
    if ($opensslConfig !== false && $opensslConfig !== '') {
        $config['config'] = $opensslConfig;
    }

    $paire = openssl_pkey_new($config);

    if ($paire === false) {
        throw new Exception(
            crypto_error('Erreur lors de la génération RSA : ', 'RSA key generation error: ')
            . openssl_error_string()
        );
    }

    // On extrait la clé privée en clair (temporairement)
    openssl_pkey_export($paire, $cle_privee, null, $config);

    // On récupère la clé publique
    $details = openssl_pkey_get_details($paire);
    $cle_publique = $details['key'];

    return [
        'publique' => $cle_publique,
        'privee' => $cle_privee,
    ];
}

// PROTECTION DE LA CLÉ PRIVÉE RSA
// On utilise le mot de passe pour la chiffrer avant la BDD

function chiffrer_cle_privee($cle_privee_pem, $mdp)
{
    // On crée un sel aléatoire et on dérive la clé AES avec PBKDF2
    $sel = random_bytes(16);
    $cle_derivee = hash_pbkdf2('sha256', $mdp, $sel, 100000, 32, true);

    $iv = random_bytes(16);
    $tag = '';

    $texte_chiffre = openssl_encrypt(
        $cle_privee_pem,
        'aes-256-gcm',
        $cle_derivee,
        OPENSSL_RAW_DATA,
        $iv,
        $tag,
        '',
        16
    );

    if ($texte_chiffre === false) {
        throw new Exception(crypto_error(
            'Impossible de chiffrer la clé privée.',
            'Unable to encrypt the private key.'
        ));
    }

    // On colle tout ensemble (Sel + IV + Tag + Message chiffré) et on encode pour la base de données
    return base64_encode($sel . $iv . $tag . $texte_chiffre);
}

function dechiffrer_cle_privee($donnee_base64, $mdp)
{
    $donnees = base64_decode($donnee_base64);

    // On découpe la chaîne pour récupérer chaque morceau
    $sel = substr($donnees, 0, 16);
    $iv = substr($donnees, 16, 16);
    $tag = substr($donnees, 32, 16);
    $texte_chiffre = substr($donnees, 48);

    // On recrée la clé AES à partir du mot de passe saisi
    $cle_derivee = hash_pbkdf2('sha256', $mdp, $sel, 100000, 32, true);

    $cle_privee = openssl_decrypt(
        $texte_chiffre,
        'aes-256-gcm',
        $cle_derivee,
        OPENSSL_RAW_DATA,
        $iv,
        $tag
    );

    if ($cle_privee === false) {
        throw new Exception(crypto_error(
            'Mot de passe incorrect ou clé corrompue.',
            'Incorrect password or corrupted key.'
        ));
    }

    return $cle_privee;
}

// CHIFFREMENT RSA (Pour l'enveloppe numérique / Partages)

function chiffrer_rsa($donnees, $cle_publique_pem)
{
    // On utilise le padding OAEP pour plus de sécurité
    $success = openssl_public_encrypt(
        $donnees,
        $chiffre,
        $cle_publique_pem,
        OPENSSL_PKCS1_OAEP_PADDING
    );

    if (!$success) {
        throw new Exception(crypto_error('Erreur lors du chiffrement RSA.', 'RSA encryption error.'));
    }

    return base64_encode($chiffre);
}

function dechiffrer_rsa($donnee_base64, $cle_privee_pem)
{
    $chiffre = base64_decode($donnee_base64);

    $success = openssl_private_decrypt(
        $chiffre,
        $dechiffre,
        $cle_privee_pem,
        OPENSSL_PKCS1_OAEP_PADDING
    );

    if (!$success) {
        throw new Exception(crypto_error('Erreur lors du déchiffrement RSA.', 'RSA decryption error.'));
    }

    return $dechiffre;
}

// CHIFFREMENT AES-256-GCM

function chiffrer_texte_aes($texte, $cle_aes)
{
    $iv = random_bytes(16);
    $tag = '';

    $texte_chiffre = openssl_encrypt(
        $texte,
        'aes-256-gcm',
        $cle_aes,
        OPENSSL_RAW_DATA,
        $iv,
        $tag,
        '',
        16
    );

    if ($texte_chiffre === false) {
        throw new Exception(crypto_error(
            'Erreur de chiffrement AES sur le texte.',
            'AES text encryption error.'
        ));
    }

    return base64_encode($iv . $tag . $texte_chiffre);
}

function dechiffrer_texte_aes($donnee_base64, $cle_aes)
{
    $donnees = base64_decode($donnee_base64);

    $iv = substr($donnees, 0, 16);
    $tag = substr($donnees, 16, 16);
    $texte_chiffre = substr($donnees, 32);

    $texte = openssl_decrypt(
        $texte_chiffre,
        'aes-256-gcm',
        $cle_aes,
        OPENSSL_RAW_DATA,
        $iv,
        $tag
    );

    if ($texte === false) {
        throw new Exception(crypto_error(
            'Erreur de déchiffrement AES sur le texte.',
            'AES text decryption error.'
        ));
    }

    return $texte;
}

// CHIFFREMENT AES-256-GCM (Pour les gros fichiers)

function generer_cle_aes()
{
    return random_bytes(32); // Génère une clé de 256 bits
}

function chiffrer_fichier_aes($donnees, $cle_aes)
{
    $iv = random_bytes(16);
    $tag = '';

    $texte_chiffre = openssl_encrypt(
        $donnees,
        'aes-256-gcm',
        $cle_aes,
        OPENSSL_RAW_DATA,
        $iv,
        $tag,
        '',
        16
    );

    if ($texte_chiffre === false) {
        throw new Exception(crypto_error('Erreur de chiffrement sur le fichier.', 'File encryption error.'));
    }

    // On renvoie les données chiffrées + l'empreinte pour vérifier l'intégrité plus tard
    return [
        'contenu_chiffre' => $iv . $tag . $texte_chiffre,
        'hash_original' => hash('sha256', $donnees),
    ];
}

function dechiffrer_fichier_aes($donnees_brutes, $cle_aes)
{
    $iv = substr($donnees_brutes, 0, 16);
    $tag = substr($donnees_brutes, 16, 16);
    $texte_chiffre = substr($donnees_brutes, 32);

    $donnees = openssl_decrypt(
        $texte_chiffre,
        'aes-256-gcm',
        $cle_aes,
        OPENSSL_RAW_DATA,
        $iv,
        $tag
    );

    if ($donnees === false) {
        throw new Exception(crypto_error('Fichier corrompu ou clé invalide.', 'Corrupted file or invalid key.'));
    }

    return $donnees;
}
