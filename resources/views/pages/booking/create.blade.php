@php
    $slotMap = collect($months)
        ->flatMap(fn (array $month) => collect($month['weeks'])->flatten(1))
        ->filter(fn (array $day) => $day['in_month'])
        ->mapWithKeys(fn (array $day) => [$day['date'] => $day['slots']]);

    $courseMap = $courses->mapWithKeys(fn ($course) => [
        $course->id => [
            'name' => $course->name,
            'price' => Number::currency((float) $course->price, 'EUR', 'sk', 0),
            'duration' => $course->duration_label,
        ],
    ]);

    $oldDate = old('date');
    $initialMonth = $oldDate && str_starts_with($oldDate, $months[1]['key']) ? 1 : 0;
@endphp

<x-layouts.public :seo="$seo">
    <section class="section bg-surface" id="rezervacia">
        <div class="container-page">
            <div class="mx-auto mb-12 flex max-w-3xl flex-col items-center gap-3 text-center">
                <span class="eyebrow bg-primary-container text-on-primary">
                    <x-site.icon name="edit_calendar" class="size-4" />
                    {{ __('booking.eyebrow') }}
                </span>
                <h1 class="text-headline-xl-mobile lg:text-headline-xl text-on-surface font-bold">
                    {{ __('booking.title') }}
                </h1>
                <p class="text-body-lg text-on-surface-variant">{{ __('booking.lead') }}</p>
            </div>

            <form
                method="POST"
                action="{{ route('booking.store') }}"
                novalidate
                class="card p-5 shadow-xl lg:p-10"
                x-data="bookingForm({
                    month: {{ $initialMonth }},
                    date: @js($oldDate),
                    slot: @js(old('time_slot')),
                    course: @js((string) old('course_id', $selectedCourse?->id)),
                    courses: @js($courseMap),
                })"
                data-testid="booking-form"
            >
                @csrf

                @if ($errors->any())
                    <div
                        class="border-error bg-error-container text-error mb-8 flex items-start gap-3 rounded-xl border p-4"
                        role="alert"
                    >
                        <x-site.icon name="error" class="size-6" />
                        <ul class="text-body-sm space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="grid grid-cols-1 gap-10 lg:grid-cols-12">
                    {{-- LEFT: date, slot, workshop --}}
                    <div class="flex flex-col gap-8 lg:col-span-7">
                        {{-- Step 1: day --}}
                        <fieldset>
                            <legend class="mb-3 flex w-full items-center justify-between gap-2">
                                <span class="flex items-center gap-2">
                                    <x-site.step-number>1</x-site.step-number>
                                    <span
                                        class="text-headline-sm text-on-surface font-heading font-bold"
                                        >{{ __('booking.steps.date') }}</span
                                    >
                                </span>
                            </legend>

                            <div class="bg-surface-container-low rounded-xl p-4">
                                @foreach ($months as $index => $month)
                                    <div
                                        x-show="month === {{ $index }}"
                                        @if ($index !== $initialMonth) x-cloak @endif
                                        data-month="{{ $month['key'] }}"
                                    >
                                        <div class="mb-4 flex items-center justify-between">
                                            <h2
                                                class="font-heading text-headline-sm text-on-surface font-bold"
                                                aria-live="polite"
                                            >
                                                {{ $month['label'] }}
                                            </h2>
                                            <div class="flex gap-2">
                                                <button
                                                    type="button"
                                                    class="bg-surface-container-lowest text-on-surface hover:bg-surface-container flex size-9 items-center justify-center rounded-lg disabled:opacity-30"
                                                    @click="month = 0"
                                                    @disabled ($index === 0)
                                                    aria-label="{{ __('booking.calendar.previous') }}"
                                                >
                                                    <x-site.icon name="chevron_left" class="size-5" />
                                                </button>
                                                <button
                                                    type="button"
                                                    class="bg-surface-container-lowest text-on-surface hover:bg-surface-container flex size-9 items-center justify-center rounded-lg disabled:opacity-30"
                                                    @click="month = 1"
                                                    @disabled ($index === 1)
                                                    aria-label="{{ __('booking.calendar.next') }}"
                                                    data-testid="next-month"
                                                >
                                                    <x-site.icon name="chevron_right" class="size-5" />
                                                </button>
                                            </div>
                                        </div>

                                        <div
                                            class="font-heading text-label-badge text-on-surface-variant mb-2 grid grid-cols-7 text-center font-bold uppercase"
                                            aria-hidden="true"
                                        >
                                            @foreach (__('booking.calendar.weekdays') as $weekday)
                                                <span>{{ $weekday }}</span>
                                            @endforeach
                                        </div>

                                        <div class="text-body-sm grid grid-cols-7 gap-1.5 text-center font-semibold">
                                            @foreach ($month['weeks'] as $week)
                                                @foreach ($week as $day)
                                                    @if (! $day['in_month'])
                                                        <span class="p-2" aria-hidden="true"></span>
                                                    @elseif ($day['status'] === 'available')
                                                        <label
                                                            class="has-[:focus-visible]:ring-primary-container relative cursor-pointer rounded-lg p-2 has-[:focus-visible]:ring-2"
                                                            :class="date === '{{ $day['date'] }}' ? 'bg-primary-container text-on-primary shadow-sm' : 'bg-surface-container-lowest text-primary hover:bg-primary-fixed'"
                                                            data-date="{{ $day['date'] }}"
                                                            data-status="available"
                                                        >
                                                            <input
                                                                type="radio"
                                                                name="date"
                                                                value="{{ $day['date'] }}"
                                                                class="sr-only"
                                                                x-model="date"
                                                                @change="selectDay('{{ $day['date'] }}')"
                                                                @checked ($oldDate === $day['date'])
                                                            />
                                                            <span aria-hidden="true">{{ $day['day'] }}</span>
                                                            <span
                                                                class="sr-only"
                                                                >{{ __('booking.calendar.day_available', ['date' => \Illuminate\Support\Carbon::parse($day['date'])->translatedFormat('l j. F')]) }}</span
                                                            >
                                                            <span
                                                                class="absolute bottom-1 left-1/2 size-1 -translate-x-1/2 rounded-full"
                                                                :class="date === '{{ $day['date'] }}' ? 'bg-on-primary' : 'bg-primary-container'"
                                                            ></span>
                                                        </label>
                                                    @else
                                                        <span
                                                            class="bg-surface-container-low text-on-surface-variant/40 cursor-not-allowed rounded-lg p-2 line-through"
                                                            data-date="{{ $day['date'] }}"
                                                            data-status="{{ $day['status'] }}"
                                                        >
                                                            <span aria-hidden="true">{{ $day['day'] }}</span>
                                                            <span
                                                                class="sr-only"
                                                                >{{ __('booking.calendar.day_unavailable', ['date' => \Illuminate\Support\Carbon::parse($day['date'])->translatedFormat('l j. F')]) }}</span
                                                            >
                                                        </span>
                                                    @endif
                                                @endforeach
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach

                                <div class="text-body-sm text-on-surface-variant mt-4 flex flex-wrap gap-x-6 gap-y-2">
                                    <span class="flex items-center gap-2"
                                        ><span class="bg-primary-container inline-block size-2.5 rounded-full"></span
                                        >{{ __('booking.calendar.legend_available') }}</span
                                    >
                                    <span class="flex items-center gap-2"
                                        ><span
                                            class="bg-on-surface-variant/30 inline-block size-2.5 rounded-full"
                                        ></span
                                        >{{ __('booking.calendar.legend_unavailable') }}</span
                                    >
                                </div>
                            </div>
                            @error ('date')
                                <p class="field-error">{{ $message }}</p>
                            @enderror
                        </fieldset>

                        {{-- Step 2: slot --}}
                        <fieldset x-ref="slots">
                            <legend class="mb-3 flex items-center gap-2">
                                <x-site.step-number>2</x-site.step-number>
                                <span
                                    class="text-headline-sm text-on-surface font-heading font-bold"
                                    >{{ __('booking.steps.slot') }}</span
                                >
                            </legend>
                            <p
                                class="text-body-sm text-on-surface-variant mb-3"
                                x-show="!date"
                            >{{ __('booking.calendar.pick_date_first') }}</p>
                            <p
                                class="text-body-sm text-on-surface mb-3 font-semibold"
                                x-show="date"
                                x-cloak
                                x-text="dateLabel"
                            ></p>
                            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                                @foreach ($slots as $slot)
                                    <label
                                        class="has-[:focus-visible]:ring-primary-container flex cursor-pointer flex-col rounded-xl p-4 text-center transition-colors has-[:disabled]:cursor-not-allowed has-[:disabled]:opacity-40 has-[:focus-visible]:ring-2"
                                        :class="slot === '{{ $slot->value }}' ? 'bg-surface-container-highest ring-primary-container ring-2' : 'bg-surface-container-low hover:bg-surface-container'"
                                        data-slot="{{ $slot->value }}"
                                    >
                                        <input
                                            type="radio"
                                            name="time_slot"
                                            value="{{ $slot->value }}"
                                            class="sr-only"
                                            x-model="slot"
                                            :disabled="date && @js($slotMap)[date]?.['{{ $slot->value }}'] !== 'available'"
                                            @checked (old('time_slot') === $slot->value)
                                        />
                                        <span
                                            class="font-heading text-label-lg text-on-surface font-bold"
                                            >{{ $slot->label() }}</span
                                        >
                                        <span class="text-body-sm text-on-surface-variant" x-show="date" x-cloak>
                                            <span
                                                x-show="@js($slotMap)[date]?.['{{ $slot->value }}'] === 'available'"
                                                >{{ __('booking.calendar.slot_free') }}</span
                                            >
                                            <span
                                                x-show="date && @js($slotMap)[date]?.['{{ $slot->value }}'] !== 'available'"
                                                >{{ __('booking.calendar.slot_taken') }}</span
                                            >
                                        </span>
                                    </label>
                                @endforeach
                            </div>
                            @error ('time_slot')
                                <p class="field-error">{{ $message }}</p>
                            @enderror
                        </fieldset>

                        {{-- Step 3: workshop --}}
                        <fieldset>
                            <legend class="mb-3 flex items-center gap-2">
                                <x-site.step-number>3</x-site.step-number>
                                <span
                                    class="text-headline-sm text-on-surface font-heading font-bold"
                                    >{{ __('booking.steps.course') }}</span
                                >
                            </legend>
                            <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
                                @foreach ($courses as $course)
                                    <label
                                        class="has-[:focus-visible]:ring-primary-container cursor-pointer rounded-xl p-3.5 text-left transition-all has-[:focus-visible]:ring-2"
                                        :class="course === '{{ $course->id }}' ? 'bg-surface-container-highest ring-primary-container shadow-sm ring-2' : 'bg-surface-container-low hover:bg-surface-container'"
                                    >
                                        <input
                                            type="radio"
                                            name="course_id"
                                            value="{{ $course->id }}"
                                            class="sr-only"
                                            x-model="course"
                                            @checked ((string) old('course_id', $selectedCourse?->id) === (string) $course->id)
                                        />
                                        @if ($course->badge)
                                            <span
                                                class="font-heading text-label-badge text-primary mb-1 block font-bold uppercase"
                                                >{{ $course->badge }}</span
                                            >
                                        @endif
                                        <span
                                            class="font-heading text-label-lg text-on-surface block font-bold"
                                            >{{ $course->name }}</span
                                        >
                                        <span class="text-body-sm text-on-surface-variant">
                                            {{ Number::currency((float) $course->price, 'EUR', 'sk', 0) }} {{ __('site.common.per_group') }}
                                        </span>
                                    </label>
                                @endforeach
                            </div>
                            @error ('course_id')
                                <p class="field-error">{{ $message }}</p>
                            @enderror
                        </fieldset>
                    </div>

                    {{-- RIGHT: contact details + summary --}}
                    <div class="flex flex-col lg:col-span-5">
                        <div class="bg-surface-container-low flex flex-col gap-5 rounded-xl p-6">
                            <div class="flex items-center gap-2">
                                <x-site.step-number>4</x-site.step-number>
                                <h2 class="text-headline-sm text-on-surface font-bold">
                                    {{ __('booking.steps.contact') }}
                                </h2>
                            </div>

                            <div class="flex flex-col gap-4">
                                <x-site.field
                                    name="contact_name"
                                    :label="__('booking.fields.contact_name')"
                                    :placeholder="__('booking.placeholders.contact_name')"
                                    autocomplete="name"
                                    required
                                />
                                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                                    <x-site.field
                                        name="phone"
                                        type="tel"
                                        :label="__('booking.fields.phone')"
                                        :placeholder="__('booking.placeholders.phone')"
                                        autocomplete="tel"
                                        required
                                    />
                                    <x-site.field
                                        name="email"
                                        type="email"
                                        :label="__('booking.fields.email')"
                                        :placeholder="__('booking.placeholders.email')"
                                        autocomplete="email"
                                        required
                                    />
                                </div>
                                <x-site.field
                                    name="address"
                                    :label="__('booking.fields.address')"
                                    :placeholder="__('booking.placeholders.address')"
                                    autocomplete="street-address"
                                    required
                                >
                                    <p class="text-body-sm text-on-surface-variant mt-1">{{ __('booking.area_note') }}</p>
                                </x-site.field>

                                <div
                                    x-data="{ billing: @js((bool) old('billing_different')) }"
                                    class="flex flex-col gap-3"
                                    data-testid="billing"
                                >
                                    <label class="text-body-sm text-on-surface flex items-start gap-3">
                                        <input
                                            type="checkbox"
                                            name="billing_different"
                                            value="1"
                                            class="accent-primary-container mt-0.5 size-5 shrink-0"
                                            x-model="billing"
                                            @checked (old('billing_different'))
                                        />
                                        <span>{{ __('booking.fields.billing_different') }}</span>
                                    </label>
                                    <div
                                        x-show="billing"
                                        x-collapse
                                        @if (! old('billing_different')) style="display: none" @endif
                                    >
                                        <x-site.field
                                            name="billing_address"
                                            type="textarea"
                                            :label="__('booking.fields.billing_address')"
                                            :placeholder="__('booking.placeholders.billing_address')"
                                            x-bind:required="billing"
                                            required
                                        />
                                    </div>
                                </div>

                                <fieldset>
                                    <legend class="field-label">{{ __('booking.fields.participants') }} *</legend>
                                    <div class="grid grid-cols-3 gap-2">
                                        @foreach (range(\App\Models\Booking::MIN_PARTICIPANTS, \App\Models\Booking::MAX_PARTICIPANTS) as $count)
                                            <label
                                                class="bg-surface-container-lowest text-body-sm hover:bg-surface-container has-[:checked]:bg-primary-container has-[:checked]:text-on-primary has-[:focus-visible]:ring-primary-container flex cursor-pointer items-center justify-center rounded-lg p-2.5 font-semibold shadow-sm has-[:focus-visible]:ring-2"
                                            >
                                                <input
                                                    type="radio"
                                                    name="participants"
                                                    value="{{ $count }}"
                                                    class="sr-only"
                                                    @checked ((int) old('participants', 4) === $count)
                                                />
                                                {{ trans_choice('booking.participants_option', $count) }}
                                            </label>
                                        @endforeach
                                    </div>
                                    @error ('participants')
                                        <p class="field-error">{{ $message }}</p>
                                    @enderror
                                </fieldset>

                                <x-site.field
                                    name="note"
                                    type="textarea"
                                    :label="__('booking.fields.note')"
                                    :placeholder="__('booking.placeholders.note')"
                                />

                                {{-- Honeypot --}}
                                <div class="hidden" aria-hidden="true">
                                    <input type="text" name="website" tabindex="-1" autocomplete="off" />
                                </div>

                                <div>
                                    <label class="text-body-sm text-on-surface flex items-start gap-3">
                                        <input
                                            type="checkbox"
                                            name="consent"
                                            value="1"
                                            class="accent-primary-container mt-0.5 size-5 shrink-0"
                                            @checked (old('consent'))
                                            required
                                        />
                                        <span>
                                            {!! __('booking.fields.consent', [
                                                'terms' => '<a class="text-primary underline" href="'.route('legal.terms').'" target="_blank">'.e(__('booking.fields.consent_terms')).'</a>',
                                                'privacy' => '<a class="text-primary underline" href="'.route('legal.privacy').'" target="_blank">'.e(__('booking.fields.consent_privacy')).'</a>',
                                            ]) !!}
                                        </span>
                                    </label>
                                    @error ('consent')
                                        <p class="field-error">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <div class="bg-surface-container-lowest mt-2 rounded-xl p-4 shadow-sm" aria-live="polite">
                                <h3
                                    class="font-heading text-label-badge text-on-surface-variant mb-2 font-bold tracking-wider uppercase"
                                >
                                    {{ __('booking.summary.title') }}
                                </h3>
                                <dl class="text-body-sm space-y-1.5">
                                    <div class="flex justify-between gap-4">
                                        <dt class="text-on-surface-variant">{{ __('booking.summary.course') }}:</dt>
                                        <dd
                                            class="text-on-surface text-right font-bold"
                                            x-text="selectedCourse?.name ?? @js(__('booking.summary.not_selected'))"
                                        >
                                            {{ $selectedCourse?->name }}
                                        </dd>
                                    </div>
                                    <div class="flex justify-between gap-4">
                                        <dt class="text-on-surface-variant">{{ __('booking.summary.date') }}:</dt>
                                        <dd class="text-on-surface text-right font-bold">
                                            <span
                                                x-text="dateLabel ?? @js(__('booking.summary.not_selected'))"
                                                >{{ __('booking.summary.not_selected') }}</span
                                            >
                                            <span x-show="slot === 'morning'" x-cloak
                                                >, {{ \App\Enums\TimeSlot::Morning->range() }}</span
                                            >
                                            <span x-show="slot === 'afternoon'" x-cloak
                                                >, {{ \App\Enums\TimeSlot::Afternoon->range() }}</span
                                            >
                                        </dd>
                                    </div>
                                    <div class="flex justify-between gap-4">
                                        <dt class="text-on-surface-variant">{{ __('booking.summary.duration') }}:</dt>
                                        <dd
                                            class="text-on-surface text-right font-bold"
                                            x-text="selectedCourse?.duration ?? '—'"
                                        >
                                            {{ $selectedCourse?->duration_label }}
                                        </dd>
                                    </div>
                                    <div
                                        class="border-surface-container mt-2 flex items-center justify-between gap-4 border-t pt-2"
                                    >
                                        <dt class="text-on-surface-variant">{{ __('booking.summary.price') }}:</dt>
                                        <dd
                                            class="font-heading text-headline-md text-primary font-bold"
                                            x-text="selectedCourse?.price ?? '—'"
                                        >
                                            {{ $selectedCourse ? Number::currency((float) $selectedCourse->price, 'EUR', 'sk', 0) : '—' }}
                                        </dd>
                                    </div>
                                </dl>
                            </div>
                        </div>

                        <button
                            type="submit"
                            class="btn-primary text-headline-sm mt-6 w-full py-4"
                            data-testid="booking-submit"
                        >
                            {{ __('booking.submit') }}
                            <x-site.icon name="check_circle" class="size-5" />
                        </button>
                        <p class="text-body-sm text-on-surface-variant mt-3 flex items-center justify-center gap-2 text-center">
                            <x-site.icon name="lock" class="size-4" />
                            {{ __('booking.payment_note') }}
                        </p>
                    </div>
                </div>
            </form>
        </div>
    </section>
</x-layouts.public>
