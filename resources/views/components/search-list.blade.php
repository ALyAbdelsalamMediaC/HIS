@props(['searchQuery' => ''])

@php
  $items = [
    ['icon' => 'user', 'text' => 'John Doe - Patient'],
    ['icon' => 'calendar', 'text' => 'Appointment #1234'],
    ['icon' => 'hospital', 'text' => 'Department: Cardiology'],
    ['icon' => 'user', 'text' => 'Dr. Sarah Smith'],
    ['icon' => 'calendar', 'text' => 'Appointment #1235'],
    ['icon' => 'hospital', 'text' => 'Department: Neurology']
  ];

  $searchQuery = $searchQuery ?? '';
  $filteredItems = collect($items)->filter(function ($item) use ($searchQuery) {
    return empty($searchQuery) ||
    str_contains(strtolower($item['text']), strtolower($searchQuery));
  })->all();
@endphp

<div class="search-list">
  @forelse($filteredItems as $item)
    <div class="search-item">
    <x-svg-icon :name="$item['icon']" size="16" color="#ADADAD" />
    <span>{{ $item['text'] }}</span>
    </div>
  @empty
    <div class="search-item text-muted">
    No results found
    </div>
  @endforelse
</div>