# 📋 Logique Métier des Paiements - HabitaGo

## 🎯 Principes Fondamentaux

### 1️⃣ Référence Absolue : `contrat.date_debut`

Tous les calculs de paiement se basent sur la **date d'entrée du locataire**.

**Exemple** :
```
date_debut = 20 janvier 2025
```

### 2️⃣ Première Échéance : +1 Mois

Le premier loyer est dû **exactement 1 mois après** la date d'entrée.

**Exemple** :
```
date_debut    = 20 janvier 2025
1ère échéance = 20 février 2025 ✅
```

### 3️⃣ Paiement Interdit Avant l'Entrée

❌ **IMPOSSIBLE** de payer pour une période antérieure à la date d'entrée.

**Exemple interdite** :
```
date_debut = 20 janvier 2025

❌ Paiement "janvier" = REFUSÉ
   (janvier < 20 janvier)

✅ Paiement "février" = AUTORISÉ
   (février ≥ 20 janvier)
```

### 4️⃣ Statut "En Retard"

Un paiement est **EN RETARD** si :
- La date d'échéance est **passée** (< aujourd'hui)
- **ET** le paiement n'est **pas encore payé**

**Exemple** :
```
Paiement de février (échéance 20 février)
Aujourd'hui = 25 février
Statut = "en_retard" ⚠️
```

## 🛠️ Architecture de la Solution

### Classe Principale : `PaiementMetierService`

#### Méthodes clés :

| Méthode | Rôle |
|---------|------|
| `calculerDateEcheance()` | Calcule la date d'échéance d'une période |
| `estPaiementAutorise()` | Vérifie si un paiement est permis |
| `estEnRetard()` | Détermine si un paiement est en retard |
| `determinerStatut()` | Met à jour le statut (payé/attente/retard) |
| `validerPaiement()` | Valide avant sauvegarde (règles métier) |
| `genererPaiementsAttendus()` | Crée auto les paiements mensuels |

## 📖 Cas d'Usage

### ✅ Cas 1 : Paiement Autorisé

```
Contrat :
  date_debut = 15 janvier 2025
  montant_loyer = 800€

Paiement prévu :
  periode = "février"
  
Résultat :
  ✅ AUTORISÉ
  Échéance = 15 février 2025
  Statut = "en_attente" (si pas payé avant le 15 février)
  Statut = "en_retard" (si pas payé après le 15 février)
```

### ❌ Cas 2 : Paiement Refusé (Avant l'Entrée)

```
Contrat :
  date_debut = 15 janvier 2025

Tentative :
  periode = "janvier"

Résultat :
  ❌ REFUSÉ
  Message : "Période antérieure à la date d'entrée"
```

### ⚠️ Cas 3 : Paiement En Retard

```
Paiement :
  periode = "mars"
  contrat.date_debut = 15 janvier
  Échéance = 15 mars 2025

Aujourd'hui = 20 mars 2025
Paiement statut = ?

Résultat :
  ⚠️ RETARD
  (20 mars > 15 mars ET pas payé)
```

## 💡 Logique du Statut

```
┌─────────────────┐
│ Paiement Créé   │
└────────┬────────┘
         │
    ┌────▼────┐
    │ Payé ?  │
    └────┬────┘
         │
    ┌────▼────┐
    │ Non     │──► determinerStatut()
    └────┬────┘
         │
    ┌────▼──────────┐
    │ Échéance      │
    │ dépassée ?    │
    └────┬──────────┘
         │
    ┌────▼─────┐
    │ Oui      │──► Statut = "en_retard"
    └──────────┘
    
    │ Non      │──► Statut = "en_attente"
    └──────────┘
```

## 🔐 Validation Avant Sauvegarde

Avant de sauvegarder un paiement, appeler :

```php
$erreurs = $this->paiementMetier->validerPaiement($paiement);

if (!empty($erreurs)) {
    // Afficher les erreurs à l'utilisateur
    foreach ($erreurs as $erreur) {
        $this->addFlash('error', $erreur);
    }
    return; // Ne pas sauvegarder
}
```

## 🤖 Génération Automatique

À chaque accès à `/payments/new`, les paiements attendus sont générés automatiquement :

```php
$this->paiementMetier->genererPaiementsAttendus($contrat);
```

Cela crée les paiements mensuels jusqu'à la date actuelle.

## ✨ Points Clés à Retenir

✅ **Toujours utiliser `contrat.date_debut` comme référence**  
✅ **Première échéance = date_debut + 1 mois**  
❌ **Jamais de paiement avant la date d'entrée**  
⚠️ **En retard = date d'échéance dépassée + pas payé**  
🔄 **Mettre à jour les statuts à chaque consultation**
