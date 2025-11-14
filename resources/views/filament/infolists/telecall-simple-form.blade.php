<div class="space-y-4">
    <!-- Kod Cula Toggle Buttons -->
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Culaan</label>
        <div class="grid grid-cols-2 gap-2 text-xs">
            @foreach([
                "VA" => "🤷🏻‍♂️ Atas Pagar",
                "VB" => "💚 Undi Bulan", 
                "VC" => "⚪ Condong Bulan",
                "VD" => "⚖️ BN",
                "VN" => "🚀 PH",
                "VS" => "🪢 PN",
                "VT" => "🪢 Rakan PN",
                "VR" => "🗻 GRS",
                "VW" => "❌ Salah nombor",
                "VX" => "📵 Tiada jawapan",
                "VY" => "🙅🏻‍♂️ Enggan respon",
                "VZ" => "💆🏻‍♂️ Benci politik"
            ] as $code => $label)
                <label class="flex items-center">
                    <input type="radio" 
                           wire:model="kod_cula" 
                           value="{{ $code }}" 
                           class="mr-2">
                    <span class="text-xs">{{ $label }}</span>
                </label>
            @endforeach
        </div>
    </div>

    <!-- Catatan Textarea -->
    <div>
        <label for="catatan" class="block text-sm font-medium text-gray-700 mb-2">Catatan</label>
        <textarea 
            wire:model="catatan"
            id="catatan"
            rows="3"
            class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
            placeholder="Masukkan catatan..."></textarea>
    </div>
    
    <!-- Save Button -->
    <div class="flex justify-end">
        <button 
            wire:click="saveCulaan"
            wire:loading.attr="disabled"
            class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 active:bg-green-900 focus:outline-none focus:border-green-900 focus:ring focus:ring-green-300 disabled:opacity-25 transition"
        >
            <div wire:loading wire:target="saveCulaan" class="inline-block animate-spin w-4 h-4 mr-2 border-2 border-white border-t-transparent rounded-full"></div>
            Simpan Culaan
        </button>
    </div>
</div>