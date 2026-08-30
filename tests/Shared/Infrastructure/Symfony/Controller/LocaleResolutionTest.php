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
        $this->assertSame('en', $client->getRequest()->getLocale());
    }

    public function testHomeServesFrenchUnderFrPrefix(): void
    {
        $client = self::createClient();
        $client->request(Request::METHOD_GET, '/fr');

        $this->assertResponseIsSuccessful();
        $this->assertSame('fr', $client->getRequest()->getLocale());
    }

    public function testPlacesServesEnglish(): void
    {
        $placeRepository = new FakePlaceRepository();
        $placeRepository->add(new Place());

        $client = self::createClient();
        $client->getContainer()
            ->set(PlaceRepository::class, $placeRepository);

        $client->request(Request::METHOD_GET, '/places');
        $this->assertResponseIsSuccessful();
        $this->assertSame('en', $client->getRequest()->getLocale());
    }

    public function testLieuxServesFrench(): void
    {
        $placeRepository = new FakePlaceRepository();
        $placeRepository->add(new Place());

        $client = self::createClient();
        $client->getContainer()
            ->set(PlaceRepository::class, $placeRepository);

        $client->request(Request::METHOD_GET, '/fr/lieux');
        $this->assertResponseIsSuccessful();
        $this->assertSame('fr', $client->getRequest()->getLocale());
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
