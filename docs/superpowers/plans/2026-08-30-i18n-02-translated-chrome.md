# i18n — Plan 2: Chrome traduit (helper t_html, catalogue, Home + nav + footer) — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Fournir le helper Twig `t_html` (liens `<0>…</0>` dans les traductions), le catalogue ICU des chaînes globales, et traduire le chrome global: page Home, libellés de menu, footer — EN + FR.

**Architecture:** Extension Twig `I18nExtension::t_html` traduit une clé puis remplace `<n>texte</n>` par `<a …>texte</a>` (texte + attributs échappés, `<a>` construit par nous). Les chaînes vivent dans `translations/messages+intl-icu.{en,fr}.yaml`. Les templates utilisent `|trans` (texte simple) ou `t_html` (texte avec liens). Les URLs passent par `path()` (déjà locale-aware depuis le Plan 1).

**Tech Stack:** Symfony 8.1, PHP 8.5, Twig, Symfony Translation (domaine `messages`, format intl-icu), PHPUnit.

**Spec:** `docs/superpowers/specs/2026-08-30-i18n-public-site-design.md` (§2 Catalogue + §2.1 t_html).

## Global Constraints

- Catalogue: domaine `messages`, fichiers `translations/messages+intl-icu.{en,fr}.yaml`, indentation **4 espaces**, clés imbriquées.
- **Apostrophes SIMPLES** (comme le catalogue existant: `Chambre d'hôtes`). L'ICU rend une apostrophe isolée littéralement; NE PAS la doubler (doubler la casse). Vérifié empiriquement sur cette version de Symfony.
- Ne mettre une valeur YAML entre quotes QUE si elle contient `: ` (deux-points + espace). Dans ce cas: single-quote YAML + apostrophes doublées POUR YAML (le YAML rend alors une apostrophe simple -> ICU OK). Une seule chaîne concernée ici: `home.about.p3` en FR.
- Les placeholders `<0>…</0>` / `<1>…</1>` traversent l'ICU intacts et sont consommés par `t_html`.
- Texte SIMPLE -> `{{ 'clé'|trans }}`. Texte AVEC liens -> `{{ t_html('clé', [...]) }}`.
- URLs via `path('nom.de.route')` (jamais de chemin en dur). Enum route CV = `app.cv.index`, Blog home = `app.blog.home`.
- Tout fichier PHP: `declare(strict_types=1);`, classes `final`. Tests: `#[CoversClass]` ou `#[CoversNothing]` obligatoire, sortie pristine.
- Hors périmètre Plan 2: chrome blog (Plan 3), chrome CV (Plan 4), Places UI + SEO/switcher/hreflang (Plan 5). Ne PAS y toucher.
- Le titre `Hugo Alliaume` (nom propre) et les libellés `nav.blog`/`nav.cv` (identiques EN/FR) restent, mais passent quand même par le catalogue pour l'uniformité du menu.

---

### Task 1: Extension Twig `I18nExtension` avec `t_html`

**Files:**

- Create: `src/Shared/Application/Twig/Extension/I18nExtension.php`
- Test: `tests/Shared/Application/Twig/Extension/I18nExtensionTest.php`

**Interfaces:**

- Consumes: `Symfony\Contracts\Translation\TranslatorInterface` (autowired).
- Produces: fonction Twig `t_html(string $key, array $links = [], array $parameters = [], string $domain = 'messages'): string` (is_safe html). `$links[$n]` est une map attribut => valeur pour le lien `<n>`. Consommé par le template Home (Task 2) et plus tard par le blog/cv.

- [ ] **Step 1: Write the failing test**

