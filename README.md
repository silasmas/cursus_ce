# PHILA-CE — Plateforme digitale de formation

> **Cité d'Exaucement** · *Cultiver la vie de Christ*  
> Plateforme numérique de formation et de suivi des fidèles.

Ce document est le **fil conducteur** du projet. Il reprend le [Cahier des Charges — Plateforme Phila Cité d'Exaucement](file:///c:/Users/ZBOOK/Documents/client/Phila%20CE/Cahier%20Des%20Charges%20-%20Plateforme%20Phila%20Cit%C3%A9%20D'exaucement.pdf) et décrit la **vision cible** validée, l’**existant** et la **roadmap**.

Site de référence : [ce.church](https://ce.church/)

---

## Sommaire

1. [Contexte et objectifs](#1-contexte-et-objectifs)
2. [Vision produit — deux axes](#2-vision-produit--deux-axes)
3. [Cursus indépendants (hors vacation ECAP)](#3-cursus-indépendants-hors-vacation-ecap)
4. [ECAP — vacation par génération](#4-ecap--vacation-par-génération)
5. [Inscription, page publique et espace membre](#5-inscription-page-publique-et-espace-membre)
6. [Acteurs et rôles](#6-acteurs-et-rôles)
7. [Schéma de données cible](#7-schéma-de-données-cible)
8. [État actuel vs cible](#8-état-actuel-vs-cible)
9. [Architecture technique](#9-architecture-technique)
10. [Avancement et journal](#10-avancement-et-journal)
11. [Roadmap de migration](#11-roadmap-de-migration)
12. [Installation et développement](#12-installation-et-développement)

**Documents complémentaires :**

- [Architecture multi-extensions — analyse pour décision](docs/architecture-multi-extensions.md) *(phase M9)*

**Légende des statuts :** ✅ Fait · 🟡 En cours / modèle prêt · ⬜ Cible / à faire

---

## 1. Contexte et objectifs

L'église **Phila Cité d'Exaucement** organise des cursus d'affermissement en présentiel et en ligne. La plateforme **PHILA-CE** centralise les parcours, le suivi pédagogique et l'administration.

| Objectif | Description |
|----------|-------------|
| Digitaliser | Porter les parcours en ligne là où c'est pertinent |
| Flexibilité | Accès selon cursus, session ECAP et mode de suivi |
| Suivi personnalisé | Espace membre configuré à l'inscription + validation admin |
| Former les leaders | Enseignants, modérateurs, mentors, inspecteurs |
| Centraliser | Gestion académique (ECAP) et pastorale (autres cursus) |

> *« Tu n'es pas ici par hasard. Dieu t'attend. »* — [ce.church](https://ce.church/)

---

## 2. Vision produit — deux axes

Les **5 cursus** coexistent sur une même plateforme, mais **ne partagent pas le même modèle de fonctionnement**.

```
┌──────────────────────────────────────────────────────────────────────────┐
│  AXE A — Cursus indépendants (chacun vit sa propre logique)               │
│  Connaissez-vous PHILA · Métamorpho · Gifted · Eyano                      │
│  → Accès selon inscription + ouverture admin + validation « déjà passé »   │
└──────────────────────────────────────────────────────────────────────────┘

┌──────────────────────────────────────────────────────────────────────────┐
│  AXE B — ECAP uniquement (École d'Apolos — vacation structurée)           │
│  Génération (ex. 5ᵉ génération) · calendrier · périodes · groupes          │
│  Inscription en ligne OU présentiel (exclusif) · acteurs de vacation      │
└──────────────────────────────────────────────────────────────────────────┘
```

**Règle fondamentale :** ouvrir une **session ECAP** n'ouvre **pas** automatiquement les autres cursus. En revanche, si un autre cursus est **ouvert** au moment de l'inscription, le fidèle peut y accéder ; sinon il reste **en attente** pour ce cursus.

---

## 3. Cursus indépendants (hors vacation ECAP)

### 3.1 Les cinq cursus

| # | Cursus | Slug | Rôle |
|---|--------|------|------|
| 1 | Connaissez-vous PHILA | `connaissez-phila` | Accueil, vision, valeurs |
| 2 | Métamorpho | `metamorpho` | Accompagnement spirituel (mentor) |
| 3 | ECAP | `ecap` | Formation biblique structurée — **voir axe B** |
| 4 | École des dons (Gifted) | `gifted` | Identification des dons |
| 5 | Eyano | `eyano` | École de prière |

### 3.2 Règles d'accès par cursus (paramétrage admin)

À la création / configuration d'un cursus :

| Règle | Effet |
|-------|--------|
| **Obligatoire** | Toujours proposé / requis selon politique église |
| **Choix à l'inscription** | Le fidèle coche s'il souhaite le suivre |
| **Optionnel** (flag) | Peut être ignoré selon profil |
| **Ouvert** (flag admin) | Accessible maintenant sur la plateforme |
| **Programmé** | Date d'ouverture future — affichage « dans X jours » |

### 3.3 Espace membre personnalisé

L'inscription collecte notamment : déjà reçu Jésus, baptême, **déjà fait Métamorpho**, **déjà suivi ECAP**, etc.

- Le **tableau de bord** n'affiche que les cursus **pertinents** pour ce profil.
- L'**administration** peut **valider** qu'une personne a **déjà passé** un cursus (dispense / statut acquis) sans refaire le parcours en ligne.

### 3.4 Statuts cible par cursus (membre)

| Statut | Signification |
|--------|----------------|
| `en_attente` | Cursus pas encore ouvert pour ce membre |
| `ouvert` | Peut commencer ou continuer |
| `en_cours` | Progression enregistrée |
| `acquis` | Terminé ou validé par admin |
| `dispense` | Admin a confirmé un parcours antérieur |

---

## 4. ECAP — vacation par génération

> **Toute la structuration session / calendrier / périodes / groupes / quiz de module / acteurs de vacation concerne uniquement ECAP.**

### 4.1 Session (génération)

- Nommée par **génération** (ex. *5ᵉ génération*).
- **Calendrier global** : date de début et de fin de la session.
- Dans cette fenêtre : **début / fin de chaque module** (cours) du programme ECAP.
- Chaque **module** contient des **chapitres**.

### 4.2 Quiz de fin de module (obligatoire pour passer)

| Paramètre | Valeur |
|-----------|--------|
| Nombre de questions | **5** |
| Seuil de réussite | **80 %** |
| Lien pédagogique | Chaque question est rattachée à un **chapitre** — en cas de mauvaise réponse, renvoi vers le cours concerné |

Le passage au **module suivant** est conditionné par la réussite du quiz (et les règles modérateur pour les grands TP — voir acteurs).

### 4.3 Périodes dans la session

Après création de la session et de son calendrier, l'admin définit des **périodes** (fenêtres dans la grande session), par exemple :

- Période des **cours**
- Période des **travaux de fin d'études**
- Période des **défenses**

On **affecte** ensuite des contenus à chaque période : formations, quiz, examens de session, etc.

### 4.4 Inscription ECAP — mode en ligne / présentiel

Lors de l'inscription à une **génération ECAP**, le fidèle choisit un **mode de suivi exclusif** :

| Mode | Comportement |
|------|----------------|
| **En ligne** | Accès au parcours ECAP sur `/mon-espace` (lecteur, quiz, TP, groupes en ligne, etc.). **Non compté** comme présentiel. |
| **Présentiel** | Suivi géré hors parcours en ligne actif. **Pas d'accès** aux actions ECAP en ligne (progression, quiz, TP en ligne). |

**Affichage côté fidèle (option retenue) :** le cursus ECAP reste **visible** en espace membre avec un badge du type **« Présentiel — accès en ligne désactivé »** (lecture seule / message explicite), afin que le fidèle comprenne son statut.

**Bascule admin :** un administrateur peut **changer le mode** (ex. présentiel → en ligne) si la situation l'exige ; l'accès en ligne s'active alors selon les règles de la session.

```
Inscription ECAP
      │
      ├── En ligne ──────► Parcours ECAP actif sur la plateforme
      │
      └── Présentiel ────► ECAP visible mais lecture seule en ligne
                           (admin peut basculer vers En ligne)
```

### 4.5 Groupes

| Type | Description |
|------|-------------|
| **Groupe de vacation** | Créé automatiquement de façon **équitable et équilibrée** — groupe d'étude partagé toute la session |
| **Small group** | Groupe de fin d'études (3 sujets à date fixée dans le calendrier). Affectation selon **niveau de module atteint** et **présence**. `capacité_min` / `capacité_max` définies à la création de session |
| **Groupe des inspecteurs** | Ouvriers dédiés — **rapport journalier** sur les 3 dirigeants (enseignant, superviseur, modérateur). Modèle de rapport défini par le **service académique** (admin) |

### 4.6 Schéma ECAP (calendrier)

```
Génération ECAP (5ᵉ génération)
│
├── Inscriptions (ouverture / clôture / countdown public)
├── Calendrier session [début ───────────────────────── fin]
│   ├── Module 1 [début ── fin] → chapitres → quiz fin module (5 Q, 80 %)
│   ├── Module 2 [début ── fin] → ...
│   └── Module N
├── Périodes
│   ├── Cours
│   ├── Travaux de fin d'études
│   └── Défenses
├── Groupes vacation → small groups
└── Rôles vacation (enseignant, superviseur, modérateur, inspecteurs)
```

---

## 5. Inscription, page publique et espace membre

### 5.1 Inscription globale

| Étape | Exigence |
|-------|----------|
| Profil | Données personnelles et spirituelles (Jésus, baptême, parcours déjà suivis…) |
| Choix cursus | Selon cursus ouverts et questions d'inscription |
| **Règlement intérieur** | **Lu et approuvé** avant la fin de l'inscription |
| Inscription ECAP | Si session ouverte : choix **en ligne** ou **présentiel** |

### 5.2 Page publique (`/`)

| Affichage | Détail |
|-----------|--------|
| Session ECAP | Session **ouverte** ou **prochaine** |
| Countdown | Temps restant (jours / heures) avant **clôture des inscriptions** |
| Session programmée | Statut « programmée » + « ouverture dans X jours » |

### 5.3 Espace membre (`/mon-espace`)

- Sidebar / cartes cursus selon **droits réels** (pas une seule chaîne rigide 1→2→3).
- ECAP : selon inscription session + **mode en ligne / présentiel**.
- Autres cursus : selon **ouverture** et **validation admin**.

---

## 6. Acteurs et rôles

### 6.1 Acteurs ECAP (vacation)

| Acteur | Mission |
|--------|---------|
| **Enseignant** | Enseigne, répond aux questions, dépose le **TP modèle**. Ses réponses sont visibles par **tous les enseignants** (commentaires entre pairs). |
| **Superviseur** | Gestionnaire de la vacation (étudiants, ouvriers) ; répond aux questions qui lui sont adressées. |
| **Modérateur** | Facilitateur de classe ; dirige les TP ; corrige les **grands TP** ; **condition de passage** au module suivant ; gère le **cahier de méditation** (modèle → remplissage → correction) ; gère groupes vacation et small groups. |
| **Inspecteurs** | Rapport journalier sur les 3 dirigeants ; cadre défini par le service académique. |

### 6.2 Autres rôles plateforme

| Rôle | Périmètre | Statut impl. |
|------|----------|--------------|
| **Fidèle** | Portail `/mon-espace` | ✅ |
| **Mentor** | Cursus **Métamorpho** (hors logique vacation ECAP) | 🟡 Portail `/mentor` |
| **Administrateur** | Filament `/admin` | ✅ |
| Enseignant / Superviseur / Modérateur ECAP | Vacation ECAP | ⬜ Cible |

> Le **mentor Métamorpho** et le **modérateur ECAP** sont des rôles **distincts**.

---

## 7. Schéma de données cible

### 7.1 Cursus et accès membre

```
User
 └── Profile (choix inscription)
 └── ProgramAccess
       ├── program_id
       ├── status (pending | open | in_progress | completed | waived)
       ├── source (registration | admin_validated)
       └── validated_by / validated_at

Program
 ├── rules: mandatory, optional_at_registration, is_open, scheduled_open_at
 └── Course → CourseModule → Chapter → ContentBlock
```

### 7.2 ECAP — vacation

```
GenerationSession          # ex. 5ᵉ génération
 ├── starts_at / ends_at
 ├── registration_opens_at / registration_closes_at
 ├── status (draft | scheduled | open | closed)
 ├── small_group_min / small_group_max
 ├── ModuleSchedule       # début/fin par module
 ├── SessionPeriod          # cours, TFE, défenses…
 ├── PeriodContent        # contenus affectés
 └── LearningGroup        # vacation | small_group | inspectors

SessionEnrollment
 ├── user_id
 ├── generation_session_id
 ├── delivery_mode: online | onsite    # exclusif
 ├── delivery_mode_changed_at          # traçabilité bascule admin
 └── roi_accepted_at (si applicable)

ModuleExitQuiz             # fin de module ECAP
 ├── 5 questions, 80 %, chaque Question → chapter_id
```

### 7.3 Existant réutilisable

| Modèle actuel | Usage cible |
|---------------|-------------|
| `Program`, `Course`, `CourseModule`, `Chapter` | Contenu pédagogique |
| `AcademicSession` | À faire évoluer → `GenerationSession` ECAP |
| `LearningGroup` | + types vacation / small / inspectors |
| `Enrollment`, `ChapterProgress` | Progression (filtrée par mode ECAP en ligne) |
| `Assessment`, `Question` | Quiz module, examens |
| `MentorAssignment` | Métamorpho uniquement |
| `AssignmentSubmission` | TP fidèles / mentors |

---

## 8. État actuel vs cible

| Domaine | Aujourd'hui (Mai 2026) | Cible validée |
|---------|-------------------------|---------------|
| Cursus | 5 programmes, déblocage séquentiel global | Cursus **indépendants**, accès par inscription + admin |
| ECAP | `AcademicSession` basique, pas de génération complète | Vacation **génération**, calendrier, périodes, groupes |
| Inscription ECAP | Partielle | Mode **en ligne / présentiel** + bascule admin |
| Quiz | Lié chapitre ; actuellement non bloquant globalement | Quiz **fin de module** ECAP : 5 Q, 80 %, lien chapitre |
| Mentor | Portail mentor, TP, RDV | Reste sur **Métamorpho** |
| Page publique | Landing | Session ECAP + countdown inscriptions |
| ROI | ⬜ | Obligatoire avant fin inscription |
| Acteurs ECAP | ⬜ | Enseignant, superviseur, modérateur, inspecteurs |

### Fonctionnalités déjà livrées (référence rapide)

| Zone | Livrable |
|------|----------|
| Portail fidèle | Landing, OTP, inscription, dashboard, lecteur cours, 5 cursus sidebar |
| Mentor | `/mentor`, formulaires RDV/TP, clôture accompagnement, notifications |
| Admin | Filament, ressources contenus, TP mentors à valider, Shield |
| Notifications | Cloche portail, e-mails (`PortalNotificationService`) |

---

## 9. Architecture technique

### 9.1 Stack

| Couche | Technologie |
|--------|-------------|
| Backend | Laravel 13 |
| Admin | Filament 5 + Shield |
| Frontend fidèle | Inertia.js + React + Vite |
| Styles | Tailwind CSS v4 |
| Auth fidèle | OTP e-mail |
| Permissions | Spatie Permission |

### 9.2 Design system

| Token | Valeur |
|-------|--------|
| `--color-phila-black` | `#0A0A0A` |
| `--color-phila-orange` | `#F39200` |
| Typographies | Plus Jakarta Sans + Inter |

### 9.3 Dossiers clés

```
app/
├── Filament/Resources/
├── Http/Controllers/Auth|Student|Mentor/
├── Models/
└── Services/Student|Mentor|Portal|Admin/

resources/js/Pages/     # Landing, Auth, Dashboard, Course, Mentor
config/cursus.php       # Définition des 5 cursus (slugs, libellés)
```

---

## 10. Avancement et journal

> Dernière mise à jour : **Mai 2026**

### Vue d'ensemble

| Phase | Avancement | Description |
|-------|------------|-------------|
| Fondations | ████████░░ 80 % | Laravel, Filament, modèles |
| Portail fidèle | ███████░░░ 70 % | Auth, dashboard, lecteur |
| Portail mentor | ██████░░░░ 60 % | RDV, TP, clôture, stats |
| **Cible ECAP vacation** | ░░░░░░░░░░ 0 % | Génération, modes, périodes, acteurs |
| **Cible accès cursus** | ██░░░░░░░░ 15 % | Inscription partielle, pas encore ProgramAccess |

### Journal des itérations

| Date | Itération |
|------|-----------|
| Mai 2026 | Setup admin Filament, 36+ ressources, Shield |
| Mai 2026 | Portail fidèle : Landing, OTP, inscription, Mon Espace, 5 cursus |
| Mai 2026 | Lecteur de cours, progression chapitres |
| Mai 2026 | Portail mentor : RDV multi-mentorés, TP, clôture, notifications + e-mails |
| Mai 2026 | **README restructuré** — vision cursus indépendants + ECAP vacation + modes inscription |
| Mai 2026 | **M4** — Périodes ECAP (cours, TFE, défenses), affectation contenus admin, garde portail |
| Mai 2026 | **M5** — Quiz fin de module ECAP : 5 Q, 80 %, révision chapitre, blocage module suivant |
| Mai 2026 | **M6 (partiel)** — Acteurs ECAP, Q&R, TP, corrections quiz rédigées, verrou collaboratif |
| Mai 2026 | **Proposition M9** — Document [architecture multi-extensions](docs/architecture-multi-extensions.md) pour décision produit |

## 11. Roadmap de migration

| Phase | Contenu | Priorité |
|-------|---------|----------|
| **M1** | `ProgramAccess` + règles cursus + validation admin « déjà passé » | Haute |
| **M2** | `GenerationSession` ECAP + calendrier modules + page publique (countdown) | Haute |
| **M3** | Inscription ECAP : `delivery_mode` online/onsite + UI lecture seule + bascule admin | Haute |
| **M4** | Périodes + affectation contenus | ✅ Livré (admin + garde portail) |
| **M5** | Quiz fin de module ECAP (5 Q, 80 %, lien chapitre) | ✅ Livré |
| **M6** | Rôles vacation + Q&R enseignants + modérateur (TP, méditation) | Moyenne |
| **M7** | Groupes auto vacation + small groups | Moyenne |
| **M8** | Inspecteurs + rapports journaliers | Basse |
| **M9** | **Multi-extensions** — centralisation par extension, héritage contenu, calendrier délégué | Haute (long terme) |

### Phase M9 — Multi-extensions

> **Document détaillé :** [docs/architecture-multi-extensions.md](docs/architecture-multi-extensions.md)

Vision : plusieurs extensions Phila (Antananarivo, diaspora, etc.) sur **une seule plateforme**, avec autonomie locale (calendrier, staff, inscriptions) et possibilité d'**hériter** du contenu pédagogique d'une autre extension.

| Sous-phase | Contenu | Priorité |
|------------|---------|----------|
| **M9.0** | Entité `Extension`, rattachement profil, admin CRUD | Haute |
| **M9.1** | Inscription : famille Phila → choix extension ; page publique par extension | Haute |
| **M9.2** | Portail fidèle scopé (calendrier + cursus de son extension) | Haute |
| **M9.3** | Staff par extension (admin, superviseur, modérateur, inspecteur) | Moyenne |
| **M9.4** | Héritage cours / quiz / TP / contraintes (`ExtensionContentResolver`) | Moyenne (après 2 extensions) |
| **M9.5** | Délégation calendrier entre extensions | Basse |
| **M9.6** | Q&R : profs locaux vs hérités (config par extension) | Basse |

**Recommandation :** démarrer par **M9.0 → M9.2 sans héritage** ; reporter **M9.4** jusqu'à ce qu'une seconde extension consomme le contenu de la première. Voir le document pour complexité, avantages, inconvénients et questions de décision.

---

## 12. Installation et développement

### Prérequis

- PHP 8.3+, Composer, Node.js 20+, MySQL ou SQLite

### Installation

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan db:seed
php artisan db:seed --class=FormationContentSeeder

php artisan serve   # Terminal 1
npm run dev         # Terminal 2
```

### URLs

| URL | Description |
|-----|-------------|
| `/` | Page publique |
| `/inscription` | Inscription |
| `/connexion` | Login OTP |
| `/mon-espace` | Espace membre |
| `/mentor` | Espace mentor |
| `/admin` | Administration |

### Comptes de test

| Rôle | E-mail | Accès |
|------|--------|-------|
| Super admin | `admin@example.com` | `/admin` (mot de passe : `password`) |
| Fidèle | via inscription | OTP (`MAIL_MAILER=log` en local) |

### Variables d'environnement

```env
APP_NAME="PHILA-CE"
APP_URL=http://localhost:8000
MAIL_MAILER=smtp
MAIL_FROM_ADDRESS="noreply@ce.church"
```

---

## Contribution

1. Mettre à jour ce README à chaque livraison significative.
2. Respecter les conventions du projet (Pint, commentaires services, 2 espaces).
3. Ne pas committer `.env` ni de secrets.

---

**PHILA – Cité d'Exaucement** · © 2026 PHILA-CE
