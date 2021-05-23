# REPLACE

Simple and flexible package template.

# Usage

- Replace all occurances of `REPLACE` (case sensitive) with the name of the package namespace. E.g. the `Foo` in `ArchTech\Foo`.
- Replace all occurances of `replace2` with the name of the package on composer, e.g. the `bar` in `archtechx/bar`.
- If MySQL is not needed, remove `docker-compose.yml`, remove the line that runs docker from `./check`, and set `DB_CONNECTION` in `phpunit.xml` to `sqlite`, and `DB_DATABASE` to `:memory:`.

---

## Installation

```sh
composer require stancl/replace2
```

## Usage

```php
// ...
```

## Development

Running all checks locally:

```sh
./check
```

Running tests:

```sh
MYSQL_PORT=3307 docker-compose up -d

phpunit
```

Code style will be automatically fixed by php-cs-fixer.
