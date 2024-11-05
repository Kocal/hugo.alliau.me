<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Database\Doctrine\DBAL\Types;

use App\Shared\Domain\Data\ValueObject\Id;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\Exception\InvalidType;
use Doctrine\DBAL\Types\Exception\ValueNotConvertible;
use Doctrine\DBAL\Types\Type;

abstract class AbstractIdType extends Type
{
    abstract public function getName(): string;

    #[\Override]
    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        if ($this->hasNativeGuidType($platform)) {
            return $platform->getGuidTypeDeclarationSQL($column);
        }

        return $platform->getBinaryTypeDeclarationSQL([
            'length' => 16,
            'fixed' => true,
        ]);
    }

    /**
     * @throws ConversionException
     */
    #[\Override]
    public function convertToPHPValue(mixed $value, AbstractPlatform $platform): ?Id
    {
        if ($value instanceof Id || $value === null) {
            return $value;
        }

        if (! \is_string($value)) {
            throw InvalidType::new($value, $this->getName(), ['null', 'string', Id::class]);
        }

        try {
            return $this->getIdClass()::fromString($value);
        } catch (\InvalidArgumentException $invalidArgumentException) {
            throw ValueNotConvertible::new($value, $this->getName(), null, $invalidArgumentException);
        }
    }

    /**
     * @throws ConversionException
     */
    #[\Override]
    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string
    {
        $toString = $this->hasNativeGuidType($platform) ? 'toRfc4122' : 'toBinary';

        if ($value instanceof Id) {
            return $value->{$toString}();
        }

        if ($value === null || $value === '') {
            return null;
        }

        if (! \is_string($value)) {
            throw InvalidType::new($value, $this->getName(), ['null', 'string', Id::class]);
        }

        try {
            return $this->getIdClass()::fromString($value)->{$toString}();
        } catch (\InvalidArgumentException $invalidArgumentException) {
            throw ValueNotConvertible::new($value, $this->getName(), null, $invalidArgumentException);
        }
    }

    /**
     * @return class-string<Id>
     */
    abstract protected function getIdClass(): string;

    private function hasNativeGuidType(AbstractPlatform $platform): bool
    {
        return $platform->getGuidTypeDeclarationSQL([]) !== $platform->getStringTypeDeclarationSQL([
            'fixed' => true,
            'length' => 36,
        ]);
    }
}
