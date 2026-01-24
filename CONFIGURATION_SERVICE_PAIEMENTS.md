# 🚀 Configuration et Intégration - Service Métier des Paiements

## 📦 Architecture

```
src/
├── Service/
│   └── PaiementMetierService.php      ← Service métier (logique pure)
├── Controller/
│   └── PaymentsController.php         ← Utilise le service
├── Entity/
│   ├── Contrat.php                    ← Référence absolue
│   └── Paiement.php                   ← Entité gérée
└── Repository/
    └── PaiementRepository.php
```

## 🔧 Configuration Symfony

### 1️⃣ Service Auto-Enregistré

Le service est **automatiquement enregistré** par Symfony grâce à l'auto-configuration.

```yaml
# config/services.yaml
services:
  _defaults:
    autowire: true
    autoconfigure: true

  App\Service\:
    resource: '../src/Service'
```

### 2️⃣ Injection de Dépendance

Le service est injecté automatiquement dans les contrôleurs :

```php
public function __construct(PaiementMetierService $paiementMetier)
{
    $this->paiementMetier = $paiementMetier;
}
```

## 🎯 Points Clés d'Intégration

### A. Dans le Contrôleur PaymentsController

**Route `/payments` (index)**
```php
// Mettre à jour les statuts
foreach ($paiements as $paiement) {
    $this->paiementMetier->determinerStatut($paiement);
}
```

**Route `/payments/{id}/pay`**
```php
// Valider avant traitement
$erreurs = $this->paiementMetier->validerPaiement($paiement);
if (!empty($erreurs)) {
    // Afficher les erreurs
    return $this->redirectToRoute('payments_index');
}
```

**Route `/payments/new`**
```php
// Générer les paiements attendus
$this->paiementMetier->genererPaiementsAttendus($contrat);
```

### B. Dans le Repository

Ajouter une méthode helper :

```php
// src/Repository/PaiementRepository.php

public function findPaymentsByContractAndPeriod(
    Contrat $contrat,
    string $periode
): ?Paiement {
    return $this->createQueryBuilder('p')
        ->andWhere('p.contrat = :contrat')
        ->andWhere('p.periode = :periode')
        ->setParameter('contrat', $contrat)
        ->setParameter('periode', $periode)
        ->getQuery()
        ->getOneOrNullResult();
}
```

### C. Dans les Templates Twig

Afficher le statut avec l'icône appropriée :

```twig
{% for paiement in paiements %}
    <tr>
        <td>{{ paiement.periode }}</td>
        <td>
            {% if paiement.statut == 'paye' %}
                <span class="badge badge-success">✅ Payé</span>
            {% elseif paiement.statut == 'en_retard' %}
                <span class="badge badge-danger">⚠️ En retard</span>
            {% else %}
                <span class="badge badge-warning">⏳ En attente</span>
            {% endif %}
        </td>
        <td>{{ paiement.montant }}€</td>
    </tr>
{% endfor %}
```

## 🔄 Flux de Données

```
Utilisateur
    │
    ▼
PaymentsController
    │
    ├─► genererPaiementsAttendus()
    ├─► validerPaiement()
    ├─► estPaiementAutorise()
    ├─► estEnRetard()
    └─► determinerStatut()
         │
         ▼
     Doctrine ORM
         │
         ▼
     MySQL Database
```

## 📊 Cas d'Usage Par Route

### Route : `GET /payments`

```
1. Récupérer utilisateur
2. Récupérer ses contrats
3. Récupérer ses paiements
4. ⭐ Pour chaque paiement : determinerStatut()
5. Calculer les stats
6. Afficher le tableau
```

### Route : `POST /payments/new`

```
1. Récupérer contrat actif
2. ⭐ genererPaiementsAttendus()
3. Créer nouvelle entité Paiement
4. ⭐ validerPaiement()
5. Si valide → sauvegarder
6. Rediriger avec message
```

### Route : `POST /payments/{id}/pay`

```
1. Récupérer le paiement
2. ⭐ validerPaiement()
3. Si valide → traiter paiement
4. Enregistrer date_paiement
5. ⭐ determinerStatut() → "paye"
6. Sauvegarder
7. Rediriger avec succès
```

## 🧪 Tests Pratiques

### Test 1 : Vérifier la première échéance

```bash
# Dans la console Symfony
php bin/console doctrine:query:sql "
  SELECT p.periode, p.statut, c.date_debut
  FROM paiement p
  JOIN contrat c ON p.contrat_id = c.id
  WHERE c.date_debut = '2025-01-20'
  LIMIT 1
"
```

**Résultat attendu :**
- periode = "février" (pas "janvier")
- date d'échéance = 20 février

### Test 2 : Vérifier les retards

```bash
# Voir les paiements en retard
php bin/console doctrine:query:sql "
  SELECT p.id, p.periode, p.statut
  FROM paiement p
  WHERE p.statut = 'en_retard'
"
```

### Test 3 : Générer les paiements

```bash
# Créer une commande console
php bin/console app:paiements:generer
```

## 🛡️ Sécurité

### Vérifications Implémentées

✅ **Accès utilisateur**
```php
if ($paiement->getContrat()->getUtilisateur() !== $this->getUser()) {
    throw $this->createAccessDeniedException();
}
```

✅ **Validation métier**
```php
$erreurs = $this->paiementMetier->validerPaiement($paiement);
```

✅ **Unicité des paiements**
```php
// Refusé s'il existe déjà un paiement pour cette période
```

## 📈 Performance

### Optimisations

1. **Génération paresseuse** : Les paiements ne sont générés que quand nécessaire
2. **Mise en cache des calculs** : Les statuts sont calculés une seule fois par consultation
3. **Requêtes optimisées** : Utilisation de `findBy()` avec index sur (contrat_id, periode)

## 🐛 Débogage

### Activer les logs

```yaml
# config/packages/monolog.yaml
monolog:
  handlers:
    paiements:
      type: stream
      path: "%kernel.logs_dir%/paiements.log"
      level: debug
      channels:
        - App\Service\PaiementMetierService
```

### Log des opérations

```php
// Dans PaiementMetierService
private LoggerInterface $logger;

public function validerPaiement(Paiement $paiement): array
{
    $this->logger->info("Validation paiement : {$paiement->getId()}");
    // ...
}
```

## 📚 Documentations Associées

- 📋 [LOGIQUE_PAIEMENTS.md](LOGIQUE_PAIEMENTS.md) - Logique métier
- 📊 [SCHEMA_PAIEMENTS.md](SCHEMA_PAIEMENTS.md) - Diagrammes visuels
- 💻 [EXEMPLES_UTILISATION_PAIEMENTS.php](EXEMPLES_UTILISATION_PAIEMENTS.php) - Exemples de code

## ✅ Checklist d'Intégration

- [ ] Service `PaiementMetierService.php` créé
- [ ] `PaymentsController.php` mis à jour
- [ ] `ContratRepository::findActiveContractForUser()` existe
- [ ] Routes `/payments`, `/payments/{id}/pay`, `/payments/new` fonctionnent
- [ ] Templates affichent les statuts correctement
- [ ] Tests unitaires passent
- [ ] Paiements générés automatiquement
- [ ] Retards calculés correctement
- [ ] Validations métier appliquées

