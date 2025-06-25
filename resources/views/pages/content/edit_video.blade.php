@extends('layouts.app')
@section('title', 'HIS | Edit Video')
@section('content')
  <section>
    <div class="gap-3 d-flex align-items-center">
    <a href="{{ url()->previous() }}" class="arrow-back-btn">
      <x-svg-icon name="arrow-left2" size="16" color="#35758C" />
    </a>

    <div>
      <h2 class="h2-semibold" style="color:#35758C;">Edit Video</h2>
      <p class="h5-ragular" style="color:#ADADAD;">Update your video details</p>
    </div>
    </div>

    <!-- Current Video Preview Section -->
    <div class="p-4 mt-4 mb-4 rounded border" style="background-color: #f8f9fa;">
    <h4 class="mb-3 h4-semibold" style="color:#35758C;">Current Video</h4>
    @if($media->file_path)
    <div class="row">
      <div class="col-md-6">
      <div class="video-preview-container">
      <video controls style="width: 100%; border-radius: 8px;" preload="metadata">
      <source src="{{ route('content.stream', ['id' => $media->id]) }}" type="video/mp4">
      Your browser does not support the video tag.
      </video>
      </div>
      </div>
      <div class="col-md-6">
      <div class="video-info">
      <h5 class="mb-2 h5-semibold">{{ $media->title }}</h5>
      <p class="mb-2 h6-ragular" style="color:#676767;">
      <strong>Duration:</strong>
      @if($media->duration)
      @php
      $hours = floor($media->duration / 3600);
      $minutes = floor(($media->duration % 3600) / 60);
      $seconds = floor($media->duration % 60);
      $duration = '';
      if ($hours > 0) {
      $duration .= $hours . 'h ';
      }
      if ($minutes > 0 || $hours > 0) {
      $duration .= $minutes . 'm ';
      }
      $duration .= $seconds . 's';
      @endphp
      {{ $duration }}
      @else
      N/A
      @endif
      </p>
      <!-- <p class="mb-2 h6-ragular" style="color:#676767;">
      <strong>Category:</strong> {{ $media->category->name ?? 'N/A' }}  
      </p> -->
      <p class="mb-2 h6-ragular" style="color:#676767;">
      <strong>Status:</strong>
      <span
        class="badge bg-{{ $media->status === 'published' ? 'success' : ($media->status === 'pending' ? 'warning' : 'danger') }}">
        {{ ucfirst($media->status) }}
      </span>
      </p>
      <p class="mb-2 h6-ragular" style="color:#676767;">
      <strong>Uploaded:</strong> {{ $media->created_at->format('M d, Y \a\t g:i A') }}
      </p>
      @if($media->description)
      <p class="mb-2 h6-ragular" style="color:#676767;">
      <strong>Description:</strong> {{ Str::limit($media->description, 100) }}
      </p>
      @endif
      </div>
      </div>
    </div>
    @else
    <p class="h6-ragular" style="color:#dc3545;">No video file found.</p>
    @endif
    </div>

    <form method="POST" action="{{ route('content.update', $media->id) }}" enctype="multipart/form-data" novalidate>
    @csrf
    @method('PUT')

    <!-- Video Upload Section -->
    <div class="mt-4 mb-4">
      <h4 class="mb-3 h4-semibold" style="color:#35758C;">Update Video File (Optional)</h4>
      <p class="mb-3 h6-ragular" style="color:#676767;">Leave empty to keep the current video file.</p>
      <x-drag-drop-upload name="file" accept="video/mp4" max-size="1GB" supported-formats="MP4" :required="false"
      :current-file="$media->file_path ? [
      'name' => basename(parse_url($media->file_path, PHP_URL_PATH)) ?: 'Current Video',
      'url' => $media->file_path
      ] : null" />
    </div>

    <div class="mt-3 form-infield">
      <x-text_label for="thumbnail_path">Upload Thumbnail (Optional)</x-text_label>
      <div style="position: relative;">
      <x-text_input type="file" id="thumbnail_path" name="thumbnail_path"
        placeholder="Choose an thumbnail from your gallery" accept="image/jpeg,image/jpg,image/png"
        style="color: transparent; cursor: pointer;" onchange="updateFileName(this)" />
      <div style="position: absolute; right: 0; top: 50%; transform: translateY(-50%); padding-right: 16px;">
        <x-button type="button" onclick="document.getElementById('thumbnail_path').click()">Choose
        file</x-button>
      </div>
      </div>
      @if($media->thumbnail_path)
      <div class="mt-2">
      <img src="{{ asset($media->thumbnail_path) }}" alt="Current thumbnail" style="max-width: 200px;">
      </div>
    @endif
      <div id="thumbnail-error-container">
      <x-input-error :messages="$errors->get('thumbnail_path')" class="mt-2" />
      </div>
    </div>

    <div class="form-infield">
      <x-text_label for="image">Image</x-text_label>
      <div style="position: relative;">
      <x-text_input type="file" id="image_path" name="image_path" placeholder="Choose an image from your gallery"
        accept="image/jpeg,image/jpg,image/png" style="color: transparent; cursor: pointer;"
        onchange="updateFileName(this)" />
      <div style="position: absolute; right: 0; top: 50%; transform: translateY(-50%); padding-right: 16px;">
        <x-button type="button" onclick="document.getElementById('image_path').click()">Choose
        file</x-button>
      </div>
      </div>
      @if($media->image_path)
      <div class="mt-2">
      <img src="{{ asset($media->image_path) }}" alt="Current image" style="max-width: 200px;">
      </div>
    @endif
    </div>

    <div class="form-infield">
      <x-text_label for="pdf">Media PDF</x-text_label>
      <div style="position: relative;">
      <x-text_input type="file" id="pdf" name="pdf" placeholder="Choose a PDF file" accept="application/pdf"
        style="color: transparent; cursor: pointer;"
        onchange="validateFile(this, 'application/pdf', 30 * 1024 * 1024, 'PDF size exceeds 30MB. Please choose a smaller file.', 'Choose a PDF file')" />
      <div style="position: absolute; right: 0; top: 50%; transform: translateY(-50%); padding-right: 16px;">
        <x-button type="button" onclick="document.getElementById('pdf').click()">Choose file</x-button>
      </div>
      </div>
      @if($media->pdf)
      <div class="mt-2">
      <a href="{{ asset($media->pdf) }}" target="_blank">View Current PDF</a>
      </div>
    @endif
    </div>

    <div class="form-infield">
      <x-text_label for="year" :required="true">Year</x-text_label>
      <x-select id="year" name="year"
        :options="collect(range(date('Y'), 2015))->mapWithKeys(fn($y) => [$y => $y])->all()"
        :selected="old('year', $media->year ?? null)"
        placeholder="Select Year" data-required="true" data-name="Year" />
      <div id="year-error-container">
        <x-input-error :messages="$errors->get('year')" class="mt-2" />
      </div>
    </div>

    <div class="form-infield">
      <x-text_label for="month" :required="true">Month</x-text_label>
      <x-select id="month" name="month"
        :options="[
          1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
          5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
          9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December'
        ]"
        :selected="old('month', $media->month ?? null)"
        placeholder="Select Month" data-required="true" data-name="Month" />
      <div id="month-error-container">
        <x-input-error :messages="$errors->get('month')" class="mt-2" />
      </div>
    </div>

    <div class="form-infield">
      <x-text_label for="title" :required="true">Title</x-text_label>
      <x-text_input type="text" id="title" name="title" placeholder="Title" data-required="true" data-name="Title"
      value="{{ $media->title }}" />
      <div id="title-error-container">
      <x-input-error :messages="$errors->get('title')" />
      </div>
    </div>

    <div class="form-infield">
      <x-text_label for="description">Description (optional)</x-text_label>
      <x-textarea name="description" id="description" placeholder="Enter Description" rows="3"
      value="{{ $media->description }}" />
    </div>

    <div class="form-infield">
        <x-text_label for="mention">Mention Users</x-text_label>
        <select class="select2-mentions form-control" name="mention[]" multiple="multiple" id="mention">
            @foreach($users as $user)
                <option value="{{ $user->name }}" {{ in_array($user->name, json_decode($media->mentions ?? '[]', true) ?? []) ? 'selected' : '' }}>
                    {{ $user->name }}
                </option>
            @endforeach
            @foreach(array_diff(json_decode($media->mentions ?? '[]', true) ?? [], $users->pluck('name')->toArray()) as $customMention)
                <option value="{{ $customMention }}" selected>{{ $customMention }}</option>
            @endforeach
        </select>
    </div>

    <div class="mt-3 mb-2 form-check">
      <input class="form-check-input" type="checkbox" name="is_featured" value="1" id="is_featured" {{ $media->is_featured ? 'checked' : '' }}>
      <label class="form-check-label" for="is_featured">Featured</label>
    </div>

    <div class="mt-3 mb-2 form-check">
      <input class="form-check-input" type="checkbox" name="is_favorite" value="1" id="is_favorite" {{ $article->is_favorite ? 'checked' : '' }}>
      <label class="form-check-label" for="is_favorite">Is favorite</label>
    </div>

    <div class="mt-3 d-flex justify-content-end">
      <x-button type="submit">Update Video</x-button>
    </div>
    </form>
  </section>
