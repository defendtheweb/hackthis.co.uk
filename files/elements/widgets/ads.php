<?php
	$ads = array(
			array('donate', 'https://www.hackthis.co.uk/donator.php'),
			array('facebook', 'https://www.facebook.com/hackthisuk'),
			array('twitter', 'https://twitter.com/hackthisuk')
		  );


	$id = array_rand($ads);
	$image = $ads[$id][0];
	$link = $ads[$id][1];
?>

                   <article class="widget ad">
                        <div class="center">
                            <a href='<?=$link;?>' class='hide-external'>
                            	<img src='/files/images/ads/<?=$image;?>.png'/>
                            </a>
                        </div>
                    </article>