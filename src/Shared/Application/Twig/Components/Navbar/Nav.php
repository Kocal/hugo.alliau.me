<?php

declare(strict_types=1);

namespace App\Shared\Application\Twig\Components\Navbar;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
final class Nav
{
    public string $label;
}
