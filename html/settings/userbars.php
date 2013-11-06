<?php
    $custom_css = array('settings.scss');
    $custom_js = array('highlight.js');
    require_once('init.php');

    $app->page->title = 'Settings - Userbar';

    require_once('header.php');

    $tab = 'userbars';
    include('elements/tabs_settings.php');
?>

    <h1>Userbars</h1>
    <p>Userbars are small graphics which you can use in your signature for any forum. Tell the world about HackThis!!</p>
    <div class='row'>
        <div class='col span_12'>
            <img src="/user/userbar.png" />
            <div class="bbcode_code small">
                <pre class="bbcode_code_body prettyprint">[url=https://www.hackthis.co.uk?ref=<?=$app->user->uid;?>][img]https://www.hackthis.co.uk/user/userbar.png[/img][/url]</pre>
            </div>
        </div>
        <div class='col span_12'>
            <img src="/user/<?=$app->user->username;?>/userbar.png" />
            <div class="bbcode_code small">
                <pre class="bbcode_code_body prettyprint">[url=https://www.hackthis.co.uk?ref=<?=$app->user->uid;?>][img]https://www.hackthis.co.uk/user/<?=$app->user->username;?>/userbar.png[/img][/url]</pre>
            </div>
        </div>
    </div>
<?php
    require_once('footer.php');
?>