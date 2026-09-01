<?php

declare(strict_types=1);

namespace App\Recipes\Infrastructure\EasyAdmin\Controller;

use App\Recipes\Domain\Data\Recipe;
use App\Recipes\Domain\Data\RecipeType;
use App\Recipes\Infrastructure\EasyAdmin\Field\RecipeContentField;
use App\Shared\Domain\Data\Locale;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\SlugField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\UrlField;
use Symfony\Contracts\Translation\TranslatableInterface;

/**
 * @extends AbstractCrudController<Recipe>
 */
class RecipeCrudController extends AbstractCrudController
{
    #[\Override]
    public static function getEntityFqcn(): string
    {
        return Recipe::class;
    }

    #[\Override]
    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setDefaultSort([
                'updatedAt' => 'DESC',
            ])
            ->showEntityActionsInlined()
        ;
    }

    #[\Override]
    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->hideOnForm();

        yield FormField::addFieldset('Identification');
        yield TextField::new('name', 'Nom');
        yield SlugField::new('slug', 'Slug')
            ->setTargetFieldName('name')
            ->setHelp('Utilisé dans l\'URL de la recette.')
            ->onlyOnForms()
        ;

        yield FormField::addFieldset('Classification');
        yield ChoiceField::new('type', 'Type')
            ->setFormTypeOption('choice_label', static fn (RecipeType $case): TranslatableInterface => $case->toTranslatable())
            ->setColumns(3)
        ;
        yield ChoiceField::new('locale', 'Langue')
            ->setFormTypeOption('choice_label', static fn (Locale $case): TranslatableInterface => $case->toTranslatable())
            ->setColumns(3)
        ;
        yield IntegerField::new('servings', 'Personnes')
            ->setHelp('Nombre de personnes pour lesquelles les quantités sont prévues.')
            ->setColumns(3)
        ;
        yield BooleanField::new('visible', 'Visible')->setColumns(3);

        yield FormField::addFieldset('Source');
        yield TextField::new('sourceLabel', 'Source')
            ->setHelp('Personne ou publication dont la recette est adaptée, par exemple « Maangchi ».')
            ->hideOnIndex()
        ;
        yield UrlField::new('sourceUrl', 'URL de la source')
            ->setHelp('Lien vers la recette originale.')
            ->hideOnIndex()
        ;

        yield FormField::addFieldset('Contenu');
        yield RecipeContentField::new('content', 'Contenu')->setColumns(12);
    }
}
