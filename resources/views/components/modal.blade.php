@props([
    'id',
    'title' => 'Details',
    'data' => [], // Array of key-value pairs for dynamic data
    'model' => null, // Optional: The model for comments (e.g., Meeting)
    'commentRoute' => 'comments.store', // Default route for comments
    'image' => null, // Added image parameter
])

<div class="modal fade" id="{{ $id }}" tabindex="-1" aria-labelledby="{{ $id }}Label" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <!-- Header with Custom Styling -->
      <div class="modal-header h3-semibold">
        <h5 class="modal-title" id="{{ $id }}Label">{{ $title }}</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
      <!-- Body -->
          <div class="modal-body">
              <!-- Image Section -->
              @if($image)
              <div class="mb-4 text-center">
                  <img src="{{ $image }}" alt="User Image" class="rounded-circle" style="width: 100px; height: 100px; object-fit: cover;">
              </div>
              @endif
              
              <!-- Data Section -->
              @if(!empty($data))
          <div class="row">
              <!-- Render dynamic key-value pairs -->
              @foreach($data as $label => $value)
          <div class="col-md-6">
          <div class="form-infield">
          <x-text_label for={{ $label }}>{{ $label }}</x-text_label>
          <x-text_input type="text" id={{ $label }} name={{ $label }} placeholder={{ $label }}
          value="{{ $value ?? 'Not Set' }}" disabled style="background-color: #F7F7F7; border:none" />
          </div>
          </div>
      @endforeach
          </div>
      @endif

        <!-- Always render the slot content -->
        {{ $slot }}
      </div>
    </div>
  </div>
</div>