<?php

namespace App\Console\Commands;

use App\Models\Database;
use App\Services\DatabaseConnectionService;
use App\Services\DaftaraService;
use Illuminate\Console\Command;

class TestDatabaseConnections extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:test-connections 
                           {--search-ic= : Test by searching for specific IC number}
                           {--search-name= : Test by searching for specific name}
                           {--stats : Show database statistics}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test dynamic database connections and daftara table access';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Testing Database Connections...');
        
        $connectionService = new DatabaseConnectionService();
        $daftaraService = new DaftaraService($connectionService);

        // Test basic connectivity
        $this->testConnections($connectionService);

        // Test searches if requested
        if ($icNumber = $this->option('search-ic')) {
            $this->testICSearch($daftaraService, $icNumber);
        }

        if ($name = $this->option('search-name')) {
            $this->testNameSearch($daftaraService, $name);
        }

        if ($this->option('stats')) {
            $this->showStatistics($daftaraService);
        }

        $this->info('Test completed!');
    }

    private function testConnections(DatabaseConnectionService $service)
    {
        $this->info("\n=== Testing Basic Connections ===");
        
        $databases = Database::all();
        
        if ($databases->isEmpty()) {
            $this->warn('No databases found in the system.');
            return;
        }

        foreach ($databases as $database) {
            $this->line("Testing: {$database->name} ({$database->host}:{$database->port})");
            
            try {
                $isConnected = $service->testConnection($database);
                
                if ($isConnected) {
                    $this->info("  ✓ Connected successfully");
                } else {
                    $this->error("  ✗ Connection failed");
                }
            } catch (\Exception $e) {
                $this->error("  ✗ Error: " . $e->getMessage());
            }
        }
    }

    private function testICSearch(DaftaraService $service, string $icNumber)
    {
        $this->info("\n=== Testing IC Search ===");
        $this->line("Searching for IC: {$icNumber}");

        try {
            $results = $service->searchByIC($icNumber);
            
            if (empty($results)) {
                $this->warn('No results found.');
                return;
            }

            foreach ($results as $result) {
                if (isset($result['error'])) {
                    $this->error("Database {$result['database']}: {$result['error']}");
                } else {
                    $this->info("Found in {$result['database']}: {$result['Nama']} ({$result['No_KP_Baru']})");
                }
            }
        } catch (\Exception $e) {
            $this->error("Search failed: " . $e->getMessage());
        }
    }

    private function testNameSearch(DaftaraService $service, string $name)
    {
        $this->info("\n=== Testing Name Search ===");
        $this->line("Searching for name: {$name}");

        try {
            $results = $service->searchByName($name);
            
            if (empty($results)) {
                $this->warn('No results found.');
                return;
            }

            $count = 0;
            foreach ($results as $result) {
                if (isset($result['error'])) {
                    $this->error("Database {$result['database']}: {$result['error']}");
                } else {
                    $this->info("Found in {$result['database']}: {$result['Nama']} ({$result['No_KP_Baru']})");
                    $count++;
                }
            }
            
            $this->line("Total matches: {$count}");
        } catch (\Exception $e) {
            $this->error("Search failed: " . $e->getMessage());
        }
    }

    private function showStatistics(DaftaraService $service)
    {
        $this->info("\n=== Database Statistics ===");

        try {
            $statistics = $service->getDatabaseStatistics();
            
            $totalRecords = 0;
            $connectedDatabases = 0;

            foreach ($statistics as $stat) {
                $this->line("Database: {$stat['database']}");
                $this->line("  Host: {$stat['host']}");
                $this->line("  Status: {$stat['status']}");
                
                if ($stat['status'] === 'connected') {
                    $this->line("  Records: {$stat['total_records']}");
                    $this->line("  Last Updated: {$stat['last_updated']}");
                    $totalRecords += $stat['total_records'];
                    $connectedDatabases++;
                } else if (isset($stat['error'])) {
                    $this->error("  Error: {$stat['error']}");
                }
                
                $this->line('');
            }

            $this->info("Summary:");
            $this->line("  Total Databases: " . count($statistics));
            $this->line("  Connected: {$connectedDatabases}");
            $this->line("  Total Records: {$totalRecords}");
        } catch (\Exception $e) {
            $this->error("Failed to get statistics: " . $e->getMessage());
        }
    }
}
