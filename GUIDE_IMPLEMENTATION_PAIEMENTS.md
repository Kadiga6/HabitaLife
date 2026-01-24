# 🚀 Guide d'implémentation - Paiements HabitaGo

## 📋 Résumé des changements

Cette implémentation ajoute une fonctionnalité complète de gestion des paiements au portail HabitaGo.

### ✨ Fichiers modifiés

| Fichier | Type | Modification |
|---------|------|--------------|
| `src/Entity/Paiement.php` | Entity | Ajout des colonnes `periode` et `montant` |
| `src/Form/PaiementFormType.php` | Form | **CRÉÉ** - Formulaire de paiement |
| `src/Controller/PaymentsController.php` | Controller | Implémentation complète |
| `src/Repository/PaiementRepository.php` | Repository | Ajout de méthodes de requête |
| `templates/payments/index.html.twig` | Template | Refonte avec données dynamiques |
| `templates/payments/pay.html.twig` | Template | **CRÉÉ** - Page de paiement |
| `migrations/Version20260120UpdatePaiement.php` | Migration | **CRÉÉ** - Migration de schéma |

---

## 🔧 Étapes d'installation

### 1. Appliquer les modifications du code

Les fichiers ont déjà été modifiés. Vérifiez que tous les fichiers sont en place :

```bash
cd c:\wamp64\www\IRIS\Bachelor\HabitaLife

# Vérifier que les fichiers existent
dir src\Form\PaiementFormType.php
dir src\Controller\PaymentsController.php
dir templates\payments\pay.html.twig
```

### 2. Exécuter la migration

```bash
# Afficher les migrations à faire
php bin/console doctrine:migrations:status

# Exécuter les migrations
php bin/console doctrine:migrations:migrate
```

**Résultat attendu :**
```
Exécution de 1 migration…
Exécution de Version20260120UpdatePaiement
Migrations exécutées : 1
```

### 3. Insérer des données de test (optionnel)

Ouvrez phpMyAdmin et exécutez le contenu de `sql/test_paiements.sql` :

```sql
-- Ou en ligne de commande :
mysql -u root habitago < sql/test_paiements.sql
```

### 4. Tester l'application

1. **Démarrer le serveur Symfony**
   ```bash
   symfony serve
   ```
   Ou avec PHP :
   ```bash
   php -S 127.0.0.1:8000 -t public/
   ```

2. **Se connecter** avec un compte utilisateur

3. **Accéder à la page Paiements** : `/payments`

4. **Tester les actions :**
   - Voir le tableau avec les paiements
   - Cliquer sur "Payer" pour un paiement en attente
   - Sélectionner un mode de paiement
   - Valider le paiement
   - Vérifier que le statut passe à "Payé"

---

## 🎯 Fonctionnalités implémentées

### ✅ Page Liste des paiements (`/payments`)

**Affichage :**
- Compteurs de paiements (Payés, En attente, En retard)
- Tableau historique avec :
  - Période du paiement
  - Montant
  - Logement concerné
  - Statut (badge coloré)
  - Date de paiement
  - Actions (bouton Payer)

**Interaction :**
- Messages flash de confirmation après paiement
- Boutons "Payer" cliquables pour les paiements en attente/retard
- Boutons désactivés pour les paiements payés

### ✅ Page Paiement (`/payments/{id}/pay`)

**Affichage :**
- Récapitulatif du paiement :
  - Période et badge
  - Montant en vert
  - Adresse du logement
  - Dates du contrat
- Formulaire avec :
  - Sélection du mode de paiement
  - Information dynamique selon le mode choisi
  - Boutons Valider / Annuler

**Logique métier :**
- Vérification des droits d'accès
- Enregistrement en base de données
- Passage du statut à "paye"
- Enregistrement de la date et du mode de paiement
- Redirection avec message de confirmation

---

## 📊 Détails techniques

### Base de données

**Colonnes ajoutées à `paiement` :**
```sql
periode VARCHAR(100)          -- Ex: "Janvier 2026"
montant NUMERIC(10, 2)        -- Ex: 850.00
```

**Modifications :**
```sql
statut VARCHAR(255) NOT NULL DEFAULT 'en_attente'
date_paiement DATE NULL       -- Nullable
moyen_paiement VARCHAR(50) NULL -- Nullable
```

### Routes

