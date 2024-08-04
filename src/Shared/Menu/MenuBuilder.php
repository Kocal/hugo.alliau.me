<?php

declare(strict_types=1);

namespace App\Shared\Menu;

use App\Blog\Domain\Route as RouteBlog;
use App\CV\Domain\Route as RouteCv;
use App\Places\Domain\Route as RoutePlaces;
use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag(name: 'knp_menu.menu_builder', attributes: [
    'method' => 'createMainMenu',
    'alias' => 'main',
])]
final class MenuBuilder
{
    public function __construct(
        private FactoryInterface $factory
    ) {
    }

    /**
     * @param array<string, mixed> $options
     */
    public function createMainMenu(array $options): ItemInterface
    {
        $menu = $this->factory->createItem('root');

        $menu->addChild('Blog', [
            'route' => RouteBlog::HOME->value,
            'extras' => [
                'routes' => [
                    [
                        'route' => RouteBlog::POST_VIEW->value,
                    ],
                    [
                        'route' => RouteBlog::TAG_LIST->value,
                    ],
                    [
                        'route' => RouteBlog::TAG_VIEW->value,
                    ],
                ],
            ],
        ]);

        $menu->addChild('CV', [
            'route' => RouteCv::INDEX->value,
        ]);

        $menu->addChild('Places', [
            'route' => RoutePlaces::INDEX->value,
        ]);

        return $menu;
    }
}
