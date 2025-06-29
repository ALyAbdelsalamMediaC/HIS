@props([
    'name' => 'file',
    'accept' => 'video/mp4',
    'maxSize' => '1GB',
    'supportedFormats' => 'mp4',
    'multiple' => false,
    'required' => false,
    'currentFile' => null
])

<div class="upload-container" x-data="dragDropUpload({
    name: '{{ $name }}',
    accept: '{{ $accept }}',
    maxSize: '{{ $maxSize }}',
    multiple: {{ $multiple ? 'true' : 'false' }},
    required: {{ $required ? 'true' : 'false' }},
    currentFile: {{ $currentFile ? json_encode($currentFile) : 'null' }}
})">
    <!-- Hidden file input -->
    <input 
        type="file" 
        name="{{ $name }}" 
        accept="{{ $accept }}"
        {{ $multiple ? 'multiple' : '' }}
        {{ $required ? 'required' : '' }}
        class="d-none" 
        x-ref="fileInput"
        @change="handleFileSelect"
    >

    <!-- Upload Box -->
    <div 
        class="uploadBox"
        :class="{
            'dragging': isDragging, 
            'success': hasSelectedFiles, 
            'error': hasError 
        }"
        @dragover.prevent="isDragging = true"
        @dragleave.prevent="isDragging = false"
        @drop.prevent="handleDrop"
        @click="openFileDialog"
        style="cursor: pointer; transition: all 0.3s ease;"
    >
        <!-- Upload Icon -->
        <div class="upload-icon">
            <img src="/images/global/upload-icon.svg" alt="upload">
        </div>

        <!-- Upload Text -->
        <div>
            <h3 class="mt-3 h2-semibold">
                <span x-show="!isDragging && currentFile === null && !hasSelectedFiles">Drag & drop files or <span style="color:#35758C;">Browse</span></span>
                <span x-show="!isDragging && currentFile !== null && !hasSelectedFiles">Drag & drop new file or <span style="color:#35758C;">Browse</span></span>
                <span x-show="!isDragging && hasSelectedFiles">Files selected successfully!</span>
                <span x-show="isDragging">Drop files here</span>
            </h3>
            <p class="mt-1 h4-ragular" style="color:#676767;">Supported formats: {{ $supportedFormats }}</p>
            <p class="h4-ragular" style="color:#676767;">Maximum file size: {{ $maxSize }}</p>
        </div>

        <!-- Current File Info (for edit mode) -->
        <div x-show="currentFile !== null && !hasSelectedFiles" class="mt-3">
            <div class="p-3 rounded border" style="background-color: #e9ecef;">
                <p class="mb-1 h5-ragular" style="color:#35758C;">
                    <strong>Current File:</strong>
                </p>
                <p class="mb-1 h6-ragular" style="color:#676767;">
                    <span x-text="currentFile && currentFile.name ? currentFile.name : 'Video file'"></span>
                </p>
                <p class="mb-0 h6-ragular" style="color:#676767;">
                    <small>Upload a new file to replace the current one</small>
                </p>
            </div>
        </div>

        <!-- Selected File Info -->
        <div x-show="hasSelectedFiles" class="mt-3">
            <template x-if="!multiple && selectedFile">
                <div>
                    <p class="h5-ragular" style="color:#28a745;">
                        ✓ Selected: <span x-text="selectedFile.name"></span>
                    </p>
                    <p class="h6-ragular" style="color:#676767;">
                        Size: <span x-text="formatFileSize(selectedFile.size)"></span>
                    </p>
                </div>
            </template>
            <template x-if="multiple && selectedFiles.length > 0">
                <div>
                    <p class="h5-ragular" style="color:#28a745;">
                        ✓ Selected: <span x-text="selectedFiles.length + ' file(s)'"></span>
                    </p>
                </div>
            </template>
        </div>

        <!-- Error Message -->
        <div x-show="hasError" class="mt-2">
            <p class="h5-ragular" style="color:#dc3545;" x-text="errorMessage"></p>
        </div>
    </div>

    <!-- Preview Section for Selected Files -->
    <div x-show="hasSelectedFiles" class="mt-3">
        <h4 class="h4-semibold" style="color:#35758C;">Preview:</h4>
        
        <!-- Single File Preview -->
        <template x-if="!multiple && selectedFile">
            <div class="preview-container">
                <!-- Video Preview -->
                <template x-if="selectedFile.type.startsWith('video/')">
                    <div class="video-preview">
                        <video controls style="width: 100%; max-width: 400px; border-radius: 8px;">
                            <source :src="URL.createObjectURL(selectedFile)" :type="selectedFile.type">
                            Your browser does not support the video tag.
                        </video>
                        <p class="mt-2 h6-ragular" style="color:#676767;">
                            <strong>Preview:</strong> <span x-text="selectedFile.name"></span>
                        </p>
                    </div>
                </template>
                
                <!-- Image Preview -->
                <template x-if="selectedFile.type.startsWith('image/')">
                    <div class="image-preview">
                        <img :src="URL.createObjectURL(selectedFile)" 
                             :alt="selectedFile.name" 
                             style="max-width: 400px; max-height: 300px; border-radius: 8px; object-fit: cover;">
                        <p class="mt-2 h6-ragular" style="color:#676767;">
                            <strong>Preview:</strong> <span x-text="selectedFile.name"></span>
                        </p>
                    </div>
                </template>
                
                <!-- Other File Types -->
                <template x-if="!selectedFile.type.startsWith('video/') && !selectedFile.type.startsWith('image/')">
                    <div class="file-preview">
                        <div class="p-3 rounded border" style="background-color: #f8f9fa;">
                            <p class="mb-1 h5-ragular" style="color:#35758C;">
                                <strong>File:</strong> <span x-text="selectedFile.name"></span>
                            </p>
                            <p class="mb-0 h6-ragular" style="color:#676767;">
                                Type: <span x-text="selectedFile.type"></span> | 
                                Size: <span x-text="formatFileSize(selectedFile.size)"></span>
                            </p>
                        </div>
                    </div>
                </template>
            </div>
        </template>

        <!-- Multiple Files Preview -->
        <template x-if="multiple && selectedFiles.length > 0">
            <div class="multiple-preview-container">
                <template x-for="(file, index) in selectedFiles" :key="index">
                    <div class="mb-3 preview-item">
                        <h5 class="h5-semibold" style="color:#35758C;" x-text="'File ' + (index + 1) + ': ' + file.name"></h5>
                        
                        <!-- Video Preview -->
                        <template x-if="file.type.startsWith('video/')">
                            <div class="video-preview">
                                <video controls style="width: 100%; max-width: 400px; border-radius: 8px;">
                                    <source :src="URL.createObjectURL(file)" :type="file.type">
                                    Your browser does not support the video tag.
                                </video>
                            </div>
                        </template>
                        
                        <!-- Image Preview -->
                        <template x-if="file.type.startsWith('image/')">
                            <div class="image-preview">
                                <img :src="URL.createObjectURL(file)" 
                                     :alt="file.name" 
                                     style="max-width: 400px; max-height: 300px; border-radius: 8px; object-fit: cover;">
                            </div>
                        </template>
                        
                        <!-- Other File Types -->
                        <template x-if="!file.type.startsWith('video/') && !file.type.startsWith('image/')">
                            <div class="file-preview">
                                <div class="p-2 rounded border" style="background-color: #f8f9fa;">
                                    <p class="mb-0 h6-ragular" style="color:#676767;">
                                        Type: <span x-text="file.type"></span> | 
                                        Size: <span x-text="formatFileSize(file.size)"></span>
                                    </p>
                                </div>
                            </div>
                        </template>
                    </div>
                </template>
            </div>
        </template>
    </div>

    <!-- File List for Multiple Files -->
    <div x-show="multiple && selectedFiles.length > 0 && !hasError" class="mt-3">
        <h4 class="h4-semibold" style="color:#35758C;">Selected Files:</h4>
        <template x-for="(file, index) in selectedFiles" :key="index">
            <div class="py-2 d-flex justify-content-between align-items-center border-bottom">
                <div>
                    <p class="mb-0 h5-ragular" x-text="file.name"></p>
                    <p class="mb-0 h6-ragular" style="color:#676767;" x-text="formatFileSize(file.size)"></p>
                </div>
                <button 
                    type="button" 
                    class="btn btn-sm btn-outline-danger"
                    @click="removeFile(index)"
                >
                    Remove
                </button>
            </div>
        </template>
    </div>
