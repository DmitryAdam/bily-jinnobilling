<x-layouts.admin>
    <x-slot name="title">
        {{ trans_choice('general.loans', 1) }} - {{ $loan->contact_name }}
    </x-slot>

    <x-slot name="favorite"
        title="{{ trans_choice('general.loans', 2) }}"
        icon="account_balance_wallet"
        :route="['loans.show', $loan->id]"
    ></x-slot>

    <x-slot name="buttons">
        <x-link href="{{ route('loans.index') }}">
            {{ trans('general.back') }}
        </x-link>
    </x-slot>

    <x-slot name="content">
        {{-- Loan Details --}}
        <div class="flex flex-col lg:flex-row gap-8 mb-8">
            <div class="w-full lg:w-2/3">
                <div class="bg-white rounded-xl border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold mb-4 border-b pb-2">{{ trans('loans.loan_details') }}</h3>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <span class="text-sm text-gray-500">{{ trans('loans.contact_name') }}</span>
                            <p class="font-medium">{{ $loan->contact_name }}</p>
                        </div>

                        <div>
                            <span class="text-sm text-gray-500">{{ trans_choice('general.accounts', 1) }}</span>
                            <p class="font-medium">{{ $loan->account->name }}</p>
                        </div>

                        <div>
                            <span class="text-sm text-gray-500">{{ trans('general.date') }}</span>
                            <p class="font-medium"><x-date date="{{ $loan->issued_at }}" /></p>
                        </div>

                        <div>
                            <span class="text-sm text-gray-500">{{ trans('general.status') }}</span>
                            <p class="font-medium">
                                @if ($loan->status == 'paid')
                                    <span class="px-2.5 py-1 text-xs font-medium rounded-xl bg-green-100 text-green-800">
                                        {{ trans('loans.statuses.paid') }}
                                    </span>
                                @elseif ($loan->status == 'partial')
                                    <span class="px-2.5 py-1 text-xs font-medium rounded-xl bg-yellow-100 text-yellow-800">
                                        {{ trans('loans.statuses.partial') }}
                                    </span>
                                @else
                                    <span class="px-2.5 py-1 text-xs font-medium rounded-xl bg-blue-100 text-blue-800">
                                        {{ trans('loans.statuses.active') }}
                                    </span>
                                @endif
                            </p>
                        </div>

                        @if ($loan->description)
                            <div class="sm:col-span-2">
                                <span class="text-sm text-gray-500">{{ trans('general.description') }}</span>
                                <p class="font-medium">{{ $loan->description }}</p>
                            </div>
                        @endif

                        @if ($loan->reference)
                            <div>
                                <span class="text-sm text-gray-500">{{ trans('general.reference') }}</span>
                                <p class="font-medium">{{ $loan->reference }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="w-full lg:w-1/3">
                <div class="bg-white rounded-xl border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold mb-4 border-b pb-2">{{ trans('loans.summary') }}</h3>

                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-gray-500">{{ trans('loans.total_amount') }}</span>
                            <span class="font-semibold"><x-money :amount="$loan->amount" :currency="$loan->currency_code" /></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">{{ trans('loans.paid') }}</span>
                            <span class="font-semibold text-green-600"><x-money :amount="$loan->paid_amount" :currency="$loan->currency_code" /></span>
                        </div>
                        <div class="flex justify-between border-t pt-3">
                            <span class="text-gray-500 font-semibold">{{ trans('loans.remaining') }}</span>
                            <span class="font-bold text-lg"><x-money :amount="$loan->remaining_amount" :currency="$loan->currency_code" /></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Payment History --}}
        <div class="bg-white rounded-xl border border-gray-200 p-6 mb-8">
            <h3 class="text-lg font-semibold mb-4 border-b pb-2">{{ trans('loans.payment_history') }}</h3>

            @if ($loan->payments->count())
                <x-table>
                    <x-table.thead>
                        <x-table.tr>
                            <x-table.th class="w-2/12">{{ trans('general.date') }}</x-table.th>
                            <x-table.th class="w-3/12">{{ trans_choice('general.accounts', 1) }}</x-table.th>
                            <x-table.th class="w-2/12" kind="amount">{{ trans('general.amount') }}</x-table.th>
                            <x-table.th class="w-2/12" hidden-mobile>{{ trans('general.payment_methods') }}</x-table.th>
                            <x-table.th class="w-2/12" hidden-mobile>{{ trans('general.description') }}</x-table.th>
                            <x-table.th class="w-1/12">{{ trans('general.actions') }}</x-table.th>
                        </x-table.tr>
                    </x-table.thead>

                    <x-table.tbody>
                        @foreach($loan->payments as $payment)
                            <x-table.tr>
                                <x-table.td class="w-2/12">
                                    <x-date date="{{ $payment->paid_at }}" />
                                </x-table.td>

                                <x-table.td class="w-3/12">
                                    {{ $payment->account->name }}
                                </x-table.td>

                                <x-table.td class="w-2/12" kind="amount">
                                    <x-money :amount="$payment->amount" :currency="$payment->currency_code" />
                                </x-table.td>

                                <x-table.td class="w-2/12" hidden-mobile>
                                    {{ $payment->payment_method }}
                                </x-table.td>

                                <x-table.td class="w-2/12" hidden-mobile>
                                    {{ $payment->description ?? '-' }}
                                </x-table.td>

                                <x-table.td class="w-1/12">
                                    @can('delete-banking-loans')
                                        <x-delete-link :model="$payment" :route="['loans.payments.destroy', $loan->id, $payment->id]" />
                                    @endcan
                                </x-table.td>
                            </x-table.tr>
                        @endforeach
                    </x-table.tbody>
                </x-table>
            @else
                <p class="text-gray-500 text-sm">{{ trans('loans.no_payments') }}</p>
            @endif
        </div>

        {{-- Add Payment Form --}}
        @if ($loan->status != 'paid')
            @can('create-banking-loans')
                <div class="bg-white rounded-xl border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold mb-4 border-b pb-2">{{ trans('loans.add_payment') }}</h3>

                    <x-form id="loan-payment" :route="['loans.payments.store', $loan->id]">
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                            <x-form.group.select name="account_id" label="{{ trans_choice('general.accounts', 1) }}" :options="$accounts" :selected="$loan->account_id" />

                            <x-form.group.money name="amount" label="{{ trans('general.amount') }}" value="0" :currency="$currency" dynamicCurrency="currency" />

                            <x-form.group.date name="paid_at" label="{{ trans('general.date') }}" icon="calendar_today" value="{{ Date::now()->toDateString() }}" show-date-format="{{ company_date_format() }}" date-format="Y-m-d" autocomplete="off" />

                            <x-form.group.payment-method />

                            <x-form.group.text name="description" label="{{ trans('general.description') }}" not-required />

                            <x-form.group.text name="reference" label="{{ trans('general.reference') }}" not-required />
                        </div>

                        <x-form.input.hidden name="loan_id" :value="$loan->id" />

                        <div class="flex justify-end mt-4">
                            <button type="submit" class="relative flex items-center justify-center bg-green hover:bg-green-700 text-white px-6 py-1.5 text-base rounded-lg disabled:bg-green-100">
                                {{ trans('loans.add_payment') }}
                            </button>
                        </div>
                    </x-form>
                </div>
            @endcan
        @endif
    </x-slot>

    @push('scripts_start')
        <script type="text/javascript">
            if (typeof aka_currency !== 'undefined') {
                aka_currency = {!! json_encode(! empty($currency) ? $currency : config('money.currencies.' . company()->currency)) !!};
            } else {
                var aka_currency = {!! json_encode(! empty($currency) ? $currency : config('money.currencies.' . company()->currency)) !!};
            }
        </script>
    @endpush
</x-layouts.admin>
