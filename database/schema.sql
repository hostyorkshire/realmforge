-- RealmForge Database Schema
-- MySQL 5.7+ / MariaDB compatible

CREATE DATABASE IF NOT EXISTS realmforge CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE realmforge;

-- ── Players ──────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS players (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    session_id    VARCHAR(128) NOT NULL UNIQUE,
    location      VARCHAR(100) NOT NULL DEFAULT 'stonebridge_village',
    health        SMALLINT     NOT NULL DEFAULT 100,
    gold          INT          NOT NULL DEFAULT 25,
    inventory     JSON         NOT NULL DEFAULT ('[]'),
    quests        JSON         NOT NULL DEFAULT ('[]'),
    faction_rep   JSON         NOT NULL DEFAULT ('{}'),
    history       JSON         NOT NULL DEFAULT ('[]'),
    story_summary TEXT,
    created_at    TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at    TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_session (session_id)
) ENGINE=InnoDB;

-- ── Adventure Log ─────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS adventure_log (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    player_id  INT UNSIGNED NOT NULL,
    action     VARCHAR(255) NOT NULL,
    narrative  TEXT,
    location   VARCHAR(100),
    logged_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (player_id) REFERENCES players(id) ON DELETE CASCADE,
    INDEX idx_player (player_id),
    INDEX idx_logged (logged_at)
) ENGINE=InnoDB;

-- ── World Cache ───────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS world_cache (
    id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    cache_key    VARCHAR(64)  NOT NULL UNIQUE,
    data         LONGTEXT     NOT NULL,
    generated_at TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_key (cache_key)
) ENGINE=InnoDB;

-- ── Image Cache ───────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS image_cache (
    id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    prompt_hash  VARCHAR(32)  NOT NULL UNIQUE,
    prompt       TEXT         NOT NULL,
    file_path    VARCHAR(255) NOT NULL,
    image_type   VARCHAR(20)  NOT NULL DEFAULT 'scene',
    created_at   TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_hash (prompt_hash),
    INDEX idx_type (image_type)
) ENGINE=InnoDB;
