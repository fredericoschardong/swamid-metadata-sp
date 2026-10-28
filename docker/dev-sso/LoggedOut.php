<?php

# Development stand-in for the IdP login page: shown for /admin after /Shibboleth.sso/Logout.
http_response_code(403);
?>
<!doctype html>
<html lang="en">
<head><meta charset="utf-8"><title>Logged out</title></head>
<body style="font-family: sans-serif; margin: 3rem auto; max-width: 30rem">
  <h3>You are logged out</h3>
  <p>This is the development stand-in for the Shibboleth login (docker/dev-sso).</p>
  <p><a href="/Shibboleth.sso/Login?target=/admin/">Log in again</a> &middot; <a href="/">Home</a></p>
</body>
</html>
