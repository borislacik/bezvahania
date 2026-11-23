<x-filament-panels::page>
    <x-filament::section>
        <p class="text-sm text-gray-600 dark:text-gray-400">{{ __('admin.availability.help') }}</p>
        <p class="mt-1 text-sm font-medium text-gray-900 dark:text-white">{{ $windowText }}</p>
    </x-filament::section>

    <x-filament::section>
        <div class="mb-4 flex items-center justify-between gap-2">
            <h2 class="text-lg font-bold text-gray-950 dark:text-white" data-testid="availability-month">
                {{ $label }}
            </h2>
            <div class="flex gap-2">
                <x-filament::button
                    color="gray"
                    size="sm"
                    icon="heroicon-m-chevron-left"
                    wire:click="previousMonth"
                    :label-sr-only="true"
                >
                    {{ __('admin.availability.previous') }}
                </x-filament::button>
                <x-filament::button color="gray" size="sm" wire:click="currentMonth">
                    {{ __('admin.availability.today') }}
                </x-filament::button>
                <x-filament::button
                    color="gray"
                    size="sm"
                    icon="heroicon-m-chevron-right"
                    wire:click="nextMonth"
                    :label-sr-only="true"
                >
                    {{ __('admin.availability.next') }}
                </x-filament::button>
            </div>
        </div>

        <div class="grid grid-cols-7 gap-2 text-center text-xs font-bold text-gray-500 uppercase">
            @foreach ($weekdays as $weekday)
                <div>{{ $weekday }}</div>
            @endforeach
        </div>

        <div class="mt-2 grid grid-cols-7 gap-2">
            @foreach ($days as $day)
                @if (! $day['in_month'])
                    <div></div>
                @else
                    <button
                        type="button"
                        wire:click="toggleDay('{{ $day['date'] }}')"
                        wire:key="day-{{ $day['date'] }}"
                        @disabled ($day['past'])
                        data-date="{{ $day['date'] }}"
                        data-blocked="{{ $day['blocked'] ? '1' : '0' }}"
                        @class ([
                            'flex min-h-24 flex-col items-start gap-1 rounded-lg border p-2 text-left text-sm transition',
                            'cursor-not-allowed border-gray-100 bg-gray-50 text-gray-400 dark:border-white/5 dark:bg-white/5' => $day['past'],
                            'border-gray-300 bg-gray-200 text-gray-500 line-through dark:border-white/10 dark:bg-white/10' => $day['blocked'] && ! $day['past'],
                            'border-gray-200 bg-white hover:border-primary-500 dark:border-white/10 dark:bg-gray-900' => ! $day['blocked'] && ! $day['past'],
                            'ring-primary-500 ring-2' => $day['today'],
                        ])
                    >
                        <span class="font-bold">{{ $day['day'] }}</span>
                        @if ($day['blocked'])
                            <span class="text-xs no-underline">{{ __('admin.availability.blocked') }}</span>
                        @endif
                        @foreach ($day['bookings'] as $booking)
                            <span
                                class="bg-primary-50 text-primary-700 dark:bg-primary-500/10 dark:text-primary-400 w-full truncate rounded px-1 py-0.5 text-xs no-underline"
                            >
                                {{ $booking->time_slot->range() }} · {{ $booking->contact_name }}
                            </span>
                        @endforeach
                    </button>
                @endif
            @endforeach
        </div>
    </x-filament::section>
</x-filament-panels::page>
