<div class='level-header center'>
<?php if (isset($level->level_before_uri)): ?>
	<a class='left previous-level' href='<?=$level->level_before_uri;?>'><i class='icon-caret-left'></i></a>
<?php else: ?>
	<span class='left previous-level dark'><i class='icon-caret-left'></i></span>
<?php 
	endif;
	if (isset($level->level_after_uri) && ($level->group != 'main' || $level->completed)):
?>
	<a class='right next-level' href='<?=$level->level_after_uri;?>'><i class='icon-caret-right'></i></a>
<?php elseif(isset($level->level_after_uri) && $level->group == 'main'): ?>
	<span class='right next-level dark hint--left' data-hint="You must complete main levels in order,&#10;but you can attempt any other level."><i class='icon-caret-right'></i></span>
<?php else: ?>
	<span class='right next-level dark'><i class='icon-caret-right'></i></span>
<?php endif; ?>

	<h1 class='no-margin'><?=ucwords($level->title);?></h1>
	<span class='dark'>Attempts: <?=$level->attempts;?>
<?php if ($level->completed): ?>
		&middot; Duration: <?=$app->utils->timeSince($level->started, true, $level->completed_time);?>
<?php endif; ?>
	</span>
	<span class='hint--top' data-hint="This information is not public and only reflects&#10;the first time you completed the level."><i class='icon-info'></i></span><br/>

	<span class='strong <?=$level->completed?'green':'red';?>'><?=$level->completed?'Completed':'Incomplete';?></span><br/>


<?php
	if (isset($level->attempt)) {
		echo "<br/>";
		if ($level->attempt === true)
			$app->utils->message('Level complete', 'good');
		else
			$app->utils->message('Invalid details');
	}
?>
</div>