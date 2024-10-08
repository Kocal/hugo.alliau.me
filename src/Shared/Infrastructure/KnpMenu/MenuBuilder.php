<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\KnpMenu;

use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

#[AutoconfigureTag(name: 'knp_menu.menu_builder', attributes: [
    'method' => 'createMainMenu',
    'alias' => 'main',
])]
final readonly class MenuBuilder
{
    /**
     * @param list<array{
     *     label: string,
     *     route: string,
     *     extras?: array{
     *         routes?: list<array{ route: string }>
     *     }
     * }> $menuDefinition
     */
    public function __construct(
        #[Autowire(param: 'app.menu_definition')]
        private array $menuDefinition,
        private FactoryInterface $factory,
    ) {
    }

    public function createMainMenu(): ItemInterface
    {
        $menu = $this->factory->createItem('root');

        foreach ($this->menuDefinition as $item) {
            $label = $item['label'];
            $options = $item;
            unset($options['label']);

            $menu->addChild($label, $options);
        }

        return $menu;
    }
}
