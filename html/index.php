<?php
    require_once('header.php');
?>

	    <!-- Add your site or application content here -->
	    <p>Hello world! This is HTML5 Boilerplate.</p>


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