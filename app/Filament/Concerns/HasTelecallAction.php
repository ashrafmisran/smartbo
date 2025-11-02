<?php

namespace App\Filament\Concerns;

use App\Filament\Schemas\TelecallModalSchema;
use Filament\Actions\Action;
use Filament\Support\Enums\Width;

trait HasTelecallAction
{
    /**
     * Get the telecall action for use in tables or pages (instance method)
     */
    protected function getTelecallAction(): Action
    {
        return static::makeTelecallAction();
    }

    /**
     * Get the telecall action (static method for resources)
     */
    public static function makeTelecallAction(): Action
    {
        return Action::make('hubungi')
            ->icon('heroicon-o-phone')
            ->iconButton()
            ->schema(TelecallModalSchema::getSchema())
            ->modalWidth(Width::Screen)
            ->slideOver(true)
            ->color('success')
            ->button();
    }
}
