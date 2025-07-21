# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

This is a Laravel 12 application using the Livewire starter kit with Filament v3.3 admin panel. It's a wishlist application where users can manage items they want to purchase, with features for tracking purchases and deliveries.

## Key Technologies

- **Backend**: Laravel 12 with PHP 8.2+
- **Frontend**: Livewire/Flux v2.1+ with TailwindCSS v4
- **Admin Panel**: Filament v3.3
- **Database**: SQLite (default), supports other Laravel-compatible databases
- **Testing**: PestPHP v3.8
- **Build Tools**: Vite 6 with Laravel Vite plugin

## Development Commands

### Running the Application
```bash
composer dev  # Runs server, queue, logs, and vite concurrently
php artisan serve  # Development server only
npm run dev  # Frontend build with hot reload
```

### Testing
```bash
composer test  # Runs config:clear and executes tests
php artisan test  # Direct test execution
vendor/bin/pest  # PestPHP test runner
```

### Code Quality
```bash
vendor/bin/pint  # Laravel Pint code formatting (available via composer)
```

### Database Operations
```bash
php artisan migrate  # Run migrations
php artisan migrate:fresh --seed  # Fresh migration with seeding
php artisan queue:listen --tries=1  # Process queues
```

### Asset Building
```bash
npm run build  # Production build
npm run dev  # Development build with watch
```

## Architecture

### Models and Data Structure
- **Item Model** (`app/Models/Item.php`): Core entity with money casting for prices, includes observer pattern
- **User Model**: Standard Laravel authentication model
- **MoneyCast** (`app/Casts/MoneyCast.php`): Custom cast for handling money values (stores as cents, displays as dollars)

### Filament Admin Panel
- **Admin Panel Path**: `/admin` with authentication required  
- **ItemResource**: Full CRUD operations for items with form fields: name, size, color, link, price, store
- **Theme**: Red primary color with Poppins font
- **Auto-discovery**: Automatically discovers resources, pages, and widgets in respective directories

### Database Schema (Items)
- id, uuid, user_id (foreign key)
- name, image, size, color, link, price (integer cents), store
- purchase tracking: purchased (boolean), purchased_by, purchased_date
- delivery tracking: delivered (boolean), delivered_date
- timestamps

### Testing Structure
- **Framework**: PestPHP with Laravel plugin
- **Structure**: Feature tests in `tests/Feature/`, Unit tests in `tests/Unit/`
- **Database**: Uses in-memory SQLite for testing
- **Authentication Tests**: Complete auth flow testing included

### Frontend Architecture
- **Livewire Components**: Located in `app/Livewire/` with auth and settings components
- **Flux UI**: Uses Livewire Flux for modern UI components
- **Blade Templates**: In `resources/views/` with component-based structure
- **Assets**: TailwindCSS v4 with Vite build system

## Important Files

- `composer.json`: Contains dev script for concurrent development services
- `app/Observers/ItemObserver.php`: Handles Item model events
- `database/factories/ItemFactory.php`: Factory for generating test items
- `app/Providers/Filament/AdminPanelProvider.php`: Filament panel configuration
- `vite.config.js`: Frontend build configuration with TailwindCSS integration

## Development Notes

- Uses PHP 8.2+ attributes for model observers and type declarations
- Money values stored as integers (cents) and cast to floats for display
- Filament resources use auto-discovery for simplified registration
- Testing environment uses array drivers for cache, sessions, and mail
- SQLite database file: `database/database.sqlite`