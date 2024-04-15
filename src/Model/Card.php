<?php

namespace App\Model;

class Card
{
    public $value;
    public $name;
    public $suit;
    public $alternativeValue;

    public static $UNICODE_CARDS = [
        'spade' => [
            '1' => '🂡',
            '2' => '🂢',
            '3' => '🂣',
            '4' => '🂤',
            '5' => '🂥',
            '6' => '🂦',
            '7' => '🂧',
            '8' => '🂨',
            '9' => '🂩',
            '10' => '🂪',
            '11' => '🂫',
            '12' => '🂭',
            '13' => '🂮'
        ],
        'heart' => [
            '1' => '🂱',
            '2' => '🂲',
            '3' => '🂳',
            '4' => '🂴',
            '5' => '🂵',
            '6' => '🂶',
            '7' => '🂷',
            '8' => '🂸',
            '9' => '🂹',
            '10' => '🂺',
            '11' => '🂻',
            '12' => '🂽',
            '13' => '🂾'
        ],
        'diamond' => [
            '1' => '🃁',
            '2' => '🃂',
            '3' => '🃃',
            '4' => '🃄',
            '5' => '🃅',
            '6' => '🃆',
            '7' => '🃇',
            '8' => '🃈',
            '9' => '🃉',
            '10' => '🃊',
            '11' => '🃋',
            '12' => '🃍',
            '13' => '🃎'
        ],
        'club' => [
            '1' => '🃑',
            '2' => '🃒',
            '3' => '🃓',
            '4' => '🃔',
            '5' => '🃕',
            '6' => '🃖',
            '7' => '🃗',
            '8' => '🃘',
            '9' => '🃙',
            '10' => '🃚',
            '11' => '🃛',
            '12' => '🃝',
            '13' => '🃞'
        ]
    ];

    public function __construct(string $suit, string $name, int $value, ?int $alternativeValue = null)
    {
        $this->value = $value;
        $this->suit = $suit;
        $this->name = $name;
        $this->alternativeValue = $alternativeValue;
    }

    public function getCard()
    {
        return [$this->suit, $this->name, $this->value, $this->alternativeValue];
    }

    public function getValue()
    {
        return $this->value;
    }

    public function getSuit()
    {
        return $this->suit;
    }

    public function getAlternativeValue()
    {
        return $this->alternativeValue;
    }

    public static function getUnicodeRepresentation(int $value, string $suit)
    {
        return self::$UNICODE_CARDS[$suit][$value];
    }

    public function __toString()
    {
        return $this->getUnicodeRepresentation($this->value, strtolower($this->suit));
    }
}
