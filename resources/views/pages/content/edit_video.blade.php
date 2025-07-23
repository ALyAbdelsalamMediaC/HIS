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
    <div class="p-4 mt-4 mb-4 border rounded" style="background-color: #f8f9fa;">
    <h4 class="mb-3 h4-semibold" style="color:#35758C;">Current Video</h4>
    @if($media->file_path)
    <div class="row">
      <div class="col-md-6">
      <div class="video-preview-container">
        <div class="video-container">
            <video 
                controls 
                class="video-player-edit"
                preload="none"
                @if($media->thumbnail_path)
                     poster="{{ $media->thumbnail_path }}"
                @endif
            >
                <source src="{{ route('content.stream', ['id' => $media->id]) }}" type="video/mp4">
                Your browser does not support the video tag.
            </video>
        </div>
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
        class="card-status {{ $media->status }}">
        {{ ucfirst($media->status) }}
      </span>
      </p>
      <p class="mb-2 h6-ragular" style="color:#676767;">
      <strong>Uploaded:</strong> {{ $media->created_at->format('M d, Y \a\t g:i A') }}
      </p>
      @if($media->description)
      <p class="mb-2 h6-ragular" style="color:#676767;">
      <strong>Description:</strong> {!! Str::words(strip_tags($media->description), 15, '...') !!}
      </p>
      @endif
      </div>
      </div>
    </div>
    @else
    <p class="h6-ragular" style="color:#dc3545;">No video file found.</p>
    @endif
    </div>


    <form id="video-upload-form" method="POST" action="{{ route('content.update', $media->id) }}" enctype="multipart/form-data" novalidate>
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
            <input type="hidden" id="uploaded_video_path" name="uploaded_video_path" value="" />
        </div>

    <div class="mt-3 form-infield">
      <x-text_label for="thumbnail_path">Upload Thumbnail (Optional)</x-text_label>
      <div style="position: relative;">
      <x-text_input type="file" id="thumbnail_path" name="thumbnail_path"
        placeholder="Choose an thumbnail from your gallery" accept="image/jpeg,image/jpg,image/png"
        style="color: transparent; cursor: pointer;" onchange="updateFileName(this); previewImage(this, 'thumbnail-preview')" />
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
      <!-- New Thumbnail Preview -->
      <div id="thumbnail-preview" class="mt-2" style="display: none;">
        <h6 class="h6-semibold" style="color:#35758C;">New Thumbnail Preview:</h6>
        <img id="thumbnail-preview-img" src="" alt="New thumbnail preview" style="max-width: 200px; border-radius: 8px;">
      </div>
      <div id="thumbnail-error-container">
      <x-input-error :messages="$errors->get('thumbnail_path')" class="mt-2" />
      </div>
    </div>

    <div class="form-infield">
      <x-text_label for="image">Image</x-text_label>
      <div style="position: relative;">
      <x-text_input type="file" id="image_path" name="image_path" placeholder="Choose an image from your gallery"
        accept="image/jpeg,image/jpg,image/png" style="color: transparent; cursor: pointer;"
        onchange="updateFileName(this); previewImage(this, 'image-preview')" />
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
      <!-- New Image Preview -->
      <div id="image-preview" class="mt-2" style="display: none;">
        <h6 class="h6-semibold" style="color:#35758C;">New Image Preview:</h6>
        <img id="image-preview-img" src="" alt="New image preview" style="max-width: 200px; border-radius: 8px;">
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
      @if($media->pdf)
      <div class="mt-2">
      <a href="{{ asset($media->pdf) }}" target="_blank">View Current PDF</a>
      </div>
    @endif
    </div>

    <div class="form-infield">
      <x-text_label for="year" :required="true">Year</x-text_label>
      <x-select id="year" name="year"
        :options="collect(range(date('Y'), 2018))->mapWithKeys(fn($y) => [$y => $y])->all()"
        :selected="old('year', $yearName ?? null)"
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
        :selected="old('month', $monthName ?? null)"
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
      <x-text-editor name="description" id="description" placeholder="Enter Description">{{ old('description', $media->description) }}</x-text-editor>
    </div>

    <div class="form-infield">
        <x-text_label for="mention">Mention Users</x-text_label>
        <select class="select2-mentions form-control" name="mention[]" multiple="multiple" id="mention">
            @foreach($users as $user)
                <option value="{{ $user->name }}" {{ in_array($user->name, json_decode($media->mention ?? '[]', true) ?? []) ? 'selected' : '' }}>
                    {{ $user->name }}
                </option>
            @endforeach
            @foreach(array_diff(json_decode($media->mention ?? '[]', true) ?? [], $users->pluck('name')->toArray()) as $customMention)
                <option value="{{ $customMention }}" selected>{{ $customMention }}</option>
            @endforeach
        </select>
    </div>

    <div class="mt-3 mb-2 form-check">
      <input class="form-check-input" type="checkbox" name="is_featured" value="1" id="is_featured" {{ $media->is_featured ? 'checked' : '' }}>
      <label class="form-check-label" for="is_featured">Featured</label>
    </div>

        <div class="mt-3 d-flex justify-content-end">
            <x-button type="button" id="upload-btn">Update Video</x-button>
        </div>
        <div id="video-upload-progress" style="margin-top:10px; text-align:center;"></div>
        <div id="video-upload-error" style="margin-top:10px; color:red; text-align:center;"></div>
        <div id="upload-retry" style="text-align:center; margin-top:10px; display:none;"></div>
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

        // Wrap all jQuery code in a single $(function(){ ... }) block
        $(function() {
            // Select2 initialization
            $('.select2-mentions').select2({
                placeholder: 'Select or type names to mention',
                tags: true,
                tokenSeparators: [',', ' '],
                width: '100%'
            });

            // Resumable.js upload logic
            var r = new Resumable({
                target: '/content/videos/upload-chunk',
                query: {_token: $('meta[name="csrf-token"]').attr('content')},
                headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                fileType: ['mp4'],
                chunkSize: 1 * 1024 * 1024, // 1MB for smoother progress
                simultaneousUploads: 3,
                testChunks: true,
                throttleProgressCallbacks: 0.5, // Update progress every 0.5s
                maxFiles: 1
            });

            var progress = $('#video-upload-progress');
            var error = $('#video-upload-error');
            var hiddenPath = $('#uploaded_video_path');
            var fileInput = $("input[name='file']");
            var form = $('#video-upload-form');
            var uploadBtn = $('#upload-btn');
            var uploadStarted = false;
            var uploadFinished = false;

            // Retry button
            var tryAgainBtn = $('<button type="button" id="retry-upload-btn" style="display:none;margin-top:10px;">Try Again</button>');
            error.after(tryAgainBtn);

            // Verify file input
            if (!fileInput.length) {
                console.error('File input not found');
                error.text('File input not found. Please refresh the page.');
                return;
            }
            r.assignBrowse(fileInput[0]);

            // Add progress bar
            progress.html('<div class="progress"><div class="progress-bar" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div></div>');

            r.on('fileAdded', function(file) {
                if (file.size > 1024 * 1024 * 1024) { // 1GB limit
                    error.text('File exceeds 1GB limit.');
                    r.cancel();
                    return;
                }
                console.log('File added:', file.fileName, file.size, 'bytes');
                progress.find('.progress-bar').css('width', '0%').attr('aria-valuenow', 0).text('');
                error.text('');
                uploadStarted = false;
                uploadFinished = false;
                tryAgainBtn.hide();
            });

            r.on('fileProgress', function(file) {
                var percent = Math.floor(file.progress() * 100);
                console.log('Upload progress:', percent + '%');
                progress.find('.progress-bar').css('width', percent + '%').attr('aria-valuenow', percent).text(`Uploading to server: ${percent}%`);
            });

            r.on('fileSuccess', function(file, response) {
                console.log('File upload success:', response);
                progress.find('.progress-bar').css('width', '100%').text('Server upload complete. Sending to Google Drive...');
                error.text('');
                uploadFinished = true;
                tryAgainBtn.hide();
                try {
                    var res = JSON.parse(response);
                    if (res.path) {
                        hiddenPath.val(res.path);
                        console.log('Setting uploaded_video_path:', res.path);
                        form.off('submit.resumable').submit();
                    } else {
                        error.text('Upload completed but no file path returned.');
                        console.error('Invalid response:', res);
                    }
                } catch (e) {
                    error.text('Upload finished but server response invalid.');
                    console.error('Response parse error:', e, response);
                }
            });

            r.on('fileError', function(file, message) {
                console.error('File upload error at chunk', file.chunkNumber, ':', message);
                try {
                    const errorObj = JSON.parse(message);
                    error.text('Upload failed: ' + (errorObj.error || message));
                } catch (e) {
                    error.text('Upload failed: ' + message);
                }
                progress.find('.progress-bar').css('width', '0%').text('');
                uploadStarted = false;
                tryAgainBtn.show();
            });

            r.on('pause', function() {
                console.log('Upload paused');
                progress.find('.progress-bar').text('Upload paused');
            });

            r.on('cancel', function() {
                console.log('Upload canceled');
                progress.find('.progress-bar').css('width', '0%').text('Upload canceled');
                error.text('');
                uploadStarted = false;
                tryAgainBtn.hide();
            });

            tryAgainBtn.on('click', function() {
                if (navigator.onLine) {
                    console.log('Retrying upload');
                    error.text('');
                    tryAgainBtn.hide();
                    r.upload();
                } else {
                    error.text('Still offline. Please check your internet connection.');
                }
            });

            form.on('submit.resumable', function(e) {
                e.preventDefault();
                // Validate required fields
                const requiredFields = [
                    { id: 'year', name: 'Year' },
                    { id: 'month', name: 'Month' },
                    { id: 'title', name: 'Title' }
                ];
                for (let field of requiredFields) {
                    const input = document.getElementById(field.id);
                    if (!input.value || (field.type === 'file' && !input.files.length)) {
                        showToast(`Please fill in the ${field.name} field`, 'danger');
                        return;
                    }
                }
                
                // Check if we're in edit mode with an existing video
                const hasExistingVideo = $('.video-player-edit').length > 0;
                
                // If there's no existing video and no new file selected
                if (!hasExistingVideo && r.files.length === 0) {
                    error.text('Please select a video file before submitting.');
                    return;
                }
                
                // If there's an existing video and no new file selected, submit the form normally
                if (hasExistingVideo && r.files.length === 0) {
                    error.empty();
                    form.off('submit.resumable').submit();
                    return;
                }
                if (!uploadFinished && r.files.length > 0) {
                    uploadStarted = true;
                    progress.find('.progress-bar').css('width', '0%').text('Uploading to server: 0%');
                    error.text('');
                    console.log('Starting chunk upload');
                    r.upload();
                } else if (uploadFinished && hiddenPath.val()) {
                    console.log('Form submitting with uploaded_video_path:', hiddenPath.val());
                    progress.find('.progress-bar').text('Processing upload to Google Drive...');
                    uploadBtn.prop('disabled', true);
                    form.off('submit.resumable').submit();
                } else {
                    error.text('Video upload did not complete. Please try again.');
                    console.error('No uploaded_video_path value');
                }
            });

            form.on('submit', function(e) {
                const hasExistingVideo = $('.video-player-edit').length > 0;
                const hasNewVideo = r.files.length > 0;
                
                // If we have a new video upload
                if (hasNewVideo && (!uploadFinished || !hiddenPath.val())) {
                    e.preventDefault();
                    console.log('Form submission prevented, triggering resumable submit');
                    form.trigger('submit.resumable');
                    return;
                }
                
                // If we're just editing with existing video
                if (hasExistingVideo && !hasNewVideo) {
                    console.log('Submitting form with existing video');
                    error.empty();
                    return true;
                }
                
                console.log('Form data:', new FormData(this));
            });

            uploadBtn.on('click', function(e) {
                console.log('Upload button clicked');
                form.trigger('submit.resumable');
            });

            // Check browser support
            if (!r.support) {
                error.text('Your browser does not support chunked uploads. Please use a modern browser.');
                console.error('Resumable.js not supported');
                uploadBtn.prop('disabled', true);
            }
        });
    </script>
@endpush
