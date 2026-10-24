-- Apply once to an existing OMDS database to enable account disabling and soft deletion.
-- This adds columns only and preserves existing account data.
USE omds;

ALTER TABLE users
    ADD COLUMN is_disabled TINYINT(1) NOT NULL DEFAULT 0,
    ADD COLUMN deleted_at DATETIME NULL;
