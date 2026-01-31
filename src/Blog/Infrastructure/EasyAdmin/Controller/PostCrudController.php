<?php

declare(strict_types=1);

namespace App\Blog\Infrastructure\EasyAdmin\Controller;

use App\Blog\Domain\Data\Post;
use App\Blog\Domain\Data\PostSeo;
use App\Blog\Domain\Data\Route;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CodeEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\SlugField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

/**
 * @extends AbstractCrudController<Post>
 */
class PostCrudController extends AbstractCrudController
{
    #[\Override]
    public static function getEntityFqcn(): string
    {
        return Post::class;
    }

    #[\Override]
    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setDefaultSort([
                'publishedAt' => 'DESC',
            ])
            ->showEntityActionsInlined()
        ;
    }

    #[\Override]
    public function configureActions(Actions $actions): Actions
    {
        $actionView = Action::new('View', icon: 'fas fa-eye')
            ->linkToUrl(fn (Post $post): string => $this->generateUrl(Route::POST_VIEW->value, [
                'slug' => $post->getSlug(),
            ]))
            ->displayIf(static fn (Post $post): bool => $post->isPublished())
        ;

        $actionPreview = Action::new('Preview', icon: 'fas fa-eye-slash')
            ->linkToUrl(fn (Post $post): string => $this->generateUrl(Route::POST_VIEW->value, [
                'slug' => $post->getSlug(),
                'preview' => 'true',
            ]))
            ->displayIf(static fn (Post $post): bool => $post->isDraft());

        $actions
            ->add(Crud::PAGE_INDEX, $actionView)
            ->add(Crud::PAGE_INDEX, $actionPreview)
        ;

        return $actions;
    }

    #[\Override]
    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->hideOnForm();

        yield FormField::addColumn('col-xxl-8');
        yield TextField::new('title');
        yield SlugField::new('slug')->setTargetFieldName('title')->onlyOnForms();
        yield TextareaField::new('description');
        yield ArrayField::new('tags')
            ->onlyOnForms();
        yield CodeEditorField::new('content')
            ->onlyOnForms()
            ->setLanguage('markdown')
            ->hideLineNumbers()
        ;

        yield FormField::addColumn('col-xxl-4');
        yield FormField::addFieldset('Publishing');
        yield ChoiceField::new('status')
            ->setTemplatePath('admin/fields/post_status.html.twig');
        yield DateTimeField::new('publishedAt');

        yield FormField::addFieldset('SEO');
        yield ArrayField::new('seo.dependencies')
            ->setLabel('Dependencies')
            ->onlyOnForms()
        ;
        yield ChoiceField::new('seo.proficiencyLevel')
            ->setLabel('Proficiency Level')
            ->setChoices(array_combine(PostSeo::PROFICIENCY_LEVEL, PostSeo::PROFICIENCY_LEVEL))
            ->onlyOnForms()
        ;
    }
}
