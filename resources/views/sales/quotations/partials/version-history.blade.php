<div class="mt-6">
    <h3 class="text-lg font-medium mb-4">{{ trans('quotations.version_history') }}</h3>

    <div class="bg-white rounded-lg border">
        <table class="min-w-full divide-y divide-gray-200">
            <thead>
                <tr class="bg-gray-50">
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ trans('quotations.version') }}</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ trans('quotations.quotation_number') }}</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ trans('general.date') }}</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">{{ trans('general.amount') }}</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ trans('quotations.revision.notes') }}</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ trans('general.status') }}</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider"></th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach($versions as $version)
                    <tr class="{{ $version->id === $current->id ? 'bg-blue-50' : '' }}">
                        <td class="px-4 py-3 whitespace-nowrap text-sm">
                            v{{ $version->version }}
                            @if($version->id === $current->id)
                                <span class="ml-1 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">
                                    {{ trans('quotations.current_version') }}
                                </span>
                            @endif
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                            {{ $version->document_number }}-v{{ $version->version }}
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">
                            {{ \App\Utilities\Date::parse($version->issued_at)->format(company_date_format()) }}
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-right text-gray-900">
                            @money($version->amount, $version->currency_code, true)
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-500 max-w-xs">
                            @if($version->revision_notes)
                                <span class="line-clamp-2" title="{{ $version->revision_notes }}">{{ $version->revision_notes }}</span>
                            @else
                                <span class="text-gray-300">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-{{ $version->status_label }} text-text-{{ $version->status_label }}">
                                {{ trans('quotations.statuses.' . $version->status) }}
                            </span>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-right text-sm">
                            @if($version->id !== $current->id)
                                <a href="{{ route('quotations.show', $version->id) }}" class="text-blue-600 hover:text-blue-900">
                                    {{ trans('general.show') }}
                                </a>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Amount change indicator --}}
    @if($versions->count() > 1)
        <div class="mt-3 flex items-center gap-x-4 text-sm text-gray-600">
            @php
                $firstVersion = $versions->first();
                $latestVersion = $versions->last();
                $diff = $latestVersion->amount - $firstVersion->amount;
                $percentChange = $firstVersion->amount > 0 ? round(($diff / $firstVersion->amount) * 100, 1) : 0;
            @endphp

            <span>
                v1 &rarr; v{{ $latestVersion->version }}:
                @if($diff > 0)
                    <span class="text-red-600">+@money($diff, $latestVersion->currency_code, true) (+{{ $percentChange }}%)</span>
                @elseif($diff < 0)
                    <span class="text-green-600">@money($diff, $latestVersion->currency_code, true) ({{ $percentChange }}%)</span>
                @else
                    <span class="text-gray-500">{{ trans('general.na') }}</span>
                @endif
            </span>
        </div>
    @endif
</div>
