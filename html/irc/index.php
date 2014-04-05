<?php
    define("PAGE_PUBLIC", true);

    require_once('init.php');
    $app->page->title = 'IRC';
    require_once('header.php');
?>

    <h1>IRC</h1>
    <p>
        <iframe src="https://kiwiirc.com/client/irc.macak.co.uk:+6697/?nick=<?=$app->user->loggedIn?$app->user->username:'cat_?';?>&theme=cli#hackthis" style="border:0; width:100%; height:450px;"></iframe>
    </p>
    <p>
        <h3>Connecting to IRC</h3>
        For information about IRC please read our <a href='/articles/introduction-to-irc'>Introduction to IRC</a><br/><br/>
        <strong>Server:</strong> irc.macak.co.uk<br/>
        <strong>Port:</strong> 6697 SSL Only<br/>
        <strong>Channel:</strong> #hackthis<br/>
    </p>
<?php  
    require_once('footer.php');
?>