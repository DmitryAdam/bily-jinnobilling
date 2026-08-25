@php
    $template = setting('quotation.template', 'default');
    $template = $template === 'default' ? 'ddefault' : $template;
@endphp

<x-layouts.print>
    <x-slot name="title">
        {{ trans_choice('general.quotations', 1) . ': ' . $quotation->display_number }}
    </x-slot>

    <x-slot name="content">
        <x-dynamic-component
            :component="'documents.template.' . $template"
            type="quotation"
            :document="$quotation"
        />
    </x-slot>
</x-layouts.print>
