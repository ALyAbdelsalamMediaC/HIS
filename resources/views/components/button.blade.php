<button {{ $attributes->merge(['class' => 'btn-comp', 'type' => 'button']) }}>
  {{ $slot }}
</button>