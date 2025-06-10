@props(['seconds'])

@php
  $minutes = floor($seconds / 60);
  $remainingSeconds = round($seconds % 60);
  $formattedDuration = sprintf('%02d:%02d', $minutes, $remainingSeconds);
@endphp

<span {{ $attributes }}>{{ $formattedDuration }}</span>