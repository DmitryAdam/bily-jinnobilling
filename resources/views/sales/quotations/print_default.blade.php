<x-layouts.print>
    <x-slot name="title">
        {{ trans_choice('general.quotations', 1) . ': ' . $quotation->document_number . '-v' . $quotation->version }}
    </x-slot>

    <x-slot name="content">
        <x-documents.template.ddefault
            type="quotation"
            :document="$quotation"
        />
    </x-slot>
</x-layouts.print>
