<?php

# Development stand-in for Shibboleth's login: clears the fake "logout" cookie.
setcookie('dev_logged_out', '', array('expires' => 1, 'path' => '/'));
$target = $_GET['target'] ?? '/admin/';
if (!preg_match('~^/(?!/)~', $target)) {
  $target = '/admin/';
}
header('Location: ' . $target);
