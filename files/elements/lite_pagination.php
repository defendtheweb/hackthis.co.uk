<div class="lite-pagination">
    <ul>
<?php
    if ($pagination->count > 4) {
        for($i = 1; $i <= 2; $i++) {
            echo "<li><a href='".$pagination->root.$i."'>" . $i . "</a></li>";
        }
        echo "      <li class='inactive'>...</li>\n";
        for($i = $pagination->count-2; $i <= $pagination->count; $i++) {
            echo "<li><a href='".$pagination->root.$i."'>" . $i . "</a></li>";
        }
    } else {
        for($i = 1; $i <= $pagination->count; $i++) {
            echo "<li><a href='".$pagination->root.$i."'>" . $i . "</a></li>";
        }
    }
?>
    </ul>
</div>
