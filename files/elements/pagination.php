<div class="pagination">
	<ul>
<?php
	if (!function_exists('print_item')) {
		function print_item($i, $p) {
			if ($i != $p->current)
				echo "<li><a href='".$p->root.$i."'>" . $i . "</a></li>";
			else
				echo "<li><a href='".$p->root.$i."' class='current'>" . $i . "</a></li>";
		}
	}

	if ($pagination->current > 1)
		echo "<li class='left'><a href='".$pagination->root.($pagination->current-1)."' rel='prev'>&lt; PREV</a></li>";

	if ($pagination->count > 10) {
		if ($pagination->current <= 6) {
			for($i = 1; $i <= 8; $i++) {
				print_item($i, $pagination);
			}
			echo "		<li class='inactive'>...</li>\n";
			for($i = $pagination->count-2; $i <= $pagination->count; $i++) {
				print_item($i, $pagination);
			}
		} else if ($pagination->current > $pagination->count - 6) {	
			for($i = 1; $i <= 3; $i++) {
				print_item($i, $pagination);
			}
			echo "		<li class='inactive'>...</li>\n";
			for($i = $pagination->count-7; $i <= $pagination->count; $i++) {
				print_item($i, $pagination);
			}
		} else {
			for($i = 1; $i <= 3; $i++) {
				print_item($i, $pagination);
			}
			echo "		<li class='inactive'>...</li>\n";
			for($i = $pagination->current-2; $i <= $pagination->current+2; $i++) {
				print_item($i, $pagination);
			}			
			echo "		<li class='inactive'>...</li>\n";
			for($i = $pagination->count-2; $i <= $pagination->count; $i++) {
				print_item($i, $pagination);
			}
		}
	} else {
		for($i = 1; $i <= $pagination->count; $i++) {
			print_item($i, $pagination);
		}
	}
	if ($pagination->current < $pagination->count)
		echo "<li class='right'><a href='".$pagination->root.($pagination->current+1)."' rel='next'>NEXT &gt;</a></li>";
?>
	</ul>
</div>
