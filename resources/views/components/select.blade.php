@props([
    'id' => null,
    'name' => null,
    'options' => [], // Can now accept a simple array or associative array
    'placeholder' => 'Select an option', // Default placeholder text
    'selected' => null, // Selected value
])

<select id="{{ $id }}" name="{{ $name }}" {{ $attributes->merge(['class' => 'select-form-inner']) }}>
  <option value="">{{ $placeholder }}</option>
  @foreach($options as $value => $label)
    <option value="{{ $value }}" @selected($value == old($name, $selected))>{{ $label }}</option>
  @endforeach
</select>