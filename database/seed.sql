-- Jeu de demonstration (fictif). Optionnel : importer apres schema.sql.
SET NAMES utf8mb4;

INSERT INTO searches (id, user_id, ville, zone_label, categorie, bbox, nb_trouves, nb_sans_site, statut)
VALUES (1, NULL, 'Tunis', 'Tunis, Tunisie', 'restaurant', '36.7519,10.0962,36.9109,10.3285', 42, 11, 'terminee');

INSERT INTO prospects
(osm_type, osm_id, nom, categorie, adresse, ville, code_postal, telephone, telephone_normalise, email, facebook, instagram, horaires, latitude, longitude, site_web_statut, score_confiance, statut_prospection, notes, search_id)
VALUES
('node', 9000001, 'Café El Menzah', 'restaurant', '12 Avenue Habib Bourguiba', 'Tunis', '1001', '71 234 567', '+21671234567', '', '', '', 'Mo-Su 07:00-23:00', 36.8065, 10.1815, 'aucun', 'moyen', 'a_contacter', '', 1),
('node', 9000002, 'Restaurant Le Jasmin', 'restaurant', '3 Rue de Marseille', 'Tunis', '1002', '+216 71 987 654', '+21671987654', 'jasmin@example.tn', '', '', 'Mo-Sa 11:00-22:00', 36.7981, 10.1789, 'aucun', 'eleve', 'contacte', 'Proposition envoyée par WhatsApp.', 1),
('way',  9000003, 'Pizzeria Ariana', 'restaurant', '5 Avenue de la Liberté', 'Ariana', '2080', '98 765 432', '+21698765432', '', 'https://facebook.com/pizzeria.ariana', '', 'Mo-Su 12:00-23:30', 36.8625, 10.1956, 'reseaux_sociaux', 'moyen', 'nouveau', '', 1),
('node', 9000004, 'Le Fournil de la Médina', 'boulangerie', 'Rue Sidi Ben Arous', 'Tunis', '1008', '+216 71 555 111', '+21671555111', '', '', '', 'Mo-Su 05:30-19:00', 36.8022, 10.1734, 'verifie_aucun', 'eleve', 'interesse', 'Intéressé, rappeler vendredi.', NULL),
('node', 9000005, 'Garage El Kef Auto', 'garage', 'Route de Le Kef km 3', 'Le Kef', '7100', '78 111 222', '+21678111222', 'garage@example.tn', '', '', 'Mo-Sa 08:00-18:00', 36.1744, 8.7042, 'aucun', 'faible', 'nouveau', '', NULL),
('node', 9000006, 'Salon Élégance', 'coiffure', '9 Rue de Rome', 'Sousse', '4000', '73 222 333', '+21673222333', '', 'https://facebook.com/salon.elegance.sousse', 'https://instagram.com/salon.elegance', 'Mo-Sa 09:00-19:00', 35.8256, 10.6411, 'reseaux_sociaux', 'moyen', 'rdv', 'RDV fixé le 12.', NULL),
('node', 9000007, 'Pharmacie du Centre', 'pharmacie', 'Avenue Farhat Hached', 'Sfax', '3000', '74 333 444', '+21674333444', 'pharma-centre@example.tn', '', '', 'Mo-Su 08:00-20:00', 34.7406, 10.7603, 'aucun', 'moyen', 'refuse', 'Pas intéressé pour le moment.', NULL),
('node', 9000008, 'Fit Club Sousse', 'fitness', 'Route de Monastir', 'Sousse', '4051', '73 444 555', '+21673444555', '', '', '', 'Mo-Su 06:00-22:00', 35.8385, 10.6555, 'aucun', 'eleve', 'client', 'Client depuis octobre.', NULL);

INSERT INTO interactions (prospect_id, user_id, type, note)
VALUES
(2, NULL, 'whatsapp', 'Premier contact WhatsApp, demande de brochure.'),
(2, NULL, 'appel', 'Appel de relance : a promis de regarder le devis.'),
(4, NULL, 'visite', 'Visite sur place, très intéressé par un site vitrine.'),
(6, NULL, 'email', 'Envoi du portfolio par e-mail.'),
(8, NULL, 'appel', 'Signature du contrat de création de site.');
