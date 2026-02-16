<x-layouts.admin>
    <x-slot name="title">
        {{ setting('quotation.title', trans_choice('general.quotations', 1)) . ': ' . $displayNumber }}
    </x-slot>

    <x-slot name="status">
        <x-show.status status="{{ $quotation->status }}" background-color="bg-{{ $quotation->status_label }}" text-color="text-text-{{ $quotation->status_label }}" />
    </x-slot>

    <x-slot name="buttons">
        <x-documents.show.buttons type="quotation" :document="$quotation" />
    </x-slot>

    <x-slot name="moreButtons">
        <x-documents.show.more-buttons type="quotation" :document="$quotation" />
    </x-slot>

    <x-slot name="content">
        <x-documents.show.content type="quotation" :document="$quotation" hide-receive hide-make-payment hide-get-paid hide-schedule hide-children />

        {{-- Revision Notes --}}
        @if($quotation->revision_notes)
            <div class="mt-4 p-4 bg-amber-50 border border-amber-200 rounded-lg">
                <div class="flex items-start gap-x-2">
                    <span class="material-icons text-amber-500 text-lg mt-0.5">sticky_note_2</span>
                    <div>
                        <h4 class="text-sm font-medium text-amber-800">{{ trans('quotations.revision.notes') }} (v{{ $quotation->version }})</h4>
                        <p class="text-sm text-amber-700 mt-1">{{ $quotation->revision_notes }}</p>
                    </div>
                </div>
            </div>
        @endif

        {{-- Version History --}}
        @if($versions->count() > 1)
            @include('sales.quotations.partials.version-history', ['versions' => $versions, 'current' => $quotation])
        @endif

        {{-- Quotation-specific action buttons --}}
        <div class="flex items-center justify-end mt-6 gap-x-3">
            @if(in_array($quotation->status, ['draft', 'sent', 'viewed']))
                <a href="{{ route('quotations.revise', $quotation->id) }}" class="px-6 py-1.5 bg-blue-500 hover:bg-blue-700 text-white rounded-lg text-sm font-medium">
                    {{ trans('quotations.version') }} {{ $quotation->version + 1 }} - {{ trans('general.title.new', ['type' => trans('quotations.version')]) }}
                </a>
            @endif

            @if($quotation->status === 'accepted')
                <a href="{{ route('quotations.convert-to-invoice', $quotation->id) }}" class="px-6 py-1.5 bg-green-500 hover:bg-green-700 text-white rounded-lg text-sm font-medium">
                    {{ trans('quotations.messages.converted_to_invoice') }}
                </a>
            @endif
        </div>
    </x-slot>

    @push('stylesheet')
        <link rel="stylesheet" href="{{ asset('css/print.css?v=' . version('short')) }}" type="text/css">
    @endpush

    <x-documents.script type="quotation" :document="$quotation" />
</x-layouts.admin>