```php
<?php

declare(strict_types=1);

namespace App\Tests\Shared\Application\Twig\Extension;

use App\Shared\Application\Twig\Extension\I18nExtension;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Translation\Loader\ArrayLoader;
use Symfony\Component\Translation\Translator;

#[CoversClass(I18nExtension::class)]
final class I18nExtensionTest extends TestCase
{
    private function extensionWith(string $message): I18nExtension
    {
        $translator = new Translator('en');
        $translator->addLoader('array', new ArrayLoader());
        $translator->addResource('array', ['msg' => $message], 'en', 'messages');

        return new I18nExtension($translator);
    }

    public function testItReplacesPlaceholdersWithAnchors(): void
    {
        $extension = $this->extensionWith('See my <0>CV</0> and my <1>blog</1>.');

        $html = $extension->tHtml('msg', [
            ['href' => '/cv'],
            ['href' => '/blog', 'class' => 'link'],
        ]);

        self::assertSame('See my <a href="/cv">CV</a> and my <a href="/blog" class="link">blog</a>.', $html);
    }

    public function testItEscapesLinkTextAndAttributes(): void
    {
        $extension = $this->extensionWith('Danger <0>a & b</0>');

        $html = $extension->tHtml('msg', [
            ['href' => '/x?a=1&b="2"'],
        ]);

        self::assertSame('Danger <a href="/x?a=1&amp;b=&quot;2&quot;">a &amp; b</a>', $html);
    }

    public function testItLeavesTextWithoutPlaceholdersUnchanged(): void
    {
        $extension = $this->extensionWith('Just text');

        self::assertSame('Just text', $extension->tHtml('msg'));
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `symfony php vendor/bin/phpunit tests/Shared/Application/Twig/Extension/I18nExtensionTest.php`
Expected: FAIL (`Class "App\Shared\Application\Twig\Extension\I18nExtension" not found`).

- [ ] **Step 3: Write the implementation**

```php
<?php

declare(strict_types=1);

namespace App\Shared\Application\Twig\Extension;

use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class I18nExtension extends AbstractExtension
{
    public function __construct(
        private readonly TranslatorInterface $translator,
    ) {
    }

    #[\Override]
    public function getFunctions(): iterable
    {
        yield new TwigFunction('t_html', $this->tHtml(...), [
            'is_safe' => ['html'],
        ]);
    }

    /**
     * @param list<array<string, string>> $links
     * @param array<string, mixed> $parameters
     */
    public function tHtml(string $key, array $links = [], array $parameters = [], string $domain = 'messages'): string
    {
        $translated = $this->translator->trans($key, $parameters, $domain);

        return (string) preg_replace_callback(
            '#<(\d+)>(.*?)</\1>#',
            static function (array $matches) use ($links): string {
                $index = (int) $matches[1];
                $text = htmlspecialchars($matches[2], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

                $attributes = '';
                foreach ($links[$index] ?? [] as $name => $value) {
                    $attributes .= sprintf(' %s="%s"', $name, htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'));
                }

                return sprintf('<a%s>%s</a>', $attributes, $text);
            },
            $translated,
        );
    }
}
```

- [ ] **Step 4: Run test to verify it passes**

Run: `symfony php vendor/bin/phpunit tests/Shared/Application/Twig/Extension/I18nExtensionTest.php`
Expected: PASS (3 tests).

- [ ] **Step 5: Commit**

```bash
git add src/Shared/Application/Twig/Extension/I18nExtension.php tests/Shared/Application/Twig/Extension/I18nExtensionTest.php
git commit -m "feat(i18n): add t_html Twig helper for links in translations"
```

---

### Task 2: Catalogue + traduction du chrome (Home, menu, footer)

TDD: on écrit d'abord un test fonctionnel qui échoue sur `/fr` (chrome non traduit), puis on ajoute les clés de catalogue et on branche les templates.

**Files:**

- Test: `tests/Shared/Infrastructure/Symfony/Controller/ChromeTranslationTest.php`
- Modify: `translations/messages+intl-icu.en.yaml` (append)
- Modify: `translations/messages+intl-icu.fr.yaml` (append)
- Modify: `templates/home.html.twig` (lines 6, 13, 17-23)
- Modify: `config/services.yaml:14,21,23` (menu labels -> clés)
- Modify: `templates/menu/main.html.twig:14` (`|trans`)
- Modify: `templates/components/AppFooter.html.twig:20,68`

**Interfaces:**

- Consumes: `t_html` (Task 1), `path()` (Plan 1 routes), fonction Twig existante `prefetch()`.
- Produces: chrome bilingue rendu.

- [ ] **Step 1: Write the failing functional test**

```php
<?php

declare(strict_types=1);

namespace App\Tests\Shared\Infrastructure\Symfony\Controller;

use PHPUnit\Framework\Attributes\CoversNothing;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;

#[CoversNothing]
final class ChromeTranslationTest extends WebTestCase
{
    #[\Override]
    protected function tearDown(): void
    {
        self::ensureKernelShutdown();
    }

    public function testHomeRendersEnglishChrome(): void
    {
        $client = self::createClient();
        $client->request(Request::METHOD_GET, '/');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'About me');
        $this->assertSelectorTextContains('header', 'Places');
    }

    public function testHomeRendersFrenchChrome(): void
    {
        $client = self::createClient();
        $client->request(Request::METHOD_GET, '/fr');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'À propos de moi');
        $this->assertSelectorTextContains('header', 'Lieux');
    }
}
```

- [ ] **Step 2: Run to verify it fails**

Run: `symfony php vendor/bin/phpunit tests/Shared/Infrastructure/Symfony/Controller/ChromeTranslationTest.php`
Expected: `testHomeRendersFrenchChrome` FAILS (la page `/fr` rend encore "About me" et "Places"). `testHomeRendersEnglishChrome` peut déjà passer (texte EN en dur).

- [ ] **Step 3: Append the EN catalog keys**

À la FIN de `translations/messages+intl-icu.en.yaml` (nouveau bloc de clés racine, indentation 4 espaces):

```yaml
home:
    meta_description: Personal website of Hugo Alliaume, full-stack web-developer and open-source contributor.
    about:
        title: About me
        p1: Hugo Alliaume, a full-stack developer since 2009, French and Lyonnais at heart, I enjoy learning new things and being as versatile as possible. Since then, I have specialized in web development, mainly with PHP and JavaScript, as well as in optimizing performance on both the server and the browser.
        p2: I joined the <0>Symfony UX</0> Core Team in August 2024. An open-source enthusiast since the beginning, I contribute either with my own projects or by working on Symfony, particularly on Webpack Encore and Symfony UX.
        p3: I try to attend most PHP and Symfony conferences and meetups, like SymfonyLive or ForumPHP, and the AFUP Lyon meetups. If you want to chat a bit, you'll probably find me at the buffet! 🥪
        discover: On this site you'll find my social profiles, my <0>CV</0> and my <1>blog</1>.
