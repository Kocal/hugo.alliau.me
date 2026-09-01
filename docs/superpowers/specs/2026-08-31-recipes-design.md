# Design — Recettes de cuisine (bounded context `Recipes`)

Date: 2026-08-31. Branche: `feat/recipes-bounded-context`.

## Objectif

Ajouter un bounded context `Recipes` permettant de saisir des recettes de cuisine depuis l'admin et de les afficher côté public sous forme de **grille de fusion** (style _Cooking for Engineers_): les ingrédients en colonne de gauche, les étapes en colonnes successives vers la droite, chaque étape couvrant en `rowspan` les ingrédients et les étapes qu'elle consomme.

Stack existante: Symfony 8.1, PHP 8.5, PostgreSQL, DDD par bounded context (`Blog` / `CV` / `Places` / `User` / `Shared`), EasyAdmin 5, Twig, Stimulus + Turbo, Vite via Symfony Reprise, cache HTTP Cloudflare, sitemap presta, site bilingue EN (racine) / FR (`/fr`).

## Décisions actées

1. **Une recette = une langue.** Colonne `locale` sur `recipe`, comme `Blog\Post`. Pas de table de traduction: traduire chaque ingrédient et chaque étape serait disproportionné, et le multiplicateur devrait fonctionner sur les deux versions.
2. **L'arbre est stocké en `jsonb`**, dans une seule colonne, et mappé vers des value objects PHP immuables par Valinor. Pas de tables relationnelles: la recette est éditée d'un bloc, jamais requêtée par ingrédient.
3. **L'ordre d'affichage des ingrédients est dérivé de l'arbre**, jamais saisi. C'est ce qui rend un `rowspan` cassé structurellement impossible.
4. **Quantités structurées**: `{min, max?, unit, label}`. Les fourchettes («2 à 3 c. à s.») et les fractions («½ concombre») sont représentables, et l'unité s'accorde via les pluriels ICU.
5. **Le multiplicateur de portions est entièrement côté client.** Le serveur ne rend que les quantités de référence, donc une seule réponse HTML reste cachable par Cloudflare.
6. **L'éditeur admin est un composant Vue**, monté par un contrôleur Stimulus dans un champ EasyAdmin custom. EasyAdmin ne sait pas éditer un arbre imbriqué, et faire ça en manipulation DOM manuelle coûterait deux à trois fois plus de code.

## 1. Modèle de données

### 1.1 Entité `Recipe` et table

Table `recipe`, une seule migration.

| Colonne                    | Type                     | Note                                                                                                 |
| -------------------------- | ------------------------ | ---------------------------------------------------------------------------------------------------- |
| `id`                       | `recipe_id` (uuid)       | Pattern `PostId` / `PlaceId` / `ProjectId`                                                           |
| `name`                     | `varchar(255)`           | `Assert\NotBlank`, `Assert\Length(3, 255)`                                                           |
| `slug`                     | `varchar(255)` UNIQUE    | `Assert\NotBlank`                                                                                    |
| `type`                     | `varchar`, enum          | `RecipeType`: `STARTER` / `MAIN` / `DESSERT`                                                         |
| `locale`                   | `varchar(5)`, enum       | `Shared\Domain\Data\Locale`, défaut `EN`                                                             |
| `servings`                 | `smallint`               | `Assert\Positive`. Le nombre de personnes de référence, celui pour lequel les quantités sont saisies |
| `content`                  | `jsonb`                  | L'arbre, via un type DBAL custom                                                                     |
| `visible`                  | `boolean` défaut `false` | Pattern `CV\Project`                                                                                 |
| `created_at`, `updated_at` | `timestamptz`            | `#[ORM\HasLifecycleCallbacks]` + `PreUpdate`                                                         |

`Recipe implements CacheableEntity`, comme les entités des autres contextes.

### 1.2 Value objects du domaine

