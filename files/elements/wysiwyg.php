<?php
    array_push($minifier->custom_js, 'wysiwyg.js');
    array_push($minifier->custom_js, 'autosuggest.js');
?>

<div class='wysiwyg'>
    <ul class='controls'>
        <li><a href='#' class='strong hint--top' data-tag='b' data-hint="Bold">B</a></li>
        <li><a href='#' class='italic hint--top' data-tag='i' data-hint="Italic">I</a></li>
        <li><a href='#' class='underline hint--top' data-tag='u' data-hint='Underline'>U</a></li>
        <li class='seperator'><a href='#' class='strike hint--top' data-tag='s' data-hint='Strike'>S</a></li>
        <li><a href='#' class='hint--top' data-tag='left' data-hint='Align left'><i class='icon-align-left'></i></a></li>
        <li><a href='#' class='hint--top' data-tag='center' data-hint='Align center'><i class='icon-align-center'></i></a></li>
        <li><a href='#' class='hint--top' data-tag='right' data-hint="Align right"><i class='icon-align-right'></i></a></li>
        <li><a href='#' class='hint--top' data-tag='justify' data-hint='Justify'><i class='icon-align-justify'></i></a></li>
        <li><a href='#' class='hint--top' data-tag='float' data-value='right' data-hint='Float left'><i class='icon-insertpictureleft'></i></a></li>
        <li class='seperator'><a href='#' class='hint--top' data-tag='float' data-value='left' data-hint='Float right'><i class='icon-insertpictureright'></i></a></li>
        <li><a href='#' class='hint--top' data-hint='Insert link' data-tag='url'><i class='icon-link'></i></a></li>
        <li><a href='#' class='hint--top' data-hint='Insert image' data-tag='img'><i class='icon-image'></i></a></li>
        <li class='seperator'><a href='#' class='hint--top' data-hint='Upload image'><i class='icon-upload'></i></a></li>
        <li><a href='#' class='hint--top' data-hint='Quote' data-tag='quote' data-value='author'><i class='icon-chat-2'></i></a></li>
        <li class='seperator'><a href='#' class='hint--top' data-hint='Code' data-tag='code'><i class='icon-console'></i></a></li>
        <li><a href='#' class='hint--top' data-hint='youtube' data-hint='YouTube' data-tag='youtube'><i class='icon-youtube'></i></a></li>
        <li class='seperator'><a href='#' class='hint--top' data-hint='Vimeo' data-tag='vimeo'><i class='icon-vimeo'></i></a></li>
        <li><a href='#' class='hint--top show-smilies' data-hint='Emoticons'><i class='icon-smiley'></i></a></li>

        <li class='right'><a href='#' class='preview-button hint--top' data-hint="Preview"><i class='icon-eye'></i></a></li>
        <li class='right seperator active'><a href='#' class='edit-button hint--top' data-hint='Edit post'><i class='icon-code'></i></a></li>
    </ul>
    <ul class='controls smilies'>
        <li><a href='#' class='icon-happy' data-value=':D'></a></li>
        <li><a href='#' class='icon-smiley' data-value=':)'></a></li>
        <li><a href='#' class='icon-tongue' data-value=':p'></a></li>
        <li><a href='#' class='icon-sad' data-value=':('></a></li>
        <li><a href='#' class='icon-wink' data-value=';)'></a></li>
        <li><a href='#' class='icon-cool' data-value='B)'></a></li>
        <li><a href='#' class='icon-angry' data-value=':@'></a></li>
        <li><a href='#' class='icon-shocked' data-value=':o'></a></li>
        <li><a href='#' class='icon-confused' data-value=':s'></a></li>
        <li><a href='#' class='icon-neutral' data-value=':|'></a></li>
        <li><a href='#' class='icon-wondering' data-value=':/'></a></li>
    </ul>

    <textarea name='body' class='editor suggest' <?=isset($wysiwyg_placeholder)?"placeholder='{$wysiwyg_placeholder}'":'';?>><?=isset($wysiwyg_text)?$wysiwyg_text:'';?></textarea>
    <div class='preview'>&nbsp;</div>
</div>