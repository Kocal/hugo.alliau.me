<?php

declare(strict_types=1);

namespace App\Tests\Places\Infrastructure\EasyAdmin\Controller;

use App\Places\Application\Command\CreatePlace;
use App\Places\Application\Command\CreatePlaceHandler;
use App\Places\Domain\Data\Address;
use App\Places\Domain\Data\Place;
use App\Places\Domain\Data\PlaceType;
use App\Places\Domain\Repository\PlaceRepository;
use App\Places\Infrastructure\EasyAdmin\Controller\PlaceCrudController;
use App\Places\Infrastructure\Google\Place\AddressComponent;
use App\Places\Infrastructure\Google\Place\Autocomplete;
use App\Tests\Places\Infrastructure\Google\Factory\AutocompleteFactory;
use App\User\Infrastructure\Foundry\Factory\UserFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

#[CoversClass(PlaceCrudController::class)]
#[UsesClass(Autocomplete::class)]
#[UsesClass(CreatePlace::class)]
#[UsesClass(CreatePlaceHandler::class)]
#[UsesClass(Address::class)]
#[UsesClass(Place::class)]
final class PlaceCrudControllerTest extends WebTestCase
{
    use Factories;
    use ResetDatabase;

    #[\Override]
    protected function tearDown(): void
    {
        self::ensureKernelShutdown();
    }

    public function testItImportsAPlaceFromGooglePlacesAutocomplete(): void
    {
        $client = self::createClient();
        $client->loginUser(UserFactory::createOne([
            'roles' => ['ROLE_ADMIN'],
        ]));

        $crawler = $client->request(Request::METHOD_GET, '/admin/place');
        self::assertResponseIsSuccessful();

        $form = $crawler->filter('[data-testid="import-from-google-places"]')
            ->form();
        $client->submit($form, [
            'place_autocomplete' => $this->toGoogleJson(AutocompleteFactory::seoulTower()),
        ]);

        self::assertResponseRedirects();

        $places = self::getContainer()->get(PlaceRepository::class)->findAll();
        $this->assertCount(1, $places);
        $this->assertSame('N Seoul Tower', $places[0]->getAddress()?->getName());
        $this->assertSame('Seoul', $places[0]->getAddress()?->getCity());
        $this->assertSame('Corée du Sud', $places[0]->getAddress()?->getCountry());
        $this->assertSame('https://maps.gstatic.com/mapfiles/place_api/icons/v2/generic_pinlet.svg', $places[0]->getIconMaskUri());
        $this->assertSame([
            PlaceType::TOURIST_ATTRACTION,
            PlaceType::POINT_OF_INTEREST,
            PlaceType::ESTABLISHMENT,
        ], $places[0]->getTypes());
    }

    private function toGoogleJson(Autocomplete $autocomplete): string
    {
        return json_encode([
            'name' => $autocomplete->name,
            'url' => $autocomplete->url,
            'formatted_address' => $autocomplete->formattedAddress,
            'geometry' => [
                'location' => [
                    'lat' => $autocomplete->geometry->location->lat,
                    'lng' => $autocomplete->geometry->location->lng,
                ],
            ],
            'address_components' => array_map(static fn (AddressComponent $component): array => [
                'long_name' => $component->longName,
                'short_name' => $component->shortName,
                'types' => $component->types,
            ], $autocomplete->addressComponents),
            'icon_mask_base_uri' => $autocomplete->iconMaskBaseUri,
            'types' => array_map(static fn (PlaceType $type): string => $type->value, $autocomplete->types),
        ], \JSON_THROW_ON_ERROR);
    }
}
