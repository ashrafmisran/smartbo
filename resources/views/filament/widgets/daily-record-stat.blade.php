<x-filament-widgets::widget>

    <x-filament::section>

        <div class="space-y-6">

            <div>
                {{ $this->form }}
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">

                    <thead>
                        <tr class="border-b">
                            <th class="text-left py-3">
                                Nama Cula
                            </th>

                            <th class="text-right py-3">
                                Bil. Rekod
                            </th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($this->getRecords() as $record)

                            <tr class="border-b">

                                <td class="py-3">
                                    {{ $record->nama_cula ?? '-' }}
                                </td>

                                <td class="text-right py-3">
                                    {{ number_format($record->total_count) }}
                                </td>

                            </tr>

                        @empty

                            <tr>
                                <td colspan="2" class="text-center py-4 text-gray-500">
                                    Tiada rekod
                                </td>
                            </tr>

                        @endforelse
                    </tbody>

                </table>
            </div>

        </div>

    </x-filament::section>

</x-filament-widgets::widget>