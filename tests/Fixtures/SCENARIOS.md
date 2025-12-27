<!-- AI-assisted -->
# Accounting Test Scenarios

This document describes the test scenarios available in `accounting_fixtures.json` for testing the Kassabok accounting system.

## Overview

All scenarios follow Swedish BAS (Bas Accounting System) chart of accounts and demonstrate realistic Swedish company accounting scenarios.

## Scenario A: "Negativt resultat"

### Financial Summary

- **Total Revenue**: 10,000 SEK
- **Total Expenses**: 11,614 SEK
- **Net Result**: **-1,614 SEK (LOSS)**

### Revenue Breakdown

- Account 3041 (Försäljning tjänster, 25% moms): 10,000 SEK

### Expense Breakdown

- Account 6570 (Bankavgifter): 100 SEK
- Account 6540 (Konsulttjänster och företagsledning): 1,000 SEK
- Account 7010 (Löner till tjänstemän): 8,000 SEK
- Account 7510 (Arbetsgivaravgifter): 2,514 SEK

### Transactions (10 total)

1. Aktiekapital insatt (25,000 SEK)
2. Bankavgift (100 SEK)
3. Programvara/IT-tjänst (1,000 SEK + 250 VAT)
4. Fakturering webbutveckling (10,000 + 2,500 VAT)
5. Kund betalar faktura (12,500 SEK)
6. Bruttolön (8,000 SEK)
7. Utbetalning nettolön (6,000 SEK)
8. Arbetsgivaravgifter (2,514 SEK)
9. Momsavräkning
10. Betalning moms och arbetsgivaravgifter

### Expected Year-End Closing Entry

When closing this scenario:

- Debit account 3041 (Revenue): 10,000 SEK (to zero it out)
- Credit account 6570 (Expense): 100 SEK (to zero it out)
- Credit account 6540 (Expense): 1,000 SEK (to zero it out)
- Credit account 7010 (Expense): 8,000 SEK (to zero it out)
- Credit account 7510 (Expense): 2,514 SEK (to zero it out)
- **Debit account 2099** (Årets resultat - Retained Earnings): **1,614 SEK** (loss reduces equity)

### Balance Sheet After Transactions (Before Year-End)

**Assets**:

- Account 1930 (Företagskonto/checkkonto): 24,886 SEK

**Equity**:

- Account 2081 (Aktiekapital): 25,000 SEK

**Liabilities**:

- Account 2710 (Personalens källskatt): 2,000 SEK

**Note**: Balance sheet doesn't balance before year-end closure because income statement accounts (revenue/expense) haven't been closed yet.

---

## Scenario B: "Negativt resultat" (Larger Loss)

### Financial Summary

- **Total Revenue**: 5,000 SEK
- **Total Expenses**: 14,742 SEK
- **Net Result**: **-9,742 SEK (LOSS)**

### Revenue Breakdown

- Account 3041 (Försäljning tjänster, 25% moms): 5,000 SEK

### Expense Breakdown

- Account 6570 (Bankavgifter): 100 SEK
- Account 6540 (Konsulttjänster och företagsledning): 1,500 SEK
- Account 7010 (Löner till tjänstemän): 10,000 SEK
- Account 7510 (Arbetsgivaravgifter): 3,142 SEK

### Expected Year-End Closing Entry

- Debit account 3041: 5,000 SEK
- Credit expenses (total): 14,742 SEK
- **Debit account 2099**: **9,742 SEK** (larger loss)

---

## Scenario C: "Enkel - endast kostnader" (Simple - Expenses Only)

### Financial Summary

- **Total Revenue**: 0 SEK
- **Total Expenses**: 6,300 SEK
- **Net Result**: **-6,300 SEK (LOSS)**

### Expense Breakdown

- Account 5010 (Lokalhyra): 5,000 SEK
- Account 5060 (El för belysning): 800 SEK
- Account 6100 (Kontorsmaterial och blanketter): 500 SEK

### Expected Year-End Closing Entry

- No revenue accounts to close
- Credit expenses (total): 6,300 SEK
- **Debit account 2099**: **6,300 SEK**

### Use Case

Tests year-end closure when there are NO revenue accounts (startup phase, pre-revenue company).

---

## Scenario D: "Enkel med rättning" (Simple with Correction)

### Financial Summary

- **Total Revenue**: 0 SEK
- **Total Expenses**: 1,700 SEK
- **Net Result**: **-1,700 SEK (LOSS)**

### Expense Breakdown

- Account 6100 (Kontorsmaterial och blanketter): 500 SEK
- Account 6230 (Telefon): 400 SEK
- Account 6420 (Representation, avdragsgill): 800 SEK

### Special Feature: Correction Pattern

Demonstrates proper Swedish accounting correction methodology:

**Transaction 2**: Incorrect booking (reversed debit/credit)

```
Debit: 6100 (0 - WRONG)
Credit: 1930 (625 - WRONG)
```

**Transaction 3**: Reversal of incorrect entry

```
Debit: 6100 (500 - reverses wrong entry)
Debit: 2641 (125 - reverses wrong VAT)
Credit: 1930 (625 - reverses wrong entry)
```

**Transaction 4**: Correct booking

```
Debit: 6100 (500 - CORRECT)
Debit: 2641 (125 - CORRECT VAT)
Credit: 1930 (625 - CORRECT)
```

### Use Case

