# Filament Simple Translation

[![Latest Version on Packagist](https://img.shields.io/packagist/v/aslnbxrz/filament-simple-translation.svg)](https://packagist.org/packages/aslnbxrz/filament-simple-translation)
[![Total Downloads](https://img.shields.io/packagist/dt/aslnbxrz/filament-simple-translation.svg)](https://packagist.org/packages/aslnbxrz/filament-simple-translation)
[![License](https://img.shields.io/badge/license-MIT-blue.svg)](LICENSE)

*A lightweight Filament page to manage translations powered
by [Simple Translation](https://packagist.org/packages/aslnbxrz/simple-translation).*

---

## ✨ Features

- Filament page to browse and edit translations.
- Scope-based filtering (e.g., `app`, `filament`).
- Inline editing of translations with language-specific columns.
- Filters for missing/filled translations.
- Supports multiple languages configured via `AppLanguageService`.

---

## 📦 Installation

```bash
composer require aslnbxrz/filament-simple-translation
```

## Then publish the config

```bash
php artisan vendor:publish --tag=filament-simple-translation-config
```

## ⚙️ Configuration

```php
return [
    'default_scope' => 'app',
    'scopes' => [
        'app',
        'filament',
        // add your scopes here
    ],
];
```

### default_scope – which scope to load by default.

### scopes – list of available scopes to filter translations.



