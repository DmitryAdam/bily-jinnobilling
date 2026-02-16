<x-layouts.admin>
    <x-slot name="title">
        {{ trans('general.title.edit', ['type' => trans_choice('general.quotations', 1)]) }}
    </x-slot>

    <x-slot name="content">
        <x-documents.form.content type="quotation" :document="$quotation" />
    </x-slot>

    <x-documents.script type="quotation" :items="$quotation->items()->get()" :document="$quotation" />
</x-layouts.admin>
