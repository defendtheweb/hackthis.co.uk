<?php
    require_once('header.php');
?>
		<section class='news'>
			<article>
				<header>
					<h1><a href='/'>New SQLi Levels</a></h1>
					<time pubdate datetime="2009-10-10T19:10-08:00">30/04/2013</time> - <a href='/user/flabbyrabbit'>flabbyrabbit</a>
					<a href='/' class='right'>1 comment</a>
				</header>
				<p>A new group has been added to the levels section of the site. The new SQLi section will focus on common SQL injection attacks. Currently there are only two levels online but more will be coming soon. SQLi is one of the most common real world attacks. When found a vulnerability of this kind can allow an attacker access to large amounts of personal information as well as leverage to form further attacks. Hopefully these levels will give you an introduction and understanding of how both SQL works and it's pitfalls.</p>
				<p>As always if you find yourself stuck on any part of the site head over to the forum where you can find more information and ask your own questions.</p>
				<div class="col span_4">
					hello
				</div>
				<div class="col span_4">
					hello
				</div>
				<div class="col span_4 last">
					hello
				</div>
			</article>

		<section>

	    <?php
	        if ($user->loggedIn) {
	            echo "yo";
	            if (isset($_GET['logout']) && $user->logout())
	                echo ", BYEEEEE";
	        } else {
	            $response = $user->login('flabbyrabbit', 'cat');
	            if ($response) {
	                echo "Welcome, " . $user;
	            } else {
	                echo "Invalid details";
	            }
	        }
	    ?>

<?php
    require_once('footer.php');
?>