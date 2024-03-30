<?php

namespace App\Http\Controller\Admin\Blog;

use App\Domain\Blog\Post;
use App\Domain\Blog\PostSeo;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\SlugField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class PostCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Post::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id'),
            TextField::new('title'),
            SlugField::new('slug')
                ->setTargetFieldName('title'),
            TextareaField::new('description'),
            TextareaField::new('content'),
            DateTimeField::new('publishedAt'),
            ArrayField::new('seo.dependencies'),
            ChoiceField::new('seo.proficiencyLevel')
                ->setChoices(array_combine(PostSeo::PROFICIENCY_LEVEL, PostSeo::PROFICIENCY_LEVEL))
        ];
    }
}
