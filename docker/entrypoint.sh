#!/bin/sh
set -e

# Fake Shibboleth attributes for local development (see docker/apache.conf)
if [ -n "$DEV_EPPN" ]; then
  cat > /etc/apache2/conf-available/dev-user.conf <<CONF
# Logged in unless the dev logout cookie is set (see docker/dev-sso)
AliasMatch "^/Shibboleth\.sso/(Login|Logout|LoggedOut)$" /usr/local/share/dev-sso/\$1.php
<Directory /usr/local/share/dev-sso>
  Require all granted
</Directory>
<Location /admin>
<If "%{HTTP_COOKIE} =~ /dev_logged_out=1/">
  Require all denied
  ErrorDocument 403 /Shibboleth.sso/LoggedOut
</If>
<If "%{HTTP_COOKIE} !~ /dev_logged_out=1/">
  SetEnv eduPersonPrincipalName "${DEV_EPPN}"
  SetEnv givenName "${DEV_GIVEN_NAME:-Dev}"
  SetEnv sn "${DEV_SURNAME:-User}"
  SetEnv displayName "${DEV_GIVEN_NAME:-Dev} ${DEV_SURNAME:-User}"
  SetEnv mail "${DEV_MAIL:-$DEV_EPPN}"
  SetEnv Shib-Identity-Provider "https://idp.dev.localhost/idp"
  SetEnv Shib-Session-ID "_dev-session"
  SetEnv eduPersonScopedAffiliation "employee@example.org;member@example.org"
  SetEnv eduPersonAffiliation "employee;member"
</If>
</Location>
CONF
  a2enconf dev-user >/dev/null
  echo "WARNING: DEV_EPPN set, /admin is open and logged in as ${DEV_EPPN} (log out: /Shibboleth.sso/Logout, back in: /Shibboleth.sso/Login)" >&2
fi

# admin/mds.php reads an SQLite DB that, in production, is filled by the MDS service. Create an empty
# one with the expected tables so the page works (empty) in development.
MDS_DB=/opt/metadata/MDS.db
if [ ! -f "$MDS_DB" ]; then
  php -r '
    $db = new PDO("sqlite:" . $argv[1]);
    $db->exec("
      CREATE TABLE hosts (id INTEGER PRIMARY KEY, ip TEXT, name TEXT);
      CREATE TABLE softwares (id INTEGER PRIMARY KEY, name TEXT);
      CREATE TABLE feeds (id INTEGER PRIMARY KEY, name TEXT);
      CREATE TABLE hostSoftware (hosts_id INTEGER, softwares_id INTEGER, lastSeen TEXT);
      CREATE TABLE hostFeed (hosts_id INTEGER, feeds_id INTEGER, lastSeen TEXT);
    ");
  ' "$MDS_DB"
  chown www-data:www-data "$MDS_DB"
fi

exec docker-php-entrypoint "$@"
