<?php
	$ticker = $app->ticker->get();
?>

<div class='home-module home-module-light home-ticker'>
    <ul>
<?php
	foreach ($ticker AS $item):
?>
    	<li><span class='source'><?=$item->source;?></span><a href='<?=$item->url;?>' target='_blank'><?=$item->text;?></a></li>
<?php
    endforeach;
?>
    </ul>
    <a class='more' href='/ticker.php'><i class='icon-caret-right'></i></a>
</div>