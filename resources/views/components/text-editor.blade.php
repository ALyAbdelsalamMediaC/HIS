@props([
    'name' => 'description',
    'id' => 'description',
    'placeholder' => 'Enter Description',
])
<div>
  <div id="quill-wrapper-{{ $id }}" class="grammarly-disable" data-gramm="false" data-gramm_editor="false" data-enable-grammarly="false" data-gr="false">
    <div id="editor-{{ $id }}" class="quill-editor grammarly-disable" data-gramm="false" data-gramm_editor="false" data-enable-grammarly="false" data-gr="false" style="min-height: 150px;">{!! $slot !!}</div>
    <input type="hidden" name="{{ $name }}" id="{{ $id }}" value="{!! str_replace('"', '"', $slot) !!}">
  </div>
</div>
@push('scripts')
<script>
  (function() {
    // Prevent multiple initializations
    if (window['quill_' + @json($id)]) return;
    window['quill_' + @json($id)] = true;

    // Function to disable Grammarly on an element and its children
    function disableGrammarly(element) {
      if (!element || typeof element.setAttribute !== 'function') return;
      element.setAttribute('data-gramm', 'false');
      element.setAttribute('data-gramm_editor', 'false');
      element.setAttribute('data-enable-grammarly', 'false');
      element.setAttribute('data-gr', 'false');
      element.classList.add('grammarly-disable');
      Array.from(element.children).forEach(disableGrammarly);
    }

    // Initialize Quill after DOM is fully loaded
    document.addEventListener('DOMContentLoaded', function() {
      var editorElement = document.getElementById('editor-{{ $id }}');
      if (!editorElement) return;

      // Disable Grammarly on editor and children
      disableGrammarly(editorElement);

      // Initialize Quill editor
      var quill = new Quill('#editor-{{ $id }}', {
        theme: 'snow',
        placeholder: @json($placeholder),
        modules: {
          toolbar: [
            [{ header: [1, 2, false] }],
            ['bold', 'italic', 'underline', 'strike'],
            [{ 'list': 'ordered'}, { 'list': 'bullet' }],
            [{ 'align': [] }],
            ['link'],
            ['clean']
          ]
        }
      });

      // Set initial value if any
      var initialValue = document.getElementById('{{ $id }}').value;
      if (initialValue) {
        quill.root.innerHTML = initialValue;
      }

      // Update hidden input on change
      quill.on('text-change', function() {
        document.getElementById('{{ $id }}').value = quill.root.innerHTML;
      });

      // On form submit, update hidden input
      var form = document.getElementById('editor-{{ $id }}').closest('form');
      if (form) {
        form.addEventListener('submit', function() {
          document.getElementById('{{ $id }}').value = quill.root.innerHTML;
        });
      }

      // Reapply Grammarly disable after toolbar interactions
      quill.on('selection-change', function() {
        disableGrammarly(editorElement);
      });
    });
  })();
</script>
@endpush