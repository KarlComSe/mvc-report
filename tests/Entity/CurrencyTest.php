<?php

namespace App\Tests\Entity;

use App\Entity\Currency;
use PHPUnit\Framework\TestCase;

class CurrencyTest extends TestCase
{
    public function testBasicGettersAndSetters(): void
    {
        $currency = new Currency();

        $currency->setAlpabethicCode('abc');
        $currency->setNumericCode('123');
        $currency->setEntity('abc');
        $currency->setDecimalDigits(2);

        $this->assertEquals('abc', $currency->getAlpabethicCode());
        $this->assertEquals('abc', $currency->getEntity());
        $this->assertEquals('123', $currency->getNumericCode());
        $this->assertEquals(2, $currency->getDecimalDigits());
    }

    public function testGetIdReturnsNullBeforePersistence(): void
    {
        $currency = new Currency();
        $this->assertNull($currency->getId());
    }
}
