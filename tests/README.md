# Testy

PHPUnit 10, SQLite `:memory:` (skupina `tests` v `app/Config/Database.php`).

```bash
composer test
```

Coverage: `composer test-coverage` (Xdebug nebo PCOV).

| Složka | Obsah |
| --- | --- |
| `tests/feature` | HTTP požadavky |
| `tests/database` | `NewsModel` |
| `tests/unit` | jednotkové testy |

Konfigurace: `phpunit.dist.xml`.