nav:
    blog: Blog
    cv: CV
    places: Places
footer:
    role:
        dev: Full-stack web developer
        oss: open-source contributor
        team: Symfony UX Core Team
    revision: Revision
```

- [ ] **Step 4: Append the FR catalog keys**

À la FIN de `translations/messages+intl-icu.fr.yaml` (note: `home.about.p3` est la SEULE valeur quotée, car elle contient `: ` — single-quote YAML + apostrophes doublées):

```yaml
home:
    meta_description: Site personnel de Hugo Alliaume, développeur web full-stack et contributeur open source.
    about:
        title: À propos de moi
        p1: Hugo Alliaume, développeur full-stack depuis 2009, français et lyonnais dans l'âme, j'aime apprendre de nouvelles choses et rester le plus polyvalent possible. Depuis, je me suis spécialisé dans le développement web, principalement avec PHP et JavaScript, ainsi que dans l'optimisation des performances côté serveur et navigateur.
        p2: J'ai rejoint la Core Team de <0>Symfony UX</0> en août 2024. Passionné d'open source depuis toujours, je contribue soit à mes propres projets, soit à Symfony, notamment sur Webpack Encore et Symfony UX.
        p3: "J'essaie d'assister à la plupart des conférences et meetups PHP et Symfony, comme SymfonyLive ou ForumPHP, ainsi qu'aux meetups AFUP Lyon. Pour discuter un peu, direction le buffet : c'est probablement là que je serai ! 🥪"
        discover: Ce site regroupe mes profils sociaux, mon <0>CV</0> et mon <1>blog</1>.
nav:
    blog: Blog
    cv: CV
    places: Lieux
footer:
    role:
        dev: Développeur web full-stack
        oss: contributeur open source
        team: Core Team Symfony UX
    revision: Révision
```

- [ ] **Step 5: Edit `templates/home.html.twig`**

Ligne 6 (meta description):

```twig
    <meta name="description" content="{{ 'home.meta_description'|trans }}">
```

Ligne 13 (h1):

```twig
            {{ 'home.about.title'|trans }}
```

Remplacer le bloc des 4 paragraphes (lignes 17 à 23, y compris le `</p>` orphelin ligne 23 à supprimer) par:

```twig
            <p>{{ 'home.about.p1'|trans }}</p>
            <p>{{ t_html('home.about.p2', [{href: 'https://ux.symfony.com/'}]) }}</p>
            <p>{{ 'home.about.p3'|trans }}</p>
            <p>{{ t_html('home.about.discover', [
                {href: prefetch(path('app.cv.index')), title: 'Hugo Alliaume — CV', 'aria-label': 'Curriculum Vitae'},
                {href: prefetch(path('app.blog.home')), title: 'Hugo Alliaume — Blog', 'aria-label': 'Blog'},
            ]) }}</p>
