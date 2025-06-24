<!-- resources/views/components/text_input.blade.php -->
@props([ 
    'type' => 'text',
    'name',
    'placeholder' => null,
    'value' => '',
])

<input 
    type="{{ $type }}" 
    name="{{ $name }}" 
    autocomplete="off"
    @if($placeholder) placeholder="{{ $placeholder }}" @endif
    value="{{ $value }}" 
    {{ $attributes->merge(['class' => 'input-form-inner']) }} 
/>
