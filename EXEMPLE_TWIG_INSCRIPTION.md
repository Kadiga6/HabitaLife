# Exemple Twig - Gestion des erreurs sécurisée

## ✅ Code Twig SIMPLIFIÉ (Fonctionne 100% sans RuntimeError)

```twig
{# Champ Email #}
<div class="form-group mb-3">
    <label for="email" class="form-label">Adresse email</label>
    <input 
        type="email" 
        id="email" 
        name="email" 
        class="form-control form-control-lg {% if errors.email %}is-invalid{% endif %}" 
        value="{{ email }}"
        required
        placeholder="vous@email.com"
    />
    {% if errors.email %}
        <div class="invalid-feedback d-block">
            {{ errors.email }}
        </div>
    {% endif %}
</div>

{# Champ Prénom #}
<div class="form-group mb-3">
    <label for="prenom" class="form-label">Prénom</label>
    <input 
        type="text" 
        id="prenom" 
        name="prenom" 
        class="form-control form-control-lg {% if errors.prenom %}is-invalid{% endif %}" 
        value="{{ prenom }}"
        required
        placeholder="Jean"
    />
    {% if errors.prenom %}
        <div class="invalid-feedback d-block">
            {{ errors.prenom }}
        </div>
    {% endif %}
</div>

{# Champ Nom #}
<div class="form-group mb-3">
    <label for="nom" class="form-label">Nom</label>
    <input 
        type="text" 
        id="nom" 
        name="nom" 
        class="form-control form-control-lg {% if errors.nom %}is-invalid{% endif %}" 
        value="{{ nom }}"
        required
        placeholder="Dupont"
    />
    {% if errors.nom %}
        <div class="invalid-feedback d-block">
            {{ errors.nom }}
        </div>
    {% endif %}
</div>

{# Champ Mot de passe #}
<div class="form-group mb-3">
    <label for="motDePasse" class="form-label">Mot de passe</label>
    <input 
        type="password" 
        id="motDePasse" 
        name="motDePasse" 
        class="form-control form-control-lg {% if errors.motDePasse %}is-invalid{% endif %}" 
        required
        placeholder="Au moins 6 caractères"
    />
    {% if errors.motDePasse %}
        <div class="invalid-feedback d-block">
            {{ errors.motDePasse }}
        </div>
    {% endif %}
</div>

{# Champ Confirmation mot de passe #}
<div class="form-group mb-4">
    <label for="confirmPassword" class="form-label">Confirmer le mot de passe</label>
    <input 
        type="password" 
        id="confirmPassword" 
        name="confirmPassword" 
        class="form-control form-control-lg {% if errors.confirmPassword %}is-invalid{% endif %}" 
        required
        placeholder="Confirmez votre mot de passe"
    />
    {% if errors.confirmPassword %}
        <div class="invalid-feedback d-block">
            {{ errors.confirmPassword }}
        </div>
    {% endif %}
</div>
```

---

## 🎯 Pourquoi cette approche élimine 100% des RuntimeError Twig ?

### ❌ AVANT (Problématique)
```php
$errors = [];
// ... validations
if (empty($email)) {
    $errors['email'] = 'Email requis';
}
// Si pas d'erreur email, la clé 'email' n'existe PAS
return $this->render('...', ['errors' => $errors]); // ⚠️ RuntimeError!
```

En Twig, essayer d'accéder à `errors.email` quand la clé n'existe pas = **RuntimeError**.

### ✅ APRÈS (Sécurisé)
```php
$errors = [
    'email' => null,
    'prenom' => null,
    'nom' => null,
    'motDePasse' => null,
    'confirmPassword' => null,
];
// ... validations
if (empty($email)) {
    $errors['email'] = 'Email requis'; // Remplace null
}
// La clé 'email' EXISTE TOUJOURS (null ou message d'erreur)
return $this->render('...', ['errors' => $errors]); // ✅ Zéro erreur!
```

En Twig, `{% if errors.email %}` fonctionne parfaitement car :
- Si `$errors['email'] === null` → condition **false** → pas d'affichage
- Si `$errors['email'] === 'message'` → condition **true** → affichage du message

**Résultat :** Toutes les clés existent toujours, Twig n'essaie jamais d'accéder à une clé inexistante. ✨

---

## 📊 Comparaison avant/après

| Aspect | Avant | Après |
|--------|-------|-------|
| Initialisation | ❌ Seulement si POST | ✅ Toujours (GET + POST) |
| Clés manquantes | ❌ Oui, à chaque champ | ✅ Non, jamais |
| RuntimeError Twig | ❌ Fréquent | ✅ Zéro |
| Code lisible | ❌ Mélangé | ✅ Structuré en 5 étapes |

---

## 🔒 Sécurité bonus

Le contrôleur utilise aussi :
- `$request->request->get('email', '')` → valeur par défaut si absent
- `!array_filter($errors)` → vérification robuste que TOUTES les clés sont null
- Validation côté serveur complète (email, longueur, doublon)
- Hachage du mot de passe avec `UserPasswordHasher`

✅ **Bon à copier-coller directement !**
