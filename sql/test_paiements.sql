-- =====================================================
-- Données de test pour la fonctionnalité Paiements
-- À exécuter après la migration
-- =====================================================

-- Insérer des paiements de test (adapter les contrat_id selon vos données)
-- Supposant que contrat_id = 1 existe et appartient à un utilisateur

INSERT INTO paiement (contrat_id, periode, montant, moyen_paiement, statut, date_paiement, date_creation) VALUES
-- Paiements passés (payés)
(1, 'Janvier 2026', 850.00, 'carte_bancaire', 'paye', '2026-01-05', '2026-01-05 10:30:00'),
(1, 'Décembre 2025', 850.00, 'virement', 'paye', '2025-12-01', '2025-12-01 14:15:00'),
(1, 'Novembre 2025', 850.00, 'carte_bancaire', 'paye', '2025-11-01', '2025-11-01 09:45:00'),
(1, 'Octobre 2025', 850.00, 'especes', 'paye', '2025-10-05', '2025-10-05 16:20:00'),
(1, 'Septembre 2025', 850.00, 'virement', 'paye', '2025-09-01', '2025-09-01 11:00:00'),

-- Paiements à venir (en attente)
(1, 'Février 2026', 850.00, NULL, 'en_attente', NULL, NOW()),
(1, 'Mars 2026', 850.00, NULL, 'en_attente', NULL, NOW()),

-- Paiements en retard
(1, 'Août 2025', 850.00, NULL, 'en_retard', NULL, '2025-08-15 10:00:00');

-- Récapitulatif des données insérées
SELECT 
    COUNT(*) as total_paiements,
    SUM(CASE WHEN statut = 'paye' THEN 1 ELSE 0 END) as payés,
    SUM(CASE WHEN statut = 'en_attente' THEN 1 ELSE 0 END) as en_attente,
    SUM(CASE WHEN statut = 'en_retard' THEN 1 ELSE 0 END) as en_retard
FROM paiement;

-- Afficher les paiements insérés
SELECT 
    p.id,
    p.periode,
    p.montant,
    p.moyen_paiement,
    p.statut,
    DATE_FORMAT(p.date_paiement, '%d/%m/%Y') as date_paiement,
    p.contrat_id
FROM paiement p
ORDER BY p.periode DESC;
