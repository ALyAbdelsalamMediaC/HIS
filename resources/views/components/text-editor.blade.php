@props([
    'name' => 'description',
    'id' => 'description',
    'placeholder' => 'Enter Description',
])
<div>
  <div id="quill-wrapper-{{ $id }}">
    <div id="editor-{{ $id }}" style="min-height: 150px;">{!! $slot !!}</div>
    <input type="hidden" name="{{ $name }}" id="{{ $id }}" value="{!! str_replace('"', '&quot;', $slot) !!}">
  </div>
</div>
@push('scripts')
<script>
  (function() {
    // Only initialize once per id
    if (window['quill_' + @json($id)]) return;
    window['quill_' + @json($id)] = true;
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
  })();
</script>
@endpush
