<?php

namespace App\Admin\Infrastructure\Http\Blog;

use App\Blog\Domain\Post;
use App\Blog\Domain\PostSeo;
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

class PostCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Post::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setDefaultSort([
                'publishedAt' => 'DESC',
            ])
            ->showEntityActionsInlined()
        ;
    }

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
        ;

        yield FormField::addColumn('col-xxl-4');
        yield FormField::addFieldset('Publishing');
        yield ChoiceField::new('status');
        yield DateTimeField::new('publishedAt');

        yield FormField::addFieldset('SEO');
        yield ArrayField::new('seo.dependencies')
            ->setLabel('Dependencies');
        yield ChoiceField::new('seo.proficiencyLevel')
            ->setLabel('Proficiency Level')
            ->setChoices(array_combine(PostSeo::PROFICIENCY_LEVEL, PostSeo::PROFICIENCY_LEVEL));
    }
}
