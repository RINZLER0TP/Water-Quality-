@props(['href' => null])

@if($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => 'water-button inline-block text-center px-4 py-3 rounded-xl font-semibold']) }}>
        {{ $slot }}
    </a>
@else
    <button {{ $attributes->merge(['class' => 'water-button inline-block text-center px-4 py-3 rounded-xl font-semibold']) }}>
        {{ $slot }}
    </button>
@endif
