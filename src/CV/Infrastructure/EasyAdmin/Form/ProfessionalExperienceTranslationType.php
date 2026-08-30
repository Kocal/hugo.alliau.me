<?php

declare(strict_types=1);

namespace App\CV\Infrastructure\EasyAdmin\Form;

use App\CV\Domain\Data\ProfessionalExperienceTranslation;
use App\Shared\Domain\Data\Locale;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends AbstractType<ProfessionalExperienceTranslation>
 */
final class ProfessionalExperienceTranslationType extends AbstractType
{
    /**
     * @param array<string, mixed> $options
     */
    #[\Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('locale', EnumType::class, [
                'class' => Locale::class,
            ])
            ->add('jobName', TextType::class)
            ->add('description', TextareaType::class)
            ->add('badges', CollectionType::class, [
                'entry_type' => TextType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'required' => false,
            ])
        ;
    }

    #[\Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ProfessionalExperienceTranslation::class,
        ]);
    }
}
