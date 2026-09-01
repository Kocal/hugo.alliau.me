<?php

declare(strict_types=1);

namespace App\Tests\Recipes\Infrastructure\Symfony\Controller;

use App\Recipes\Domain\Data\Ingredient;
use App\Recipes\Domain\Data\Recipe;
use App\Recipes\Domain\Data\RecipeContent;
use App\Recipes\Domain\Data\Step;
use App\Recipes\Domain\Repository\RecipeRepository;
use App\Shared\Domain\Data\Locale;
use App\Tests\Recipes\Infrastructure\Double\Repository\FakeRecipeRepository;
use PHPUnit\Framework\Attributes\CoversNothing;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;

#[CoversNothing]
final class RecipeLocaleSwitcherTest extends WebTestCase
{
    #[\Override]
    protected function tearDown(): void
    {
        self::ensureKernelShutdown();
    }

    private function visibleRecipe(Locale $locale = Locale::EN): Recipe
    {
        return new Recipe()
            ->setName('Udon noodles')
            ->setSlug('udon')
            ->setVisible(true)
            ->setLocale($locale)
            ->setContent(new RecipeContent([
                new Step('s1', 'Cuire les nouilles', [new Ingredient('i1', 'de nouilles Udon', 300.0)]),
            ]));
    }

    public function testTheLanguageSwitcherDoesNotLinkToAUrlThatRedirects(): void
    {
        $repository = new FakeRecipeRepository();
        $repository->add($this->visibleRecipe());

        $client = self::createClient();
        $client->getContainer()
            ->set(RecipeRepository::class, $repository);

        $crawler = $client->request(Request::METHOD_GET, '/recipes/udon');

        self::assertResponseIsSuccessful();

        $frenchFlag = $crawler->filter('header a[hreflang="fr"]');
        $this->assertCount(1, $frenchFlag);

        $href = $frenchFlag->attr('href');
        $this->assertNotNull($href);
        $this->assertNotSame('/fr/recettes/udon', $href, 'The switcher must not link to a URL the recipe controller would 301 away from.');
        $this->assertSame('/fr/recettes', $href);

        $client->request(Request::METHOD_GET, $href);
        self::assertResponseIsSuccessful();
    }

    public function testThePageDoesNotEmitHreflangAlternatesForALocaleTheRecipeDoesNotExistIn(): void
    {
        $repository = new FakeRecipeRepository();
        $repository->add($this->visibleRecipe());

        $client = self::createClient();
        $client->getContainer()
            ->set(RecipeRepository::class, $repository);

        $crawler = $client->request(Request::METHOD_GET, '/recipes/udon');

        self::assertResponseIsSuccessful();
        $this->assertCount(0, $crawler->filter('link[rel="alternate"][hreflang]'));
    }
}