@endsection

@push('scripts')
  <script src="{{ asset('js/validations.js') }}"></script>
  <script src="{{ asset('js/showToast.js') }}"></script>
  <script>
    function validateFile(input, expectedType, maxSizeBytes, errorMessage, defaultPlaceholder) {
    const file = input.files[0];
    if (file) {
      const validTypes = expectedType.split(',');
      if (!validTypes.includes(file.type)) {
      showToast(`Please select a valid ${expectedType.includes('image') ? 'image (JPEG, JPG, PNG)' : 'PDF'} file`, 'danger');
      input.value = '';
      input.setAttribute('data-placeholder', defaultPlaceholder);
      return;
      }
      if (file.size > maxSizeBytes) {
      showToast(errorMessage, 'danger');
      input.value = '';
      input.setAttribute('data-placeholder', defaultPlaceholder);
      return;
      }
      input.setAttribute('data-placeholder', file.name);
    } else {
      input.setAttribute('data-placeholder', defaultPlaceholder);
    }
    }

    function updateFileName(input) {
    validateFile(input, 'image/jpeg,image/jpg,image/png', 3 * 1024 * 1024, 'Image size exceeds 3MB. Please choose a smaller image.', 'Choose an image from your gallery');
    }

    document.addEventListener('DOMContentLoaded', function () {
    const fileInputs = [
      { id: 'thumbnail_path', placeholder: 'Choose an thumbnail from your gallery' },
      { id: 'image_path', placeholder: 'Choose an image from your gallery' },
      { id: 'pdf', placeholder: 'Choose a PDF file' }
    ];

    fileInputs.forEach(({ id, placeholder }) => {
      const input = document.getElementById(id);
      input.style.setProperty('--webkit-file-upload-button', 'none');
      input.style.setProperty('--file-selector-button', 'none');
      if (!input.files || input.files.length === 0) {
      input.setAttribute('data-placeholder', placeholder);
      }
    });

    const style = document.createElement('style');
    style.textContent = `
      input[type="file"]::-webkit-file-upload-button,
      input[type="file"]::file-selector-button {
      display: none;
      }
      input[type="file"] {
      color: transparent;
      }
      input[type="file"]::before {
      content: attr(data-placeholder);
      color: #6c757d;
      position: absolute;
      padding-left:11px;
      left: 12px;
      top: 50%;
      transform: translateY(-50%);
      pointer-events: none;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
      max-width: calc(100% - 130px);
      }
      `;
    document.head.appendChild(style);
    });

    $(document).ready(function() {
      $('.select2-mentions').select2({
        placeholder: 'Select or type names to mention',
        tags: true,
        tokenSeparators: [',', ' '],
        width: '100%'
      });
    });
  </script>
@endpush