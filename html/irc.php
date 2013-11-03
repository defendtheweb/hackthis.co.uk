<?php
    define("PAGE_PUBLIC", true);

    $custom_css = array('faq.scss');
    require_once('init.php');
    $app->page->title = 'IRC';
    require_once('header.php');
?>

    <h1>IRC</h1>
    <iframe src="https://kiwiirc.com/client/irc.hackthis.co.uk/?nick=<?=$app->user->loggedIn?$app->user->username:'cat_?';?>&theme=cli#hackthis" style="border:0; width:100%; height:450px;"></iframe>
<?php  
    require_once('footer.php');
?>