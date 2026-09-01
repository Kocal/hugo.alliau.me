<?php

declare(strict_types=1);

namespace App\Recipes\Infrastructure\EasyAdmin\Form;

use App\Recipes\Domain\Data\Recipe;
use App\Recipes\Domain\Data\Unit;
use App\Shared\Domain\Data\Locale;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @extends AbstractType<mixed>
 */
final class RecipeContentType extends AbstractType
{
    public function __construct(
        private readonly RecipeContentTransformer $transformer,
        private readonly TranslatorInterface $translator,
    ) {
    }

    /**
     * @param array<string, mixed> $options
     */
    #[\Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addModelTransformer($this->transformer);
    }

    /**
     * @param array<string, mixed> $options
     */
    #[\Override]
    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        // Les unités suivent la langue de la recette, pas celle de l'admin:
        // on écrit une recette française avec des cuillères à soupe.
        $recipe = $form->getParent()?->getData();
        $locale = $recipe instanceof Recipe ? $recipe->getLocale() : Locale::default();

        $view->vars['unit_labels'] = $this->unitLabels($locale);
    }

    /**
     * @return array<string, string>
     */
    private function unitLabels(Locale $locale): array
    {
        $labels = [];

        foreach (Unit::cases() as $unit) {
            $labels[$unit->value] = $unit->toTranslatable(1)->trans($this->translator, $locale->value);
        }

        return $labels;
    }

    #[\Override]
    public function getParent(): string
    {
        return HiddenType::class;
    }

    /**
     * C'est ce préfixe qui fait chercher à Symfony le bloc `recipe_content_widget`
     * du thème de formulaire, avant de retomber sur `hidden_widget`.
     */
    #[\Override]
    public function getBlockPrefix(): string
    {
        return 'recipe_content';
    }
}
