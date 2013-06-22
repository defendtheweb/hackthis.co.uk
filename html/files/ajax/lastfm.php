<?php
    header('Content-Type: application/json');
    
    $uri = "http://ws.audioscrobbler.com/2.0/?method=user.getrecenttracks&user={$user}&limit=5&api_key=829d9a823fab4ac2fda03a7f1afaa582&format=json";
?>