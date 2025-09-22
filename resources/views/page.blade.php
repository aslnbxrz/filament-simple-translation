<x-filament-panels::page>
    @isset($this->table)
        {{ $this->table }}
    @endisset
    <x-filament-actions::modals/>
</x-filament-panels::page>