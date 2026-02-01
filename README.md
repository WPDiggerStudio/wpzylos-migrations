# WPZylos Migrations

[![PHP Version](https://img.shields.io/badge/php-%5E8.0-blue)](https://php.net)
[![License](https://img.shields.io/badge/license-MIT-green)](LICENSE)
[![GitHub](https://img.shields.io/badge/GitHub-WPDiggerStudio-181717?logo=github)](https://github.com/WPDiggerStudio/wpzylos-migrations)

dbDelta-based migration runner for WPZylos framework.

📖 **[Full Documentation](https://wpzylos.com)** | 🐛 **[Report Issues](https://github.com/WPDiggerStudio/wpzylos-migrations/issues)**

---

## ✨ Features

- **dbDelta Integration** — Uses WordPress dbDelta for safe schema changes
- **Version Tracking** — Tracks migration history
- **Up/Down Methods** — Reversible migrations
- **CLI Support** — Run migrations via WP-CLI
- **Schema Builder** — Fluent table schema definition

---

## 📋 Requirements

| Requirement | Version |
| ----------- | ------- |
| PHP         | ^8.0    |
| WordPress   | 6.0+    |

---

## 🚀 Installation

```bash
composer require wpdiggerstudio/wpzylos-migrations
```

---

## 📖 Quick Start

```php
use WPZylos\Framework\Migrations\Migration;
use WPZylos\Framework\Database\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function ($table) {
            $table->id();
            $table->string('name');
            $table->decimal('price', 10, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
```

---

## 🏗️ Core Features

### Schema Builder

```php
Schema::create('orders', function ($table) {
    $table->id();
    $table->bigInteger('user_id')->unsigned();
    $table->string('status', 50)->default('pending');
    $table->decimal('total', 12, 2);
    $table->text('notes')->nullable();
    $table->timestamps();

    $table->index('user_id');
    $table->index('status');
});
```

### Column Types

```php
$table->id();                          // BIGINT UNSIGNED AUTO_INCREMENT
$table->string('name', 255);           // VARCHAR(255)
$table->text('content');               // TEXT
$table->integer('count');              // INT
$table->bigInteger('amount');          // BIGINT
$table->decimal('price', 10, 2);       // DECIMAL(10,2)
$table->boolean('active');             // TINYINT(1)
$table->datetime('published_at');      // DATETIME
$table->timestamps();                  // created_at, updated_at
```

### Running Migrations

```php
$migrator = new Migrator($context);
$migrator->run();       // Run pending migrations
$migrator->rollback();  // Rollback last batch
```

---

## 📦 Related Packages

| Package                                                                | Description            |
| ---------------------------------------------------------------------- | ---------------------- |
| [wpzylos-core](https://github.com/WPDiggerStudio/wpzylos-core)         | Application foundation |
| [wpzylos-database](https://github.com/WPDiggerStudio/wpzylos-database) | Query builder          |
| [wpzylos-scaffold](https://github.com/WPDiggerStudio/wpzylos-scaffold) | Plugin template        |

---

## 📖 Documentation

For comprehensive documentation, tutorials, and API reference, visit **[wpzylos.com](https://wpzylos.com)**.

---

## ☕ Support the Project

If you find this package helpful, consider buying me a coffee! Your support helps maintain and improve the WPZylos ecosystem.

<a href="https://www.paypal.com/donate/?hosted_button_id=66U4L3HG4TLCC" target="_blank">
  <img src="https://img.shields.io/badge/Donate-PayPal-blue.svg?style=for-the-badge&logo=paypal" alt="Donate with PayPal" />
</a>

---

## 📄 License

MIT License. See [LICENSE](LICENSE) for details.

---

## 🤝 Contributing

Contributions are welcome! Please see [CONTRIBUTING.md](CONTRIBUTING.md) for guidelines.

---

**Made with ❤️ by [WPDiggerStudio](https://github.com/WPDiggerStudio)**
