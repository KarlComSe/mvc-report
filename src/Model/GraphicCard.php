<?php

namespace App\Model;

use App\Model\Card;

class GraphicCard extends Card
{
    private string $graphic;

    public function __construct(string $suit, string $name, int $value, string $graphic, ?int $alternativeValue = null)
    {
        parent::__construct($suit, $name, $value, $alternativeValue);
        $this->graphic = $graphic;
    }

    /**
     * Returns an array containing the card's suit, name, value, alternative value, and graphic.
     *
     * @return array<mixed> The card's suit, name, value, alternative value, and graphic.
     */
    public function getCard(): array
    {
        return [$this->suit, $this->name, $this->value, $this->alternativeValue, $this->graphic];
    }
}
