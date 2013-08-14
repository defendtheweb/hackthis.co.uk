<?php
    // Site variables
    $config['path'] = realpath($_SERVER["DOCUMENT_ROOT"] . '/../');

    // Database configuration
    $config['db']['driver'] = 'mysql';
    $config['db']['host'] = 'localhost';
    $config['db']['username'] = 'root';
    $config['db']['password'] = 'pass';
    $config['db']['database'] = 'hackthis';

    $config['git'] = 'anystring';
    
    $config['facebook']['secret'] = '';
    $config['facebook']['public'] = '';

    $config['twitter']['secret'] = '';
    $config['twitter']['public'] = '';

    $config['lastfm']['public'] = '';
?>