@props(['tabs' => [], 'activeTab' => ''])

<ul class="nav nav-pills nav-fill my-4" id="requestsTabs" role="tablist">
  @foreach($tabs as $tab)
    <li class="nav-item" role="presentation">
    <a href="{{ $tab['route'] }}" class="nav-link {{ $activeTab === $tab['id'] ? 'active' : '' }}" role="tab">
      {{ $tab['label'] }}
    </a>
    </li>
  @endforeach
</ul>