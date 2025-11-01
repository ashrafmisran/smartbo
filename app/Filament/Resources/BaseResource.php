<?php

namespace App\Filament\Resources;

use Filament\Panel;
use Filament\Resources\Resource;
use Illuminate\Support\Str;

abstract class BaseResource extends Resource
{
    public static function getSlug(?Panel $panel = null): string
    {
        $modelClass = static::getModel();
        $modelName = class_basename($modelClass);
        
        // Convert model name to kebab-case with "senarai-" prefix (singular form)
        $slug = 'senarai-' . Str::kebab($modelName);
        
        return $slug;
    }
    
    public static function getNavigationGroup(): ?string
    {
        return static::getNavigationConfiguration()[static::class]['group'] ?? null;
    }
    
    public static function getNavigationSort(): ?int
    {
        $config = static::getNavigationConfiguration();
        $group = $config[static::class]['group'] ?? null;
        $sort = $config[static::class]['sort'] ?? null;
        
        // Add group-based sorting to ensure DATA PILIHAN RAYA comes first
        $groupSort = match($group) {
            'DATA PILIHAN RAYA' => 1000,
            'UTILITI' => 2000,
            default => 9000,
        };
        
        return $groupSort + ($sort ?? 999);
    }
    
    public static function getNavigationLabel(): string
    {
        return static::getNavigationConfiguration()[static::class]['label'] ?? parent::getNavigationLabel();
    }
    
    public static function getNavigationBadge(): ?string
    {
        if (method_exists(static::class, 'getModel')) {
            $model = static::getModel();
            if (class_exists($model)) {

                return (string) $model::count();
            }
        }
        return null;
    }
    
    protected static function getNavigationConfiguration(): array
    {
        return [
            // DATA PILIHAN RAYA GROUP
            \App\Filament\Resources\Negeris\NegeriResource::class => [
                'group' => 'DATA PILIHAN RAYA',
                'sort' => 1,
                'label' => 'Negeri',
            ],
            \App\Filament\Resources\Parlimens\ParlimenResource::class => [
                'group' => 'DATA PILIHAN RAYA',
                'sort' => 2,
                'label' => 'Parlimen',
            ],
            \App\Filament\Resources\Duns\DunResource::class => [
                'group' => 'DATA PILIHAN RAYA',
                'sort' => 3,
                'label' => 'DUN',
            ],
            \App\Filament\Resources\Daerahs\DaerahResource::class => [
                'group' => 'DATA PILIHAN RAYA',
                'sort' => 4,
                'label' => 'Daerah',
            ],
            \App\Filament\Resources\Lokalitis\LokalitiResource::class => [
                'group' => 'DATA PILIHAN RAYA',
                'sort' => 5,
                'label' => 'Lokaliti',
            ],
            \App\Filament\Resources\Pengundis\PengundiResource::class => [
                'group' => 'DATA PILIHAN RAYA',
                'sort' => 6,
                'label' => 'Pengundi',
            ],
            
            // UTILITI GROUP
            \App\Filament\Resources\Databases\DatabaseResource::class => [
                'group' => 'UTILITI',
                'sort' => 1,
                'label' => 'Pengkalan Data',
            ],
            \App\Filament\Resources\Users\UserResource::class => [
                'group' => 'UTILITI',
                'sort' => 2,
                'label' => 'Pengguna',
            ],
        ];
    }
}