<?php

namespace App\Tests\Form;

use App\Entity\Currency;
use App\Form\CurrencyType;
use Symfony\Component\Form\Test\TypeTestCase;

class FormTest extends TypeTestCase
{
    public function testCurrencyTypeSubmitValidData(): void
    {
        $formData = [
            'alpabethicCode' => 'SEK',
            'decimalDigits' => 2,
            'entity' => 'Sweden',
            'numericCode' => '752',
        ];

        $model = new Currency();
        $form = $this->factory->create(CurrencyType::class, $model);

        $form->submit($formData);

        $this->assertTrue($form->isSynchronized());
        $this->assertSame('SEK', $model->getAlpabethicCode());
        $this->assertSame(2, $model->getDecimalDigits());
        $this->assertSame('Sweden', $model->getEntity());
        $this->assertSame('752', $model->getNumericCode());
    }

    public function testCurrencyTypeFormHasExpectedFields(): void
    {
        $form = $this->factory->create(CurrencyType::class);

        $this->assertTrue($form->has('alpabethicCode'));
        $this->assertTrue($form->has('decimalDigits'));
        $this->assertTrue($form->has('entity'));
        $this->assertTrue($form->has('numericCode'));
    }
}