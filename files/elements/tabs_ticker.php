    <ul class='tabs'>
        <li <?=$tab=='top'?"class='active'":'';?>><a href='?top'>Top</a></li>
        <li <?=$tab=='latest'?"class='active'":'';?>><a href='?latest'>Latest</a></li>
        <li <?=$tab=='favourites'?"class='active'":'';?>><a href='?favourites'>Favourites</a></li>
        <li <?=$tab=='submissions'?"class='active'":'';?>><a href='?submissions'>Submissions</a></li>
        <li class='right ticker-add-link'><a href='#'>Submit link</a></li>
<?php
    if ($app->user->admin_pub_priv):
?>
        <li class='right <?=$tab=='admin'?"active":'';?>'><a href='?admin'>Admin</a></li>
<?php
    endif;
?>
    </ul>
    <div class='row ticker-add'>
        <form class='span_12'>
            <label for="title">Title:</label> <input type="text" name="title" id="title"/><br/>
            <label for="url">Link:</label> <input type="text" name="url" id="url" value="http://"/><br/>
            <span class='success'><i class='icon-good'></i> Link submitted</span>
            <span class='error'><i class='icon-error'></i> Error submitting link</span>
            <input type="submit" class="button" value="Add"/>
        </form>
    </div>