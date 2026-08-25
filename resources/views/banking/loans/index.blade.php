<x-layouts.admin>
    <x-slot name="title">
        {{ trans_choice('general.' . $slug, 2) }}
    </x-slot>

    <x-slot name="favorite"
        title="{{ trans_choice('general.' . $slug, 2) }}"
        icon="{{ $icon }}"
        route="{{ $slug }}.index"
    ></x-slot>

    <x-slot name="buttons">
        @can('create-banking-' . $slug)
            <x-link href="{{ route($slug . '.create') }}" kind="primary">
                {{ trans('general.title.new', ['type' => trans_choice('general.' . $slug, 1)]) }}
            </x-link>
        @endcan
    </x-slot>

    <x-slot name="content">
        {{-- Summary Widget --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">{{ trans($lang . '.total_lent') }}</p>
                        <p class="text-xl font-bold text-gray-800 mt-1">
                            <x-money :amount="$total" :currency="$currency" />
                        </p>
                    </div>
                    <div class="w-10 h-10 flex items-center justify-center rounded-full bg-blue-100">
                        <span class="material-icons text-blue-600 text-xl">{{ $icon }}</span>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">{{ trans($lang . '.total_repaid') }}</p>
                        <p class="text-xl font-bold text-green-600 mt-1">
                            <x-money :amount="$totalPaid" :currency="$currency" />
                        </p>
                    </div>
                    <div class="w-10 h-10 flex items-center justify-center rounded-full bg-green-100">
                        <span class="material-icons text-green-600 text-xl">check_circle</span>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">{{ trans($lang . '.total_outstanding') }}</p>
                        <p class="text-xl font-bold text-red-600 mt-1">
                            <x-money :amount="$totalUnpaid" :currency="$currency" />
                        </p>
                    </div>
                    <div class="w-10 h-10 flex items-center justify-center rounded-full bg-red-100">
                        <span class="material-icons text-red-600 text-xl">pending</span>
                    </div>
                </div>
            </div>
        </div>

        @if ($items->count() || request()->get('search', false))
            <x-index.container>
                <x-index.search
                    search-string="App\Models\Banking\Loan"
                    :bulk-action="$bulkAction"
                />

                <div class="overflow-x-auto">
                    <x-table class="min-w-[900px]">
                        <x-table.thead>
                            <x-table.tr>
                                <x-table.th kind="bulkaction">
                                    <x-index.bulkaction.all />
                                </x-table.th>

                                <x-table.th class="w-2/12">
                                    <x-sortablelink column="loan_number" title="{{ trans($lang . '.number') }}" />
                                </x-table.th>

                                <x-table.th class="w-1/12">
                                    <x-sortablelink column="issued_at" title="{{ trans('general.date') }}" />
                                </x-table.th>

                                <x-table.th class="w-2/12">
                                    <x-sortablelink column="contact_name" title="{{ trans($lang . '.contact_name') }}" />
                                </x-table.th>

                                <x-table.th class="w-2/12">
                                    {{ trans_choice('general.accounts', 1) }}
                                </x-table.th>

                                <x-table.th class="w-2/12" kind="amount">
                                    <x-sortablelink column="amount" title="{{ trans('general.amount') }}" />
                                </x-table.th>

                                <x-table.th class="w-2/12" kind="amount">
                                    {{ trans($lang . '.paid') }}
                                </x-table.th>

                                <x-table.th class="w-2/12" kind="amount">
                                    {{ trans($lang . '.remaining') }}
                                </x-table.th>
                            </x-table.tr>
                        </x-table.thead>

                        <x-table.tbody>
                            @foreach($items as $item)
                                <x-table.tr href="{{ route($slug . '.show', $item->id) }}">
                                    <x-table.td kind="bulkaction">
                                        <x-index.bulkaction.single id="{{ $item->id }}" name="{{ $item->contact_name }}" />
                                    </x-table.td>

                                    <x-table.td class="w-2/12">
                                        <a href="{{ route($slug . '.show', $item->id) }}" class="text-purple font-medium">{{ $item->loan_number }}</a>
                                    </x-table.td>

                                    <x-table.td class="w-1/12">
                                        <x-date date="{{ $item->issued_at }}" />
                                    </x-table.td>

                                    <x-table.td class="w-2/12">
                                        {{ $item->contact_name }}
                                    </x-table.td>

                                    <x-table.td class="w-2/12">
                                        {{ $item->account->name }}
                                    </x-table.td>

                                    <x-table.td class="w-2/12 whitespace-nowrap" kind="amount">
                                        <x-money :amount="$item->amount" :currency="$item->currency_code" />
                                    </x-table.td>

                                    <x-table.td class="w-2/12 whitespace-nowrap" kind="amount">
                                        @if ($item->status == 'paid')
                                            <span class="text-green-600 font-medium">
                                                <x-money :amount="$item->paid_amount" :currency="$item->currency_code" />
                                            </span>
                                        @elseif ($item->paid_amount > 0)
                                            <span class="text-yellow-600 font-medium">
                                                <x-money :amount="$item->paid_amount" :currency="$item->currency_code" />
                                            </span>
                                        @else
                                            <span class="text-red-600 font-medium">
                                                <x-money :amount="$item->paid_amount" :currency="$item->currency_code" />
                                            </span>
                                        @endif
                                    </x-table.td>

                                    <x-table.td class="w-2/12 whitespace-nowrap" kind="amount">
                                        @if ($item->status == 'paid')
                                            <span class="text-green-600 font-medium">
                                                <x-money :amount="$item->remaining_amount" :currency="$item->currency_code" />
                                            </span>
                                        @elseif ($item->paid_amount > 0)
                                            <span class="text-yellow-600 font-medium">
                                                <x-money :amount="$item->remaining_amount" :currency="$item->currency_code" />
                                            </span>
                                        @else
                                            <span class="text-red-600 font-medium">
                                                <x-money :amount="$item->remaining_amount" :currency="$item->currency_code" />
                                            </span>
                                        @endif
                                    </x-table.td>

                                    <x-table.td kind="action">
                                        <x-table.actions :model="$item" />
                                    </x-table.td>
                                </x-table.tr>
                            @endforeach
                        </x-table.tbody>
                    </x-table>
                </div>

                <x-pagination :items="$items" />
            </x-index.container>
        @else
            <x-empty-page group="banking" page="{{ $slug }}" />
        @endif
    </x-slot>

    <x-script folder="banking" file="loans" />
</x-layouts.admin>