```
src/Recipes/Domain/Data/
  Recipe.php           entité Doctrine
  RecipeType.php       enum STARTER | MAIN | DESSERT, + toTranslatable()
  Unit.php             enum, + toTranslatable(float $count)
  Ingredient.php       final readonly
  Step.php             final readonly
  RecipeContent.php    final readonly, la liste des racines
  Route.php            enum LIST | VIEW
```

```php
final readonly class Ingredient
{
    public function __construct(
        public string $id,
        public string $label,
        public ?float $min = null,
        public ?float $max = null,
        public ?Unit $unit = null,
    ) {}
}

final readonly class Step
{
    /** @param list<Step|Ingredient> $children */
    public function __construct(
        public string $id,
        public string $text,
        public array $children,
    ) {}
}

final readonly class RecipeContent
{
    /** @param list<Step|Ingredient> $roots */
    public function __construct(public array $roots) {}
}
```

Chaque nœud porte un `id` court et stable, généré par l'éditeur. Sans lui, l'état «étape terminée» stocké côté navigateur pointerait sur des positions et casserait au premier réordonnancement.

`RecipeContent::$roots` est une **liste** et non une racine unique: une recette peut avoir plusieurs étapes finales indépendantes, et l'autoriser ne coûte rien à l'algorithme de layout.

#### L'enum `Unit`

`G`, `KG`, `ML`, `CL`, `L`, `TBSP`, `TSP`, `PINCH`, `CLOVE`, `SLICE`, `BUNCH`, `SPRIG`, `HANDFUL`, `DROP`, `CAN`, `PACK`.

Enum `string` adossé aux mêmes valeurs en minuscules (`g`, `kg`, `tbsp`, …), comme `Locale` et `PlaceType`. Ce sont ces valeurs qui apparaissent dans le JSON et qui servent de suffixe aux clés de traduction.

Pas de cas `PIECE`: un ingrédient qui se compte sans unité («2 oignons», «½ concombre») a simplement `unit = null`.

#### Le `label` porte la préposition

`label` est du texte libre écrit par l'auteur, **préposition incluse**: `"de poitrine de porc coupée en petits dés"`, `"d'ail émincées"`, `"concombre coupé en julienne"`. Le rendu est une simple concaténation `{quantité} {unité} {label}`.

C'est ce que fait déjà la maquette de référence, où seule la partie quantité est en gras. Ça évite d'écrire un moteur d'élision et d'accord en français pour un gain nul.

### 1.3 Format JSON

```json
{
    "roots": [
        {
            "type": "step",
            "id": "s9",
            "text": "Servir nouilles, sauce et concombre",
            "children": [
                {
                    "type": "step",
                    "id": "s7",
                    "text": "Verser mélange farine, laisser épaissir",
                    "children": ["..."]
                },
                {
                    "type": "step",
                    "id": "s8",
                    "text": "Cuire nouilles Udon, égoutter",
                    "children": [
                        {
                            "type": "ingredient",
                            "id": "i12",
                            "min": 300,
                            "max": null,
                            "unit": "g",
                            "label": "de nouilles Udon"
                        }
                    ]
                },
                {
                    "type": "ingredient",
                    "id": "i13",
                    "min": 0.5,
                    "max": null,
                    "unit": null,
                    "label": "concombre coupé en julienne"
                }
            ]
        }
    ]
}
```

Le discriminant `type` vaut `step` ou `ingredient`. Le mapping polymorphe se fait avec `->infer()` de Valinor sur l'union `Step|Ingredient`, en s'appuyant sur cette clé.

### 1.4 Mapping Doctrine

`App\Recipes\Infrastructure\Doctrine\DBAL\Type\RecipeContentType`, enregistré sous le nom `recipe_content`, avec `jsonb` comme type SQL.

- `convertToPHPValue()` délègue au mapper Valinor et renvoie un `RecipeContent`.
- `convertToDatabaseValue()` normalise le graphe d'objets en JSON.

