@props([
    'id' => 'comment',
    'name' => 'comment',
    'placeholder' => 'Add a comment',
    'value' => '',
    'style' => 'background-color: transparent; border-radius: 38px; min-height: 60px; max-height: 120px; resize: none;',
    'action' => 'add-comment', // 'add-comment' or 'add-reply'
    'parentId' => null
])

<div class="input-icon comment-input-wrapper" 
     data-action="{{ $action }}"
     @if($parentId) data-parent-id="{{ $parentId }}" @endif>
    <x-textarea 
        id="{{ $id }}" 
        name="{{ $name }}" 
        placeholder="{{ $placeholder }}" 
        style="{{ $style }}" 
        rows="1" 
        class="comment-textarea"
    >{{ $value }}</x-textarea>
    <div class="input-icon-send comment-submit-btn" 
         style="cursor:pointer;" 
         title="Submit">
        <x-svg-icon name="send" size="14" color="#fff" />
    </div>
</div>