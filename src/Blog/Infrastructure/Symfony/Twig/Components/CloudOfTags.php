<?php

declare(strict_types=1);

namespace App\Blog\Infrastructure\Symfony\Twig\Components;

use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;
use Symfony\UX\TwigComponent\Attribute\PreMount;

#[AsTwigComponent]
final class CloudOfTags
{
    /**
     * @var list<array{ tag: string, occurrences: int }>
     */
    public array $tags;

    /**
     * @param array<mixed> $data
     *
     * @return array<mixed>
     */
    #[PreMount]
    public function preMount(array $data): array
    {
        $optionsResolver = new OptionsResolver();
        $optionsResolver
            ->setIgnoreUndefined()
            ->setRequired(['tags'])
            ->setDefault('tags', function (OptionsResolver $resolver): void {
                $resolver
                    ->setPrototype(true)
                    ->setRequired('tag')
                    ->setAllowedTypes('tag', 'string')
                    ->setRequired('occurrences')
                    ->setAllowedTypes('occurrences', 'int')
                ;
            })
        ;

        return $optionsResolver->resolve($data) + $data;
    }
}
