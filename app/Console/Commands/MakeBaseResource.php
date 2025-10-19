<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;

class MakeBaseResource extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:base-resource {model}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new Filament resource that extends BaseResource';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $model = $this->argument('model');
        
        // First create the standard Filament resource
        $this->call('make:filament-resource', [
            'model' => $model,
            '--generate' => true,
            '--view' => true,
        ]);
        
        // Then modify it to extend BaseResource
        $this->modifyResourceToExtendBase($model);
        
        // Create custom view page with papar- prefix
        $this->createPaparViewPage($model);
        
        // Create custom create page with tambah- prefix
        $this->createTambahCreatePage($model);
        
        // Create custom edit page with pinda- prefix
        $this->createPindaEditPage($model);
        
        $this->info("Resource for {$model} created and configured to extend BaseResource!");
        $this->info("Index URL: /admin/senarai-" . Str::kebab($model));
        $this->info("Create URL: /admin/senarai-" . Str::kebab($model) . "/tambah");
        $this->info("View URL: /admin/senarai-" . Str::kebab($model) . "/{record}/papar");
        $this->info("Edit URL: /admin/senarai-" . Str::kebab($model) . "/{record}/pinda");
        $this->info("");
        $this->warn("Note: Don't forget to add navigation configuration in BaseResource::getNavigationConfiguration()");
        $this->info("Add this entry to the configuration array:");
        $this->info("\\App\\Filament\\Resources\\{$model}s\\{$model}Resource::class => [");
        $this->info("    'group' => 'YOUR_GROUP_NAME',");
        $this->info("    'sort' => X,");
        $this->info("    'label' => '{$model}',");
        $this->info("],");
    }
    
    private function modifyResourceToExtendBase($model)
    {
        $resourcePath = app_path("Filament/Resources/{$model}s/{$model}Resource.php");
        
        if (file_exists($resourcePath)) {
            $content = file_get_contents($resourcePath);
            
            // Replace the import and extends
            $content = str_replace(
                'use Filament\Resources\Resource;',
                "use App\Filament\Resources\BaseResource;\nuse Filament\Resources\Resource;",
                $content
            );
            
            $content = str_replace(
                "extends Resource",
                "extends BaseResource",
                $content
            );
            
            // Remove the duplicate Resource import
            $content = str_replace(
                "use App\Filament\Resources\BaseResource;\nuse Filament\Resources\Resource;",
                "use App\Filament\Resources\BaseResource;",
                $content
            );
            
            file_put_contents($resourcePath, $content);
        }
    }
    
    private function createPaparViewPage($model)
    {
        $viewPagePath = app_path("Filament/Resources/{$model}s/Pages/Papar{$model}.php");
        $namespace = "App\\Filament\\Resources\\{$model}s\\Pages";
        $resourceClass = "App\\Filament\\Resources\\{$model}s\\{$model}Resource";
        
        $content = "<?php

namespace {$namespace};

use {$resourceClass};
use App\Filament\Resources\Pages\BaseViewRecord;
use Filament\Actions\EditAction;

class Papar{$model} extends BaseViewRecord
{
    protected static string \$resource = {$model}Resource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
";
        
        file_put_contents($viewPagePath, $content);
        
        // Update the resource to use the new view page
        $this->updateResourceToUsePaparView($model);
        
        // Update the resource to use custom create and edit pages
        $this->updateResourceToUseCustomPages($model);
    }
    
    private function updateResourceToUsePaparView($model)
    {
        $resourcePath = app_path("Filament/Resources/{$model}s/{$model}Resource.php");
        
        if (file_exists($resourcePath)) {
            $content = file_get_contents($resourcePath);
            
            // Add import for Papar page
            $content = str_replace(
                "use App\\Filament\\Resources\\{$model}s\\Pages\\View{$model};",
                "use App\\Filament\\Resources\\{$model}s\\Pages\\View{$model};\nuse App\\Filament\\Resources\\{$model}s\\Pages\\Papar{$model};",
                $content
            );
            
            // Update getPages method to use Papar view
            $content = str_replace(
                "'view' => View{$model}::route('/{record}'),",
                "'view' => Papar{$model}::route('/{record}/papar'),",
                $content
            );
            
            file_put_contents($resourcePath, $content);
        }
    }
    
    private function createTambahCreatePage($model)
    {
        $createPagePath = app_path("Filament/Resources/{$model}s/Pages/Tambah{$model}.php");
        $namespace = "App\\Filament\\Resources\\{$model}s\\Pages";
        $resourceClass = "App\\Filament\\Resources\\{$model}s\\{$model}Resource";
        
        $content = "<?php

namespace {$namespace};

use {$resourceClass};
use App\Filament\Resources\Pages\BaseCreateRecord;

class Tambah{$model} extends BaseCreateRecord
{
    protected static string \$resource = {$model}Resource::class;
}
";
        
        file_put_contents($createPagePath, $content);
    }
    
    private function createPindaEditPage($model)
    {
        $editPagePath = app_path("Filament/Resources/{$model}s/Pages/Pinda{$model}.php");
        $namespace = "App\\Filament\\Resources\\{$model}s\\Pages";
        $resourceClass = "App\\Filament\\Resources\\{$model}s\\{$model}Resource";
        
        $content = "<?php

namespace {$namespace};

use {$resourceClass};
use App\Filament\Resources\Pages\BaseEditRecord;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;

class Pinda{$model} extends BaseEditRecord
{
    protected static string \$resource = {$model}Resource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
";
        
        file_put_contents($editPagePath, $content);
    }
    
    private function updateResourceToUseCustomPages($model)
    {
        $resourcePath = app_path("Filament/Resources/{$model}s/{$model}Resource.php");
        
        if (file_exists($resourcePath)) {
            $content = file_get_contents($resourcePath);
            
            // Add imports for custom pages
            $content = str_replace(
                "use App\\Filament\\Resources\\{$model}s\\Pages\\Create{$model};",
                "use App\\Filament\\Resources\\{$model}s\\Pages\\Create{$model};\nuse App\\Filament\\Resources\\{$model}s\\Pages\\Tambah{$model};",
                $content
            );
            
            $content = str_replace(
                "use App\\Filament\\Resources\\{$model}s\\Pages\\Edit{$model};",
                "use App\\Filament\\Resources\\{$model}s\\Pages\\Edit{$model};\nuse App\\Filament\\Resources\\{$model}s\\Pages\\Pinda{$model};",
                $content
            );
            
            // Update getPages method to use custom pages
            $content = str_replace(
                "'create' => Create{$model}::route('/create'),",
                "'create' => Tambah{$model}::route('/tambah'),",
                $content
            );
            
            $content = str_replace(
                "'edit' => Edit{$model}::route('/{record}/edit'),",
                "'edit' => Pinda{$model}::route('/{record}/pinda'),",
                $content
            );
            
            $content = str_replace(
                "'view' => View{$model}::route('/{record}'),",
                "'view' => Papar{$model}::route('/{record}/papar'),",
                $content
            );
            
            file_put_contents($resourcePath, $content);
        }
    }
}
