<?php

namespace App\Model;

use App\Model\Card;

class GraphicCard extends Card
{
    private $graphic;

    public function __construct(string $suit, string $name, int $value, string $graphic, ?int $alternativeValue = null)
    {
        parent::__construct($suit, $name, $value, $alternativeValue);
        $this->graphic = $graphic;
    }

    public function getCard()
    {
        return [$this->suit, $this->name, $this->value, $this->alternativeValue, $this->graphic];
    }
}
