<?php

namespace App\Places\Domain;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Embeddable]
class Address
{
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $formattedAddress = null;

    #[ORM\Column(length: 100)]
    private ?string $name = null;

    #[ORM\Column(length: 100)]
    private ?string $city = null;

    #[ORM\Column(length: 40)]
    private ?string $country = null;

    /**
     * @var array{0: float, 1: float }
     */
    #[ORM\Column(type: Types::JSON, options: ['jsonb' => true])]
    #[Assert\Count(exactly: 2)]
    private array $coordinates = [];

    public function getFormattedAddress(): ?string
    {
        return $this->formattedAddress;
    }

    public function setFormattedAddress(?string $formattedAddress): Address
    {
        $this->formattedAddress = $formattedAddress;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(?string $city): static
    {
        $this->city = $city;

        return $this;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(?string $country): static
    {
        $this->country = $country;

        return $this;
    }


    public function getCoordinates(): array
    {
        return $this->coordinates;
    }

    public function setCoordinates(array $coordinates): static
    {
        $this->coordinates = $coordinates;

        return $this;
    }
}
