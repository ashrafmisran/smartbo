<div class="space-y-4">
    {{ $this->form }}
    
    <div class="flex justify-end gap-2 mt-4">
        <x-filament::button 
            type="submit"
            wire:click="save"
            color="success"
            size="sm"
        >
            Simpan Culaan
        </x-filament::button>
    </div>
</div>