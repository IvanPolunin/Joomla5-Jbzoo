CREATE TABLE IF NOT EXISTS "ak_stats"
(
    "id"              INTEGER PRIMARY KEY,
    "description"     VARCHAR(255) NOT NULL,
    "comment"         TEXT,
    "backupstart"     DATETIME     NULL     DEFAULT NULL,
    "backupend"       DATETIME     NULL     DEFAULT NULL,
    "status"          VARCHAR(123) NOT NULL DEFAULT 'run',
    "origin"          VARCHAR(30)  NOT NULL DEFAULT 'backend',
    "type"            VARCHAR(30)  NOT NULL DEFAULT 'full',
    "profile_id"      BIGINT(20)   NOT NULL DEFAULT '1',
    "archivename"     TEXT,
    "absolute_path"   TEXT,
    "multipart"       INT(11)      NOT NULL DEFAULT '0',
    "tag"             VARCHAR(255)          DEFAULT NULL,
    "backupid"        VARCHAR(255)          DEFAULT NULL,
    "filesexist"      TINYINT(3)   NOT NULL DEFAULT '1',
    "remote_filename" VARCHAR(1000)         DEFAULT NULL,
    "total_size"      BIGINT(20)   NOT NULL DEFAULT '0',
    "frozen"          TINYINT(1)   NOT NULL DEFAULT '0',
    "instep"          TINYINT(1)   NOT NULL DEFAULT '0'
);