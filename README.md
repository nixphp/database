<div style="text-align: center;">

![Logo](https://nixphp.github.io/docs/assets/nixphp-logo-small-square.png)

[![NixPHP Database Plugin](https://github.com/nixphp/database/actions/workflows/php.yml/badge.svg)](https://github.com/nixphp/database/actions/workflows/php.yml)

</div>

[← Back to NixPHP](https://github.com/nixphp/framework)

---

# nixphp/database

> **Simple, native PDO connection for your NixPHP application.**

This plugin provides a shared **PDO instance** via the service container,
supporting both **MySQL/MariaDB** and **SQLite** out of the box — with sensible defaults.

> 🧩 Part of the official NixPHP plugin collection.
> Install it when you need a native, PSR-compliant database connection — and nothing more.

---

## 📦 Features

* ✅ Shared `PDO` instance, ready to use
* ✅ MySQL/MariaDB and SQLite support
* ✅ Uses sane defaults (error mode, fetch mode, UTF-8 charset)
* ✅ Works with memory databases (`sqlite::memory:`)
* ✅ Available via `database()` helper or DI container

---

## 📥 Installation

```bash
composer require nixphp/database
```

Then add the following configuration to `/app/config.php`.
You can choose between **MySQL/MariaDB** or **SQLite**.

### Example: MySQL

```php
<?php

return [
    // ...
    'database' => [
        'driver'   => 'mysql',
        'host'     => '127.0.0.1',
        'database' => 'myapp',
        'username' => 'root',
        'password' => '',
        'charset'  => 'utf8mb4',
    ]
];
```

### Example: SQLite

```php
<?php

return [
    // ...
    'database' => [
        'driver'   => 'sqlite',
        'database' => __DIR__ . '/../storage/database.sqlite',
    ]
];
```

Or for in-memory SQLite:

```php
return [
    // ...
    'database' => [
        'driver'   => 'sqlite',
        'database' => ':memory:',
    ]
];
```

---

## 🚀 Usage

Access the PDO instance globally via:

```php
$pdo = database();
```

Or retrieve it manually from the service container:

```php
$pdo = app()->container()->get(Database::class);
```

Use it as usual with native PDO:

```php
$stmt = database()->prepare('SELECT * FROM users WHERE id = ?');
$stmt->execute([$id]);
$user = $stmt->fetch(); // default: FETCH_ASSOC
```

---

## ⚙️ Defaults applied

The PDO instance comes with these options:

```php
[
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]
```

---

## 🔍 Internals

* Loads config from `/app/config.php` from the key `database`
* Builds DSN based on a given driver (`mysql`, `sqlite`)
* Wraps PDO creation in a factory, handles exceptions gracefully
* Registers `database` in the container and provides the `database()` helper

---

## ✅ Requirements

* `nixphp/framework` >= 0.1.0

---

## 📄 License

MIT License.