<?php
    define("PAGE_PUBLIC", true);
    $custom_css = array('faq.scss');
    require_once('init.php');
    $app->page->title = 'FAQ';
    require_once('header.php');
?>

    <h1>Frequently Asked Questions</h1>
    <br/>
    <h2>Content</h2>
    <ul class='indent'>
        <li>Forum
            <ul class='indent'>
                <li><a href='#new-thread'>How do I start a thread in the forum‭?</a></li>
                <li><a href='#karma'>What is karma?</a></li>
                <li><a href='#watch'>How do I control which threads I get notifications for?</a></li>
                <li><a href='#report-post'>How do I report bad content within the forum?</a></li>
            </ul>
        </li>
    </ul>

    <br/>
    <h2>Forum</h2>
    <h3 id="new-thread">How do I start a thread in the forum‭?</h3>
    <p>
        A new thread can only be started under a sub-section.‭ ‬If you go to the forum,‭ ‬or even under a section,‭ ‬you will see that the button‭ “‬New thread‭” ‬is not active and if you try to click on it,‭ ‬a message will appear saying that you can only start a new thread after you chose a sub-section.‭
    </p>
    <img src='/files/images/faq_thread.png'/>
    <p>
        After you go to the sub-section in which you want to start your new thread,‭ ‬the‭ “‬New Thread‭” ‬button will become active.‭ ‬Click it and it will take you to the form where you can start your thread.
    </p>
    <h3 id="karma">What is karma?</h3>
    <p>
        Karma is a way to rate users posts within the forum. The karma rating is displayed as a number in the top of left of each forum post. Users with the bronze <a class='medal medal-bronze' href="/medals.php#karma">Karma</a> medal are allowed to upvote posts and only users with the silver <a class='medal medal-silver' href="/medals.php#karma">Karma</a> medal can down vote. You earn the medals by completing levels and being active within the forum. Posts with karma of -3 or less are automatically hidden although this isn't the main role of the karma system, see reporting posts below.
    </p>
    <h3 id="watch">How do I control which threads I get notifications for?</h3>
    <p>
        You have the ability to watch and unwatch any thread in the forum. At the top of each thread your current preference is displayed and can be simply changed by clicking the button. By posting in a thread you automatically start watching that thread.
    </p>
    <h3 id="report-post">How do I report bad content within the forum?</h3>
    <p>
        To the left of every forum post is the authors details, underneath these there should be a "Flag Post" button. If you feel a post contains bad content e.g. level answers, spam, abuse, sole purpose being to sell a product then click the button and it will flag it to a moderator. If a moderator agrees that the content should be removed it will be and the user responsible warned or banned from posting on the site.
    </p>


<?php  
    require_once('footer.php');
?>
