<x-layouts.admin>
    <x-slot name="title">
        {{ trans_choice('general.loans', 2) }}
    </x-slot>

    <x-slot name="favorite"
        title="{{ trans_choice('general.loans', 2) }}"
        icon="account_balance_wallet"
        route="loans.index"
    ></x-slot>

    <x-slot name="buttons">
        @can('create-banking-loans')
            <x-link href="{{ route('loans.create') }}" kind="primary">
                {{ trans('general.title.new', ['type' => trans_choice('general.loans', 1)]) }}
            </x-link>
        @endcan
    </x-slot>

    <x-slot name="content">
        @if ($loans->count() || request()->get('search', false))
            <x-index.container>
                <x-index.search
                    search-string="App\Models\Banking\Loan"
                    bulk-action="App\BulkActions\Banking\Loans"
                />

                <div class="overflow-x-auto">
                    <x-table class="min-w-[900px]">
                        <x-table.thead>
                            <x-table.tr>
                                <x-table.th kind="bulkaction">
                                    <x-index.bulkaction.all />
                                </x-table.th>

                                <x-table.th class="w-1/12">
                                    <x-sortablelink column="loan_number" title="{{ trans('loans.loan_number') }}" />
                                </x-table.th>

                                <x-table.th class="w-2/12">
                                    <x-sortablelink column="issued_at" title="{{ trans('general.date') }}" />
                                </x-table.th>

                                <x-table.th class="w-2/12">
                                    <x-sortablelink column="contact_name" title="{{ trans('loans.contact_name') }}" />
                                </x-table.th>

                                <x-table.th class="w-2/12">
                                    {{ trans_choice('general.accounts', 1) }}
                                </x-table.th>

                                <x-table.th class="w-2/12" kind="amount">
                                    <x-sortablelink column="amount" title="{{ trans('general.amount') }}" />
                                </x-table.th>

                                <x-table.th class="w-1/12" kind="amount">
                                    {{ trans('loans.paid') }}
                                </x-table.th>

                                <x-table.th class="w-1/12" kind="amount">
                                    {{ trans('loans.remaining') }}
                                </x-table.th>

                                <x-table.th class="w-1/12">
                                    <x-sortablelink column="status" title="{{ trans_choice('general.statuses', 1) }}" />
                                </x-table.th>
                            </x-table.tr>
                        </x-table.thead>

                        <x-table.tbody>
                            @foreach($loans as $item)
                                <x-table.tr href="{{ route('loans.show', $item->id) }}">
                                    <x-table.td kind="bulkaction">
                                        <x-index.bulkaction.single id="{{ $item->id }}" name="{{ $item->contact_name }}" />
                                    </x-table.td>

                                    <x-table.td class="w-1/12">
                                        <a href="{{ route('loans.show', $item->id) }}" class="text-purple font-medium">{{ $item->loan_number }}</a>
                                    </x-table.td>

                                    <x-table.td class="w-2/12">
                                        <x-date date="{{ $item->issued_at }}" />
                                    </x-table.td>

                                    <x-table.td class="w-2/12">
                                        {{ $item->contact_name }}
                                    </x-table.td>

                                    <x-table.td class="w-2/12">
                                        {{ $item->account->name }}
                                    </x-table.td>

                                    <x-table.td class="w-2/12" kind="amount">
                                        <x-money :amount="$item->amount" :currency="$item->currency_code" />
                                    </x-table.td>

                                    <x-table.td class="w-1/12" kind="amount">
                                        <x-money :amount="$item->paid_amount" :currency="$item->currency_code" />
                                    </x-table.td>

                                    <x-table.td class="w-1/12" kind="amount">
                                        <x-money :amount="$item->remaining_amount" :currency="$item->currency_code" />
                                    </x-table.td>

                                    <x-table.td class="w-1/12">
                                        @if ($item->status == 'paid')
                                            <span class="px-2.5 py-1 text-xs font-medium rounded-xl bg-green-100 text-green-800">
                                                {{ trans('loans.statuses.paid') }}
                                            </span>
                                        @elseif ($item->status == 'partial')
                                            <span class="px-2.5 py-1 text-xs font-medium rounded-xl bg-yellow-100 text-yellow-800">
                                                {{ trans('loans.statuses.partial') }}
                                            </span>
                                        @else
                                            <span class="px-2.5 py-1 text-xs font-medium rounded-xl bg-blue-100 text-blue-800">
                                                {{ trans('loans.statuses.active') }}
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

                <x-pagination :items="$loans" />
            </x-index.container>
        @else
            <x-empty-page group="banking" page="loans" />
        @endif
    </x-slot>

    <x-script folder="banking" file="loans" />
</x-layouts.admin>
