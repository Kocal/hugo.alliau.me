<?php

namespace App\Shared\Menu;

use App\Domain\Routing\ValueObject\RouteName;
use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag(name: 'knp_menu.menu_builder', attributes: ['method' => 'createMainMenu', 'alias' => 'main'])]
final class MenuBuilder
{
    public function __construct(
        private FactoryInterface $factory
    )
    {
    }

    public function createMainMenu(array $options): ItemInterface
    {
        $menu = $this->factory->createItem('root');

        $menu->addChild('Blog', ['route' => RouteName::BLOG_HOME]);
        $menu->addChild('CV', ['route' => RouteName::CV_HOME]);

        return $menu;
    }
}