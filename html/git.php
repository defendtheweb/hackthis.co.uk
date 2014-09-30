<?php
    $custom_css = array('faq.scss');
    require_once('init.php');
    $app->page->title = 'Version history';
    require_once('header.php');

    require_once('vendor/class.slimdown.php');
?>
    <a class="button right hide-external" href="https://github.com/HackThis/NexBot">HackThis/NexBot</a>
    <a class="button right hide-external" href="https://github.com/HackThis/hackthis.co.uk">HackThis/hackthis.co.uk</a>
    <a class="button right hide-external" href="https://github.com/HackThis"><i class='icon-github-2'></i> HackThis</a>

    <h1>Get involved</h1>
    Found a bug? Wish the site had feature x? The source code for all HackThis!! projects can be found on <a href='https://github.com/HackThis'>GitHub</a>. We encourage you to fork the code and see if you can implement the fix/feature yourself. If you do develop something that you think would be beneficial, then create pull request and we can include it in the site! All users that submit an approved pull request receive a <span class='medal medal-green'>contributor</span> medal, however small the change may be.<Br/><br/>
    Not a developer? There are a lot of other changes that you could submit, for example spelling and grammatical fixes. We are grateful for even the smallest contribution.
    <br/><br/><br/>

    <h1>Version History</h1>
    <div class='version-history'>

<?php
    $history = file_get_contents('../files/cache/version_history.md');
    echo Slimdown::render($history);
?>
    </div>

<?php
    require_once('footer.php');
?>
