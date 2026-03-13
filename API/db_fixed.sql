-- Creation de la base de donnees
CREATE DATABASE IF NOT EXISTS db;
USE db;

-- Table USER
CREATE TABLE `USER`(
   id INT AUTO_INCREMENT,
   email VARCHAR(100) UNIQUE NOT NULL,
   name VARCHAR(50) NOT NULL,
   pass VARCHAR(255) NOT NULL,
   PRIMARY KEY(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table TOURNAMENT
CREATE TABLE `TOURNAMENT`(
   id INT AUTO_INCREMENT,
   name VARCHAR(50) UNIQUE NOT NULL,
   DateDeDebut DATE DEFAULT NULL,
   DateDeFin DATE DEFAULT NULL,
   game VARCHAR(50) NOT NULL,
   TeamCount INT DEFAULT 2,
   status VARCHAR(50) NOT NULL DEFAULT 'Inscriptions ouvertes',
   idU INT NOT NULL,
   PRIMARY KEY(id),
   INDEX idx_tournament_user (idU),
   FOREIGN KEY(idU) REFERENCES `USER`(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table ROUND (mot reserve MySQL, backticks obligatoires)
CREATE TABLE `ROUND`(
   id INT AUTO_INCREMENT,
   name VARCHAR(50) NOT NULL,
   idT INT NOT NULL,
   PRIMARY KEY(id),
   INDEX idx_round_tournament (idT),
   FOREIGN KEY(idT) REFERENCES `TOURNAMENT`(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table TEAM
CREATE TABLE `TEAM`(
   id INT AUTO_INCREMENT,
   name VARCHAR(50) NOT NULL,
   points INT DEFAULT 0,
   image_path VARCHAR(255),
   idT INT NOT NULL,
   PRIMARY KEY(id),
   INDEX idx_team_tournament (idT),
   FOREIGN KEY(idT) REFERENCES `TOURNAMENT`(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table MATCH (mot reserve MySQL, backticks obligatoires)
CREATE TABLE `MATCH`(
   id INT AUTO_INCREMENT,
   team1_id INT,
   team2_id INT,
   team1_point INT DEFAULT 0,
   team2_point INT DEFAULT 0,
   idR INT NOT NULL,
   PRIMARY KEY(id),
   INDEX idx_match_team1 (team1_id),
   INDEX idx_match_team2 (team2_id),
   INDEX idx_match_round (idR),
   FOREIGN KEY(team1_id) REFERENCES `TEAM`(id) ON DELETE SET NULL,
   FOREIGN KEY(team2_id) REFERENCES `TEAM`(id) ON DELETE SET NULL,
   FOREIGN KEY(idR) REFERENCES `ROUND`(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;