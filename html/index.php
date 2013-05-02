<?php
	require_once('init.php');

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