<?php
    $custom_css = array('settings.scss');
    require_once('init.php');

    $app->page->title = 'Settings - Userbar';

    require_once('header.php');

    $tab = 'userbars';
    include('elements/tabs_settings.php');
?>

    <h1>Userbars</h1>
    <div class='row'>
        <div class='col span_12'>
            <img src="/user/<?=$app->user->username;?>/userbar" />
            <div class="bbcode_code small">
                <pre class="bbcode_code_body prettyprint">[url=https://hackthis.co.uk?<?=$app->user->username;?>][img]https://hackthis.co.uk/user/<?=$app->user->username;?>/userbar[/img][/url]</pre>
            </div>
        </div>
<?php
    for ($i = 8; $i >= 0; $i--):
        if ($i % 2):
?>
    </div>
    <div class='row'>
<?php
        endif;
?>
    <div class='col span_12'>
        <img src="/user/<?=$app->user->username;?>/userbar?display=<?=$i;?>" />
        <div class="bbcode_code small">
            <pre class="bbcode_code_body prettyprint">[url=https://hackthis.co.uk?<?=$app->user->username;?>][img]https://hackthis.co.uk/user/<?=$app->user->username;?>/userbar?display=<?=$i;?>[/img][/url]</pre>
        </div>
    </div>
<?php
    endfor;
?>
    </div>
<?php
    require_once('footer.php');
?>