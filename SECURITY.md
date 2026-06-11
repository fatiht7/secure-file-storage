# Politique de sécurité

## Statut du projet

Secure File Storage est un prototype pédagogique. Il n'a pas fait l'objet d'un audit de
sécurité indépendant et ne doit pas être déployé tel quel en production.

En particulier, l'application conserve temporairement le mot de passe en clair
dans la session PHP afin de déverrouiller la clé privée de l'utilisateur. Cette
architecture doit être revue avant tout usage réel.

## Signaler une vulnérabilité

Ne publiez pas de vulnérabilité exploitable dans une issue publique. Contactez
le propriétaire du dépôt en privé en indiquant :

- la partie concernée ;
- les étapes de reproduction ;
- l'impact estimé ;
- une proposition de correction, si disponible.

Évitez d'inclure des données personnelles, des mots de passe ou des clés privées
dans le signalement.