Le type vit dans `Recipes` et non dans `Shared`: `Shared` ne doit dépendre d'aucun autre contexte. Seul `RecipeId` va dans `Shared`, où vivent déjà tous les identifiants (`PostId`, `PlaceId`, `ProjectId`, `UserId`) avec leur type DBAL associé.

### 1.5 Arborescence

```
src/Recipes/
  Domain/Data/           Recipe, RecipeType, Unit, Ingredient, Step, RecipeContent, Route
  Domain/Repository/      RecipeRepository
  Application/            Grid, GridBuilder, QuantityFormatter
  Infrastructure/
    Doctrine/DBAL/Type/RecipeContentType.php
    Doctrine/Repository/RecipeORMRepository.php
    EasyAdmin/Controller/RecipeCrudController.php
    EasyAdmin/Field/RecipeContentField.php
    EasyAdmin/Form/RecipeContentType.php
    EasyAdmin/Form/RecipeContentTransformer.php
    Foundry/Factory/RecipeFactory.php
    Symfony/Controller/ListController.php
    Symfony/Controller/ViewController.php
    Symfony/EventListener/SitemapListener.php
    Twig/Components/Grid.php

src/Shared/Domain/Data/ValueObject/RecipeId.php
src/Shared/Infrastructure/Database/Doctrine/DBAL/Types/RecipeIdType.php

templates/recipes/list.html.twig
templates/recipes/view.html.twig
templates/components/Recipe/Grid.html.twig
templates/admin/fields/recipe_content.html.twig
```

## 2. Algorithme de layout — `Application\GridBuilder`

Fonction pure, sans dépendance Symfony, qui transforme un `RecipeContent` en `Grid` (view model). C'est la pièce centrale et la première à tester.

### 2.1 Règles

1. **Colonne.** `col(ingrédient) = 0`. `col(étape) = 1 + max(col de ses enfants)`. Le nombre total de colonnes vaut `1 + max(col)` sur tous les nœuds.
2. **Lignes.** Les ingrédients sont énumérés dans l'ordre du parcours en profondeur préfixe des racines. Cet ordre est la seule source de vérité pour les numéros de ligne.
3. **`rowspan`.** Pour tout nœud, c'est le nombre d'ingrédients présents dans son sous-arbre. Un ingrédient vaut donc toujours 1.
4. **Ligne de départ.** Celle de son premier ingrédient en profondeur.
5. **Cellules vides.** Un nœud en colonne `c` dont le parent est en colonne `p` laisse un trou de `p - c - 1` colonnes, rendu comme une seule cellule vide en `colspan`, avec le même `rowspan` que le nœud. Pour une racine, `p` vaut le nombre total de colonnes.

Comme la colonne est calculée depuis les feuilles et non depuis la racine, une préparation courte reste collée à gauche même si sa sortie n'est consommée que très à droite. C'est exactement le comportement de la maquette.

### 2.2 Exemple de référence

Recette d'udon de la maquette, 13 ingrédients, 9 étapes. Elle sert de fixture au test du `GridBuilder`.

| Nœud | Enfants        | Colonne | Lignes | `rowspan` | Trou        |
| ---- | -------------- | ------- | ------ | --------- | ----------- |
| A    | ing 1          | 1       | 1      | 1         | `colspan=1` |
| B    | ing 2, 3, 4    | 1       | 2-4    | 3         | —           |
| C    | B, ing 5, 6    | 2       | 2-6    | 5         | —           |
| D    | A, C, ing 7, 8 | 3       | 1-8    | 8         | —           |
| E    | D, ing 9       | 4       | 1-9    | 9         | —           |
| F    | ing 10, 11     | 1       | 10-11  | 2         | `colspan=3` |
| G    | E, F           | 5       | 1-11   | 11        | —           |
| H    | ing 12         | 1       | 12     | 1         | `colspan=4` |
| I    | G, H, ing 13   | 6       | 1-13   | 13        | —           |

