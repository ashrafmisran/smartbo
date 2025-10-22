<x-filament-panels::page>
    <div class="fi-page-content">
        {{-- Form Section --}}
        <div class="mb-6">
            {{ $this->form }}
        </div>

        {{-- Results Section --}}
        @if($showResults)
            {{-- Family Analysis Controls --}}
            <div class="mb-6 p-6 bg-gradient-to-br from-gray-50 to-gray-100 dark:from-gray-800 dark:to-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm">
                <div class="flex items-center justify-between mb-6">
                    <div class="flex items-center space-x-3">
                        <div class="p-2 bg-blue-100 dark:bg-blue-900/30 rounded-lg">
                            <x-heroicon-s-users class="h-6 w-6 text-blue-600 dark:text-blue-400" />
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-gray-900 dark:text-white">
                                Analisis Hubungan Keluarga
                            </h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                Kenal pasti hubungan keluarga berdasarkan nama dan alamat
                            </p>
                        </div>
                    </div>
                    
                    <div class="flex items-center space-x-3">
                        <div class="text-right">
                            <div class="text-sm font-medium text-gray-600 dark:text-gray-400">Status:</div>
                            @if($showFamilyAnalysis)
                                <div class="flex items-center space-x-1">
                                    <div class="h-2 w-2 bg-green-500 rounded-full animate-pulse"></div>
                                    <span class="text-sm font-semibold text-green-600 dark:text-green-400">Aktif</span>
                                </div>
                            @else
                                <div class="flex items-center space-x-1">
                                    <div class="h-2 w-2 bg-gray-400 rounded-full"></div>
                                    <span class="text-sm text-gray-500">Belum Dianalisis</span>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
                
                {{-- Family Groups Summary --}}
                @if($showFamilyAnalysis && !empty($familyGroups))
                    <div class="mb-6">
                        <div class="flex items-center justify-between mb-4">
                            <h4 class="text-lg font-semibold text-gray-900 dark:text-white">
                                Kumpulan Keluarga Dikenal Pasti
                            </h4>
                            <span class="px-3 py-1 bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-200 text-sm font-medium rounded-full">
                                {{ count($familyGroups) }} kumpulan
                            </span>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            @foreach($familyGroups as $group)
                                <div class="group relative">
                                    <div class="absolute -inset-0.5 bg-gradient-to-r from-blue-600 to-purple-600 rounded-lg blur opacity-25 group-hover:opacity-75 transition duration-300"></div>
                                    <div class="relative bg-white dark:bg-gray-800 p-5 rounded-lg border border-gray-200 dark:border-gray-700 hover:shadow-lg transition-all duration-300">
                                        <div class="flex items-center justify-between mb-3">
                                            <h5 class="font-bold text-gray-900 dark:text-white text-lg">
                                                {{ $group['name'] }}
                                            </h5>
                                            <div class="flex items-center space-x-2">
                                                <span class="px-2 py-1 text-xs font-medium rounded-full {{ $this->getFamilyGroupColor($group['group_id']) }} bg-opacity-20">
                                                    {{ count($group['members']) }} ahli
                                                </span>
                                            </div>
                                        </div>
                                        
                                        <div class="space-y-3">
                                            @foreach($group['members'] as $member)
                                                <div class="flex items-start space-x-3 p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                                                    <div class="flex-shrink-0">
                                                        <div class="h-8 w-8 bg-gradient-to-br from-blue-500 to-purple-600 rounded-full flex items-center justify-center">
                                                            <span class="text-white text-xs font-bold">
                                                                {{ strtoupper(substr($member['name'], 0, 1)) }}
                                                            </span>
                                                        </div>
                                                    </div>
                                                    <div class="flex-1 min-w-0">
                                                        <p class="text-sm font-medium text-gray-900 dark:text-white truncate">
                                                            {{ $member['name'] }}
                                                        </p>
                                                        <div class="flex items-center space-x-2 mt-1">
                                                            @if(isset($member['relationship']))
                                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300">
                                                                    <x-heroicon-s-user-group class="h-3 w-3 mr-1" />
                                                                    {{ $member['relationship'] }}
                                                                </span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                        
                                        @if(isset($group['confidence']))
                                            <div class="mt-4 pt-3 border-t border-gray-200 dark:border-gray-600">
                                                <div class="flex items-center justify-between">
                                                    <span class="text-xs font-medium text-gray-500 dark:text-gray-400">
                                                        Tahap Keyakinan
                                                    </span>
                                                    <div class="flex items-center space-x-2">
                                                        <div class="w-16 bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                                            <div class="bg-gradient-to-r from-green-400 to-blue-500 h-2 rounded-full transition-all duration-500" 
                                                                 style="width: {{ $group['confidence'] }}%"></div>
                                                        </div>
                                                        <span class="text-xs font-bold text-gray-700 dark:text-gray-300">
                                                            {{ $group['confidence'] }}%
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
                
                {{-- AI Analysis Results --}}
                @if(!empty($aiAnalysisResult))
                    <div class="mt-6 relative">
                        <div class="absolute -inset-0.5 bg-gradient-to-r from-cyan-600 to-blue-600 rounded-lg blur opacity-25"></div>
                        <div class="relative p-6 bg-gradient-to-br from-blue-50 to-cyan-50 dark:from-blue-900/20 dark:to-cyan-900/20 rounded-lg border border-blue-200 dark:border-blue-700">
                            <div class="flex items-center mb-4">
                                <div class="p-2 bg-blue-500 rounded-lg mr-3">
                                    <x-heroicon-s-cpu-chip class="h-6 w-6 text-white" />
                                </div>
                                <div>
                                    <h4 class="font-bold text-blue-900 dark:text-blue-100 text-lg">
                                        Analisis Kecerdasan Buatan
                                    </h4>
                                    <p class="text-sm text-blue-700 dark:text-blue-300">
                                        Pandangan mendalam berdasarkan corak nama Malaysia
                                    </p>
                                </div>
                            </div>
                            
                            <div class="bg-white dark:bg-gray-800/50 rounded-lg p-4 mb-4">
                                <p class="text-blue-800 dark:text-blue-200 leading-relaxed">
                                    {{ $aiAnalysisResult['summary'] ?? 'Analisis AI telah selesai.' }}
                                </p>
                            </div>
                            
                            @if(isset($aiAnalysisResult['confidence']))
                                <div class="flex items-center justify-between p-3 bg-white dark:bg-gray-800/50 rounded-lg mb-4">
                                    <span class="text-sm font-medium text-blue-900 dark:text-blue-100">
                                        Tahap Keyakinan Keseluruhan:
                                    </span>
                                    <div class="flex items-center space-x-3">
                                        <div class="w-24 bg-blue-200 dark:bg-blue-800 rounded-full h-2">
                                            <div class="bg-gradient-to-r from-blue-400 to-cyan-500 h-2 rounded-full transition-all duration-500" 
                                                 style="width: {{ $aiAnalysisResult['confidence'] }}%"></div>
                                        </div>
                                        <span class="text-sm font-bold text-blue-900 dark:text-blue-100">
                                            {{ $aiAnalysisResult['confidence'] }}%
                                        </span>
                                    </div>
                                </div>
                            @endif
                            
                            @if(!empty($aiAnalysisResult['ai_insights']))
                                <div class="bg-white dark:bg-gray-800/50 rounded-lg p-4">
                                    <h5 class="text-sm font-semibold text-blue-900 dark:text-blue-100 mb-3 flex items-center">
                                        <x-heroicon-s-light-bulb class="h-4 w-4 mr-2" />
                                        Pandangan AI:
                                    </h5>
                                    <div class="space-y-2">
                                        @foreach($aiAnalysisResult['ai_insights'] as $insight)
                                            <div class="flex items-start space-x-2">
                                                <div class="h-1.5 w-1.5 bg-blue-400 rounded-full mt-2 flex-shrink-0"></div>
                                                <p class="text-sm text-blue-800 dark:text-blue-200 leading-relaxed">
                                                    {{ $insight }}
                                                </p>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif
                
                {{-- Instructions --}}
                @if(!$showFamilyAnalysis)
                    <div class="text-center py-8">
                        <div class="mx-auto h-16 w-16 text-gray-400 mb-4">
                            <x-heroicon-o-light-bulb class="h-16 w-16" />
                        </div>
                        <h4 class="text-lg font-medium text-gray-900 dark:text-white mb-2">
                            Mula Analisis Keluarga
                        </h4>
                        <p class="text-gray-600 dark:text-gray-400 max-w-lg mx-auto leading-relaxed">
                            Klik <strong>"Analisis Maklumat"</strong> untuk mengkelompokkan pengundi berdasarkan potensi hubungan keluarga,
                            kemudian klik <strong>"Analisis AI Keluarga"</strong> untuk mendapat pandangan AI yang lebih mendalam tentang corak nama Malaysia.
                        </p>
                    </div>
                @endif
            </div>
            
            
            {{-- Table Section --}}
            <div class="relative">
                <div class="absolute -inset-0.5 bg-gradient-to-r from-blue-600 to-purple-600 rounded-xl blur opacity-20"></div>
                <div class="relative bg-white dark:bg-gray-800 rounded-xl shadow-xl border border-gray-200 dark:border-gray-700">
                    <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                <div class="p-2 bg-purple-100 dark:bg-purple-900/30 rounded-lg">
                                    <x-heroicon-s-table-cells class="h-6 w-6 text-purple-600 dark:text-purple-400" />
                                </div>
                                <div>
                                    <h3 class="text-xl font-bold text-gray-900 dark:text-white">
                                        Senarai Pengundi
                                    </h3>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">
                                        Data pengundi untuk lokasi yang dipilih
                                    </p>
                                </div>
                            </div>
                            
                            @if($showFamilyAnalysis && !empty($familyGroups))
                                <div class="flex items-center space-x-2">
                                    <div class="h-2 w-2 bg-green-500 rounded-full animate-pulse"></div>
                                    <span class="text-sm font-medium text-green-600 dark:text-green-400">
                                        Analisis Aktif
                                    </span>
                                </div>
                            @endif
                        </div>
                    </div>
                    
                    <div class="overflow-hidden">
                        {{ $this->table }}
                    </div>
                </div>
            </div>
        @else
            {{-- Empty State --}}
            <div class="relative">
                <div class="absolute -inset-0.5 bg-gradient-to-r from-gray-400 to-gray-600 rounded-xl blur opacity-10"></div>
                <div class="relative text-center py-16 bg-gradient-to-br from-gray-50 to-gray-100 dark:from-gray-800 dark:to-gray-900 rounded-xl border border-gray-200 dark:border-gray-700">
                    <div class="relative">
                        <div class="mx-auto h-24 w-24 text-gray-400 mb-6 relative">
                            <div class="absolute inset-0 bg-gradient-to-br from-blue-100 to-purple-100 dark:from-blue-900/20 dark:to-purple-900/20 rounded-full"></div>
                            <x-heroicon-o-home class="h-24 w-24 relative z-10 mx-auto" />
                        </div>
                        <div class="space-y-4">
                            <h3 class="text-2xl font-bold text-gray-900 dark:text-white">
                                Pilih Lokasi Untuk Mula
                            </h3>
                            <p class="text-gray-600 dark:text-gray-400 max-w-md mx-auto leading-relaxed">
                                Pilih DUN, Daerah, dan Lokaliti dalam borang di atas untuk mula menganalisis maklumat isi rumah dan hubungan keluarga.
                            </p>
                            <div class="flex justify-center space-x-8 mt-8">
                                <div class="text-center">
                                    <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900/30 rounded-full flex items-center justify-center mx-auto mb-2">
                                        <span class="text-blue-600 dark:text-blue-400 font-bold">1</span>
                                    </div>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">Pilih DUN</p>
                                </div>
                                <div class="text-center">
                                    <div class="w-12 h-12 bg-purple-100 dark:bg-purple-900/30 rounded-full flex items-center justify-center mx-auto mb-2">
                                        <span class="text-purple-600 dark:text-purple-400 font-bold">2</span>
                                    </div>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">Pilih Daerah</p>
                                </div>
                                <div class="text-center">
                                    <div class="w-12 h-12 bg-green-100 dark:bg-green-900/30 rounded-full flex items-center justify-center mx-auto mb-2">
                                        <span class="text-green-600 dark:text-green-400 font-bold">3</span>
                                    </div>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">Pilih Lokaliti</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</x-filament-panels::page>