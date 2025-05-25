@props(['href' => '#'])

<a href="{{ $href }}" {{ $attributes->merge(['class' => 'link-btn-comp']) }}>
  {{ $slot }}
</a>