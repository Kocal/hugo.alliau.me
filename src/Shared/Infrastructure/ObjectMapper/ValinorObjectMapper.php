<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\ObjectMapper;

use App\Shared\Domain\ObjectMapper\Format;
use App\Shared\Domain\ObjectMapper\ObjectMapper;
use CuyZ\Valinor\Mapper\Source\Source;
use CuyZ\Valinor\Mapper\TreeMapper;
use CuyZ\Valinor\MapperBuilder;

final readonly class ValinorObjectMapper implements ObjectMapper
{
    public function __construct(
        private MapperBuilder $mapperBuilder
    ) {
    }

    #[\Override]
    public function map(string $className, mixed $source, Format $format): mixed
    {
        $mapper = $this->getMapper();
        $source = $this->createSource($source)->camelCaseKeys();

        return $mapper->map($className, $source);
    }

    private function getMapper(): TreeMapper
    {
        return $this->mapperBuilder->allowSuperfluousKeys()->mapper();
    }

    private function createSource(mixed $source): Source
    {
        return Source::json((string) $source);
    }
}
