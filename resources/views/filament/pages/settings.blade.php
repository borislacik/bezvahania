<x-filament-panels::page>
    <form wire:submit="save" class="flex flex-col gap-6">
        {{ $this->form }}

        <div>
            <x-filament::button type="submit">{{ __('admin.settings.save') }}</x-filament::button>
        </div>
    </form>
</x-filament-panels::page>
