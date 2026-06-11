# Secure File Storage

Secure File Storage is an educational prototype for encrypted file storage and
sharing, built with PHP and PostgreSQL.

The repository contains:

- the web application in [`drivecrypto/`](drivecrypto/);
- the PostgreSQL schema in [`Scriptbdd/bdd.sql`](Scriptbdd/bdd.sql);
- the final report in PDF format, including the data models and diagrams.

## Features

- account creation and authentication;
- AES-256-GCM file encryption;
- RSA protection of file encryption keys;
- file sharing with another user;
- SHA-256 integrity verification;
- file and account deletion;
- CSRF protection for forms;
- transactional uploads with rollback on failure.
- privacy policy and terms of use for the public demonstration.

## Requirements

- PHP 8.1 or later;
- PHP extensions: `openssl`, `pdo`, and `pdo_pgsql`;
- PostgreSQL 14 or later.

## Installation

1. Create an empty PostgreSQL database.
2. Run [`Scriptbdd/bdd.sql`](Scriptbdd/bdd.sql).
3. Copy `.env.example` to `.env` and update the values.
4. Start the application from the `drivecrypto` directory.

PowerShell example:

```powershell
$env:DB_HOST = "localhost"
$env:DB_PORT = "5432"
$env:DB_NAME = "drivecrypto"
$env:DB_USER = "postgres"
$env:DB_PASSWORD = "change-me"

Set-Location drivecrypto
php -S localhost:8080
```

The application is then available at
[http://localhost:8080](http://localhost:8080).

The `drivecrypto/uploads/` directory is included in the repository, but its
contents are ignored by Git.

## Cryptographic Architecture

Each file receives a randomly generated AES key. This key is encrypted with the
RSA public key of every authorized user. File names and MIME types are also
encrypted.

Encryption is performed on the server. Therefore, the project should not be
described as providing strict end-to-end encryption.

## Security

This repository is an educational prototype and has not undergone an
independent security audit. Do not use it in production or for sensitive data
without an additional review. See [`SECURITY.md`](SECURITY.md) for details.

## Automated Checks

GitHub Actions checks the syntax of every PHP file on each push and pull
request.

## License

No open-source license is currently granted. All rights reserved.
