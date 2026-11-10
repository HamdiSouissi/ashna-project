-- LeadFinder - schema MySQL (utf8mb4). Importer via phpMyAdmin (IONOS).

SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS users (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nom VARCHAR(100) NOT NULL,
  email VARCHAR(190) NOT NULL UNIQUE,
  mot_de_passe_hash VARCHAR(255) NOT NULL,
  role ENUM('admin','user') NOT NULL DEFAULT 'user',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS searches (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NULL,
  ville VARCHAR(190) NOT NULL,
  zone_label VARCHAR(190) NOT NULL DEFAULT '',
  categorie VARCHAR(80) NOT NULL,
  bbox VARCHAR(120) NOT NULL DEFAULT '',
  nb_trouves INT UNSIGNED NOT NULL DEFAULT 0,
  nb_sans_site INT UNSIGNED NOT NULL DEFAULT 0,
  statut ENUM('en_cours','terminee','echec') NOT NULL DEFAULT 'en_cours',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY idx_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS prospects (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  osm_type ENUM('node','way','relation') NOT NULL,
  osm_id BIGINT UNSIGNED NOT NULL,
  nom VARCHAR(190) NOT NULL,
  categorie VARCHAR(80) NOT NULL DEFAULT '',
  adresse VARCHAR(255) NOT NULL DEFAULT '',
  ville VARCHAR(120) NOT NULL DEFAULT '',
  code_postal VARCHAR(20) NOT NULL DEFAULT '',
  telephone VARCHAR(60) NOT NULL DEFAULT '',
  telephone_normalise VARCHAR(24) NOT NULL DEFAULT '',
  email VARCHAR(190) NOT NULL DEFAULT '',
  facebook VARCHAR(255) NOT NULL DEFAULT '',
  instagram VARCHAR(255) NOT NULL DEFAULT '',
  horaires VARCHAR(255) NOT NULL DEFAULT '',
  latitude DECIMAL(10,7) NOT NULL DEFAULT 0,
  longitude DECIMAL(10,7) NOT NULL DEFAULT 0,
  site_web_statut ENUM('aucun','reseaux_sociaux','verifie_aucun') NOT NULL DEFAULT 'aucun',
  score_confiance ENUM('faible','moyen','eleve') NOT NULL DEFAULT 'moyen',
  statut_prospection ENUM('nouveau','a_contacter','contacte','interesse','rdv','client','refuse') NOT NULL DEFAULT 'nouveau',
  notes TEXT NULL,
  search_id INT UNSIGNED NULL,
  assigned_to INT UNSIGNED NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_osm (osm_type, osm_id),
  KEY idx_statut (statut_prospection),
  KEY idx_ville (ville),
  KEY idx_cat (categorie),
  KEY idx_search (search_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS interactions (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  prospect_id INT UNSIGNED NOT NULL,
  user_id INT UNSIGNED NULL,
  type ENUM('appel','whatsapp','email','visite') NOT NULL,
  note TEXT NOT NULL,
  date_interaction DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY idx_prospect (prospect_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS settings (
  cle VARCHAR(80) PRIMARY KEY,
  valeur TEXT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO settings (cle, valeur) VALUES
  ('nominatim_user_agent', 'LeadFinder/1.0 (prospection - contact@mondomaine.tld)'),
  ('overpass_servers', 'https://overpass-api.de/api/interpreter\nhttps://overpass.kumi.systems/api/interpreter\nhttps://maps.mail.ru/osm/tools/overpass/api/interpreter'),
  ('overpass_timeout', '30'),
  ('api_delay', '1'),
  ('cache_ttl_hours', '24'),
  ('enrichment_delay', '2.5'),
  ('default_country', 'TN'),
  ('ai_enabled', '0'),
  ('ai_base_url', ''),
  ('ai_api_key', ''),
  ('ai_model', 'llama-3.1-8b-instant'),
  ('custom_categories', '');
