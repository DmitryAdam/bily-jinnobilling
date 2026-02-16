<x-layouts.admin>
    <x-slot name="title">
        {{ trans('general.title.new', ['type' => setting('quotation.title', trans_choice('general.quotations', 1))]) }}
    </x-slot>

    <x-slot name="favorite"
        title="{{ trans('general.title.new', ['type' => setting('quotation.title', trans_choice('general.quotations', 1))]) }}"
        icon="request_quote"
        route="quotations.create"
    ></x-slot>

    <x-slot name="content">
        @push('document_number_input_end')
            <div class="mt-1">
                <button
                    type="button"
                    id="btn-generate-number"
                    class="inline-flex items-center text-xs text-purple-600 hover:text-purple-800 font-medium"
                    onclick="generateQuotationNumber()"
                >
                    <span class="material-icons text-sm mr-1">autorenew</span>
                    {{ trans('quotations.auto_generate') }}
                </button>
            </div>
        @endpush

        <x-documents.form.content type="quotation" />
    </x-slot>

    <x-documents.script type="quotation" />

    @push('scripts_end')
        <script>
            function generateQuotationNumber() {
                var btn = document.getElementById('btn-generate-number');
                btn.disabled = true;

                fetch('{{ route("quotations.generate-number") }}')
                    .then(function(response) { return response.json(); })
                    .then(function(data) {
                        if (data.number) {
                            var input = document.querySelector('input[name="document_number"]');
                            if (input) {
                                input.value = data.number;
                                input.dispatchEvent(new Event('input', { bubbles: true }));
                            }
                        }
                        btn.disabled = false;
                    })
                    .catch(function() {
                        btn.disabled = false;
                    });
            }
        </script>
    @endpush
</x-layouts.admin>
