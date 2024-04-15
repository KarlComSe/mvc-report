<?php

namespace App\Model;

class Card {
    protected $value;
    protected $name;
    protected $suit;

    public function __construct(string $suit, string $name, int $value, ?int $alternativeValue = null){
        $this->value = $value;
        $this->suit = $suit;
        $this->name = $name;
        $this->alternativeValue = $alternativeValue;
    }

    public function getCard(){
        return [$this->suit, $this->name, $this->value, $this->alternativeValue];
    }

    public function getValue(){
        return $this->value;
    }

    public function getSuit(){
        return $this->suit;
    }

    public function getAlternativeValue(){
        return $this->alternativeValue;
    }
}
