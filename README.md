# Secure File Storage

Secure File Storage est un prototype pédagogique de stockage et de partage de fichiers
chiffrés, développé en PHP avec PostgreSQL.

Le dépôt contient :

- l'application web dans [`drivecrypto/`](drivecrypto/) ;
- le schéma PostgreSQL dans [`Scriptbdd/bdd.sql`](Scriptbdd/bdd.sql) ;
- le rapport final au format PDF, avec les modèles et diagrammes.

## Fonctionnalités

- création de compte et authentification ;
- chiffrement des fichiers avec AES-256-GCM ;
- protection des clés de fichiers par RSA ;
- partage d'un fichier avec un autre utilisateur ;
- contrôle d'intégrité SHA-256 ;
- suppression des fichiers et des comptes.

## Prérequis

- PHP 8.1 ou supérieur ;
- extensions PHP `openssl`, `pdo` et `pdo_pgsql` ;
- PostgreSQL 14 ou supérieur.

## Installation

1. Créer une base PostgreSQL vide.
2. Exécuter le script [`Scriptbdd/bdd.sql`](Scriptbdd/bdd.sql).
3. Copier `.env.example` vers `.env` et adapter les valeurs.
4. Exporter les variables du fichier `.env` dans l'environnement du serveur PHP.
5. Démarrer l'application depuis le dossier `drivecrypto`.

Exemple avec PowerShell :

```powershell
$env:DB_HOST = "localhost"
$env:DB_PORT = "5432"
$env:DB_NAME = "drivecrypto"
$env:DB_USER = "postgres"
$env:DB_PASSWORD = "changez-moi"

Set-Location drivecrypto
php -S localhost:8080
```

L'application est ensuite accessible sur
[http://localhost:8080](http://localhost:8080).

Le dossier `drivecrypto/uploads/` est créé dans le dépôt, mais son contenu est
ignoré par Git.

## Architecture cryptographique

Chaque fichier reçoit une clé AES aléatoire. Cette clé est chiffrée avec la clé
publique RSA de chaque utilisateur autorisé. Les noms et types MIME sont
également chiffrés.

Ce projet effectue le chiffrement côté serveur. Il ne doit donc pas être décrit
comme un chiffrement de bout en bout au sens strict.

## Sécurité

Ce dépôt est un prototype pédagogique et n'a pas fait l'objet d'un audit de
sécurité indépendant. Ne l'utilisez pas en production ou pour stocker des
données sensibles sans revue complémentaire. Consultez
[`SECURITY.md`](SECURITY.md) pour plus de détails.

## Vérifications

La vérification automatique GitHub Actions contrôle la syntaxe de tous les
fichiers PHP à chaque contribution.

## Licence

Aucune licence open source n'est actuellement accordée. Tous droits réservés.
