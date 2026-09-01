<?php

declare(strict_types=1);

namespace App\Recipes\Infrastructure\EasyAdmin\Field;

use App\Recipes\Infrastructure\EasyAdmin\Form\RecipeContentType;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;
use EasyCorp\Bundle\EasyAdminBundle\Field\FieldTrait;
use Symfony\Contracts\Translation\TranslatableInterface;

final class RecipeContentField implements FieldInterface
{
    use FieldTrait;

    #[\Override]
    public static function new(string $propertyName, TranslatableInterface|string|bool|null $label = null): self
    {
        return new self()
            ->setProperty($propertyName)
            ->setLabel($label)
            ->setFormType(RecipeContentType::class)
            ->addFormTheme('admin/fields/recipe_content.html.twig')
            ->onlyOnForms()
        ;
    }
}
