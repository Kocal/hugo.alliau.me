<?php

declare(strict_types=1);

namespace App\Tests\Recipes\Infrastructure\Symfony\Controller;

use App\Recipes\Domain\Data\Ingredient;
use App\Recipes\Domain\Data\Recipe;
use App\Recipes\Domain\Data\RecipeContent;
use App\Recipes\Domain\Data\Step;
use App\Recipes\Domain\Repository\RecipeRepository;
use App\Recipes\Infrastructure\Symfony\Controller\ViewController;
use App\Shared\Domain\Data\Locale;
use App\Tests\Recipes\Infrastructure\Double\Repository\FakeRecipeRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[CoversClass(ViewController::class)]
#[UsesClass(Recipe::class)]
#[UsesClass(RecipeContent::class)]
#[UsesClass(Step::class)]
#[UsesClass(Ingredient::class)]
final class ViewControllerTest extends WebTestCase
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

    public function testItRendersAVisibleRecipe(): void
    {
        $repository = new FakeRecipeRepository();
        $repository->add($this->visibleRecipe());

        $client = self::createClient();
        $client->getContainer()
            ->set(RecipeRepository::class, $repository);

        $client->request(Request::METHOD_GET, '/recipes/udon');

        self::assertResponseIsSuccessful();
        self::assertResponseHeaderSame('cache-control', 'max-age=2592000, public');
    }

    public function testANotModifiedRequestReturnsAnEmpty304(): void
    {
        $repository = new FakeRecipeRepository();
        $repository->add($this->visibleRecipe());

        $client = self::createClient();
        $client->getContainer()
            ->set(RecipeRepository::class, $repository);

        $client->request(Request::METHOD_GET, '/recipes/udon');
        self::assertResponseIsSuccessful();
        $etag = $client->getResponse()
            ->headers->get('ETag');
        $this->assertNotNull($etag);

        $client->disableReboot();
        $client->request(Request::METHOD_GET, '/recipes/udon', server: [
            'HTTP_IF_NONE_MATCH' => $etag,
        ]);

        self::assertResponseStatusCodeSame(Response::HTTP_NOT_MODIFIED);
        $this->assertSame('', $client->getResponse()->getContent());
    }

    public function testAHiddenRecipeIsNotFound(): void
    {
        $repository = new FakeRecipeRepository();
        $repository->add(new Recipe()->setName('Draft')->setSlug('draft')->setLocale(Locale::EN));

        $client = self::createClient();
        $client->getContainer()
            ->set(RecipeRepository::class, $repository);

        $client->request(Request::METHOD_GET, '/recipes/draft');

        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    public function testAnUnknownSlugIsNotFound(): void
    {
        $client = self::createClient();
        $client->getContainer()
            ->set(RecipeRepository::class, new FakeRecipeRepository());

        $client->request(Request::METHOD_GET, '/recipes/nope');

        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    public function testAFrenchRecipeRedirectsToItsOwnLocale(): void
    {
        $repository = new FakeRecipeRepository();
        $repository->add($this->visibleRecipe(Locale::FR));

        $client = self::createClient();
        $client->getContainer()
            ->set(RecipeRepository::class, $repository);

        $client->request(Request::METHOD_GET, '/recipes/udon');

        self::assertResponseStatusCodeSame(Response::HTTP_MOVED_PERMANENTLY);
        self::assertResponseRedirects('/fr/recettes/udon');
    }

    public function testAnEnglishRecipeRedirectsToItsOwnLocale(): void
    {
        $repository = new FakeRecipeRepository();
        $repository->add($this->visibleRecipe());

        $client = self::createClient();
        $client->getContainer()
            ->set(RecipeRepository::class, $repository);

        $client->request(Request::METHOD_GET, '/fr/recettes/udon');

        self::assertResponseStatusCodeSame(Response::HTTP_MOVED_PERMANENTLY);
        self::assertResponseRedirects('/recipes/udon');
    }
}
