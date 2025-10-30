<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Filter Form -->
        <div class="bg-white shadow rounded-lg">
            <div class="p-6">
                {{ $this->form }}
            </div>
            <!-- Results Table -->
            @if($showResults)
                <div class="bg-white shadow rounded-lg mt-3">
                    <div class="p-6 mt-3">
                        {{ $this->table }}
                    </div>
                </div>
            @endif
        </div>

    </div>

    <div
        wire:loading
        wire:target="data.cula_pn, data.cula_lawan, data.cula_lain"
        class="absolute inset-0 z-20 flex items-center justify-center rounded-md bg-white/60 backdrop-blur-sm"
    >
        <x-filament::loading-indicator class="h-6 w-6 text-primary-600" />
        <span class="ml-2 text-sm text-gray-700">Mengemas kini...</span>
    </div>
</x-filament-panels::page>