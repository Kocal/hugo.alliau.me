<?php

declare(strict_types=1);

namespace App\Tests\Recipes\Infrastructure\Symfony\Controller;

use App\Recipes\Domain\Data\Recipe;
use App\Recipes\Domain\Data\RecipeType;
use App\Recipes\Domain\Repository\RecipeRepository;
use App\Recipes\Infrastructure\Symfony\Controller\ListController;
use App\Shared\Domain\Data\Locale;
use App\Tests\Recipes\Infrastructure\Double\Repository\FakeRecipeRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[CoversClass(ListController::class)]
#[UsesClass(Recipe::class)]
final class ListControllerTest extends WebTestCase
{
    #[\Override]
    protected function tearDown(): void
    {
        self::ensureKernelShutdown();
    }

    public function testItListsEveryVisibleRecipeWhateverItsLanguage(): void
    {
        $repository = new FakeRecipeRepository();
        $repository->add(new Recipe()->setName('Visible English')->setSlug('visible-en')->setVisible(true)->setLocale(Locale::EN));
        $repository->add(new Recipe()->setName('Hidden English')->setSlug('hidden-en')->setLocale(Locale::EN));
        $repository->add(new Recipe()->setName('Visible French')->setSlug('visible-fr')->setVisible(true)->setLocale(Locale::FR));

        $client = self::createClient();
        $client->getContainer()
            ->set(RecipeRepository::class, $repository);

        $crawler = $client->request(Request::METHOD_GET, '/recipes');

        self::assertResponseIsSuccessful();
        self::assertResponseHeaderSame('cache-control', 'max-age=2592000, public');
        $this->assertStringContainsString('Visible English', $crawler->text());
        $this->assertStringContainsString('Visible French', $crawler->text());
        $this->assertStringNotContainsString('Hidden English', $crawler->text());
    }

    public function testEachRecipeCarriesItsFlagAndTypeAndLinksToItsOwnLocale(): void
    {
        $repository = new FakeRecipeRepository();
        $repository->add(new Recipe()->setName('Visible French')->setSlug('visible-fr')->setVisible(true)->setLocale(Locale::FR)->setType(RecipeType::DESSERT));

        $client = self::createClient();
        $client->getContainer()
            ->set(RecipeRepository::class, $repository);

        $crawler = $client->request(Request::METHOD_GET, '/recipes');

        self::assertResponseIsSuccessful();
        $this->assertStringContainsString(Locale::FR->flag(), $crawler->text());
        $this->assertStringContainsString('Dessert', $crawler->text());
        $this->assertSame(
            '/fr/recettes/visible-fr',
            $crawler->filter('[data-testid="recipe-link"]')
                ->attr('href'),
        );
    }

    public function testTheFrenchListLivesUnderItsOwnPath(): void
    {
        $repository = new FakeRecipeRepository();
        $repository->add(new Recipe()->setName('Visible French')->setSlug('visible-fr')->setVisible(true)->setLocale(Locale::FR));

        $client = self::createClient();
        $client->getContainer()
            ->set(RecipeRepository::class, $repository);

        $crawler = $client->request(Request::METHOD_GET, '/fr/recettes');

        self::assertResponseIsSuccessful();
        $this->assertStringContainsString('Visible French', $crawler->text());
    }

    public function testANotModifiedRequestReturnsAnEmpty304(): void
    {
        $repository = new FakeRecipeRepository();
        $repository->add(new Recipe()->setName('Visible English')->setSlug('visible-en')->setVisible(true)->setLocale(Locale::EN));

        $client = self::createClient();
        $client->getContainer()
            ->set(RecipeRepository::class, $repository);

        $client->request(Request::METHOD_GET, '/recipes');
        self::assertResponseIsSuccessful();
        $etag = $client->getResponse()
            ->headers->get('ETag');
        $this->assertNotNull($etag);

        $client->disableReboot();
        $client->request(Request::METHOD_GET, '/recipes', server: [
            'HTTP_IF_NONE_MATCH' => $etag,
        ]);

        self::assertResponseStatusCodeSame(Response::HTTP_NOT_MODIFIED);
        $this->assertSame('', $client->getResponse()->getContent());
    }

    public function testAnEmptyRepositoryShowsTheEmptyState(): void
    {
        $client = self::createClient();
        $client->getContainer()
            ->set(RecipeRepository::class, new FakeRecipeRepository());

        $crawler = $client->request(Request::METHOD_GET, '/recipes');

        self::assertResponseIsSuccessful();
        $this->assertStringContainsString('No recipe yet.', $crawler->text());
    }
}
