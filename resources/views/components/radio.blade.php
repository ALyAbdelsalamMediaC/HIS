<!-- resources/views/components/radio.blade.php -->
@props([
    'id' => null,                // Optional: Base ID for the radio group
    'name',                      // Name attribute for the radio group
    'options' => [],             // Array of options [value => label]
    'selected' => null,          // Selected value
    'dataAttributes' => [],      // Associative array [value => [attribute => value, ...]]
])

@php
    // Generate a unique ID for the group if not provided
    $groupId = $id ?? \Illuminate\Support\Str::slug($name);
@endphp

<div {{ $id ? 'id=' . $groupId : '' }} class="radio-group">
    @foreach($options as $value => $label)
        @php
            // Generate a unique ID for each radio button
            $radioId = "{$groupId}_{$value}";

            // Retrieve data attributes for this option, if any
            $currentDataAttributes = $dataAttributes[$value] ?? [];
        @endphp

        <div class="radio-wrapper">
            <input type="radio" id="{{ $radioId }}" name="{{ $name }}" value="{{ $value }}" @checked($selected == $value)
                class="radio-form" data-toggle="toggle-section" @if(isset($currentDataAttributes['data-target']))
                data-target="{{ $currentDataAttributes['data-target'] }}" @endif>
            <label for="{{ $radioId }}" class="radio-label">{{ $label }}</label>
        </div>
    @endforeach
</div>