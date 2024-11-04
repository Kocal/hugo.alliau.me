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

    /**
     * @return class-string<Id>
     */
    abstract protected function getIdClass(): string;

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
    public function convertToPHPValue(mixed $value, AbstractPlatform $platform): ?Id
    {
        if ($value instanceof Id || null === $value) {
            return $value;
        }

        if (!\is_string($value)) {
            throw InvalidType::new($value, $this->getName(), ['null', 'string', Id::class]);
        }

        try {
            return $this->getIdClass()::fromString($value);
        } catch (\InvalidArgumentException $e) {
            throw ValueNotConvertible::new($value, $this->getName(), null, $e);
        }
    }

    /**
     * @throws ConversionException
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string
    {
        $toString = $this->hasNativeGuidType($platform) ? 'toRfc4122' : 'toBinary';

        if ($value instanceof Id) {
            return $value->$toString();
        }

        if (null === $value || '' === $value) {
            return null;
        }

        if (!\is_string($value)) {
            throw InvalidType::new($value, $this->getName(), ['null', 'string', Id::class]);
        }

        try {
            return $this->getIdClass()::fromString($value)->$toString();
        } catch (\InvalidArgumentException $e) {
            throw ValueNotConvertible::new($value, $this->getName(), null, $e);
        }
    }
    
    private function hasNativeGuidType(AbstractPlatform $platform): bool
    {
        return $platform->getGuidTypeDeclarationSQL([]) !== $platform->getStringTypeDeclarationSQL(['fixed' => true, 'length' => 36]);
    }
}
