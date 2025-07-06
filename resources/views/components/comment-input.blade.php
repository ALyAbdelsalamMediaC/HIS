@props([
    'id' => 'comment',
    'name' => 'comment',
    'placeholder' => 'Add a comment',
    'value' => '',
    'style' => 'background-color: transparent; border-radius: 38px; min-height: 60px; max-height: 120px; resize: none;'
])
<div class="input-icon">
    <x-textarea :id="$id" :name="$name" :placeholder="$placeholder" :style="$style" :value="$value" rows="1" />
    <div class="input-icon-send" style="cursor:pointer" onclick="this.closest('form').submit();">
        <x-svg-icon name="send" size="14" color="#fff" />
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const textarea = document.getElementById('{{ $id }}');
    if (textarea) {
        // Auto-resize functionality
        function autoResize() {
            textarea.style.height = 'auto';
            textarea.style.height = Math.min(textarea.scrollHeight, 120) + 'px';
        }

        // Handle Enter key
        textarea.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                // Create a new line instead of submitting
                const start = this.selectionStart;
                const end = this.selectionEnd;
                const value = this.value;
                this.value = value.substring(0, start) + '\n' + value.substring(end);
                this.selectionStart = this.selectionEnd = start + 1;
                autoResize();
            }
        });

        // Auto-resize on input
        textarea.addEventListener('input', autoResize);
        
        // Initial resize
        autoResize();
    }
});
</script>
