<x-layouts.admin>
    <x-slot name="title">
        {{ $loan->loan_number }} - {{ $loan->contact_name }}
    </x-slot>

    <x-slot name="favorite"
        title="{{ trans_choice('general.loans', 2) }}"
        icon="account_balance_wallet"
        :route="['loans.show', $loan->id]"
    ></x-slot>

    <x-slot name="buttons">
        <x-link href="{{ route('loans.index') }}">
            {{ trans('general.go_back', ['type' => trans_choice('general.loans', 2)]) }}
        </x-link>
    </x-slot>

    <x-slot name="content">
        {{-- Loan Details + Summary --}}
        <div class="flex flex-col lg:flex-row gap-8 mb-8">
            <div class="w-full lg:w-2/3">
                <div class="bg-white rounded-xl border border-gray-200 p-6">
                    <div class="flex items-center justify-between mb-4 border-b pb-2">
                        <h3 class="text-lg font-semibold">{{ trans('loans.loan_details') }}</h3>
                        @if ($loan->status == 'paid')
                            <span class="px-3 py-1 text-sm font-medium rounded-xl bg-green-100 text-green-800">
                                {{ trans('loans.statuses.paid') }}
                            </span>
                        @elseif ($loan->status == 'partial')
                            <span class="px-3 py-1 text-sm font-medium rounded-xl bg-yellow-100 text-yellow-800">
                                {{ trans('loans.statuses.partial') }}
                            </span>
                        @else
                            <span class="px-3 py-1 text-sm font-medium rounded-xl bg-blue-100 text-blue-800">
                                {{ trans('loans.statuses.active') }}
                            </span>
                        @endif
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <span class="text-sm text-gray-500">{{ trans('loans.loan_number') }}</span>
                            <p class="font-semibold text-purple text-lg">{{ $loan->loan_number }}</p>
                        </div>

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

            <div class="w-full lg:w-1/3 flex flex-col gap-4">
                {{-- Summary --}}
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

                {{-- Add Payment Button --}}
                @if ($loan->status != 'paid')
                    @can('create-banking-loans')
                        <div class="bg-white rounded-xl border border-gray-200 p-6">
                            <button type="button" class="w-full flex items-center justify-center bg-green hover:bg-green-700 text-white px-6 py-2.5 text-base rounded-lg" @click="payment_modal = true">
                                <span class="material-icons text-base mr-2">add</span>
                                {{ trans('loans.add_payment') }}
                            </button>
                        </div>
                    @endcan
                @endif
            </div>
        </div>

        {{-- Payment History --}}
        <div class="bg-white rounded-xl border border-gray-200 p-6 mb-8">
            <h3 class="text-lg font-semibold mb-4 border-b pb-2">{{ trans('loans.payment_history') }}</h3>

            @if ($loan->payments->count())
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="text-xs text-gray-500 uppercase border-b">
                            <tr>
                                <th class="py-3 pr-4">{{ trans('general.date') }}</th>
                                <th class="py-3 pr-4">{{ trans_choice('general.accounts', 1) }}</th>
                                <th class="py-3 pr-4 text-right">{{ trans('general.amount') }}</th>
                                <th class="py-3 pr-4 hidden sm:table-cell">{{ trans_choice('general.payment_methods', 1) }}</th>
                                <th class="py-3 pr-4 hidden sm:table-cell">{{ trans('general.description') }}</th>
                                <th class="py-3 text-center">{{ trans('general.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($loan->payments as $payment)
                                <tr class="border-b hover:bg-gray-50">
                                    <td class="py-3 pr-4 whitespace-nowrap">
                                        <x-date date="{{ $payment->paid_at }}" />
                                    </td>
                                    <td class="py-3 pr-4">
                                        {{ $payment->account->name }}
                                    </td>
                                    <td class="py-3 pr-4 text-right font-medium">
                                        <x-money :amount="$payment->amount" :currency="$payment->currency_code" />
                                    </td>
                                    <td class="py-3 pr-4 hidden sm:table-cell">
                                        {{ $payment->payment_method_name }}
                                    </td>
                                    <td class="py-3 pr-4 hidden sm:table-cell">
                                        {{ $payment->description ?? '-' }}
                                    </td>
                                    <td class="py-3 text-center">
                                        @can('delete-banking-loans')
                                            <x-delete-link :model="$payment" :action="route('loans.payments.destroy', [$loan->id, $payment->id])" model-title="{{ trans('loans.payment') }}" />
                                        @endcan
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-gray-500 text-sm">{{ trans('loans.no_payments') }}</p>
            @endif
        </div>

    </x-slot>

    @if ($loan->status != 'paid')
        @can('create-banking-loans')
            @push('content_content_end')
                <akaunting-modal
                    modal-dialog-class="max-w-screen-md"
                    :show="payment_modal"
                    @cancel="payment_modal = false"
                    :title="'{{ trans('loans.add_payment') }}'">
                    <template #modal-body>
                        <x-form id="loan-payment" action="{{ route('loans.payments.store', $loan->id) }}">
                            <div class="p-4">
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-3">
                                    <x-form.group.select name="account_id" label="{{ trans_choice('general.accounts', 1) }}" :options="$accounts" placeholder="{{ trans('general.form.select.field', ['field' => trans_choice('general.accounts', 1)]) }}" />

                                    <x-form.group.money name="amount" label="{{ trans('general.amount') }}" value="0" :currency="$currency" dynamicCurrency="currency" />

                                    <x-form.group.date name="paid_at" label="{{ trans('general.date') }}" icon="calendar_today" value="{{ Date::now()->toDateString() }}" show-date-format="{{ company_date_format() }}" date-format="Y-m-d" autocomplete="off" />

                                    <x-form.group.payment-method />

                                    <x-form.group.text name="description" label="{{ trans('general.description') }}" not-required />

                                    <x-form.group.text name="reference" label="{{ trans('general.reference') }}" not-required />
                                </div>
                            </div>

                            <x-form.input.hidden name="loan_id" :value="$loan->id" />

                            <div class="flex items-center justify-end p-4 border-t">
                                <button type="button" class="px-6 py-1.5 hover:bg-gray-200 rounded-lg ltr:mr-2 rtl:ml-2" @click="payment_modal = false">
                                    {{ trans('general.cancel') }}
                                </button>

                                <button type="submit" class="relative flex items-center justify-center bg-green hover:bg-green-700 text-white px-6 py-1.5 text-base rounded-lg disabled:bg-green-100">
                                    <x-button.loading>
                                        {{ trans('loans.add_payment') }}
                                    </x-button.loading>
                                </button>
                            </div>
                        </x-form>
                    </template>

                    <template #card-footer>
                        <span></span>
                    </template>
                </akaunting-modal>
            @endpush
        @endcan
    @endif

    @push('scripts_start')
        <script type="text/javascript">
            if (typeof aka_currency !== 'undefined') {
                aka_currency = {!! json_encode(! empty($currency) ? $currency : config('money.currencies.' . company()->currency)) !!};
            } else {
                var aka_currency = {!! json_encode(! empty($currency) ? $currency : config('money.currencies.' . company()->currency)) !!};
            }
        </script>
    @endpush

    <x-script folder="banking" file="loans" />
</x-layouts.admin>
