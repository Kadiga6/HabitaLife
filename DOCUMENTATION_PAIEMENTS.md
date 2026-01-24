# 📋 Documentation - Fonctionnalité Paiements HabitaGo

## 🎯 Vue d'ensemble

La fonctionnalité de paiements permet aux locataires de :
- Consulter l'historique complet de leurs paiements
- Voir l'état actuel (payés, en attente, en retard)
- Effectuer des paiements de manière simulée
- Choisir un mode de paiement

---

## 🗂️ Structure technique

### 1. Entités (Entities)

#### `Paiement.php`
**Champs :**
- `id` (int) - Identifiant unique
- `contrat` (Contrat) - Relation ManyToOne avec le contrat
- `periode` (string) - Période concernée (ex: "Janvier 2026")
- `montant` (decimal) - Montant du paiement
- `datePaiement` (DateTime, nullable) - Date du paiement effectué
- `moyenPaiement` (string, nullable) - Mode de paiement utilisé (carte_bancaire, virement, especes)
- `statut` (string) - État du paiement (en_attente, paye, en_retard)
- `dateCreation` (DateTime, nullable) - Date de création du paiement

**Relations :**
- ManyToOne vers `Contrat` (chaque paiement appartient à un contrat)

---

### 2. Formulaires (Forms)

#### `PaiementFormType.php`
**Champs :**
- `moyenPaiement` (ChoiceType) - Sélection du mode de paiement
  - Carte bancaire
  - Virement bancaire
  - Espèces

**Validation :**
- Mode de paiement obligatoire

---

### 3. Contrôleurs (Controllers)

#### `PaymentsController.php`

**Route `#[Route('', name: 'index')]`**
- **URL :** `/payments`
- **Méthode :** GET
- **Authentification :** Requise
- **Responsabilité :**
  1. Récupère l'utilisateur connecté
  2. Charge tous ses contrats
  3. Récupère tous les paiements associés
  4. Calcule les statistiques (payés, en attente, en retard)
  5. Affiche la page d'accueil des paiements

**Variables Twig :**
```php
[
    'paiements' => [],      // Tableau des paiements
    'stats' => [            // Statistiques
        'payes' => 5,
        'en_attente' => 1,
        'en_retard' => 1
    ],
    'contrats' => []        // Contrats de l'utilisateur
]
```

**Route `#[Route('/{id}/pay', name: 'pay')]`**
- **URL :** `/payments/{id}/pay`
- **Méthodes :** GET, POST
- **Authentification :** Requise
- **Responsabilité :**
  1. Récupère le paiement spécifié
  2. Vérifie que le paiement appartient à l'utilisateur connecté
  3. Affiche le formulaire de paiement
  4. Traite la soumission du formulaire

**Logique POST :**
```
1. Valider le formulaire
2. Mettre à jour le paiement :
   - statut = "paye"
   - datePaiement = maintenant
   - dateCreation = maintenant
3. Enregistrer en base de données
4. Afficher un message de succès
5. Rediriger vers la liste des paiements
```

---

### 4. Repositories

#### `PaiementRepository.php`

**Méthode `findByContratIds(array $contratIds): array`**
- Récupère tous les paiements pour une liste de contrats
- Trie par période (plus récent en premier)
- Utile pour l'affichage de la liste

**Méthode `findPendingPayments(array $contratIds): array`**
- Récupère uniquement les paiements en attente ou en retard
- Trie par période (ancien en premier)
- Utile pour identifier les paiements prioritaires

---

### 5. Vues (Templates)

#### `payments/index.html.twig`
**Sections :**

1. **En-tête**
   - Titre et description

2. **Messages Flash**
   - Affiche les confirmations de paiement réussi

3. **Cartes récapitulatives**
   - Nombre de paiements payés
   - Nombre de paiements en attente
   - Nombre de paiements en retard

4. **Tableau historique**
   - Colonnes :
     - Période
     - Montant
     - Logement concerné
     - Statut (badge coloré)
     - Date de paiement
     - Actions (bouton "Payer")

5. **Informations importantes**
   - Date d'échéance standard (1er du mois)
   - Mise en garde sur les pénalités

**Logique conditionnelle :**
- Si statut = "paye" : Bouton désactivé "Payé"
- Si statut = "en_attente" ou "en_retard" : Bouton actif "Payer"

#### `payments/pay.html.twig`
**Sections :**

1. **En-tête**
   - Titre et indication de formulaire

