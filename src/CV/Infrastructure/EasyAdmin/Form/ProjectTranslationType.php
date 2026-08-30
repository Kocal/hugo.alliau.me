<?php

declare(strict_types=1);

namespace App\CV\Infrastructure\EasyAdmin\Form;

use App\CV\Domain\Data\ProjectTranslation;
use App\Shared\Domain\Data\Locale;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends AbstractType<ProjectTranslation>
 */
final class ProjectTranslationType extends AbstractType
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
            ->add('description', TextareaType::class)
        ;
    }

    #[\Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ProjectTranslation::class,
        ]);
    }
}
