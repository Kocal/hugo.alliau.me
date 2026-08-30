# i18n — Plan 1: Fondations & routing localisé — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Poser le socle i18n (enum `Locale`) et rendre toutes les routes publiques localisées — EN à la racine, FR sous `/fr`, avec `places` -> `lieux` — sans changer les noms de routes.

**Architecture:** Chemins localisés par route via tableaux `#[Route(path: ['en' => ..., 'fr' => ...])]`. Symfony crée en interne `nom.en` / `nom.fr`, pose `_locale` selon l'URL, et `path('nom')` choisit la variante selon la locale courante. Les enums `Route` (noms) restent inchangés.

**Tech Stack:** Symfony 8.1, PHP 8.5, PHPUnit (WebTestCase / KernelTestCase), routing localisé Symfony.

**Spec:** `docs/superpowers/specs/2026-08-30-i18n-public-site-design.md` (§1 Routing, §3.2 enum Locale).

## Global Constraints

- EN = pas de préfixe (défaut). FR = préfixe `/fr`. `places` -> `lieux`; `blog`, `cv` identiques.
- **Ne jamais renommer** les routes: les valeurs des enums `App\<Ctx>\Domain\Data\Route` restent les noms canoniques.
- **Hors i18n** (path unique, non localisé): `/blog/rss.xml`, `/admin/*`, `/login`, `/logout`, `/sitemap.xml`.
- Tout fichier PHP: `declare(strict_types=1);`, classes `final`, pas de commentaire superflu.
- Tests: `#[CoversClass(...)]` ou `#[CoversNothing]` OBLIGATOIRE (config `requireCoverageMetadata="true"`). Lancer via `symfony php vendor/bin/phpunit`.
- Ne PAS toucher au cache HTTP, sitemap, templates, entités dans ce plan (plans suivants).

---

### Task 1: Enum `Locale` (socle partagé)

**Files:**

- Create: `src/Shared/Domain/Data/Locale.php`
- Test: `tests/Shared/Domain/Data/LocaleTest.php`

**Interfaces:**

- Produces: `App\Shared\Domain\Data\Locale` — enum backed `string` avec `case EN = 'en'`, `case FR = 'fr'`, méthodes `public static function default(): self` (retourne `EN`) et `public function flag(): string` (retourne `'🇬🇧'` pour EN, `'🇫🇷'` pour FR). Consommé par les plans Blog (colonne `locale`) et CV (tables de traduction).

- [ ] **Step 1: Write the failing test**

```php
<?php

declare(strict_types=1);

namespace App\Tests\Shared\Domain\Data;

use App\Shared\Domain\Data\Locale;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Locale::class)]
final class LocaleTest extends TestCase
{
    public function testItHasEnglishAndFrenchCases(): void
    {
        self::assertSame('en', Locale::EN->value);
        self::assertSame('fr', Locale::FR->value);
    }

    public function testDefaultIsEnglish(): void
    {
        self::assertSame(Locale::EN, Locale::default());
    }

    public function testFlagReturnsTheMatchingEmoji(): void
    {
        self::assertSame('🇬🇧', Locale::EN->flag());
        self::assertSame('🇫🇷', Locale::FR->flag());
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `symfony php vendor/bin/phpunit tests/Shared/Domain/Data/LocaleTest.php`
Expected: FAIL (`Class "App\Shared\Domain\Data\Locale" not found`).

- [ ] **Step 3: Write minimal implementation**

```php
<?php

declare(strict_types=1);

namespace App\Shared\Domain\Data;

enum Locale: string
{
    case EN = 'en';

    case FR = 'fr';

    public static function default(): self
    {
        return self::EN;
    }