- Tests handling of correction entries
- Demonstrates proper audit trail (don't delete, reverse and re-enter)
- Verifies that reversals don't affect final balances

---

## Scenario E: "Positivt resultat (verkligt vinst)" (Actual Profit)

### Financial Summary

- **Total Revenue**: 50,000 SEK
- **Total Expenses**: 30,313 SEK
- **Net Result**: **+19,687 SEK (PROFIT)**

### Revenue Breakdown

- Account 3041 (Försäljning tjänster, 25% moms): 50,000 SEK

### Expense Breakdown

- Account 5010 (Lokalhyra): 8,000 SEK
- Account 6100 (Kontorsmaterial och blanketter): 500 SEK
- Account 6540 (Konsulttjänster och företagsledning): 2,000 SEK
- Account 6570 (Bankavgifter): 100 SEK
- Account 7010 (Löner till tjänstemän): 15,000 SEK
- Account 7510 (Arbetsgivaravgifter): 4,713 SEK

### Transactions (14 total)

1. Aktiekapital insatt (50,000 SEK)
2. Fakturering webbutveckling projekt 1 (25,000 + 6,250 VAT)
3. Fakturering konsulttjänster projekt 2 (25,000 + 6,250 VAT)
4. Kund betalar faktura projekt 1 (31,250 SEK)
5. Kund betalar faktura projekt 2 (31,250 SEK)
6. Bankavgift (100 SEK)
7. Hyra lokal (8,000 SEK)
8. Kontorsmaterial (500 SEK + 125 VAT)
9. IT-konsulttjänster (2,000 + 500 VAT)
10. Bruttolön (15,000 SEK)
11. Utbetalning nettolön (11,250 SEK)
12. Arbetsgivaravgifter (4,713 SEK)
13. Momsavräkning
14. Betalning moms och arbetsgivaravgifter

### Expected Year-End Closing Entry

When closing this scenario:

- Debit account 3041 (Revenue): 50,000 SEK (to zero it out)
- Credit account 5010 (Expense): 8,000 SEK (to zero it out)
- Credit account 6100 (Expense): 500 SEK (to zero it out)
- Credit account 6540 (Expense): 2,000 SEK (to zero it out)
- Credit account 6570 (Expense): 100 SEK (to zero it out)
- Credit account 7010 (Expense): 15,000 SEK (to zero it out)
- Credit account 7510 (Expense): 4,713 SEK (to zero it out)
- **Credit account 2099** (Årets resultat - Retained Earnings): **19,687 SEK** (profit increases equity)

### Balance Sheet After Transactions (Before Year-End)

**Assets**:

- Account 1930 (Företagskonto/checkkonto): 73,437 SEK

**Equity**:

- Account 2081 (Aktiekapital): 50,000 SEK

**Liabilities**:

- Account 2710 (Personalens källskatt): 3,750 SEK

### Why This Scenario Is Critical

**This tests positive net income!** All other scenarios (A, B, C, D) result in losses.

This scenario specifically tests the **previously untested code path** at YearEndService.php line 137:

Without this scenario, the profit handling logic would have zero test coverage.

---

## Account Reference (BAS Chart)

### Assets (1000-1999)

- 1510: Kundfordringar (Accounts Receivable)
- 1930: Företagskonto/checkkonto (Business Bank Account)

### Equity (2000-2999, specific accounts)

- 2081: Aktiekapital (Share Capital)
- 2099: Årets resultat (Retained Earnings / Net Income)

### Liabilities (2000-2999)

- 2611: Utgående moms, 25% (Outgoing VAT 25%)
- 2641: Ingående moms (Incoming VAT)
- 2650: Redovisningskonto för moms (VAT Reconciliation Account)
- 2710: Personalens källskatt (Employee Tax Withholding)
- 2731: Avräkning arbetsgivaravgifter (Employer Tax Reconciliation)

### Revenue (3000-3999)

- 3041: Försäljning tjänster, 25% moms (Service Sales, 25% VAT)

### Expenses (4000-8999)

- 5010: Lokalhyra (Premises Rent)
- 5060: El för belysning (Electricity)
- 6100: Kontorsmaterial och blanketter (Office Supplies)
- 6230: Telefon (Telephone)
- 6420: Representation, avdragsgill (Business Entertainment, Deductible)
- 6540: Konsulttjänster och företagsledning (Consulting Services)
- 6570: Bankavgifter (Bank Fees)
- 7010: Löner till tjänstemän (Salaries - White Collar)
- 7510: Arbetsgivaravgifter (Employer Social Contributions)
- 8999: Årets resultat (temporary account, should be 0 after year-end)

---

## Usage in Tests

```php
// Load a scenario
$journal = $this->fixtureLoader->loadScenario('A');  // Loss scenario
$journal = $this->fixtureLoader->loadScenario('B');  // Larger loss
$journal = $this->fixtureLoader->loadScenario('C');  // Expenses only
$journal = $this->fixtureLoader->loadScenario('D');  // With corrections
$journal = $this->fixtureLoader->loadScenario('E');  // Profit scenario (for testing positive income path)
```

## Notes

1. All amounts are in Swedish Krona (SEK)
2. VAT rate is 25% (Swedish standard rate)
3. Employer social contributions (arbetsgivaravgifter) are approximately 31.42% of gross salary
4. Scenarios follow Swedish accounting standards (BAS)
5. All scenarios use double-entry bookkeeping (debits = credits for each transaction)
