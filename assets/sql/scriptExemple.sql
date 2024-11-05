USE db_ovschool;

INSERT INTO `etat` (`id`, `libelle`) VALUES
                                         (1, 'Créée'),
                                         (2, 'Ouverte'),
                                         (3, 'Clôturée'),
                                         (4, 'En cours'),
                                         (5, 'Terminée'),
                                         (6, 'Annulée');



INSERT INTO `ville` (`id`, `nom`, `code_postal`) VALUES
                                                     (1, 'Nantes', '44000'),
                                                     (2, 'Paris', '75000'),
                                                     (3, 'Quimper', '29000'),
                                                     (4, 'Angers', '49000'),
                                                     (5, 'Rennes', '35000');




INSERT INTO `lieu` (`id`, `ville_id`, `nom`, `rue`, `latitude`, `longitude`) VALUES
                                                                                 (1, 1, 'Centre des Congrès', 'Rue de la Cité', 47.2184, -1.5536),  -- Nantes
                                                                                 (2, 2, 'Palais des Congrès', 'Place de la Porte Maillot', 48.8858, 2.2876),  -- Paris
                                                                                 (3, 3, 'Salle Polyvalente', 'Rue de la Gare', 47.9960, -4.0977),  -- Quimper
                                                                                 (4, 4, 'Espace Culturel', 'Avenue du Général de Gaulle', 47.4782, -0.5562),  -- Angers
                                                                                 (5, 5, 'Parc des Expositions', 'Rue du Colombier', 48.1120, -1.6810);  -- Rennes

INSERT INTO `site` (`id`, `nom`) VALUES
                                     (1, 'Nantes'),
                                     (2, 'Rennes'),
                                     (3, 'Quimper'),
                                     (4, 'Niort'),
                                     (5, 'En distanciel');


INSERT INTO `user` (`id`, `site_id`, `email`, `roles`, `password`, `pseudo`, `nom`, `prenom`, `administrateur`, `telephone`, `actif`, `url_photo`) VALUES
                                                                                                                                                       (1, 1, 'user1@example.com', '["ROLE_USER"]', 'password', 'user1', 'Dupont', 'Jean', 0, '0612345678', 1, 'photo1.jpg'),
                                                                                                                                                       (2, 2, 'user2@example.com', '["ROLE_USER"]', 'password', 'user2', 'Martin', 'Sophie', 0, '0623456789', 1, 'photo2.jpg'),
                                                                                                                                                       (3, 3, 'user3@example.com', '["ROLE_USER"]', 'password', 'user3', 'Lemoine', 'Paul', 0, '0634567890', 1, 'photo3.jpg'),
                                                                                                                                                       (4, 4, 'user4@example.com', '["ROLE_USER"]', 'password', 'user4', 'Dubois', 'Claire', 0, '0645678901', 1, 'photo4.jpg'),
                                                                                                                                                       (5, 5, 'admin@example.com', '["ROLE_ADMIN"]', 'password', 'admin', 'Moreau', 'Julie', 1, '0656789012', 1, 'photo5.jpg'),
                                                                                                                                                       (6, 5, 'old-admin@example.com', '["ROLE_ADMIN"]', 'password', 'old-admin', 'Lenon', 'Bob', 1, '0656789000', 0, 'photo6.jpg');


INSERT INTO `sortie` (`id`, `etat_id`, `site_id`, `organisateur_id`, `lieu_id`, `nom`, `date_heure_debut`, `duree`, `date_limite_inscription`, `nb_inscriptions_max`, `infos_sortie`, `url_photo`) VALUES
                                                                                                                                                                                                       (1, 1, 1, 1, 1,'Conférence de dev web', '2024-11-10 09:00:00', 120, '2024-11-08 17:00:00', 100, 'Une conférence sur les meilleures pratiques.', 'url_photo1.jpg'),
                                                                                                                                                                                                       (2, 2, 2, 2, 3,'Atelier de créativité', '2024-11-12 14:00:00', 90, '2024-11-10 17:00:00', 50, 'Un atelier pour stimuler votre créativité.', 'url_photo2.jpg'),
                                                                                                                                                                                                       (3, 3, 3, 3, 5,'Festival de musique', '2024-11-15 20:00:00', 180, '2024-11-13 23:00:00', 300, 'Un festival avec plusieurs artistes.', 'url_photo3.jpg'),
                                                                                                                                                                                                       (4, 4, 4, 4, 1,'Marché artisanal', '2024-11-20 10:00:00', 240, '2024-11-18 18:00:00', 150, 'Venez découvrir des produits locaux.', 'url_photo4.jpg'),
                                                                                                                                                                                                       (5, 5, 5, 5, 2,'Exposition d’art', '2024-11-25 11:00:00', 150, '2024-11-22 15:00:00', 200, 'Une exposition de talents émergents.', 'url_photo5.jpg');


INSERT INTO `sortie_user` (`sortie_id`, `user_id`) VALUES
                                                       (1, 1),
                                                       (1, 2),
                                                       (2, 1),
                                                       (2, 3),
                                                       (3, 1);




