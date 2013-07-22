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
                    or attempts to hack into any network where they do not have authority to do so. 
                </div>
                <div id="page-footer-2">
                    <div class="container row">
                        <div class='right'>
                            Become a fan<br/>89,277
                        </div>
                        Current Version: <a href='https://github.com/HackThis/hackthis.co.uk/tree/<?=$app->version;?>'><?=$app->version;?></a><br/>
                        Copyright © 2008 - <?=date('Y');?> <a href='//www.hackthis.co.uk'>hackthis.co.uk</a> || Icons: <a href='http://fortawesome.github.io/Font-Awesome/'>Font Awesome</a>
                    </div>
                </div>
            </footer>
        </div>

        <script src="//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
        <script>window.jQuery || document.write('<script src="/files/js/jquery-1.9.1.min.js"><\/script>')</script>
        <?= $minifier->load("js"); ?>

        <script>
            var _gaq=[['_setAccount','UA-XXXXX-X'],['_trackPageview']];
            (function(d,t){var g=d.createElement(t),s=d.getElementsByTagName(t)[0];
            g.src='//www.google-analytics.com/ga.js';
            s.parentNode.insertBefore(g,s)}(document,'script'));
        </script>
    </body>
</html>