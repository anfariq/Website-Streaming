<?php
require_once 'vendor/autoload.php';

$clientID = '940542557-btokl25f0cv9algvt4t30r4qbjntl5ak.apps.googleusercontent.com';
$clientSecret = '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';
$redirectURI = 'https://garcia.my.id/sign_in.php';

// CREATE CLIENT REQUEST TO GOOGLE
$client = new Google_Client();
$client->setClientId($clientID);
$client->setClientSecret($clientSecret);
$client->setRedirectUri($redirectURI);
$client->addScope('profile');
$client->addScope('email');