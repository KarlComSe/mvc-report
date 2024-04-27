<?php

namespace App\Model;

class Card
{
    public int $value;
    public string $name;
    public string $suit;
    public ?int $alternativeValue;

    public const UNICODE_CARDS = [
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

    /**
     * Get the card details.
     *
     * @return array<string, string, int, ?int> An array containing the suit, name, value, and alternative value.
     */
    public function getCard(): array
    {
        return [$this->suit, $this->name, $this->value, $this->alternativeValue];
    }

    public function getValue(): int
    {
        return $this->value;
    }

    public function getSuit(): string
    {
        return $this->suit;
    }

    public function getAlternativeValue(): ?int
    {
        return $this->alternativeValue;
    }

    public static function getUnicodeRepresentation(int $value, string $suit): string
    {
        return self::UNICODE_CARDS[$suit][$value];
    }

    public function __toString(): string
    {
        return $this->getUnicodeRepresentation($this->value, strtolower($this->suit));
    }
}
