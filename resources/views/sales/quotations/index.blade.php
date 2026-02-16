<x-layouts.admin>
    <x-slot name="title">
        {{ trans_choice('general.quotations', 2) }}
    </x-slot>

    <x-slot name="favorite"
        title="{{ trans_choice('general.quotations', 2) }}"
        icon="request_quote"
        route="quotations.index"
    ></x-slot>

    <x-slot name="buttons">
        <x-documents.index.buttons type="quotation" />
    </x-slot>

    <x-slot name="moreButtons">
        <x-documents.index.more-buttons type="quotation" hide-import />
    </x-slot>

    <x-slot name="content">
        <x-documents.index.content type="quotation" :documents="$quotations" :total-documents="$total_quotations" hide-import />
    </x-slot>

    <x-documents.script type="quotation" />
</x-layouts.admin>