    public function flag(): string
    {
        return match ($this) {
            self::EN => '🇬🇧',
            self::FR => '🇫🇷',
        };
    }
}
```

- [ ] **Step 4: Run test to verify it passes**

Run: `symfony php vendor/bin/phpunit tests/Shared/Domain/Data/LocaleTest.php`
Expected: PASS (3 tests).

- [ ] **Step 5: Commit**

```bash
git add src/Shared/Domain/Data/Locale.php tests/Shared/Domain/Data/LocaleTest.php
git commit -m "feat(i18n): add Locale enum with flag helper"
```

---

### Task 2: Localiser les routes publiques

Sept `#[Route]` passent d'un path unique à un tableau `['en' => ..., 'fr' => ...]`. Les autres attributs (`name`, `options`, `methods`, `format`) restent identiques. Le RSS reste NON localisé.

**Files:**

- Modify: `src/Shared/Infrastructure/Symfony/Controller/HomeController.php:18`
- Modify: `src/CV/Infrastructure/Symfony/Controller/HomeController.php:21`
- Modify: `src/Blog/Infrastructure/Symfony/Controller/HomeController.php:19` (route HTML uniquement; ligne 22 RSS inchangée)
- Modify: `src/Blog/Infrastructure/Symfony/Controller/Post/ViewController.php:23`
- Modify: `src/Blog/Infrastructure/Symfony/Controller/Tag/ListController.php:19`
- Modify: `src/Blog/Infrastructure/Symfony/Controller/Tag/ViewController.php:19`
- Modify: `src/Places/Infrastructure/Symfony/Controller/ViewPlacesController.php:28`
- Test: `tests/Shared/Infrastructure/Routing/LocalizedRoutingTest.php`

**Interfaces:**

- Consumes: rien (config de routing).
- Produces: routes localisées. `router->generate($name, ['_locale' => 'fr'] + $params)` produit le path FR; `router->matchRequest(Request::create($path))` pose `_locale` et retourne `_route === "$name.$locale"`.

- [ ] **Step 1: Write the failing test**

