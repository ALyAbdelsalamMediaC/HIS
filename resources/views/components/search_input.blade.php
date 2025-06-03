@props([
    'id',
    'name',
    'placeholder' => 'Search',
    'value' => '',
])

<div class="search-input-container">
    <x-svg-icon name="search" size="18" color="#ADADAD" class="search-icon" />
    <input 
        type="text" 
        id="{{ $id }}"
        name="{{ $name }}" 
        placeholder="{{ $placeholder }}" 
        value="{{ $value }}" 
        {{ $attributes->merge(['class' => 'search-input']) }} 
    />
</div>