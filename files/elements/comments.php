<?php
	array_push($minifier->custom_js, 'comments.js');
?>

				<section id="comments" data-id="<?=$comments["id"];?>">
					<h2>Comments</h2>
					<form>
<?php
	if (!$user->loggedIn):
?>
				        <div class='msg msg-error'>
				            <i class='icon-error'></i>
				            You must be logged in to comment
				        </div>
<?php
	endif;
	if (!$user->forum_priv):
?>
				        <div class='msg msg-warning'>
				            <i class='icon-warning'></i>
				            You have been banned from posting comments
				        </div>
<?php
	else:
?>
				        <div class='hide msg msg-good'>
				            <i class='icon-good'></i>
				            Comment submitted
				        </div>
						<?php include('elements/wysiwyg.php'); ?>
						Shift+enter to add a new line
						<input id="comment_submit" type="submit" value="Submit" class="button right" <?php if (!$user->loggedIn) echo "disabled" ?>/>
<?php
	endif;
?>
					</form>
					<br/>
					<div id="comments_container">
						<div class="comments_loading center">
								<img src='/files/images/icons/loading.gif' class='icon'/> Loading comments...
						</div>
					</div>
				</section>
