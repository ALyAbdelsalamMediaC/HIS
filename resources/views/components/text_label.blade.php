@props(['for' => null, 'required' => false])

<label for="{{ $for }}" {{ $attributes->merge(['class' => "label-form-inner"]) }}>
  {{ $slot }}
  @if($required)
    <span class="text-danger" aria-hidden="true">*</span>
  @endif
</label>