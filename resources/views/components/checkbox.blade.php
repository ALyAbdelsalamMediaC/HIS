<!-- resources/views/components/checkbox.blade.php -->
@props([
  'id' => null,                // Optional: Base ID for the checkbox group
  'name',                      // Name attribute for the checkbox group
  'options' => [],             // Array of options [value => label]
  'selected' => [],            // Array of selected values
  'dataAttributes' => [],      // Associative array [value => [attribute => value, ...]]
])

<div {{ $id ? 'id=' . $id : '' }} class="checkbox-group">
  @foreach($options as $value => $label)
  @php
    // Generate a unique ID for each checkbox
    $checkboxId = $id ? "{$id}_{$value}" : "{$name}_{$value}";

    // Retrieve data attributes for this option, if any
    $currentDataAttributes = $dataAttributes[$value] ?? [];
  @endphp

  <div class="checkbox-wrapper">
    <input type="checkbox" id="{{ $checkboxId }}" name="{{ $name }}[]" value="{{ $value }}" @if(is_array($selected) && in_array($value, $selected)) checked @endif class="checkbox-form" data-toggle="toggle-section"
      @if(isset($currentDataAttributes['data-target'])) data-target="{{ $currentDataAttributes['data-target'] }}"
    @endif>
    <label for="{{ $checkboxId }}" class="checkbox-label">{{ $label }}</label>
  </div>
  @endforeach
</div>