<x-layouts.admin>
    <x-slot name="title">
        {{ trans('quotations.revision.title') . ': ' . $displayNumber }}
    </x-slot>

    <x-slot name="content">
        <div class="max-w-2xl">
            <div class="bg-white rounded-xl border p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-2">
                    {{ trans('quotations.revision.create_new') }}
                </h3>

                <p class="text-sm text-gray-500 mb-6">
                    {{ trans('quotations.revision.description', ['number' => $displayNumber, 'version' => $quotation->version + 1]) }}
                </p>

                <form method="POST" action="{{ route('quotations.revise.store', $quotation->id) }}">
                    @csrf

                    <div class="mb-6">
                        <label for="revision_notes" class="block text-sm font-medium text-gray-700 mb-2">
                            {{ trans('quotations.revision.notes') }}
                        </label>
                        <textarea
                            id="revision_notes"
                            name="revision_notes"
                            rows="4"
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500 text-sm"
                            placeholder="{{ trans('quotations.revision.notes_placeholder') }}"
                        ></textarea>
                        <p class="mt-1 text-xs text-gray-400">
                            {{ trans('quotations.revision.notes_hint') }}
                        </p>
                    </div>

                    <div class="flex items-center justify-end gap-x-3">
                        <a href="{{ route('quotations.show', $quotation->id) }}" class="px-6 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50">
                            {{ trans('general.cancel') }}
                        </a>

                        <button type="submit" class="px-6 py-2 bg-blue-500 hover:bg-blue-700 text-white rounded-lg text-sm font-medium">
                            {{ trans('quotations.revision.create_version', ['version' => $quotation->version + 1]) }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </x-slot>
</x-layouts.admin>
