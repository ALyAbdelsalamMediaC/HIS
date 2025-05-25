<!-- resources/views/components/text-area.blade.php -->

@props([
  'name',
  'placeholder' => '',
  'value' => '',
  'rows' => 4,
  // Remove 'cols' if you're handling width via CSS
])

<textarea name="{{ $name }}" placeholder="{{ $placeholder }}" {{ $attributes->merge(['class' => 'input-form-inner fixed-textarea']) }} rows="{{ $rows }}">{{ $value }}</textarea>