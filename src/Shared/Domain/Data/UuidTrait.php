<?php

declare(strict_types=1);

namespace App\Shared\Domain\Data;

use Symfony\Component\Uid\Uuid;

trait UuidTrait
{
    private Uuid $uuid;

    final private function __construct()
    {
    }

    // NB: This is only required to let Doctrine use ID object as primary key in his Identity Map
    public function __toString()
    {
        return $this->toString();
    }

    public static function fromString(string $uuid): static
    {
        $id = new static();
        $id->uuid = Uuid::fromString($uuid);

        return $id;
    }

    public static function fromUuid(Uuid $uuid): static
    {
        $id = new static();
        $id->uuid = $uuid;

        return $id;
    }

    public static function generate(): static
    {
        // TODO: add support for nextGeneratedId

        return static::fromUuid(Uuid::v7());
    }

    public function uuid(): Uuid
    {
        return $this->uuid;
    }

    public function toString(): string
    {
        return $this->uuid->toString();
    }

    public function equals(self $to): bool
    {
        return $this->uuid->equals($to->uuid());
    }
}
