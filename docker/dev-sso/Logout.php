<?php

# Development stand-in for Shibboleth's /Shibboleth.sso/Logout: remembers the "logout" in a cookie,
# docker/entrypoint.sh stops injecting the fake login attributes while it is set.
setcookie('dev_logged_out', '1', array('path' => '/', 'httponly' => true, 'samesite' => 'Lax'));
$return = $_GET['return'] ?? '/';
# only local paths, never an absolute URL (open redirect)
if (!preg_match('~^/(?!/)~', $return)) {
  $return = '/';
}
header('Location: ' . $return);
