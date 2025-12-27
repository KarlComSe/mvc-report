<?php

namespace App\Tests\Validator;

use App\Entity\JournalEntry;
use App\Entity\JournalLineItem;
use App\Validator\Constraints\BalancedJournalEntry;
use App\Validator\Constraints\BalancedJournalEntryValidator;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

/**
 * @extends ConstraintValidatorTestCase<BalancedJournalEntryValidator>
 */
class BalancedJournalEntryValidatorTest extends ConstraintValidatorTestCase
{
    protected function createValidator(): BalancedJournalEntryValidator
    {
        return new BalancedJournalEntryValidator();
    }

    public function testBalancedEntryPassesValidation(): void
    {
        $lineItem1 = $this->createMock(JournalLineItem::class);
        $lineItem1->method('getDebitAmount')->willReturn(100.0);
        $lineItem1->method('getCreditAmount')->willReturn(0.0);

        $lineItem2 = $this->createMock(JournalLineItem::class);
        $lineItem2->method('getDebitAmount')->willReturn(0.0);
        $lineItem2->method('getCreditAmount')->willReturn(100.0);

        $journalEntry = $this->createMock(JournalEntry::class);
        $journalEntry->method('getJournalLineItems')->willReturn(new ArrayCollection([$lineItem1, $lineItem2]));

        $this->validator->validate($journalEntry, new BalancedJournalEntry());

        $this->assertNoViolation();
    }

    public function testUnbalancedEntryFailsValidation(): void
    {
        $lineItem1 = $this->createMock(JournalLineItem::class);
        $lineItem1->method('getDebitAmount')->willReturn(100.0);
        $lineItem1->method('getCreditAmount')->willReturn(0.0);

        $lineItem2 = $this->createMock(JournalLineItem::class);
        $lineItem2->method('getDebitAmount')->willReturn(0.0);
        $lineItem2->method('getCreditAmount')->willReturn(50.0);

        $journalEntry = $this->createMock(JournalEntry::class);
        $journalEntry->method('getJournalLineItems')->willReturn(new ArrayCollection([$lineItem1, $lineItem2]));

        $constraint = new BalancedJournalEntry();
        $this->validator->validate($journalEntry, $constraint);

        $this->buildViolation($constraint->message)
            ->setParameter('{{ debit }}', '100.00')
            ->setParameter('{{ credit }}', '50.00')
            ->assertRaised();
    }

    public function testEmptyLineItemsPassValidation(): void
    {
        $journalEntry = $this->createMock(JournalEntry::class);
        $journalEntry->method('getJournalLineItems')->willReturn(new ArrayCollection());

        $this->validator->validate($journalEntry, new BalancedJournalEntry());

        $this->assertNoViolation();
    }

    public function testNullAmountsAreTreatedAsZero(): void
    {
        $lineItem1 = $this->createMock(JournalLineItem::class);
        $lineItem1->method('getDebitAmount')->willReturn(100.0);
        $lineItem1->method('getCreditAmount')->willReturn(null);

        $lineItem2 = $this->createMock(JournalLineItem::class);
        $lineItem2->method('getDebitAmount')->willReturn(null);
        $lineItem2->method('getCreditAmount')->willReturn(100.0);

        $journalEntry = $this->createMock(JournalEntry::class);
        $journalEntry->method('getJournalLineItems')->willReturn(new ArrayCollection([$lineItem1, $lineItem2]));

        $this->validator->validate($journalEntry, new BalancedJournalEntry());

        $this->assertNoViolation();
    }

    public function testMultipleLineItemsBalance(): void
    {
        $lineItem1 = $this->createMock(JournalLineItem::class);
        $lineItem1->method('getDebitAmount')->willReturn(100.0);
        $lineItem1->method('getCreditAmount')->willReturn(0.0);

        $lineItem2 = $this->createMock(JournalLineItem::class);
        $lineItem2->method('getDebitAmount')->willReturn(50.0);
        $lineItem2->method('getCreditAmount')->willReturn(0.0);

        $lineItem3 = $this->createMock(JournalLineItem::class);
        $lineItem3->method('getDebitAmount')->willReturn(0.0);
        $lineItem3->method('getCreditAmount')->willReturn(150.0);

        $journalEntry = $this->createMock(JournalEntry::class);
        $journalEntry->method('getJournalLineItems')->willReturn(new ArrayCollection([$lineItem1, $lineItem2, $lineItem3]));

        $this->validator->validate($journalEntry, new BalancedJournalEntry());

        $this->assertNoViolation();
    }

    public function testExceptions(): void
    {
        $this->expectException(UnexpectedValueException::class);
        $this->validator->validate("kalle anka", new BalancedJournalEntry());
    }

    public function testThrowsExceptionForInvalidConstraintType(): void
    {
        $this->expectException(UnexpectedTypeException::class);

        $constraint = $this->createMock(\Symfony\Component\Validator\Constraint::class);
        $journalEntry = $this->createMock(JournalEntry::class);

        $this->validator->validate($journalEntry, $constraint);
    }
}
