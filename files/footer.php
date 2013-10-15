                </article>
<?php include("elements/sidebar.php"); ?>
            </section>
        </div>
      </div>
        <div id="page-footer">
            <footer>
                <div class="container row">
                    <span class='strong'>Disclaimer:</span><br/>
                    The owner of this site does not accept responsibility for the actions of any users of this site.
                    Users are solely responsible for any content that they place on this site This site does not encourage or condone any illegal activity,
                    or attempts to hack into any network where they do not have authority to do so. <br/>
                    <br/>
                    <a href='/privacy'>Privacy</a> &middot; <a href='/terms'>Terms of Use</a> &middot; <a href='/contact'>Contact Us</a> 
                </div>
                <div id="page-footer-2">
                    <div class="container row">
                        <div class='right'>
                            <a class='hide-external' href='https://www.facebook.com/hackthisuk'><i class='icon-facebook'></i></a>
                            <a class='hide-external' href='https://twitter.com/hackthisuk'><i class='icon-twitter'></i></a>
                            <a class='hide-external' href='http://feeds.feedburner.com/hackthisuk'><i class='icon-feed'></i></a>
                            <!-- <a class='hide-external' href='https://plus.google.com/u/1/111391128364055041923'><i class='icon-google-plus'></i></a>
                            <a class='hide-external' href='https://www.youtube.com/channel/UCCfqc6ZuCSLdLMKYJl5hLyg'><i class='icon-youtube'></i></a> -->
                        </div>
                        Current Version: <a href='https://github.com/HackThis/hackthis.co.uk/tree/<?=trim($app->version);?>'><?=trim($app->version);?></a><br/>
                        Copyright © 2008 - <?=date('Y');?> <a href='//www.hackthis.co.uk'>hackthis.co.uk</a> || Icons: <a href='http://fortawesome.github.io/Font-Awesome/'>Font Awesome</a>
                    </div>
                </div>
            </footer>
        </div>

        <?= $minifier->load("js"); ?>
<?php
        if (isset($currentLevel) && isset($currentLevel->data['code']->pos5)) {
            echo '        '.$currentLevel->data['code']->pos5 . "\n";
        }
?>

        <script>
            var _gaq=[['_setAccount','UA-XXXXX-X'],['_trackPageview']];
            (function(d,t){var g=d.createElement(t),s=d.getElementsByTagName(t)[0];
            g.src='//www.google-analytics.com/ga.js';
            s.parentNode.insertBefore(g,s)}(document,'script'));
        </script>
    </body>
</html>