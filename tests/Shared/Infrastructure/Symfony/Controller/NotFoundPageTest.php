<?php

declare(strict_types=1);

namespace App\Tests\Shared\Infrastructure\Symfony\Controller;

use App\Recipes\Domain\Repository\RecipeRepository;
use App\Tests\Recipes\Infrastructure\Double\Repository\FakeRecipeRepository;
use PHPUnit\Framework\Attributes\CoversNothing;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[CoversNothing]
final class NotFoundPageTest extends WebTestCase
{
    #[\Override]
    protected function tearDown(): void
    {
        self::ensureKernelShutdown();
    }

    public function testUnknownUrlRendersTheSiteNotFoundPage(): void
    {
        $client = self::createClient([
            'debug' => false,
        ]);
        $client->request(Request::METHOD_GET, '/does-not-exist');

        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
        $this->assertSelectorTextContains('h1', 'Page not found');
        $this->assertSelectorTextContains('header', 'Places');
    }

    public function testMissingFrenchContentRendersTheNotFoundPageInFrench(): void
    {
        $client = self::createClient([
            'debug' => false,
        ]);
        $client->getContainer()
            ->set(RecipeRepository::class, new FakeRecipeRepository());
        $client->request(Request::METHOD_GET, '/fr/recettes/does-not-exist');

        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
        $this->assertSelectorTextContains('h1', 'Page introuvable');
        $this->assertSelectorTextContains('header', 'Lieux');
    }
}
