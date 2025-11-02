<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Info Pengundi --}}
        <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <div class="fi-section-content-ctn p-6">
                <div class="fi-section-header mb-4">
                    <h3 class="fi-section-heading text-base font-semibold text-gray-950 dark:text-white">
                        Maklumat Pengundi
                    </h3>
                </div>
                
                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <p class="text-sm font-medium text-gray-700 dark:text-gray-200">Nama</p>
                        <p class="text-lg font-bold">{{ $pengundi->Nama }}</p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-700 dark:text-gray-200">No. KP</p>
                        <p class="text-lg">{{ $pengundi->No_KP_Baru }}</p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-700 dark:text-gray-200">Umur & Jantina</p>
                        <p class="text-lg">
                            @php
                                $umur = now()->year - (2000 + (int)substr($pengundi->No_KP_Baru, 0, 2));
                                if ($umur < 18) $umur += 100;
                                $jantina = ((int)substr($pengundi->No_KP_Baru, -2) % 2 === 0) ? 'Perempuan' : 'Lelaki';
                            @endphp
                            {{ $umur }} tahun | {{ $jantina }}
                        </p>
                    </div>
                </div>
                
                <div class="grid grid-cols-3 gap-4 mt-4">
                    <div>
                        <p class="text-sm font-medium text-gray-700 dark:text-gray-200">Agama</p>
                        <p>{{ $pengundi->Agama ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-700 dark:text-gray-200">Keturunan</p>
                        <p>{{ $pengundi->Keturunan ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-700 dark:text-gray-200">Bangsa</p>
                        <p>{{ $pengundi->Bangsa ?? '-' }}</p>
                    </div>
                </div>
                
                <div class="mt-4">
                    <p class="text-sm font-medium text-gray-700 dark:text-gray-200">Nombor Telefon</p>
                    @if(count($phoneNumbers) > 0)
                        <ul class="list-disc list-inside">
                            @foreach($phoneNumbers as $phone)
                                <li>{{ $phone }}</li>
                            @endforeach
                        </ul>
                    @else
                        <p class="text-gray-500">Tiada nombor telefon</p>
                    @endif
                </div>
            </div>
        </div>

        {{-- Skrip Panggilan --}}
        <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <div class="fi-section-content-ctn p-6">
                <div class="fi-section-header mb-4">
                    <h3 class="fi-section-heading text-base font-semibold text-gray-950 dark:text-white">
                        Skrip Panggilan
                    </h3>
                </div>
                <div class="prose max-w-none">
                    {!! $skrip !!}
                </div>
            </div>
        </div>

        {{-- Culaan Form --}}
        <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <div class="fi-section-content-ctn p-6">
                <div class="fi-section-header mb-4">
                    <h3 class="fi-section-heading text-base font-semibold text-gray-950 dark:text-white">
                        Rekod Culaan
                    </h3>
                </div>
                <p class="text-gray-600">Culaan form will be added here...</p>
            </div>
        </div>
    </div>
</x-filament-panels::page>