Sept colonnes au total. Le parcours en profondeur depuis `I` produit les ingrédients dans l'ordre 1 à 13, c'est-à-dire exactement l'ordre de la maquette. Chaque ligne totalise bien 7 colonnes une fois les `rowspan` des lignes précédentes pris en compte.

### 2.3 Émission des cellules

`Grid` expose `list<Row>`, chaque `Row` étant une `list<Cell>` triée par colonne. Une `Cell` est de type `INGREDIENT`, `STEP` ou `EMPTY` et porte `col`, `rowspan`, `colspan`, et le nœud du domaine quand il y en a un.

Le rendu Twig se contente de boucler: les cellules couvertes par un `rowspan` d'une ligne au-dessus sont simplement absentes de la ligne courante, ce que HTML gère nativement.

## 3. Rendu public

### 3.1 Routes

| Route              | EN                | FR                    |
| ------------------ | ----------------- | --------------------- |
| `app.recipes.list` | `/recipes`        | `/fr/recettes`        |
| `app.recipes.view` | `/recipes/{slug}` | `/fr/recettes/{slug}` |

`app.recipes.list` porte `options: {sitemap: true}`, ce qui suffit pour une route sans paramètre variable et couvre automatiquement les deux locales.

`app.recipes.view` a un `{slug}`, donc un `Recipes\Infrastructure\Symfony\EventListener\SitemapListener` énumère les recettes visibles et ajoute une `UrlConcrete` par recette, sous la locale de la recette et non sous chacune des locales activées. C'est exactement ce que fait déjà `Blog\…\SitemapListener` pour les articles.

`ListController` filtre sur `visible = true` et sur la locale de la requête, groupe par `RecipeType` puis trie par `name`.

`ViewController` reprend le comportement de `Blog\Post\ViewController`: 404 si la recette n'est pas visible, sinon redirection **301** vers la variante de route correspondant à la locale de la recette lorsqu'elle diffère de celle de la requête. `ETag`, `Last-Modified`, `max-age` de 30 jours, réponse publique.

### 3.2 Grille desktop (≥ `md`)

Composant Twig `<twig:Recipe:Grid :recipe="recipe" />`, classe `Recipes\Infrastructure\Twig\Components\RecipeGrid`, template `templates/components/Recipe/Grid.html.twig`.

Chaque cellule ingrédient expose `data-node-id`, `data-qty-min`, `data-qty-max`, `data-unit-one`, `data-unit-other`. Les deux formes d'unité sont rendues côté serveur, car le JS n'a pas accès au catalogue de traductions.

Chaque cellule étape expose `data-node-id`.

Le tableau est enveloppé dans un conteneur `overflow-x: auto`: une recette à huit colonnes déborde même sur un écran large.

### 3.3 Fallback mobile (< `md`)

Une section «Ingrédients» en liste, puis une section «Étapes» en liste ordonnée. L'ordre des étapes est celui du parcours en profondeur **postfixe**, qui est un ordre d'exécution valide. Chaque étape rappelle les ingrédients qu'elle consomme directement.

Les deux rendus coexistent dans le HTML et sont basculés en `hidden md:block` / `md:hidden`. Le texte est donc dupliqué dans le DOM. C'est assumé: basculer en JS casserait le rendu sans JavaScript, et il n'existe pas de mise en forme CSS qui aplatisse un arbre de fusion en liste.

### 3.4 Multiplicateur de portions

Un champ numérique «Pour N personnes», initialisé à `recipe.servings`, avec deux boutons `−` / `+`.

`assets/controllers/recipe_servings_controller.js` calcule `facteur = N / recipe.servings` et réécrit les quantités à partir des `data-qty-*`. Un ingrédient sans `min` n'est jamais multiplié.

Formatage d'un nombre:

