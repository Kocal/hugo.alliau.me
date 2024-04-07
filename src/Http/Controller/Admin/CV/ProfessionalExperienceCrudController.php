<?php

namespace App\Http\Controller\Admin\CV;

use App\Domain\CV\ProfessionalExperience;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
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
        return [
            IdField::new('id')->setDisabled(),
            TextField::new('company'),
            UrlField::new('url'),
            TextField::new('jobName'),
            TextEditorField::new('description'),
            DateField::new('startDate'),
            DateField::new('endDate'),
            ArrayField::new('badges'),
        ];
    }

}