```php
<?php

declare(strict_types=1);

namespace App\Tests\Shared\Infrastructure\Routing;

use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;

#[CoversNothing]
final class LocalizedRoutingTest extends KernelTestCase
{
    /**
     * @return iterable<string, array{string, array<string, string>, string, string}>
     */
    public static function localizedPathProvider(): iterable
    {
        // [routeName, params, locale, expectedPath]
        yield 'home en' => ['app.home', [], 'en', '/'];
        yield 'home fr' => ['app.home', [], 'fr', '/fr'];
        yield 'cv en' => ['app.cv.index', [], 'en', '/cv'];
        yield 'cv fr' => ['app.cv.index', [], 'fr', '/fr/cv'];
        yield 'blog en' => ['app.blog.home', [], 'en', '/blog'];
        yield 'blog fr' => ['app.blog.home', [], 'fr', '/fr/blog'];
        yield 'post en' => ['app.blog.posts.view', ['slug' => 'foo'], 'en', '/blog/posts/foo'];
        yield 'post fr' => ['app.blog.posts.view', ['slug' => 'foo'], 'fr', '/fr/blog/posts/foo'];
        yield 'tags list en' => ['app.blog.tags.list', [], 'en', '/blog/tags'];
        yield 'tags list fr' => ['app.blog.tags.list', [], 'fr', '/fr/blog/tags'];
        yield 'tag view en' => ['app.blog.tags.view', ['tag' => 'php'], 'en', '/blog/tags/php'];
        yield 'tag view fr' => ['app.blog.tags.view', ['tag' => 'php'], 'fr', '/fr/blog/tags/php'];
        yield 'places en' => ['app.places.view_list', [], 'en', '/places'];
        yield 'places fr' => ['app.places.view_list', [], 'fr', '/fr/lieux'];
    }

    /**
     * @param array<string, string> $params
     */
    #[DataProvider('localizedPathProvider')]
    public function testItGeneratesLocalizedPaths(string $name, array $params, string $locale, string $expected): void
    {
        self::bootKernel();
        $router = self::getContainer()->get('router');
        self::assertInstanceOf(RouterInterface::class, $router);

        self::assertSame($expected, $router->generate($name, $params + ['_locale' => $locale]));
    }

    /**
     * @return iterable<string, array{string, string, string}>
     */
    public static function matchedLocaleProvider(): iterable
    {
        // [path, expectedLocale, expectedCanonicalRoute]
        // Symfony 8.1 met le nom CANONIQUE (non suffixé) dans _route via _canonical_route; _locale porte la locale.
        yield 'root is en' => ['/', 'en', 'app.home'];
        yield '/fr is fr' => ['/fr', 'fr', 'app.home'];
        yield '/cv is en' => ['/cv', 'en', 'app.cv.index'];
        yield '/fr/cv is fr' => ['/fr/cv', 'fr', 'app.cv.index'];
        yield '/places is en' => ['/places', 'en', 'app.places.view_list'];
        yield '/fr/lieux is fr' => ['/fr/lieux', 'fr', 'app.places.view_list'];
        yield '/fr/blog/posts is fr' => ['/fr/blog/posts/foo', 'fr', 'app.blog.posts.view'];
    }

    #[DataProvider('matchedLocaleProvider')]
    public function testItMatchesLocaleFromPath(string $path, string $expectedLocale, string $expectedRoute): void
    {
        self::bootKernel();
        $router = self::getContainer()->get('router');
        self::assertInstanceOf(RouterInterface::class, $router);

        $match = $router->matchRequest(Request::create($path));

        self::assertSame($expectedLocale, $match['_locale']);
        self::assertSame($expectedRoute, $match['_route']);
    }

    public function testRssStaysUnlocalized(): void
    {
        self::bootKernel();
        $router = self::getContainer()->get('router');
        self::assertInstanceOf(RouterInterface::class, $router);

        self::assertSame('/blog/rss.xml', $router->generate('app.blog.rss'));
        self::assertSame('app.blog.rss', $router->matchRequest(Request::create('/blog/rss.xml'))['_route']);
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `symfony php vendor/bin/phpunit tests/Shared/Infrastructure/Routing/LocalizedRoutingTest.php`
Expected: FAIL (les chemins FR n'existent pas encore -> `generate` renvoie l'ancien path / `matchRequest('/fr/cv')` lève `ResourceNotFoundException`).

- [ ] **Step 3: Localiser chaque route**

`src/Shared/Infrastructure/Symfony/Controller/HomeController.php` — remplacer l'attribut ligne 18:

```php
    #[Route(path: [
        'en' => '/',
        'fr' => '/fr',
    ], name: RouteShared::HOME->value, options: [
        'sitemap' => true,
    ], methods: ['GET'])]
```

`src/CV/Infrastructure/Symfony/Controller/HomeController.php` — remplacer l'attribut ligne 21:

```php
    #[Route(path: [
        'en' => '/cv',
        'fr' => '/fr/cv',
    ], name: RouteCv::INDEX->value, options: [
        'sitemap' => true,
    ], methods: ['GET'])]
```

`src/Blog/Infrastructure/Symfony/Controller/HomeController.php` — remplacer UNIQUEMENT la 1re route (ligne 19), garder la route RSS ligne 22 telle quelle:

```php
    #[Route(path: [
        'en' => '/blog',
        'fr' => '/fr/blog',
    ], name: RouteBlog::HOME->value, options: [
        'sitemap' => true,
    ], methods: ['GET'], format: 'html')]
    #[Route('/blog/rss.xml', name: RouteBlog::RSS->value, methods: ['GET'], format: 'xml')]
```

`src/Blog/Infrastructure/Symfony/Controller/Post/ViewController.php` — remplacer l'attribut ligne 23:

```php
    #[Route(path: [
        'en' => '/blog/posts/{slug}',
        'fr' => '/fr/blog/posts/{slug}',
    ], name: RouteBlog::POST_VIEW->value, methods: ['GET'])]