1. Valeur entière: rendue telle quelle.
2. Sinon, si la partie fractionnaire correspond à ⅛, ¼, ⅓, ⅜, ½, ⅝, ⅔, ¾ ou ⅞ à 0,01 près: partie entière puis le glyphe (`1 ½`).
3. Sinon: arrondi à deux décimales, séparateur décimal selon la locale.

L'accord de l'unité utilise `Intl.PluralRules` sur la locale de la page, avec les deux formes déjà présentes dans les `data-*`. Une fourchette est rendue via la clé `quantity.range`.

Ce formateur existe **en double**, une fois en PHP pour le rendu initial et une fois en JS pour les recalculs. C'est le prix du rendu sans JavaScript. Les deux font une trentaine de lignes; la version PHP est couverte par des tests unitaires, et les deux jeux de cas sont identiques.

Rien de tout ça ne touche le serveur: une seule réponse HTML par recette, donc le cache HTTP existant reste entièrement valide.

### 3.5 Cochage des étapes

`assets/controllers/recipe_progress_controller.js`. Un clic sur une cellule bascule une classe `is-done` (opacité réduite et texte barré). L'état est un tableau de `data-node-id` en `localStorage` sous la clé `recipe:{slug}`, avec un bouton de remise à zéro. Aucune persistance serveur: il n'y a pas de comptes utilisateurs côté public.

Le même contrôleur pilote les deux rendus, desktop et mobile, puisqu'ils partagent les identifiants de nœuds.

## 4. Admin

### 4.1 CRUD

`RecipeCrudController` (`extends AbstractCrudController<Recipe>`), entrée de menu dans `Shared\Infrastructure\EasyAdmin\Controller\DashboardController` sous une section «Recipes».

Champs: `IdField` (masqué en formulaire), `BooleanField` sur `visible`, `TextField` sur `name` et `slug`, `ChoiceField` sur `type` et `locale` (support natif des enums en EasyAdmin 5), `IntegerField` sur `servings`, puis `RecipeContentField` en `onlyOnForms()`.

### 4.2 Éditeur Vue

```
src/Recipes/Infrastructure/EasyAdmin/Field/RecipeContentField.php
templates/admin/fields/recipe_content.html.twig
assets/controllers/recipe_editor_controller.js     monte l'app Vue
assets/vue/RecipeEditor.vue                        l'état = l'arbre
assets/vue/TreeNode.vue                            récursif, ingrédient ou étape
```

`recipe_content.html.twig` est un **thème de formulaire** et non un template de champ: dans EasyAdmin, `setTemplatePath()` gouverne les pages index et detail, pas le rendu d'un widget. Le champ déclare donc `addFormTheme()` et le type de formulaire un `getBlockPrefix()` de `recipe_content`, ce qui fait chercher à Symfony le bloc `recipe_content_widget`.

Ce bloc rend un `<div data-controller="recipe-editor">` contenant un `<input type="hidden">` et un point de montage. Le contrôleur Stimulus monte l'application Vue, qui écrit le JSON sérialisé dans l'input à chaque modification. Ce pont Stimulus vers Vue garde l'admin cohérent avec le reste des assets.

Fonctions de l'éditeur: ajouter et supprimer un ingrédient ou une étape, rattacher un nœud à une étape parente, réordonner les enfants, et une prévisualisation de la grille utilisant le même algorithme de layout porté en JS.

Nouvelles dépendances: `vue` et `@vitejs/plugin-vue`, plus l'ajout du plugin dans `vite.config.ts`. À vérifier au passage: la prise en charge des fichiers `.vue` par `oxlint` et `oxfmt`, et les exclure de la configuration si elle est absente.

### 4.3 Validation

Un `DataTransformer` sur le champ mappe la chaîne JSON via Valinor et convertit un `MappingError` en `TransformationFailedException`, donc en erreur de formulaire.

Faire remonter l'échec depuis le type DBAL produirait une 500 au moment du flush, bien après que l'utilisateur ait quitté le formulaire.

## 5. Internationalisation

Nouvelles clés dans `translations/messages+intl-icu.{en,fr}.yaml`:

