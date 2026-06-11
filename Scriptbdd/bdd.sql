/*==============================================================*/
/* Nom de SGBD :  PostgreSQL                                    */
/* Date de création :  18/03/2026                               */
/* Stockage sécurisé de fichiers - Schéma complet               */
/*==============================================================*/


DROP TABLE IF EXISTS partager;
DROP TABLE IF EXISTS fichiers;
DROP TABLE IF EXISTS utilisateurs;

/*==============================================================*/
/* Table : utilisateurs                                         */
/*==============================================================*/
CREATE TABLE utilisateurs (
    id_utilisateur       SERIAL               NOT NULL,
    username             VARCHAR(50)          NOT NULL UNIQUE,
    email                VARCHAR(255)         NOT NULL UNIQUE,
    mot_de_passe_hash    VARCHAR(255)         NOT NULL,
    cle_publique         TEXT                 NOT NULL,
    cle_privee_chiffree  TEXT                 NOT NULL,
    date_creation        TIMESTAMP            NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT pk_utilisateurs PRIMARY KEY (id_utilisateur)
);

/*==============================================================*/
/* Table : fichiers                                             */
/*==============================================================*/
CREATE TABLE fichiers (
    id_fichier           SERIAL               NOT NULL,
    id_utilisateur       INT4                 NOT NULL,
    nom_original_chiffre TEXT                 NOT NULL,
    nom_stockage         VARCHAR(255)         NOT NULL UNIQUE,
    type_mime_chiffre    TEXT                 NOT NULL,
    taille               INT8                 NOT NULL,
    hash_sha256          VARCHAR(64)          NOT NULL,
    date_upload          TIMESTAMP            NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT pk_fichiers PRIMARY KEY (id_fichier)
);

/*==============================================================*/
/* Table : partager (système d'enveloppe numérique)             */
/* Contient l'accès du propriétaire + les partages              */
/*==============================================================*/
CREATE TABLE partager (
    id_utilisateur       INT4                 NOT NULL,
    id_fichier           INT4                 NOT NULL,
    cle_aes_chiffree     TEXT                 NOT NULL,
    date_partage         TIMESTAMP            NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT pk_partager PRIMARY KEY (id_utilisateur, id_fichier)
);

/*==============================================================*/
/* Clés étrangères avec CASCADE                                 */
/*==============================================================*/
ALTER TABLE fichiers
    ADD CONSTRAINT fk_fichiers_possede_utilisat FOREIGN KEY (id_utilisateur)
        REFERENCES utilisateurs (id_utilisateur)
        ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE partager
    ADD CONSTRAINT fk_partager_utilisat FOREIGN KEY (id_utilisateur)
        REFERENCES utilisateurs (id_utilisateur)
        ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE partager
    ADD CONSTRAINT fk_partager_fichiers FOREIGN KEY (id_fichier)
        REFERENCES fichiers (id_fichier)
        ON DELETE CASCADE ON UPDATE CASCADE;