2. **Récapitulatif**
   - Période
   - Montant à payer
   - Logement concerné
   - Dates du contrat

3. **Formulaire**
   - Sélection du mode de paiement
   - Informations contextuelles dynamiques (JavaScript)
   - Bouton "Valider"
   - Bouton "Annuler"

4. **Informations latérales**
   - Avertissement : Paiement simulé
   - Liste des modes de paiement disponibles

**Fonctionnalité JavaScript :**
- Au changement du mode de paiement, affiche une description adaptée
- Messages informatifs pour chaque mode

---

## 🔄 Flux utilisateur

### Consultation des paiements
```
1. Utilisateur clique sur "Paiements" dans le menu
2. Symfony récupère ses contrats et paiements
3. Affichage de la liste avec statistiques
```

### Paiement d'une échéance
```
1. Utilisateur clique sur "Payer" pour une échéance en attente
2. Accès à la page de paiement (/payments/{id}/pay)
3. Visualisation du récapitulatif
4. Sélection du mode de paiement
5. Clique sur "Valider le paiement"
6. Enregistrement en base de données
7. Message de confirmation
8. Redirection vers la liste (statut = "paye")
```

---

## 🔐 Sécurité

### Authentification
- Toutes les routes requièrent une authentification
- Redirection vers `/connexion` si non connecté

### Autorisation
- Un utilisateur ne peut voir que ses propres paiements
- Vérification lors de l'accès à un paiement spécifique :
  ```php
  if ($paiement->getContrat()->getUtilisateur() !== $utilisateur) {
      throw $this->createAccessDeniedException();
  }
  ```

---

## 📊 Modèle de données

### Relation entre tables

```
Utilisateur
    ↓ (1:N)
Contrat
    ↓ (1:N)
Paiement
```

### Exemple de donnée Paiement

```
id: 1
contrat_id: 5
periode: "Janvier 2026"
montant: 850.00
date_paiement: 2026-01-05
moyen_paiement: "carte_bancaire"
statut: "paye"
date_creation: 2026-01-05 10:30:00
```

---

## 🛠️ Installation et migration

### 1. Appliquer la migration
```bash
php bin/console doctrine:migrations:migrate
```

### 2. Insérer des données de test (optionnel)
```bash
php bin/console doctrine:fixtures:load
```

---

## 💡 Points clés

### ✅ Points forts de l'implémentation
1. **Cohérence métier** - Statuts réalistes (en_attente, paye, en_retard)
2. **Sécurité** - Vérification des droits d'accès
3. **UX simple** - Interface intuitive niveau Bachelor
4. **Dynamique** - Informations mises à jour en temps réel
5. **Extensibilité** - Structure simple pour ajouter des fonctionnalités

### ⚠️ Limitations acceptables
1. **Simulation** - Pas de vrai paiement bancaire
2. **Statut en_retard** - À calculer/mettre à jour manuellement ou via cron
3. **Reçus** - Pas de génération PDF (peut être ajouté plus tard)

---

## 🔮 Évolutions futures possibles

1. **Génération de reçus PDF**
   - Créer un bouton "Télécharger reçu" pour les paiements payés
   - Utiliser TCPDF ou Dompdf

2. **Alertes de retard**
   - Tâche cron pour marquer les paiements en retard
   - Email de rappel

3. **Paiement automatique**
   - Intégration réelle avec Stripe
   - Prélèvement bancaire

4. **Historique détaillé**
   - Logs des modifications de statut
   - Traçabilité complète

5. **Gestion des arriérés**
   - Calcul des intérêts de retard
   - Plan de régularisation

---

## 📝 Notes techniques

### Conventions Symfony utilisées
- **Routes** : Attributs PHP 8 (`#[Route(...)]`)
- **Injection de dépendances** : Via constructeur et paramètres de méthode
- **Sécurité** : Gardes d'authentification intégrées
- **Validation** : Via Symfony Forms
- **ORM** : Doctrine 2 avec annotations PHP 8

### Standards de code
- PSR-12 : Format de code PHP
- Nommage en camelCase pour les variables et méthodes
- Nommage en snake_case pour les colonnes de base de données
- Documentation in-line avec PHPDoc

---

## 📞 Support et questions

Pour intégrer cette fonctionnalité :
1. Appliquer la migration
2. Vérifier que les contrats existent en base
3. Créer des paiements de test
4. Tester le flux complet utilisateur
