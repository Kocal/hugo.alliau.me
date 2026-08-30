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
