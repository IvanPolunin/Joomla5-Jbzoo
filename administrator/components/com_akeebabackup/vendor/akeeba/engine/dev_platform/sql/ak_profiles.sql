CREATE TABLE IF NOT EXISTS "ak_profiles"
(
    "id"            INTEGER PRIMARY KEY,
    "description"   VARCHAR(255) NOT NULL,
    "configuration" TEXT,
    "filters"       TEXT,
    "quickicon"     TINYINT(3)   NOT NULL DEFAULT '1'
);

INSERT INTO "ak_profiles"
("id", "description", "configuration", "filters", "quickicon")
VALUES (1, 'Default Backup Profile', '', '', 1);
