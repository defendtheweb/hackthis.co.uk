                </article>
<?php include("elements/sidebar.php"); ?>
            </section>
        </div>
        <div id="page-footer">
            <footer class="container row">
                Insert footer stuff here.
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