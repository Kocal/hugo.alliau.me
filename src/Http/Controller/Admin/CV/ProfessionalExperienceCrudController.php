<?php

namespace App\Http\Controller\Admin\CV;

use App\Domain\CV\ProfessionalExperience;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\UrlField;

class ProfessionalExperienceCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return ProfessionalExperience::class;
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->hideOnForm();

        yield FormField::addColumn('col-xxl-8');
        yield FormField::addFieldset('Job Information');
        yield TextField::new('jobName');
        yield TextareaField::new('description')->onlyOnForms();
        yield DateField::new('startDate')->setColumns(6);
        yield DateField::new('endDate')->setColumns(6);
        yield ArrayField::new('badges');

        yield FormField::addColumn('col-xxl-4');
        yield FormField::addFieldset('Company Information');
        yield TextField::new('company');
        yield UrlField::new('url');
    }
}