| Route | Méthode | Nom | Description |
|-------|---------|-----|-------------|
| `/payments` | GET | `payments_index` | Liste des paiements |
| `/payments/{id}/pay` | GET, POST | `payments_pay` | Formulaire de paiement |

### Statuts possibles

```
en_attente   → Paiement attendant d'être effectué
paye         → Paiement effectué avec succès
en_retard    → Paiement passé la date d'échéance
```

### Modes de paiement

```
carte_bancaire  → Carte bancaire
virement        → Virement bancaire
especes         → Espèces (remise en main propre)
```

---

## 🔐 Sécurité

### ✅ Mesures implémentées

1. **Authentification** obligatoire
   - Redirection vers `/connexion` si non connecté

2. **Autorisation** par utilisateur
   - Vérification que le paiement appartient à l'utilisateur
   - Lancer une exception d'accès refusé si tentative non autorisée

3. **Validation** du formulaire
   - Mode de paiement obligatoire
   - Validation côté serveur

---

## 🧪 Tests recommandés

### Scénario 1 : Consultation
```
1. Connecté → Page Paiements
2. Voir les paiements avec le bon statut
3. Vérifier les compteurs
```

### Scénario 2 : Paiement valide
```
1. Cliquer sur "Payer"
2. Sélectionner un mode
3. Cliquer "Valider"
4. Vérifier le message de succès
5. Vérifier le statut = "paye" en liste
```

### Scénario 3 : Sécurité
```
1. Connecté avec Utilisateur A
2. Accéder à /payments/{id}/pay d'un paiement d'Utilisateur B
3. Vérifier que l'accès est refusé
```

### Scénario 4 : Formulaire
```
1. Accès à /payments/{id}/pay
2. Essayer de valider sans mode de paiement
3. Vérifier que le formulaire affiche une erreur
```

---

## 📝 Personnalisation

### Ajouter des modes de paiement

**Fichier :** `src/Form/PaiementFormType.php`

```php
'choices' => [
    'Carte bancaire' => 'carte_bancaire',
    'Virement bancaire' => 'virement',
    'Espèces' => 'especes',
    'Chèque' => 'cheque',  // ← Ajouter ici
],
```

### Modifier les messages

**Fichier :** `templates/payments/pay.html.twig`

Chercher la section JavaScript et modifier `paymentMessages`.

### Changer les seuils de statut

Pour automatiser le passage en "en_retard", créer une tâche cron :

```php
// src/Command/UpdatePaymentStatusCommand.php
// À cron : 0 2 * * * php bin/console app:update-payment-status
```

---

## ❓ Dépannage

### Erreur : "SQLSTATE[42S21]: Column not found"

→ Migration non exécutée
```bash
php bin/console doctrine:migrations:migrate
```

### Erreur : "Accès refusé à ce paiement"

→ L'utilisateur tente d'accéder à un paiement d'un autre utilisateur
→ C'est normal, comportement de sécurité

### Pas de paiements affichés

→ Vérifier qu'il existe des contrats pour cet utilisateur
→ Vérifier que les paiements existent en base de données

```sql
SELECT * FROM utilisateur WHERE email = 'test@example.com';
SELECT * FROM contrat WHERE utilisateur_id = 1;
SELECT * FROM paiement WHERE contrat_id IN (SELECT id FROM contrat WHERE utilisateur_id = 1);
```

---

## 🎓 Points pédagogiques

Cette implémentation démontre :

✅ **Patterns Symfony**
- Injection de dépendances
- Routes avec attributs PHP 8
- Formulaires Symfony
- Validation

✅ **Doctrine ORM**
- Relations ManyToOne
- Migrations
- QueryBuilder

✅ **Sécurité**
- Authentification (Security Voter possible)
- Validation des droits d'accès
- CSRF protection (automatique)

✅ **Frontend**
- Bootstrap 5 pour le responsive
- Conditionnels Twig
- JavaScript vanilla pour l'interactivité
- Messages flash

---

## 📞 Contact / Questions

Pour des questions sur cette implémentation :
1. Consulter `DOCUMENTATION_PAIEMENTS.md`
2. Vérifier les logs Symfony : `var/log/dev.log`
3. Utiliser le web profiler : `/_profiler/`

---

## 🎉 C'est prêt !

L'implémentation est complète et fonctionnelle. À vous de l'intégrer et la tester !
