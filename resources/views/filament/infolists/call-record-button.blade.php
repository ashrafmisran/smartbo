<div class="flex items-center gap-2">
    <button type="button" 
            wire:click="recordCall('{{ $phone_number }}')"
            class="inline-flex items-center px-2 py-1 text-xs font-medium text-green-600 hover:text-green-700 hover:bg-green-50 rounded-md border border-green-200 hover:border-green-300 transition-colors duration-200"
            title="Rekod panggilan">
        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
        </svg>
        Rekod
    </button>
</div>