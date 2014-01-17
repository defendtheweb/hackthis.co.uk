<?php
    if (!$app->user->loggedIn || !$app->user->donator):
        if (!$app->user->loggedIn && defined('LANDING_PAGE') && LANDING_PAGE):
?>
                    <article class="widget adverts">
                        <div class="center">
                            <script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
                            <!-- landing page -->
                            <ins class="adsbygoogle"
                                 style="display:inline-block;width:336px;height:280px"
                                 data-ad-client="ca-pub-1120564121036240"
                                 data-ad-slot="7541400418"></ins>
                            <script>
                            (adsbygoogle = window.adsbygoogle || []).push({});
                            </script>
                        </div>
                    </article>
<?php
        else:
?>
                    <article class="widget adverts">
                        <h1>Adverts</h1>
                        <div class="center">
                            <script type="text/javascript"><!--
                                google_ad_client = "ca-pub-1120564121036240";
                                /* Sidebar */
                                google_ad_slot = "5769452815";
                                google_ad_width = 200;
                                google_ad_height = 200;
                                //-->
                            </script>
                            <script type="text/javascript" src="https://pagead2.googlesyndication.com/pagead/show_ads.js"></script>
                        </div>
                    </article>
<?php
        endif;
    endif;
?>