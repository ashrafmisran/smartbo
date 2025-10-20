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
</x-filament-panels::page>