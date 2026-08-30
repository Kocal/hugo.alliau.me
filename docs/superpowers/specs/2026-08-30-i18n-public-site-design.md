# Design — Site public multilingue (EN défaut, FR sous /fr)

Date: 2026-08-30. Branche: `feat/i18n-public-site`.

## Objectif

Rendre la partie publique du site bilingue: **Anglais (`en`) par défaut à la racine, Français (`fr`) sous le préfixe `/fr`**. Stack: Symfony 8.1, PHP 8.5, DDD (bounded contexts Blog/CV/Places/Shared), Twig, EasyAdmin, PostgreSQL, cache HTTP via Cloudflare, sitemap presta.

### Schéma d'URL cible

| Page             | EN (défaut)          | FR                      |
| ---------------- | -------------------- | ----------------------- |
| Home             | `/`                  | `/fr`                   |
| CV               | `/cv`                | `/fr/cv`                |
| Blog (index)     | `/blog`              | `/fr/blog`              |
| Blog (article)   | `/blog/posts/{slug}` | `/fr/blog/posts/{slug}` |
| Blog (tags list) | `/blog/tags`         | `/fr/blog/tags`         |
| Blog (tag)       | `/blog/tags/{tag}`   | `/fr/blog/tags/{tag}`   |
| Places           | `/places`            | `/fr/lieux`             |

**Jamais préfixé / hors i18n**: `/admin/*`, `/login`, `/logout`, `/sitemap.xml`, `/_*` (profiler), et `/blog/rss.xml` (flux RSS global unique, tous les posts).

## Décisions actées

1. **Blog**: une colonne `locale` sur `Post` (1 article = 1 langue). Les listes `/blog` et `/fr/blog` montrent **tous** les articles, chacun avec un drapeau 🇬🇧/🇫🇷. Le chrome (nav, titres, dates) suit la locale de l'URL. Slug rendu **unique** (index en base).
2. **CV**: contenu traduisible via **tables de traduction** (voir §4). Fallback EN.
3. **Places**: route localisée (`/places` <-> `/fr/lieux`) + libellés d'UI. Aucun changement d'entité (adresses = data Google brute, badges `place_type.*` déjà traduits).
4. **Sélecteur de langue**: visible dans le **header**.
5. **Article sous le préfixe de sa langue uniquement**: un article EN vit sous `/blog/posts/{slug}`, un FR sous `/fr/blog/posts/{slug}`. Le contrôleur redirige en **301** vers la locale de l'article si `_locale` ne correspond pas. Canonical = URL de la locale de l'article. Pas de hreflang alternate sur les articles (contenu mono-langue).

## 1. Routing (Symfony localized routing)

- Chemins localisés **par route**, en tableau dans `#[Route(path: [...])]`. Les **noms de routes** (enums `App\<Ctx>\Domain\Data\Route`) **ne changent pas** -> `path('app.cv.index')` devient locale-aware automatiquement (Symfony choisit la variante selon `_locale` de la requête). La majorité des `path()`/`url()` des templates fonctionnent sans modification.
- Exemples de paths:
    - Home: `['en' => '/', 'fr' => '/fr']`
    - CV: `['en' => '/cv', 'fr' => '/fr/cv']`
    - Blog home: `['en' => '/blog', 'fr' => '/fr/blog']`
    - Post view: `['en' => '/blog/posts/{slug}', 'fr' => '/fr/blog/posts/{slug}']`
    - Tags list / view: idem avec préfixe `/fr`
    - Places: `['en' => '/places', 'fr' => '/fr/lieux']`
    - RSS: **inchangé**, path unique `/blog/rss.xml` (pas de variante localisée).
- Symfony génère en interne `nom.en` (défaut `_locale=en`) et `nom.fr` (`_locale=fr`); `/` matche EN, `/fr` matche FR. Pas de subscriber custom: la locale du translator suit `_locale` automatiquement.
- Choix "tableaux explicites par route" plutôt que préfixe global d'import: ≈7 routes seulement, zéro risque pour `/admin`, `/login`, `/logout`, RSS, sitemap.
- **À auditer**: les chemins en dur dans les templates (`prefetch('/cv')`, `prefetch('/blog')` dans `home.html.twig:21-22`, liens `/blog` du footer/menu) -> remplacer par `path()` pour hériter de la locale.

## 2. Catalogue de traductions (ICU YAML)

