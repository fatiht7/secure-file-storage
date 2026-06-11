# Security Policy

## Project Status

Secure File Storage is an educational prototype. It has not undergone an
independent security audit and should not be deployed to production as-is.

The application temporarily stores the user's plain-text password in the PHP
session to unlock their private key. This architecture should be redesigned
before any real-world use.

## Reporting a Vulnerability

Do not publish exploitable vulnerabilities in a public issue. Contact the
repository owner privately and include:

- the affected component;
- reproduction steps;
- the estimated impact;
- a proposed fix, when available.

Do not include personal data, passwords, or private keys in the report.
