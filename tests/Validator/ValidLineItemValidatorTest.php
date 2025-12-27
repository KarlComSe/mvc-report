<?php

namespace App\Tests\Validator;

use App\Entity\Account;
use App\Entity\JournalLineItem;
use App\Validator\Constraints\ValidLineItem;
use App\Validator\Constraints\ValidLineItemValidator;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;

/**
 * @extends ConstraintValidatorTestCase<ValidLineItemValidator>
 */
class ValidLineItemValidatorTest extends ConstraintValidatorTestCase
{
    protected function createValidator(): ValidLineItemValidator
    {
        return new ValidLineItemValidator();
    }

    public function testValidLineItemWithDebitPassesValidation(): void
    {
        $account = $this->createMock(Account::class);

        $lineItem = $this->createMock(JournalLineItem::class);
        $lineItem->method('getAccount')->willReturn($account);
        $lineItem->method('getDebitAmount')->willReturn(100.0);
        $lineItem->method('getCreditAmount')->willReturn(null);

        $this->validator->validate($lineItem, new ValidLineItem());

        $this->assertNoViolation();
    }

    public function testValidLineItemWithCreditPassesValidation(): void
    {
        $account = $this->createMock(Account::class);

        $lineItem = $this->createMock(JournalLineItem::class);
        $lineItem->method('getAccount')->willReturn($account);
        $lineItem->method('getDebitAmount')->willReturn(null);
        $lineItem->method('getCreditAmount')->willReturn(100.0);

        $this->validator->validate($lineItem, new ValidLineItem());

        $this->assertNoViolation();
    }

    public function testMissingAccountFailsValidation(): void
    {
        $lineItem = $this->createMock(JournalLineItem::class);
        $lineItem->method('getAccount')->willReturn(null);
        $lineItem->method('getDebitAmount')->willReturn(100.0);
        $lineItem->method('getCreditAmount')->willReturn(null);

        $constraint = new ValidLineItem();
        $this->validator->validate($lineItem, $constraint);

        $this->buildViolation($constraint->accountRequired)
            ->atPath('property.path.account')
            ->assertRaised();
    }

    public function testMissingBothAmountsFailsValidation(): void
    {
        $account = $this->createMock(Account::class);

        $lineItem = $this->createMock(JournalLineItem::class);
        $lineItem->method('getAccount')->willReturn($account);
        $lineItem->method('getDebitAmount')->willReturn(null);
        $lineItem->method('getCreditAmount')->willReturn(null);

        $constraint = new ValidLineItem();
        $this->validator->validate($lineItem, $constraint);

        $this->buildViolation($constraint->amountRequired)
            ->atPath('property.path.debitAmount')
            ->assertRaised();
    }

    public function testZeroAmountsFailsValidation(): void
    {
        $account = $this->createMock(Account::class);

        $lineItem = $this->createMock(JournalLineItem::class);
        $lineItem->method('getAccount')->willReturn($account);
        $lineItem->method('getDebitAmount')->willReturn(0.0);
        $lineItem->method('getCreditAmount')->willReturn(0.0);

        $constraint = new ValidLineItem();
        $this->validator->validate($lineItem, $constraint);

        $this->buildViolation($constraint->amountRequired)
            ->atPath('property.path.debitAmount')
            ->assertRaised();
    }

    public function testBothDebitAndCreditFailsValidation(): void
    {
        $account = $this->createMock(Account::class);

        $lineItem = $this->createMock(JournalLineItem::class);
        $lineItem->method('getAccount')->willReturn($account);
        $lineItem->method('getDebitAmount')->willReturn(100.0);
        $lineItem->method('getCreditAmount')->willReturn(50.0);

        $constraint = new ValidLineItem();
        $this->validator->validate($lineItem, $constraint);

        $this->buildViolation($constraint->bothAmounts)
            ->atPath('property.path.debitAmount')
            ->assertRaised();
    }

    public function testThrowsExceptionForInvalidConstraintType(): void
    {
        $this->expectException(UnexpectedTypeException::class);

        $constraint = $this->createMock(Constraint::class);
        $lineItem = $this->createMock(JournalLineItem::class);

        $this->validator->validate($lineItem, $constraint);
    }

    public function testThrowsExceptionForInvalidValueType(): void
    {
        $this->expectException(UnexpectedTypeException::class);

        $this->validator->validate("invalid value", new ValidLineItem());
    }
}