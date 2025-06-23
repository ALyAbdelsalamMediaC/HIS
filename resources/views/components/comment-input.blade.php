@props([
    'id' => 'comment',
    'name' => 'comment',
    'placeholder' => 'Add a comment',
    'value' => '',
    'style' => 'background-color: transparent; border-radius: 38px;'
])
<div class="input-icon">
    <x-text_input :type="'comment'" :id="$id" :name="$name" :placeholder="$placeholder" :style="$style" :value="$value" />
    <div class="input-icon-send">
        <x-svg-icon name="send" size="14" color="#fff" />
    </div>
</div>
