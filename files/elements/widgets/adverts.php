<?php
    if (!$app->user->loggedIn || !$app->user->donator):
?>
                    <article class="widget adverts">
                        <h2>Adverts</h2>
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
?>