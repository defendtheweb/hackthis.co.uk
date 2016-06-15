<?php
    $custom_css = array('articles.scss', 'highlight.css', 'faq.scss');
    $custom_js = array('articles.js', 'highlight.js');
    define("_SIDEBAR", false);
    define("PAGE_PUBLIC", true);

    require_once('init.php');
    $app->page->title = 'Code of Conduct';
    $app->page->canonical = 'http://www.hackthis.co.uk/conduct';
    require_once('header.php');
?>

<section class="row">
    
    <div class="col span_24 article-main">
        <article class='bbcode body' itemscope itemtype="http://schema.org/Article">
            <header class='clearfix'>
                <div class='width-center'>
                    <h1 itemprop="name">Code of Conduct</h1>
                    <div class='meta'>
                        <i class="icon-clock"></i> September 7, 2014
                        <i class="icon-user"></i> <a rel='author' itemprop='author' href='/user/flabbyrabbit'>flabbyrabbit</a>
                    </div>
                </div>
            </header>
            <div itemprop='articleBody' class='articleBody width-center'>
                <p>
                    As a HackThis member:
                </p>

                <ul>
                    <li>Answers to all levels will be my own work, unless otherwise instructed.</li>
                    <li>I will not share answers to any level.</li>
                    <li>I will not participate in, condone or encourage unlawful activity, including any breach of copyright, defamation, or contempt of court.</li>
                    <li>I will not ‘spam’ other HackThis!! members by posting the same message multiple times or posting a message that is unrelated to the discussion.</li>
                    <li>As the HackThis!! community’s first language is English, I will always post contributions in English to enable all to understand</li>
                    <li>I will not use HackThis!! to advertise products or services for profit or gain.</li>
                    <li>I will not use racist, sexist, homophobic, sexually explicit or abusive terms or images, or swear words or language that is likely to cause offence.</li>
                </ul>
            </div>
        </article>
    </div>
</section>

<?php  
    require_once('footer.php');
?>
