<?php
    array_push($minifier->custom_js, 'wysiwyg.js');
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
        <li><a href='#' class='hint--top' data-tag='float' data-value='left' data-hint='Float left'><i class='icon-insertpictureleft'></i></a></li>
        <li class='seperator'><a href='#' class='hint--top' data-tag='float' data-value='right' data-hint='Float right'><i class='icon-insertpictureright'></i></a></li>

        <li><a href='#' class='hint--top' data-tag='list' data-extra='*' data-hint="Bullet list"><i class='icon-list'></i></a></li>
        <li class='seperator'><a href='#' class='hint--top' data-tag='list' data-extra='*' data-value='1' data-hint='Numbered list'><i class='icon-numbered-list'></i></a></li>

        <li><a href='#' class='hint--top' data-hint='Insert link' data-tag='url'><i class='icon-link'></i></a></li>
        <li><a href='#' class='hint--top' data-hint='Insert image' data-tag='img'><i class='icon-image'></i></a></li>
        <li class='seperator'><a href='#' class='hint--top show-upload' data-hint='Upload image'><i class='icon-upload'></i></a></li>

        <li><a href='#' class='hint--top' data-hint='Quote' data-tag='quote' data-value='author'><i class='icon-quote'></i></a></li>
        <li class='seperator'><a href='#' class='hint--top' data-hint='Code' data-tag='code'><i class='icon-console'></i></a></li>

        <li><a href='#' class='hint--top' data-hint='youtube' data-hint='YouTube' data-tag='youtube'><i class='icon-youtube'></i></a></li>
        <li class='seperator'><a href='#' class='hint--top' data-hint='Vimeo' data-tag='vimeo'><i class='icon-vimeo2'></i></a></li>

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
        <li><a href='#' class='icon-heart' data-value='<3'></a></li>
    </ul>

    <div class='overlay-upload'>
        <div class='form'>
            By uploading a file you certify that you have the right to distribute this picture and that it does not violate the <a target="_blank" href="/terms.php">Terms of Service</a>.
            <br><br>
            <label for="image">Image:</label><br>
            <input type="file" class="file" id="image" name="image" />
            <input type="button" value="Upload" class="submit-upload button" />
        </div>
    </div>

    <textarea name='<?=isset($wysiwyg_name)?htmlspecialchars($wysiwyg_name):'body';?>' class='editor suggest hide-shadow' <?=isset($wysiwyg_placeholder)?"placeholder='{$wysiwyg_placeholder}'":'';?>><?=isset($wysiwyg_text)?htmlspecialchars($wysiwyg_text):'';?></textarea>
    <div class='preview'>&nbsp;</div>
</div>
<input type="checkbox" id="enter" name="enter" <?=(isset($_COOKIE["wysiwygEnter"]) && $_COOKIE["wysiwygEnter"] == 'true')?'checked':'';?>/>
<label for="enter">Press Enter to send</label>