- Domaine unique `translations/messages+intl-icu.{en,fr}.yaml`, clés imbriquées: `nav.*`, `home.*`, `blog.*`, `cv.*`, `places.*`, `common.*`. Le `place_type.*` existant reste.
- **Contenu à extraire**: `home.html.twig` (titre, meta, "About me", 3 paragraphes bio), headings blog/places/cv, catégories de skills CV (Backend/Frontend/Web perfs), "Now", libellés filtre pays Places ("Country"/"All countries"), labels de menu (`services.yaml:13-24`), badges CV (voir §4), footer.

### 2.1 Liens dans le texte — helper `t_html` (placeholders `<0>…</0>`)

- Extension Twig `t_html(key, links = [], params = {}, domain = 'messages')`:
    1. `trans(key, params, domain)` sur la locale courante.
    2. Remplace chaque `<n>texte</n>` par `<a href="{links[n].href}" {links[n].attrs}>texte</a>`. Le `texte` est échappé; la balise `<a>` est construite par le helper (pas d'HTML utilisateur injecté) -> sûr, sortie marquée `raw`.
- Exemple: `home.about.p2 = "Have a look at my <0>CV</0> or my <1>blog</1>."`, appel `{{ t_html('home.about.p2', [{href: path('app.cv.index')}, {href: path('app.blog.home')}]) }}`.
- Équivalent propre du système `<0>` de baksla.sh.

## 3. Blog — colonne `locale`

### 3.1 Schéma & migration

- Ajouter `locale VARCHAR(5) NOT NULL` sur `blog_post`.
- Backfill déterministe: `fr` pour les slugs `2021-04-26-migration-de-notre-stack-de-developpement-vers-docker` et `une-meilleure-architecture-pour-vous-twig-components-de-symfony-ux`; `en` pour les 13 autres.
- Ajouter un **index UNIQUE sur `slug`** (absent aujourd'hui).

### 3.2 Domaine

- Enum partagé `App\Shared\Domain\Data\Locale` (`EN = 'en'`, `FR = 'fr'`), réutilisé par Post et les tables de traduction CV.
- `Post`: champ `locale` (type enum), getter/setter, exposé dans EasyAdmin (ChoiceField EN/FR).
- Helper d'affichage `locale -> emoji drapeau` (🇬🇧/🇫🇷): filtre/fonction Twig `locale_flag(locale)`.

### 3.3 Affichage & contrôleurs

- Liste blog (`blog/home.html.twig`) et header d'article: afficher le drapeau depuis `post.locale`. Contenu du post inchangé.
- `Post/ViewController`: si `request._locale !== post.locale`, **301** vers `path('app.blog.posts.view', {slug, _locale: post.locale})`. Sinon rendu normal. Canonical = URL propre (self, locale du post).
- Repository: **pas** de filtre locale sur la liste (tous les posts). Tri par `publishedAt` desc inchangé.
- RSS: un seul flux `/blog/rss.xml`, tous les posts, inchangé.

## 4. CV — tables de traduction

### 4.1 Schéma

- Table `cv_professional_experience_translation`: `id` (uuid), `experience_id` (FK -> cv_professional_experience, cascade delete), `locale` (varchar 5), `job_name` (varchar 255), `description` (text), `badges` (jsonb). Contrainte **UNIQUE(experience_id, locale)**.
- Table `cv_project_translation`: `id` (uuid), `project_id` (FK -> cv_project, cascade delete), `locale`, `description` (text). **UNIQUE(project_id, locale)**.
- Champs **neutres** restant sur les entités: expérience -> `company`, `url`, `start_date`, `end_date`; projet -> `name`, `url`, `date`, `tech_stack`, `visible`.
- Migration data: pour chaque ligne existante, créer la traduction `fr` à partir des colonnes actuelles (`job_name`/`description`/`badges` pour l'expérience, `description` pour le projet), **puis** `ALTER TABLE ... DROP COLUMN` sur les colonnes déplacées. La traduction `en` est à rédiger (voir §4.4).

### 4.2 Domaine

- Entités `ProfessionalExperienceTranslation` et `ProjectTranslation` (OneToMany depuis l'entité parente, `indexBy` locale).
- Méthode `translate(Locale $locale): ?Translation` sur chaque entité, avec **fallback**: locale demandée -> `en` -> `fr` -> première dispo.
- Twig: `experience.translate(app.request.locale).description`, etc. (via un helper si besoin pour le fallback).

### 4.3 EasyAdmin

- Gérer les traductions via `CollectionField` (formulaire embarqué, 2 lignes en/fr) sur les CRUD Experience et Project.

### 4.4 Contenu

- Les descriptions FR actuelles (rédigées récemment) deviennent les traductions `fr`.
- **Rédiger les traductions `en`** des 5 expériences + 13 projets (traduction depuis le FR, via l'agent d'édition). Étape du plan. Tant que `en` manque, le fallback affiche le FR.
- Chrome CV traduit via catalogue (headings, catégories de skills, "Now", meta). Les noms de skills (PHP, Symfony…) restent littéraux.

## 5. Places

- Route `/places` <-> `/fr/lieux`.
- Extraire les libellés d'UI (`places/home.html.twig`): titre/heading, filtre pays ("Country"/"Pays", "All countries"/"Tous les pays" — actuellement en dur en FR).
- Badges `place_type.*` déjà traduits. Aucun changement d'entité.

## 6. `base.html.twig`, `<head>` & SEO

- `<html lang="{{ app.request.locale }}">`: déjà en place.
- **hreflang**: sur les pages bilingues (home, cv, blog index, tags list/view, places), émettre `<link rel="alternate" hreflang="en">`, `hreflang="fr"`, `hreflang="x-default"` (= en), via `path(route, params|merge({_locale: 'en'}))` / `{_locale:'fr'}`. **Pas** de hreflang sur les articles (mono-langue).
- **Canonical**: par locale (URL courante). Article: canonical = URL de la locale du post.
- **Sélecteur de langue (header)**: composant Twig qui calcule l'alternative de la page courante `path(app.current_route, app.current_route_parameters|merge({_locale: <autre>}))`. Cas particulier article (mono-langue): le switcher pointe vers l'index blog de la locale cible (l'article n'existe pas dans l'autre langue).
- `json_ld`: `jobTitle` et libellés peuvent être passés au catalogue (optionnel, faible priorité).

## 7. Sitemap (presta)

- Retirer l'auto-`'sitemap' => true'` des routes localisables (sinon seule la locale par défaut est émise) et enregistrer via listener.
- Un `SitemapListener` (Shared, ou étendre celui du Blog) qui, pour chaque page localisable (home, cv, blog index, tags list, places, chaque tag), émet les **2 locales** avec alternates hreflang (`GoogleMultilangUrlDecorator`).
- Articles: émis **une fois** à l'URL canonique (locale de l'article).

## 8. Cache HTTP — purge simple (tout purger)

Décision: **ne pas** optimiser par locale. On garde le mécanisme actuel et on purge large.

- Seul changement: `SymfonyHttpCache::normalize()` boucle sur les **locales activées** et génère l'URL absolue de chaque variante (`_locale`), pour que les pages `/fr/...` soient purgées elles aussi.
- `getCacheItems()` (Post, Place) **reste inchangé** — c'est `normalize()` qui fan-out sur les locales.
- Pas de nouvelle API `CacheItem`, pas de logique par-article, pas de changement d'ETag (les URLs diffèrent par locale, donc le cache est déjà clefé par URL).

## 9. Tests

- Fonctionnels: chaque route publique résout en `/x` (en, `_locale=en`) et `/fr/x` (fr, `_locale=fr`); `/fr/lieux` OK et `/places` en EN.
- Blog: liste affiche un drapeau par carte; article FR sous `/blog/posts/{slug}` (chrome EN) redirige 301 vers `/fr/blog/posts/{slug}`; canonical correct.
- SEO: présence des `<link hreflang>` sur les pages bilingues, absence sur les articles.
- Switcher: le lien EN<->FR mène à la bonne alternative.
- Cache: `normalize()` produit bien N URLs par locale (test unitaire).

## 10. Hors périmètre / suites

- **Adresses Places** (nom/ville/pays) restent en data Google brute (non traduites).
- **Makefile.local** (`database.push-cv-to-production`, perso/gitignore): à mettre à jour pour inclure les tables de traduction CV + les nouvelles colonnes, une fois le schéma migré. Note manuelle pour l'auteur.
- La migration prod devra jouer les migrations Doctrine (nouvelles tables + drop de colonnes) avant tout push de données.

## Ordre d'implémentation suggéré (pour le plan)

1. Socle: enum `Locale`, config locale, helper Twig `t_html` + `locale_flag`.
2. Routing localisé (les ≈7 routes) + audit des chemins en dur.
3. Catalogue: extraction du chrome (home, nav, blog, cv, places, footer) + trad FR.
4. Blog: migration `locale` + unique slug, entité, drapeaux, redirection article, RSS.
5. CV: tables de traduction (migration data), entités + fallback, EasyAdmin, rédaction EN.
6. Places: route + libellés.
7. `base.html.twig`: hreflang, canonical, switcher header.
8. Sitemap par locale + purge cache large (boucle locales dans `normalize()`).
9. Tests fonctionnels + unitaires.
