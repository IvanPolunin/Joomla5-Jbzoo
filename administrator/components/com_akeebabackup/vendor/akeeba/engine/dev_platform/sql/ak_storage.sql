CREATE TABLE IF NOT EXISTS "ak_storage"
(
    "tag"        VARCHAR(255) NOT NULL,
    "lastupdate" TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    "data"       TEXT,
    PRIMARY KEY ("tag")
);
