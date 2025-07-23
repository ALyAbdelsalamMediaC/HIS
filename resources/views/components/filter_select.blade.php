@props([
    'id' => null,
    'name' => null,
    'options' => [], // Can accept a simple array or associative array
    'placeholder' => 'Select an option', // Default placeholder text
    'selected' => null, // Selected value
    'multiple' => false, // Support for multiple selections
])

<select 
    id="{{ $id }}" 
    name="{{ $name }}" 
    {{ $attributes->merge(['class' => 'select-form-inner filter-select']) }}
    {{ $multiple ? 'multiple' : '' }}
    data-placeholder="{{ $placeholder }}"
>
    @if(!$multiple)
        <option value="">{{ $placeholder }}</option>
    @endif
    @foreach($options as $value => $label)
        <option value="{{ $value }}" @selected($value == old($name, $selected))>{{ $label }}</option>
    @endforeach
</select>

@once
    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize Select2 on all filter-select elements
            $('.filter-select').select2({
                width: '100%',
                minimumResultsForSearch: 8, // Show search box only if more than 6 options
                dropdownCssClass: 'select2-dropdown-custom',
                selectionCssClass: 'select2-selection-custom',
                dropdownAutoWidth: false
            });
            
            // Handle Select2 change events for filtering
            $('.filter-select').on('change', function() {
                // This will be handled by the filters.js script
            });
        });
    </script>
    @endpush
@endonce