<?php
    $config['domain'] = 'example.org';
    $config['http-cache'] = 'http://example.org';

    // Site variables
    $config['path'] = realpath($_SERVER["DOCUMENT_ROOT"] . '/../');

    // Database configuration
    $config['db']['driver'] = 'mysql';
    $config['db']['host'] = 'localhost';
    $config['db']['username'] = 'root';
    $config['db']['password'] = 'pass';
    $config['db']['database'] = 'hackthis';

    // SMTP configuration
    $config['smtp']['host'] = '';
    $config['smtp']['port'] = '';
    $config['smtp']['username'] = '';
    $config['smtp']['password'] = '';

    $config['git'] = 'anystring';
    
    $config['facebook']['secret'] = '';
    $config['facebook']['public'] = '';
    $config['facebook']['token'] = '';

    $config['twitter']['secret'] = '';
    $config['twitter']['public'] = '';

    $config['lastfm']['public'] = '';

    $config['socket']['address'] = '';
    $config['socket']['key'] = '';

    $config['ssga-ua'] = '';
?>
