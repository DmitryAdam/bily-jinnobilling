<div class="flex items-center">
    <span @class([
            'w-3 h-3 rounded-full shrink-0 ltr:mr-1 rtl:ml-1', $backgroundColor, $textColor
        ])
        @if (! empty($backgroundStyle))
        style="background-color: {{ $backgroundStyle }}"
        @endif
    >
    </span>
    <span class="whitespace-nowrap">{{ $name }}</span>
</div>
