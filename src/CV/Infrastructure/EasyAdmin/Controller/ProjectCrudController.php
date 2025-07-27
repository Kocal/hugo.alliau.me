<?php

declare(strict_types=1);

namespace App\CV\Infrastructure\EasyAdmin\Controller;

use App\CV\Domain\Project;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\UrlField;

/**
 * @extends AbstractCrudController<Project>
 */
class ProjectCrudController extends AbstractCrudController
{
    #[\Override]
    public static function getEntityFqcn(): string
    {
        return Project::class;
    }

    #[\Override]
    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setDefaultSort([
                'date' => 'DESC',
            ])
            ->showEntityActionsInlined()
        ;
    }

    #[\Override]
    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->hideOnForm();
        yield TextField::new('name');
        yield UrlField::new('url');
        yield TextareaField::new('description')->onlyOnForms();
        yield DateField::new('date')->setRequired(true);
        yield ArrayField::new('techStack');
    }
}