```

`src/Blog/Infrastructure/Symfony/Controller/Tag/ListController.php` — remplacer l'attribut ligne 19:

```php
    #[Route(path: [
        'en' => '/blog/tags',
        'fr' => '/fr/blog/tags',
    ], name: RouteBlog::TAG_LIST->value, methods: ['GET'])]
```

`src/Blog/Infrastructure/Symfony/Controller/Tag/ViewController.php` — remplacer l'attribut ligne 19:

```php
    #[Route(path: [
        'en' => '/blog/tags/{tag}',
        'fr' => '/fr/blog/tags/{tag}',
    ], name: RouteBlog::TAG_VIEW->value, methods: ['GET'])]
```

`src/Places/Infrastructure/Symfony/Controller/ViewPlacesController.php` — remplacer l'attribut ligne 28:

```php
    #[Route(path: [
        'en' => '/places',
        'fr' => '/fr/lieux',
    ], name: RoutePlaces::INDEX->value, options: [
        'sitemap' => true,
    ], methods: ['GET'])]
```

- [ ] **Step 4: Run test to verify it passes**

Run: `symfony php vendor/bin/phpunit tests/Shared/Infrastructure/Routing/LocalizedRoutingTest.php`
Expected: PASS (tous les data sets).

- [ ] **Step 5: Vérifier le routeur à l'œil**

Run: `symfony console debug:router | grep -E "app\.(home|cv|blog|places)"`
Expected: on voit les variantes `.en` et `.fr` avec les bons paths (`/`, `/fr`, `/cv`, `/fr/cv`, `/blog`, `/fr/blog`, `/places`, `/fr/lieux`, `/blog/rss.xml` unique).

- [ ] **Step 6: Commit**

```bash
git add src tests/Shared/Infrastructure/Routing/LocalizedRoutingTest.php
git commit -m "feat(i18n): localize public routes (EN default, FR under /fr)"
```

---

### Task 3: Résolution de locale de bout en bout (Home + Places)

Vérifie que `/` sert EN, `/fr` sert FR, `/places` (EN) et `/fr/lieux` (FR) répondent 200, et que les mauvais chemins (`/lieux`, `/fr/places`) sont 404. Test d'intégration `#[CoversNothing]` (pas de comptabilité `UsesClass`).

**Files:**

- Test: `tests/Shared/Infrastructure/Symfony/Controller/LocaleResolutionTest.php`

**Interfaces:**

- Consumes: routes localisées (Task 2), `FakePlaceRepository` existant (`tests/Places/Infrastructure/Double/Repository/FakePlaceRepository.php`), `Place` (`App\Places\Domain\Data\Place`), `PlaceRepository` (`App\Places\Domain\Repository\PlaceRepository`).
- Produces: rien.

- [ ] **Step 1: Write the failing test**

