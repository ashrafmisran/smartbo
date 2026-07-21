<x-filament-widgets::widget>

    <x-filament::section>

        {{ $this->form }}

        <div class="mt-6 overflow-x-auto">

            <table class="w-full text-sm">

                <thead>

                    <tr class="border-b">

                        <th class="text-left py-2">
                            Kod Cula
                        </th>

                        <th class="text-right py-2">
                            Bil. Rekod
                        </th>

                    </tr>

                </thead>

                <tbody>

                    @forelse($this->getRecords() as $record)

                        <tr class="border-b">

                            <td class="py-2">
                                {{ $record->kod_cula }}
                            </td>

                            <td class="text-right py-2">
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

    </x-filament::section>

</x-filament-widgets::widget>