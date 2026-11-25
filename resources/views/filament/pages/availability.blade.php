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
                    <div
                        wire:key="day-{{ $day['date'] }}"
                        data-date="{{ $day['date'] }}"
                        data-blocked="{{ $day['blocked'] ? '1' : '0' }}"
                        @class ([
                            'flex min-h-28 flex-col gap-1.5 rounded-lg border p-2 text-left text-sm',
                            'border-gray-100 bg-gray-50 text-gray-400 dark:border-white/5 dark:bg-white/5' => $day['past'],
                            'border-gray-200 bg-white dark:border-white/10 dark:bg-gray-900' => ! $day['past'],
                            'ring-primary-500 ring-2' => $day['today'],
                        ])
                    >
                        <div class="flex items-center justify-between gap-1">
                            <span class="font-bold">{{ $day['day'] }}</span>
                            @unless ($day['past'] || ($day['has_bookings'] && ! $day['blocked']))
                                <button
                                    type="button"
                                    wire:click="toggleDay('{{ $day['date'] }}')"
                                    data-day-toggle
                                    title="{{ $day['blocked'] ? __('admin.availability.unblock_day') : __('admin.availability.block_day') }}"
                                    @class ([
                                        'rounded px-1.5 py-0.5 text-[11px] font-semibold transition',
                                        'bg-gray-700 text-white hover:bg-gray-600 dark:bg-gray-600' => $day['blocked'],
                                        'text-gray-500 hover:bg-gray-100 hover:text-gray-900 dark:hover:bg-white/10 dark:hover:text-white' => ! $day['blocked'],
                                    ])
                                >
                                    {{ __('admin.availability.whole_day') }}
                                </button>
                            @endunless
                        </div>

                        @foreach ($day['slots'] as $slot => $state)
                            @if ($state['booking'])
                                <span
                                    class="bg-primary-50 text-primary-700 dark:bg-primary-500/10 dark:text-primary-400 w-full truncate rounded px-1.5 py-1 text-xs"
                                    data-slot="{{ $slot }}"
                                    data-state="booked"
                                >
                                    {{ $state['range'] }} · {{ $state['booking']->contact_name }}
                                </span>
                            @else
                                <button
                                    type="button"
                                    wire:click="toggleSlot('{{ $day['date'] }}', '{{ $slot }}')"
                                    @disabled ($day['past'])
                                    data-slot="{{ $slot }}"
                                    data-state="{{ $state['blocked'] ? 'blocked' : 'free' }}"
                                    @class ([
                                        'w-full rounded border px-1.5 py-1 text-left text-xs transition',
                                        'cursor-not-allowed border-transparent' => $day['past'],
                                        'border-gray-300 bg-gray-200 text-gray-500 line-through dark:border-white/10 dark:bg-white/10' => $state['blocked'] && ! $day['past'],
                                        'hover:border-primary-500 border-gray-200 text-gray-700 dark:border-white/10 dark:text-gray-300' => ! $state['blocked'] && ! $day['past'],
                                    ])
                                >
                                    {{ $state['range'] }}
                                    @if ($state['blocked'] && ! $day['past'])
                                        <span class="sr-only">{{ __('admin.availability.blocked') }}</span>
                                    @endif
                                </button>
                            @endif
                        @endforeach
                    </div>
                @endif
            @endforeach
        </div>
    </x-filament::section>
</x-filament-panels::page>
