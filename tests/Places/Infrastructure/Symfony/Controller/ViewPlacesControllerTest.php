<?php

declare(strict_types=1);

namespace App\Tests\Places\Infrastructure\Symfony\Controller;

use App\Places\Domain\Address;
use App\Places\Domain\Place;
use App\Places\Domain\Repository\PlaceRepository;
use App\Places\Infrastructure\Symfony\Controller\ViewPlacesController;
use App\Shared\Domain\HttpCache\CacheItem;
use App\Shared\Infrastructure\Twig\Extension\JsonLdExtension;
use App\Tests\Places\Infrastructure\Double\Repository\FakePlaceRepository;
use App\User\Infrastructure\Symfony\Security\Authenticator\LoginFormAuthenticator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;

#[CoversClass(ViewPlacesController::class)]
#[UsesClass(Address::class)]
#[UsesClass(Place::class)]
#[UsesClass(CacheItem::class)]
#[UsesClass(JsonLdExtension::class)]
#[UsesClass(LoginFormAuthenticator::class)]
class ViewPlacesControllerTest extends WebTestCase
{
    #[\Override]
    protected function tearDown(): void
    {
        static::ensureKernelShutdown();
    }

    public function testItShowsPlaces(): void
    {
        $placeRepository = new FakePlaceRepository();
        $placeRepository->add(new Place());

        $client = static::createClient();
        $client->getContainer()->set(PlaceRepository::class, $placeRepository);

        $client->request(Request::METHOD_GET, '/places');

        $this->assertResponseIsSuccessful();
        $this->assertResponseHasHeader('cache-control', 'public, max-age=1200');
    }
}
