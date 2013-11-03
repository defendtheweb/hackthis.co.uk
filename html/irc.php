<?php
    define("PAGE_PUBLIC", true);

    $custom_css = array('faq.scss');
    require_once('init.php');
    $app->page->title = 'IRC';
    require_once('header.php');
?>

    <h1>IRC</h1>
    <p>
        <h3>Connecting to IRC</h3>
        For information about IRC please read our <a href='/articles/introduction-to-irc'>Introduction to IRC</a><br/><br/>
        <strong>Server:</strong> irc.hackthis.co.uk<br/>
        <strong>Port:</strong> 6667 (6697 SSL)<br/>
        <strong>Channel:</strong> #hackthis<br/>
    </p>
    <p>
        <h3>Web Client</h3>
        <iframe src="https://kiwiirc.com/client/irc.hackthis.co.uk/?nick=<?=$app->user->loggedIn?$app->user->username:'cat_?';?>&theme=cli#hackthis" style="border:0; width:100%; height:450px;"></iframe>
    </p>
<?php  
    require_once('footer.php');
?>