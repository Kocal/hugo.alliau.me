<?php

declare(strict_types=1);

namespace App\Places\Infrastructure\EasyAdmin\Controller;

use App\Places\Domain\Command\CreatePlace;
use App\Places\Domain\Google\Place\Autocomplete;
use App\Places\Domain\Place;
use App\Places\Domain\PlaceType;
use App\Shared\Domain\CQRS\CommandBus;
use App\Shared\Domain\ObjectMapper\Command\MapObject;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class PlaceCrudController extends AbstractCrudController
{
    public function __construct(
        #[Autowire(param: 'kernel.debug')]
        private readonly bool $kernelDebug,
    ) {
    }

    #[\Override]
    public static function getEntityFqcn(): string
    {
        return Place::class;
    }

    #[\Override]
    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setDefaultSort([
                'updatedAt' => 'DESC',
            ])
            ->showEntityActionsInlined()
            ->overrideTemplates([
                'crud/index' => 'admin/places/crud/index.html.twig',
            ])
        ;
    }

    #[\Override]
    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->hideOnForm();

        yield FormField::addColumn('col-xxl-8');
        yield FormField::addFieldset('Place');
        yield ChoiceField::new('types')
            ->allowMultipleChoices()
            ->autocomplete()
            ->renderAsBadges()
            ->setChoices(fn (): array => PlaceType::cases())
            ->setFormTypeOption('class', PlaceType::class)
        ;

        yield FormField::addColumn('col-xxl-4');
        yield FormField::addFieldset('Address');
        yield TextField::new('address.name');
        yield TextField::new('address.formattedAddress');
        yield TextField::new('address.country');
        yield TextField::new('address.city');
        yield ArrayField::new('address.coordinates')->onlyOnForms();
    }

    #[\Override]
    public function configureActions(Actions $actions): Actions
    {
        $goToStripe = Action::new('importFromGooglePlaces', 'Import')
            ->displayAsButton()
            ->addCssClass('h-auto')
            ->setHtmlAttributes([
                'type' => 'submit',
            ])
            ->setTemplatePath('admin/places/crud/action_import_from_google_places.html.twig')
            ->linkToCrudAction('importFromGooglePlaces')
            ->createAsGlobalAction();

        $actions->add(Crud::PAGE_INDEX, $goToStripe);

        return $actions;
    }

    public function importFromGooglePlaces(
        AdminContext $context,
        Request $request,
        LoggerInterface $logger,
        CommandBus $commandBus,
    ): Response {
        if (! $this->isCsrfTokenValid('ea-import-from-google-places', $request->request->getString('token'))) {
            $this->addFlash('danger', 'Invalid CSRF token.');

            goto redirect_to_index;
        }

        if ('' === $placeAutocompleteJson = $request->request->getString('place_autocomplete')) {
            $this->addFlash('danger', 'Please enter a location.');

            goto redirect_to_index;
        }

        try {
            $googleAutocomplete = $commandBus->dispatch(MapObject::fromJson(Autocomplete::class, $placeAutocompleteJson));
            $commandBus->dispatch(CreatePlace::fromGoogleAutocomplete($googleAutocomplete));
        } catch (\Throwable $throwable) {
            if ($this->kernelDebug) {
                throw $throwable;
            }

            $logger->error('An error occurred while importing places.', [
                'exception' => $throwable,
            ]);
            $this->addFlash('danger', 'An error occurred while importing places.');

            goto redirect_to_index;
        }

        $this->addFlash('success', 'Places imported successfully.');

        redirect_to_index:
        return $this->redirectToRoute('admin_place_index');
    }
}
