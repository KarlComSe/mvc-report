# MVC course at BTH

This is a repository for the course MVC at BTH.

![An animal which is as majestic as I aim to become in OO PHP and the Symfony framework before this course is over](https://www.student.bth.se/~KAAA19/dbwebb-kurser/mvc/me/report/public/assets/images/pexels-pixabay-55814-4a563544243af28157e6124e343cea8a.jpg)

See project live here: <https://www.student.bth.se/~KAAA19/dbwebb-kurser/mvc/me/report/public/about>

## Metrics

[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/KarlComSe/mvc-report/badges/quality-score.png?b=main)](https://scrutinizer-ci.com/g/KarlComSe/mvc-report/?branch=main)
[![Build Status](https://scrutinizer-ci.com/g/KarlComSe/mvc-report/badges/build.png?b=main)](https://scrutinizer-ci.com/g/KarlComSe/mvc-report/build-status/main)
[![Code Coverage](https://scrutinizer-ci.com/g/KarlComSe/mvc-report/badges/coverage.png?b=main)](https://scrutinizer-ci.com/g/KarlComSe/mvc-report/?branch=main)
[![Code Intelligence Status](https://scrutinizer-ci.com/g/KarlComSe/mvc-report/badges/code-intelligence.svg?b=main)](https://scrutinizer-ci.com/g/KarlComSe/mvc-report/?branch=main)

## Requirements

See Symfony requirements: <https://symfony.com/doc/current/setup.html#technical-requirements>
PHP 8.2 or later

## How to run the project

### Docker setup

@TODO.

### Manual setup (for local development)

#### Pre-reqs

- PHP 8.1+ with PDO SQLite extension (`php-sqlite3`)
- Composer
- SQLite3
- Symfony CLI

#### Clone the repo

```bash
git clone git@github.com:KarlComSe/mvc-report.git
cd mvc-report
```

#### Install dependencies

```bash
composer install
```

(I don't see why NPM should be needed)

#### Database

The database and migrations is excluded in repository and needs to be created.

```bash
php bin/console doctrine:database:create
php bin/console doctrine:migrations:diff
php bin/console doctrine:migrations:migrate --no-interaction
```

Common error: there might be files in /migrations/. Consider deleting these.

#### Seed data

To seed the BAS chart of accounts for the *Kassabok application*:

```bash
php bin/console app:seed-bas 2025
```

#### Start and access server

Start the server with Symfony CLI tool:

```bash
symfony server:start
```

Access server: `http://localhost:8000`

## Course highlights

### KMOM02: A deck of cards

Within part 2 of the course, the assignment was related to OO programming, specifically to try some OO concepts and to develop a deck of cards, an API and so forth. The UML diagram is [available here](https://www.yworks.com/yed-live/?file=https://gist.githubusercontent.com/KarlComSe/b1cb96d9a29f219e45584552f672f959/raw/ca37c2a6b8354f8d14dbca1e8b515b0712e363d5/Untitled%20Document)

### KMOM10

I decided to take my own path in KMOM10 and develop a book keeping app, I call it *Kassabok*.

Developing a book keeping app was one of the reasons I started at BTH / dbwebb in August 2023. Thus, I aim to better understand some of the challenges in this field.

#### AI disclosure

Tools such as Claude, Grok and ChatGPT has been used extensively in KMOM10. This includes some usage of Claude code CLI.

Examples of AI usage:

- AI supported on TWIG-templates and provided input on styling.
- BAS chart seed command - fully DEVELPOPED by AI.
- Error messages and relevant code has been shared to AI. AI-suggestions have been implemented.
- AI has helped with configuration, e.g. PHP MD.
- AI has been provided part of the code based and asked to review it. AI-suggestions have been implemented.
- Extensive questions around architectures has been asked to AI. Some AI-advices have been followed.
- AI has supported heavily in src/tests/Fixtures/AccountingFixtureLoader.php.
- AI has created the /accounting_fixtures.json and src/tests/Fixtures/SCENARIOS.md.
- AI provided input on what to test in some cases.
- PHPStan errors in the test suite were fixed using Claude CLI. This primarily concerned adding Type hint and PHPDoc annotations in test files.

AI didn't develop this application. I developed it, including defining and creating the business logic, the domain model and much more. AI greatly helped me in learning.

#### Testing

Testing is a target in KMOM10. This project uses [DAMADoctrineTestBundle](https://symfony.com/doc/current/testing.html#resetting-the-database-automatically-before-each-test), which wraps all test in a transaction and rolls it back once tests are complete.

#### Future development & known bugs

*This is a prototype and should not be used for actual bookkeeping. There are likely authentication and authorization bugs.*

1. *Bug*: Journal entries in a journal is not numbered in consecutive order. The scope of the numbering should be limited to each journal and be in a consecutive series, for each journal.
2. *New feature*: It should be possible to close a journal and carry the balances into next year (ingående balans = utgående balans).
3. *New feature*: SRU file import and export.
4. *New feature*: Add receipt / invoice for a journal entry.
5. *New feature*: Improved user and organization management, fully implementing user roles in each organizaiton. Adding email verification of users signing up.
6. *New feature*: Implemt custom chart of accounts.
7. *New feature*: Add dimensions to a journal entry, e.g. cost center, project and budget groups.
8. *New feature*: Parse receipt / invoices and propose booking (incl. date) [reference: SAP CONCUR](https://www.concur.com/resource-center/videos/concur-expense-receipt-capture-demo-bite?&cookie_preferences=complete).
9. *New feature*: Simplify accounting entry through accounting templates (konteringsmallar) and other UI enhancements.