- `recipe_type.starter`, `recipe_type.main`, `recipe_type.dessert`
- `unit.*`, une clé par cas de l'enum, en forme ICU plural: `"{count, plural, one {cuillère à soupe} other {cuillères à soupe}}"`
- `quantity.range`: `"{min} à {max}"` en français, `"{min} to {max}"` en anglais
- `recipes.*` pour l'interface: titre de la page, «Ingrédients», «Étapes», «Pour {count} personnes», «Réinitialiser»
- `nav.recipes` pour l'entrée de navigation

Le **contenu** d'une recette (libellés d'ingrédients, textes d'étapes) n'est jamais traduit: il est écrit dans la langue de la recette.

## 6. Cache HTTP, sitemap, navigation, architecture

- `Recipe::getEtag()` renvoie `recipes:recipe:{id}:{updatedAt}`, sur le modèle des autres entités.
- `Recipe::getCacheItems()` renvoie `CacheItem::fromRoute(Route::LIST)` et `CacheItem::fromRoute(Route::VIEW, ['slug' => $this->slug])`, purgés via le listener EasyAdmin existant.
- Entrée `nav.recipes` dans `app.menu_definition` (`config/services.yaml`), pointant sur `Route::LIST` avec `Route::VIEW` en route secondaire pour l'état actif.
- `deptrac_layers.yaml` couvre le nouveau contexte sans modification: ses regex (`^App\\.+\\Domain\\`, etc.) matchent déjà `App\Recipes\…`.
- `deptrac_domains.yaml` n'est **pas** touché. Ses regex ciblent `App\Domain\Blog\` alors que les namespaces réels sont `App\Blog\Domain\`, donc le fichier ne couvre aucune classe aujourd'hui (`Allowed: 0`). Y ajouter une layer `Recipes` correcte serait pire que de ne rien faire: `make archi` passe `--fail-on-uncovered`, et les dépendances des classes nouvellement couvertes vers `Shared` et vers les vendors remonteraient comme non couvertes. Réparer ce fichier suppose de corriger les cinq regex **et** d'ajouter une layer `Vendor` attrape-tout, ce qui est une PR à part.

## 7. Tests

1. **`GridBuilderTest`** — la recette d'udon du §2.2: colonnes, lignes, `rowspan`, cellules vides et leur `colspan`, ordre des ingrédients. Plus les cas dégénérés: une seule étape, un ingrédient sans étape, plusieurs racines. C'est le test qui compte.
2. **`QuantityFormatterTest`** — entiers, fractions vulgaires, arrondi à deux décimales, fourchettes, quantité absente, accord singulier et pluriel dans les deux langues.
3. **`RecipeContentTypeTest`** — aller-retour `jsonb` vers objets vers `jsonb`, et rejet d'un JSON malformé.
4. **`ListControllerTest` / `ViewControllerTest`** — filtrage sur `visible` et sur la locale, 404 sur une recette masquée, redirection 301 sur locale discordante.

`RecipeFactory` (Foundry) fournit les fixtures, avec un état nommé reproduisant la recette d'udon.

## 8. Hors périmètre

Retenu comme non nécessaire pour cette première version: temps de préparation et de cuisson, photo, description longue, note libre en tête de tableau, tags, difficulté, recherche, impression.

## 9. Estimation

Environ 16 heures, soit deux à trois jours.

| Bloc                                          | Estimation |
| --------------------------------------------- | ---------- |
| Modèle, type DBAL, migration, repository      | 2 h        |
| `GridBuilder` et ses tests                    | 2 h        |
| Rendu Twig de la grille, fallback mobile, CSS | 3 h        |
| Contrôleurs Stimulus (portions, cochage)      | 2 h        |
| Éditeur Vue                                   | 4 h        |
| CRUD admin et validation                      | 2 h        |
| i18n, sitemap, cache, navigation, deptrac     | 1 h        |
