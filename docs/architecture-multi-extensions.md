# Architecture multi-extensions — analyse pour décision

> **Statut :** proposition · **Dernière mise à jour :** Mai 2026  
> Document de référence pour la phase **M9 — Multi-extensions** (voir [README](../README.md#11-roadmap-de-migration)).

---

## 1. Contexte

L'église Phila Cité d'Exaucement compte **plusieurs extensions** (Antananarivo, Toamasina, diaspora, etc.). L'objectif est de **centraliser** toute la formation sur **PHILA-CE** tout en laissant à chaque extension une **autonomie locale** (calendrier, staff, inscriptions, contenu).

Ce document décrit la vision cible, le modèle de données proposé, les phases d'implémentation, la complexité, les avantages/inconvénients et les questions à trancher avant développement.

---

## 2. État actuel de la plateforme

Aujourd'hui, le modèle est **centralisé mais mono-organisation** :

| Concept actuel | Rôle |
|----------------|------|
| `Program` / `Course` | Contenu pédagogique global |
| `AcademicSession` | Génération ECAP (dates, inscriptions) |
| `Profile.academic_session_id` | Session du fidèle |
| `Profile.eglise_attache` | Texte libre, **pas** une entité structurée |
| `EcapStaffAssignment` | Acteurs liés à une session, pas à une extension |
| `ProgramSetting` | Configuration cursus globale |

Il n'existe **pas encore** de notion d'**extension** comme entité à part entière.

---

## 3. Vision cible (6 points métier)

### 3.1 Inscription et origine du fidèle

1. L'église a plusieurs extensions ; tout est centralisé sur PHILA-CE.
2. À l'arrivée d'un étudiant, savoir **d'où il vient** (quelle extension).
3. S'il est de la **famille Phila** → il précise quelle extension ; sinon il peut continuer son inscription normalement.

### 3.2 Portail fidèle scopé par extension

4. Une fois connecté, s'il appartient à une extension :
   - voir le **calendrier** publié par cette extension ;
   - suivre les **cursus ouverts** par cette extension.

### 3.3 Administration et staff par extension

5. Admins, superviseurs, modérateurs, inspecteurs… sont **programmés par extension**.
6. Une extension peut **déléguer** son calendrier à une autre (le temps de s'organiser).
7. Quand elle reprend la main, elle configure **ses propres dates**.

### 3.4 Héritage de contenu pédagogique

8. Une extension peut **hériter** pour son compte les cours (quiz, TP…) bien organisés par une autre extension.
9. Les fidèles suivent ce contenu hérité une fois arrivés au cursus concerné.
10. Sinon, l'extension configure **son propre** cours, quiz, TP…
11. L'héritage inclut aussi les **TP** et les **contraintes** posées par l'extension fournisseur (progression linéaire, quiz obligatoire, etc.).

### 3.5 Q&R et profs par extension

12. Chaque extension configure si **ses profs alignés** peuvent répondre aux questions des fidèles.
13. Si oui → ils répondent et sont **identifiables** ; sinon → seuls les profs de l'extension héritée le font.

### 3.6 Page publique et inscriptions

14. Les ouvertures d'inscription sur la page publique précisent **quelle extension** a ouvert, va ouvrir ou a clôturé les inscriptions.

---

## 4. Schéma conceptuel

```
Inscription publique
      │
      ├── Famille Phila ? ── Oui ──► Choisir extension
      │                    └── Non ──► Inscription standard
      │
      └── Extension
            ├── Calendrier propre OU hérité / délégué
            ├── Cursus propres OU hérités
            ├── Staff (admin, superviseur, modérateur, inspecteur, enseignant)
            └── Fenêtres d'inscription par extension

Héritage
      Extension A ──hérite cours/quiz/TP/contraintes──► Extension B
      Extension A ──délègue calendrier temporairement──► Extension B
      Extension A ──config profs locaux Q&R oui/non──► Règle métier
```

---

## 5. Modèle de données proposé

### 5.1 Entité centrale : `Extension`

```sql
extensions
  id, name, slug, city, country
  is_phila_family (bool)
  is_active
  settings (json)
```

### 5.2 Rattachement utilisateur

```sql
profiles.extension_id       -- nullable si non Phila
profiles.is_phila_member    (bool)
```

### 5.3 Sessions et inscriptions par extension

```sql
academic_sessions.extension_id
registration_windows (
  extension_id, program_id,
  opens_at, closes_at, status
)
```

### 5.4 Héritage de contenu

```sql
extension_content_links
  consumer_extension_id    -- extension qui consomme
  provider_extension_id    -- extension qui fournit
  program_id / course_id   -- contenu hérité
  inherit_quizzes, inherit_tps, inherit_constraints (bool)
  effective_from, effective_until
```

### 5.5 Délégation calendrier

```sql
extension_calendar_policies
  extension_id
  use_own_calendar (bool)
  delegated_extension_id (nullable)
  can_override_dates (bool)
```

### 5.6 Staff par extension

```sql
extension_staff_assignments
  extension_id, user_id
  role (admin|supervisor|moderator|inspector|teacher)
  academic_session_id (optionnel)
  can_answer_questions (bool)
```

### 5.7 Règles Q&R profs (point métier 5)

```
extension_settings.allow_local_teachers_qna (bool)

Si false : seuls les profs de l'extension héritée répondent.
Si true  : profs locaux + hérités, identifiables par badge extension.
```

### 5.8 Service de résolution de contenu

Un service **`ExtensionContentResolver`** serait appelé partout où l'on charge cours / quiz / TP / contraintes :

- « Ce quiz vient-il de l'extension locale ou héritée ? »
- « Quelles contraintes `ProgramSetting` s'appliquent ? »

---

## 6. Phases d'implémentation recommandées

| Phase | Contenu | Effort estimé | Risque |
|-------|---------|---------------|--------|
| **M9.0 — Fondations** | Table `extensions`, `profile.extension_id`, admin CRUD extension | 1–2 sem. | Faible |
| **M9.1 — Inscription** | Choix Phila → extension ; page publique « Inscriptions ouvertes par… » | 2–3 sem. | Moyen |
| **M9.2 — Scoping portail** | Fidèle voit calendrier + cursus de **son** extension | 3–4 sem. | Moyen |
| **M9.3 — Staff par extension** | Acteurs, rôles, permissions Filament scopées | 3–4 sem. | Élevé |
| **M9.4 — Héritage contenu** | Cours / quiz / TP / contraintes hérités vs locaux | 4–6 sem. | **Très élevé** |
| **M9.5 — Calendrier délégué** | Extension A utilise calendrier de B temporairement | 2–3 sem. | Élevé |
| **M9.6 — Q&R multi-extensions** | Config profs locaux vs hérités | 2 sem. | Moyen |

**Effort total réaliste : 4 à 6 mois** (1 développeur expérimenté), en livrant par phases.

---

## 7. Complexité technique

**Élevée** — impact transversal sur presque tout le système :

1. **Chaque requête** doit filtrer par `extension_id` (fidèle, staff, admin).
2. **L'héritage** implique une résolution de contenu à chaque chargement pédagogique.
3. **Les notifications** (Q&R, corrections quiz, TP) doivent cibler le bon staff selon extension + règles d'héritage.
4. **L'admin Filament** : super-admin voit tout ; admin extension ne voit que son périmètre.
5. **Migration des données existantes** : rattacher sessions / profils actuels à une extension « PHILA Centrale » par défaut.

### Approche technique recommandée

**Single database + `extension_id`** sur les entités concernées.

Ne **pas** partir sur multi-base ou multi-tenant lourd (ex. Stancl Tenancy), sauf si des centaines d'extensions doivent être isolées juridiquement.

---

## 8. Avantages

| Avantage | Détail |
|----------|--------|
| **Centralisation réelle** | Un seul déploiement, une base de vérité, statistiques globales |
| **Autonomie locale** | Chaque extension gère dates, staff, inscriptions |
| **Réutilisation du contenu** | Extension naissante hérite d'un ECAP mature pendant qu'elle s'organise |
| **Traçabilité** | Origine fidèle, calendrier et cursus suivis clairement identifiés |
| **Évolutivité** | Nouvelle extension = nouvelle ligne, pas nouveau projet |

---

## 9. Inconvénients et risques

| Risque | Impact |
|--------|--------|
| **Complexité cognitive** | L'admin doit comprendre héritage vs local |
| **Bugs de scoping** | Fuite de données entre extensions si mal filtré |
| **Héritage de contraintes** | Modifier l'extension fournisseur impacte les consommatrices |
| **Q&R ambiguë** | Mauvaise config → questions sans réponse |
| **Coût de maintenance** | Chaque nouvelle feature doit penser « extension » |
| **Migration** | Données actuelles à restructurer proprement |

---

## 10. Options de déploiement

### Option A — Big bang

Tout développer d'un coup.

**Verdict :** déconseillé — long délai sans livrable intermédiaire, risque élevé.

### Option B — Phasé (recommandé)

Commencer par **M9.0 + M9.1 + M9.2** :

- Extensions + inscription + calendrier / cursus scopés
- **Sans héritage** au début (chaque extension configure son contenu)
- Livrable utile en ~2 mois

Puis **M9.4 (héritage)** uniquement quand une 2ᵉ extension est prête à consommer le contenu de la 1ʳᵉ.

**Verdict :** recommandé.

### Option C — Minimal (extension = label)

Extension = filtre + branding + inscriptions ; contenu 100 % partagé.

**Verdict :** rapide (3–4 sem.) mais ne couvre pas l'héritage TP / quiz / contraintes.

---

## 11. Questions à trancher avant développement

1. Une extension peut-elle avoir **plusieurs sessions ECAP actives** en parallèle ?
2. L'héritage est-il **tout ou rien** (tout le cursus ECAP) ou **module par module** ?
3. Si extension A hérite de B puis crée son propre quiz, **lequel prime** ?
4. Les **inspecteurs** ont-ils un rôle distinct du superviseur dans vos processus ?
5. Faut-il un **super-admin PHILA** (toutes extensions) vs **admin local** (une extension) ?

---

## 12. Lien avec l'existant PHILA-CE

Le code actuel est **bien positionné** pour M9.0–M9.2 :

| Existant | Évolution |
|----------|-----------|
| `AcademicSession` | Ajouter `extension_id` |
| `EcapStaffAssignment` | Scoper par extension |
| `ProgramSetting` | Par extension ou hérité |
| `RegistrationAvailabilityService` | Filtrer par extension |
| `VacationQuestionService` / corrections quiz | Étendre le scoping staff |

Le morceau le plus coûteux reste **M9.4 — héritage de contenu** (`ExtensionContentResolver`).

---

## 13. Décision recommandée (proposition)

| Décision | Recommandation |
|----------|----------------|
| Démarrer quand ? | Après stabilisation M6 (acteurs ECAP) et M1 (ProgramAccess) |
| Première livraison | M9.0 + M9.1 + M9.2 sans héritage |
| Héritage contenu | Phase séparée (M9.4) une fois 2 extensions identifiées |
| Technique | Single DB + `extension_id`, pas multi-tenant lourd |

---

**Référence :** [README — Roadmap de migration](../README.md#11-roadmap-de-migration)
