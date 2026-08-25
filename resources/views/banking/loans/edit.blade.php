<x-layouts.admin>
    <x-slot name="title">
        {{ trans('general.title.edit', ['type' => trans_choice('general.' . $slug, 1)]) }}
    </x-slot>

    <x-slot name="favorite"
        title="{{ trans('general.title.edit', ['type' => trans_choice('general.' . $slug, 1)]) }}"
        icon="{{ $icon }}"
        :route="[$slug . '.edit', $loan->id]"
    ></x-slot>

    <x-slot name="content">
        <x-form.container>
            <x-form id="{{ $type }}" :route="[$slug . '.update', $loan->id]" :model="$loan" method="PATCH">
                <x-form.section>
                    <x-slot name="head">
                        <x-form.section.head title="{{ trans('general.general') }}" description="{{ trans($lang . '.form_description.edit') }}" />
                    </x-slot>

                    <x-slot name="body">
                        <x-form.group.text name="contact_name" label="{{ trans($lang . '.contact_name') }}" value="{{ $loan->contact_name }}" />

                        <x-form.group.select name="account_id" label="{{ trans_choice('general.accounts', 1) }}" :options="$accounts" :selected="$loan->account_id" disabled />

                        <x-form.group.date name="issued_at" label="{{ trans('general.date') }}" icon="calendar_today" value="{{ $loan->issued_at->format('Y-m-d') }}" show-date-format="{{ company_date_format() }}" date-format="Y-m-d" autocomplete="off" />

                        <x-form.group.money name="amount" label="{{ trans('general.amount') }}" :value="$loan->amount" :currency="$currency" dynamicCurrency="currency" disabled />

                        <x-form.group.textarea name="description" label="{{ trans('general.description') }}" :value="$loan->description" not-required />
                    </x-slot>
                </x-form.section>

                <x-form.section>
                    <x-slot name="foot">
                        <x-form.buttons cancel-route="{{ $slug }}.index" />
                    </x-slot>
                </x-form.section>
            </x-form>
        </x-form.container>
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

    <x-script folder="banking" file="loans" />
</x-layouts.admin>
