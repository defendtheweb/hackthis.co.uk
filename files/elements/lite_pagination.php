<?php
if ($pagination->current != 0 || !$pagination->last):
?>
    <div class="pagination lite-pagination">
    <?php if ($pagination->current != 0): ?>
        <a href='<?=$pagination->root.($pagination->current-1);?>'>PREV</a>
    <?php
        endif;
        if ($pagination->current != 0 && !$pagination->last)
            echo ' · ';
        if (!$pagination->last):
    ?>
        <a href='<?=$pagination->root.($pagination->current+1);?>'>NEXT</a>
    <?php endif; ?>
    </div>
<?php
endif;
?>