<?php

namespace App\Entity;

use App\Repository\CurrencyRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CurrencyRepository::class)]
class Currency
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 5)]
    private ?string $alpabethicCode = null;

    #[ORM\Column(type: Types::SMALLINT)]
    private ?int $decimalDigits = null;

    #[ORM\Column(length: 5)]
    private ?string $numericCode = null;

    #[ORM\Column(length: 255)]
    private ?string $entity = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAlpabethicCode(): ?string
    {
        return $this->alpabethicCode;
    }

    public function setAlpabethicCode(string $alpabethicCode): static
    {
        $this->alpabethicCode = $alpabethicCode;

        return $this;
    }

    public function getDecimalDigits(): ?int
    {
        return $this->decimalDigits;
    }

    public function setDecimalDigits(int $decimalDigits): static
    {
        $this->decimalDigits = $decimalDigits;

        return $this;
    }

    public function getNumericCode(): ?string
    {
        return $this->numericCode;
    }

    public function setNumericCode(string $numericCode): static
    {
        $this->numericCode = $numericCode;

        return $this;
    }

    public function getEntity(): ?string
    {
        return $this->entity;
    }

    public function setEntity(string $entity): static
    {
        $this->entity = $entity;

        return $this;
    }
}
