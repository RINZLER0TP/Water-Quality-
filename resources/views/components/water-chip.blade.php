@props(['value' => '', 'label' => ''])

<div {{ $attributes->merge(['class' => 'chip']) }}>
    <div class="chip-v">{{ $value }}</div>
    <div class="chip-l">{{ $label }}</div>
</div>
