# ⚡ QUICK START - Installation 5 minutes

## Étape 1️⃣ Migration (2 min)

```bash
cd c:\wamp64\www\IRIS\Bachelor\HabitaLife
php bin/console doctrine:migrations:migrate
```

✅ Résultat attendu : `Migrations executed successfully`

---

## Étape 2️⃣ Données de test (optionnel, 3 min)

Via phpMyAdmin ou MySQL :
```bash
mysql -u root habitago < sql/test_paiements.sql
```

✅ Vous devriez avoir 8 paiements en base

---

## Étape 3️⃣ Démarrer (1 min)

```bash
symfony serve
# Ou
php -S 127.0.0.1:8000 -t public/
```

✅ Accédez à `http://localhost:8000`

---

## Étape 4️⃣ Tester (5 min)

1. **Se connecter** → `/connexion`
2. **Aller à Paiements** → `/payments`
3. **Voir le tableau** ✓
4. **Cliquer "Payer"** ✓
5. **Sélectionner mode** ✓
6. **Valider** ✓
7. **Vérifier statut = "paye"** ✓

---

## 🎉 C'est fait !

**Total : 5-10 minutes**

### Prochaines lectures
- Courte : `README_PAIEMENTS.md` (5 min)
- Complète : `DOCUMENTATION_PAIEMENTS.md` (20 min)
- Pour étendre : `EXEMPLES_CODE_PAIEMENTS.md` (30 min)

---

**Si erreur ?** → Voir `GUIDE_IMPLEMENTATION_PAIEMENTS.md` (section Dépannage)