</div>

@push('scripts')
<script src="{{ asset('js/showToast.js') }}"></script>

<script>
function dragDropUpload(config) {
    return {
        // Configuration
        name: config.name,
        accept: config.accept,
        maxSizeBytes: parseFileSize(config.maxSize),
        multiple: config.multiple,
        required: config.required,
        currentFile: config.currentFile,

        // State
        isDragging: false,
        hasError: false,
        selectedFile: null,
        selectedFiles: [],
        errorMessage: '',

        get hasSelectedFiles() {
            return this.multiple ? this.selectedFiles.length > 0 : this.selectedFile !== null;
        },

        handleDrop(e) {
            e.preventDefault();
            this.isDragging = false;
            const files = Array.from(e.dataTransfer.files);
            this.processFiles(files);
        },

        openFileDialog() {
            this.$refs.fileInput.click();
        },

        handleFileSelect(e) {
            const files = Array.from(e.target.files);
            this.processFiles(files);
        },

        processFiles(files) {
            if (!files.length) return;

            // Reset previous states
            this.hasError = false;
            this.errorMessage = '';
            
            const validFiles = files.filter(file => this.validateFile(file));
            
            if (validFiles.length === 0) return;

            if (this.multiple) {
                this.selectedFiles = validFiles;
            } else {
                this.selectedFile = validFiles[0];
            }

            showToast('Files selected successfully!', 'success');
        },

        validateFile(file) {
            // Check file size
            if (file.size > this.maxSizeBytes) {
                showToast(`File "${file.name}" exceeds maximum size limit.`, 'danger');
                return false;
            }

            // Check file type
            const acceptedTypes = this.accept.split(',').map(type => type.trim());
            const isValidType = acceptedTypes.some(type => {
                if (type.includes('*')) {
                    const baseType = type.split('/')[0];
                    return file.type.startsWith(baseType);
                }
                return file.type === type;
            });

            if (!isValidType) {
                showToast(`File "${file.name}" format not supported.`, 'danger');
                return false;
            }

            return true;
        },

        removeFile(index) {
            this.selectedFiles.splice(index, 1);
            if (this.selectedFiles.length === 0) {
                this.reset();
            }
        },

        reset() {
            this.selectedFile = null;
            this.selectedFiles = [];
            this.hasError = false;
            this.errorMessage = '';
            this.$refs.fileInput.value = '';
        },

        formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }
    }
}

function parseFileSize(sizeStr) {
    const units = { 'B': 1, 'KB': 1024, 'MB': 1024**2, 'GB': 1024**3 };
    const match = sizeStr.match(/^(\d+(?:\.\d+)?)\s*([A-Z]+)$/i);
    if (!match) return 1024**3; // Default to 1GB
    return parseFloat(match[1]) * (units[match[2].toUpperCase()] || 1);
}
</script>
@endpush