```

- [ ] **Step 6: Edit `config/services.yaml` (menu labels -> clés)**

Remplacer les 3 `label:` du bloc `app.menu_definition`:

```yaml
app.menu_definition:
    - label: "nav.blog"
      route: !php/enum App\Blog\Domain\Data\Route::HOME->value
      extras:
          routes:
              - route: !php/enum App\Blog\Domain\Data\Route::POST_VIEW->value
              - route: !php/enum App\Blog\Domain\Data\Route::TAG_LIST->value
              - route: !php/enum App\Blog\Domain\Data\Route::TAG_VIEW->value
    - label: "nav.cv"
      route: !php/enum App\CV\Domain\Data\Route::INDEX->value
    - label: "nav.places"
      route: !php/enum App\Places\Domain\Data\Route::INDEX->value
```

- [ ] **Step 7: Edit `templates/menu/main.html.twig:14`**

```twig
                    {{ (child.label ?? child.name)|trans }}
```

- [ ] **Step 8: Edit `templates/components/AppFooter.html.twig`**

Ligne 20 (sous-titre):

```twig
                    <p class="mt-2 text-sm">{{ 'footer.role.dev'|trans }},<br>{{ 'footer.role.oss'|trans }},<br>{{ 'footer.role.team'|trans }}</p>
```

Ligne 68 (remplacer le mot `Revision`, garder le reste de la ligne):

```twig
            {{ 'now'|date('Y') }}
            &bullet; {{ 'footer.revision'|trans }} <a href="https://github.com/Kocal/hugo.alliau.me/commit/{{ SOURCE_COMMIT }}" class="font-mono">{{ SOURCE_COMMIT|slice(0, 8) }}</a>
```

- [ ] **Step 9: Run the functional test (GREEN)**

Run: `symfony php vendor/bin/phpunit tests/Shared/Infrastructure/Symfony/Controller/ChromeTranslationTest.php`
Expected: PASS (2 tests). Si `assertSelectorTextContains('header', 'Lieux')` échoue, vérifier que `menu/main.html.twig` applique bien `|trans` et que `services.yaml` utilise `nav.places`.

- [ ] **Step 10: Vérifier le rendu ICU des liens à l'œil (optionnel mais recommandé)**

Run: `symfony console debug:router >/dev/null && symfony php vendor/bin/phpunit tests/Shared/Infrastructure/Symfony/Controller/ChromeTranslationTest.php --filter testHomeRendersFrenchChrome`
Expected: vert. (Confirme que `t_html` + ICU n'ont pas cassé le rendu de `/fr`.)

- [ ] **Step 11: Run full suite (non-régression)**

Run: `symfony php vendor/bin/phpunit`
Expected: PASS, aucun test cassé.

- [ ] **Step 12: Commit**

```bash
git add translations templates config/services.yaml tests/Shared/Infrastructure/Symfony/Controller/ChromeTranslationTest.php
git commit -m "feat(i18n): translate global chrome (home, nav, footer) EN/FR"
```

---

## Self-Review

- **Spec coverage:** couvre §2 (catalogue ICU, clés imbriquées) et §2.1 (helper `t_html` pour liens). Le chrome blog/cv/places est explicitement reporté aux plans 3/4/5.
- **Placeholders:** aucun; code d'extension, test, clés de catalogue et éditions de templates fournis intégralement.
- **Cohérence des types:** `t_html(key, links, parameters, domain)` définie en Task 1, utilisée en Task 2 avec `links` = liste de maps d'attributs. Clés de catalogue référencées par les templates: `home.meta_description`, `home.about.{title,p1,p2,p3,discover}`, `nav.{blog,cv,places}`, `footer.role.{dev,oss,team}`, `footer.revision` — toutes présentes dans les blocs en+fr.
- **Risque ICU:** apostrophes simples (vérifié: rendu littéral); une seule valeur quotée (`home.about.p3` FR) avec apostrophes doublées pour YAML. Placeholders `<0>` préservés par l'ICU puis consommés par `t_html`.