```php
<?php

declare(strict_types=1);

namespace App\Tests\Shared\Infrastructure\Symfony\Controller;

use App\Places\Domain\Data\Place;
use App\Places\Domain\Repository\PlaceRepository;
use App\Tests\Places\Infrastructure\Double\Repository\FakePlaceRepository;
use PHPUnit\Framework\Attributes\CoversNothing;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;

#[CoversNothing]
final class LocaleResolutionTest extends WebTestCase
{
    #[\Override]
    protected function tearDown(): void
    {
        self::ensureKernelShutdown();
    }

    public function testHomeServesEnglishAtRoot(): void
    {
        $client = self::createClient();
        $client->request(Request::METHOD_GET, '/');

        $this->assertResponseIsSuccessful();
        self::assertSame('en', $client->getRequest()->getLocale());
    }

    public function testHomeServesFrenchUnderFrPrefix(): void
    {
        $client = self::createClient();
        $client->request(Request::METHOD_GET, '/fr');

        $this->assertResponseIsSuccessful();
        self::assertSame('fr', $client->getRequest()->getLocale());
    }

    public function testPlacesServesEnglish(): void
    {
        $placeRepository = new FakePlaceRepository();
        $placeRepository->add(new Place());

        $client = self::createClient();
        $client->getContainer()->set(PlaceRepository::class, $placeRepository);

        $client->request(Request::METHOD_GET, '/places');
        $this->assertResponseIsSuccessful();
        self::assertSame('en', $client->getRequest()->getLocale());
    }

    public function testLieuxServesFrench(): void
    {
        $placeRepository = new FakePlaceRepository();
        $placeRepository->add(new Place());

        $client = self::createClient();
        $client->getContainer()->set(PlaceRepository::class, $placeRepository);

        $client->request(Request::METHOD_GET, '/fr/lieux');
        $this->assertResponseIsSuccessful();
        self::assertSame('fr', $client->getRequest()->getLocale());
    }

    public function testWrongLocalePathsAre404(): void
    {
        $client = self::createClient();

        $client->request(Request::METHOD_GET, '/lieux');
        $this->assertResponseStatusCodeSame(404);

        $client->request(Request::METHOD_GET, '/fr/places');
        $this->assertResponseStatusCodeSame(404);
    }
}
```

- [ ] **Step 2: Run test to verify it fails, then passes**

Run: `symfony php vendor/bin/phpunit tests/Shared/Infrastructure/Symfony/Controller/LocaleResolutionTest.php`
Expected: PASS (les routes de Task 2 sont déjà en place). Si un cas échoue, corriger la route concernée dans Task 2 avant de continuer.

> Note: chaque méthode de test crée SON PROPRE client + fake (le `KernelBrowser` ne rebootant que paresseusement au prochain `request()`, réutiliser un client avec un `set()` intercalé lève "service already initialized"). D'où deux méthodes séparées `testPlacesServesEnglish` / `testLieuxServesFrench`.

- [ ] **Step 3: Lancer toute la suite (non-régression)**

Run: `symfony php vendor/bin/phpunit`
Expected: PASS. En particulier `ViewPlacesControllerTest` (`/places`) reste vert.

- [ ] **Step 4: Commit**

```bash
git add tests/Shared/Infrastructure/Symfony/Controller/LocaleResolutionTest.php
git commit -m "test(i18n): assert locale resolution for home and places"
```

---

## Self-Review

- **Spec coverage:** couvre §1 (routing localisé, RSS non localisé, `places`->`lieux`, noms de routes inchangés) et §3.2 (enum `Locale`). Le reste de la spec (catalogue, blog, cv, places UI, SEO, cache) est explicitement hors de ce plan -> plans 2 à 5.
- **Placeholders:** aucun; tout le code des tests et des attributs est fourni.
- **Cohérence des types:** noms de routes = valeurs exactes des enums (`app.home`, `app.cv.index`, `app.blog.home`, `app.blog.posts.view`, `app.blog.tags.list`, `app.blog.tags.view`, `app.places.view_list`, `app.blog.rss`). `Locale::default()`/`flag()` définis en Task 1 et cohérents.
- **Bonne nouvelle (vérifiée):** `matchRequest` retourne `_route` = nom CANONIQUE non suffixé (`app.home`), Symfony posant le suffixe locale seulement dans le registre de génération. Donc `app.current_route` renverra le nom de base -> directement utilisable par le switcher du Plan 5.
- **Fix d'env préalable (Ruling C):** sous `symfony php`, `APP_ENV=dev` est injecté dans `$_ENV`; `phpunit.xml.dist` ne forçait que `$_SERVER`, donc `KernelTestCase` bootait en `dev` (où `framework.test` est off -> "test.service_container" introuvable). Correctif = ajouter `<env name="APP_ENV" value="test" force="true"/>` dans `phpunit.xml.dist`. Bug préexistant (reproduit sur `ViewPlacesControllerTest`), commité à part.
