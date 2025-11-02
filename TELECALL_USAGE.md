# Telecall Modal - Usage Guide

## Overview
The telecall modal functionality has been extracted into reusable components that can be used across different pages and resources.

## Components Created

### 1. `TelecallService` (`app/Services/TelecallService.php`)
A service class that provides:
- `getSkripPanggilan()`: Returns the telecall script HTML
- `getPhoneNumbers($pengundi)`: Extracts phone numbers from a Pengundi record

### 2. `TelecallModalSchema` (`app/Filament/Schemas/TelecallModalSchema.php`)
Provides the complete modal schema including:
- `getSchema()`: Returns the full modal form schema
- `getCulaPnOptions()`: Returns PN voting options
- `getCulaLawanOptions()`: Returns opposition voting options
- `getCulaLainOptions()`: Returns other options

### 3. `HasTelecallAction` Trait (`app/Filament/Concerns/HasTelecallAction.php`)
A trait that provides:
- `getTelecallAction()`: Returns a configured telecall action ready to use

## Usage Examples

### Example 1: In a Filament Table Resource

```php
<?php

namespace App\Filament\Resources\Pengundis\Tables;

use App\Filament\Concerns\HasTelecallAction;
use Filament\Tables\Table;

class PengundisTable
{
    use HasTelecallAction;

    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                // your columns
            ])
            ->actions([
                (new self())->getTelecallAction(),
                // other actions
            ]);
    }
}
```

### Example 2: In a Custom Filament Page

```php
<?php

namespace App\Filament\Pages;

use App\Filament\Concerns\HasTelecallAction;
use Filament\Pages\Page;
use Filament\Tables\Contracts\HasTable;

class MyCustomPage extends Page implements HasTable
{
    use InteractsWithTable;
    use HasTelecallAction;

    protected function getTableActions(): array
    {
        return [
            $this->getTelecallAction(),
        ];
    }
}
```

### Example 3: Using the Schema Directly (Custom Implementation)

```php
use App\Filament\Schemas\TelecallModalSchema;
use Filament\Actions\Action;
use Filament\Support\Enums\Width;

Action::make('custom_telecall')
    ->icon('heroicon-o-phone')
    ->label('Hubungi')
    ->schema(TelecallModalSchema::getSchema())
    ->modalWidth(Width::Screen)
    ->slideOver()
    ->action(function (array $data) {
        // Handle the form submission
        // $data will contain: cula_pn, cula_lawan, or cula_lain
    })
```

### Example 4: Using Individual Components

```php
use App\Services\TelecallService;
use App\Filament\Schemas\TelecallModalSchema;

// Get just the script
$script = TelecallService::getSkripPanggilan();

// Get just the options
$pnOptions = TelecallModalSchema::getCulaPnOptions();
$lawanOptions = TelecallModalSchema::getCulaLawanOptions();
$lainOptions = TelecallModalSchema::getCulaLainOptions();

// Get phone numbers from a pengundi
$phoneNumbers = TelecallService::getPhoneNumbers($pengundi);
```

### Example 5: In PengundiResource

```php
<?php

namespace App\Filament\Resources\Pengundis;

use App\Filament\Concerns\HasTelecallAction;
use App\Filament\Resources\BaseResource;

class PengundiResource extends BaseResource
{
    use HasTelecallAction;

    public static function table(Table $table): Table
    {
        return $table
            ->columns([...])
            ->actions([
                (new self())->getTelecallAction(),
            ]);
    }
}
```

## Customization

### Customizing the Script
Edit `app/Services/TelecallService.php` and modify the `getSkripPanggilan()` method.

### Customizing the Options
Edit `app/Filament/Schemas/TelecallModalSchema.php` and modify the option methods.

### Customizing the Action
You can override the `getTelecallAction()` method in your class:

```php
protected function getTelecallAction(): Action
{
    return parent::getTelecallAction()
        ->color('primary')
        ->label('Custom Label')
        ->action(function (array $data, $record) {
            // Custom action handling
            // Save to database, send notification, etc.
        });
}
```

## Benefits

1. **DRY (Don't Repeat Yourself)**: Single source of truth for telecall functionality
2. **Maintainability**: Update script or options in one place
3. **Consistency**: Same UI/UX across all telecall implementations
4. **Flexibility**: Can be used in tables, pages, or custom components
5. **Easy Testing**: Isolated components are easier to test
