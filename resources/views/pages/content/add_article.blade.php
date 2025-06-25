@extends('layouts.app')
@section('title', 'HIS | Add Article')
@section('content')
  <section>
    <div class="gap-3 d-flex align-items-center">
    <div class="arrow-back-btn" onclick="window.history.back()">
      <x-svg-icon name="arrow-left2" size="16" color="#35758C" />
    </div>

    <div>
      <h2 class="h2-semibold" style="color:#35758C;">Add Article</h2>
      <p class="h5-ragular" style="color:#ADADAD;">Create, upload your article</p>
    </div>
    </div>

    <form method="POST" action="{{ route('articles.store') }}" enctype="multipart/form-data" novalidate class="mt-4">
    @csrf

    <div class="form-infield">
      <x-text_label for="thumbnail_path">Upload Thumbnail</x-text_label>
      <div style="position: relative;">
      <x-text_input type="file" id="thumbnail_path" name="image_path"
        placeholder="Choose an thumbnail from your gallery" accept="image/jpeg,image/jpg,image/png"
        style="color: transparent; cursor: pointer;" onchange="updateFileName(this)" />
      <div style="position: absolute; right: 0; top: 50%; transform: translateY(-50%); padding-right: 16px;">
        <x-button type="button" onclick="document.getElementById('thumbnail_path').click()">Choose
        file</x-button>
      </div>
      </div>
    </div>

    <div class="form-infield">
      <x-text_label for="image_path">Image</x-text_label>
      <div style="position: relative;">
      <x-text_input type="file" id="image_path" name="image_path" placeholder="Choose an image from your gallery"
        accept="image/jpeg,image/jpg,image/png" style="color: transparent; cursor: pointer;"
        onchange=" updateFileName(this)" />
      <div style="position: absolute; right: 0; top: 50%; transform: translateY(-50%); padding-right: 16px;">
        <x-button type="button" onclick="document.getElementById('image_path').click()">Choose
        file</x-button>
      </div>
      </div>
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
      <div id="pdf-error-container">
      <x-input-error :messages="$errors->get('pdf')" class="mt-2" />
      </div>
    </div>

    <div class="form-infield">
      <x-text_label for="hyperlink">Hyperlink</x-text_label>
      <x-text_input type="text" id="hyperlink" name="hyperlink" placeholder="Hyperlink" />
      <div id="hyperlink-error-container">
      <x-input-error :messages="$errors->get('hyperlink')" />
      </div>
    </div>

    <div class="form-infield">
      <x-text_label for="year" :required="true">Year</x-text_label>
      <x-select id="year" name="year"
        :options="collect(range(date('Y'), 2015))->mapWithKeys(fn($y) => [$y => $y])->all()"
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
        placeholder="Select Month" data-required="true" data-name="Month" />
      <div id="month-error-container">
        <x-input-error :messages="$errors->get('month')" class="mt-2" />
      </div>
    </div>

    <div class="form-infield">
      <x-text_label for="title" :required="true">Title</x-text_label>
      <x-text_input type="text" id="title" name="title" placeholder="Title" data-required="true" data-name="Title" />
      <div id="title-error-container">
      <x-input-error :messages="$errors->get('title')" />
      </div>
    </div>

    <div class="form-infield">
      <x-text_label for="description">Description (optional)</x-text_label>
      <x-textarea name="description" id="description" placeholder="Enter Description" rows="3" />
    </div>

    <div class="form-infield">
        <x-text_label for="mention">Mention Users</x-text_label>
        <select class="select2-mentions form-control" name="mention[]" multiple="multiple" id="mention">
            @foreach($users as $user)
                <option value="{{ $user->name }}">{{ $user->name }}</option>
            @endforeach
        </select>
    </div>

    <div class="mt-3 mb-2 form-check">
      <input class="form-check-input" type="checkbox" name="is_featured" value="1" id="is_featured">
      <label class="form-check-label" for="is_featured">Featured</label>
    </div>

    <div class="mt-3 d-flex justify-content-end">
      <x-button type="submit">Add Article</x-button>
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
      padding-left: 11px;
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