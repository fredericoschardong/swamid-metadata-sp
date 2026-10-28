#!/bin/bash
# Loads the federation metadata into the (development) database so a fresh clone has data to work with.
#  1. downloads the federation aggregate (federation.metadata_url) to federation.metadata_main_path
#  2. updateExternalEntities.php -> ExternalEntities table
#  3. importAndValidateXML.php for the EntityDescriptors registered by this federation
#     (federation.metadata_registration_authority; all of them with SEED_ALL=1) -> Entities table
#     (only if the table is empty, or when SEED_FORCE=1)
# Disable with SEED_METADATA=0. Re-run any time: docker compose run --rm seed
set -u

if [ "${SEED_METADATA:-1}" != "1" ]; then
  echo "SEED_METADATA is not 1, skipping"
  exit 0
fi

# Reads a value from the active config.php (this also creates the database tables on first run)
php_run() {
  php -r 'require "/var/www/html/vendor/autoload.php"; $c = new metadata\Configuration(); '"$1"
}

URL=$(php_run 'echo $c->getFederation()["metadata_url"] ?? "";')
FILE=$(php_run 'echo $c->getFederation()["metadata_main_path"];')
FEED=$(php_run 'echo $c->getFederation()["localFeed"];')
AUTHORITY=$(php_run 'echo $c->getFederation()["metadata_registration_authority"];')
[ "${SEED_ALL:-0}" = "1" ] && AUTHORITY=""

if [ ! -s "$FILE" ] || [ "${SEED_FORCE:-0}" = "1" ]; then
  if [ -z "$URL" ]; then
    echo "No federation.metadata_url in config.php and $FILE does not exist, nothing to load"
    exit 0
  fi
  echo "Downloading $URL"
  if ! curl -fsSL --retry 3 -m 120 -o "$FILE.tmp" "$URL"; then
    echo "Download failed, skipping seed. Put the XML at $FILE and run: docker compose run --rm seed" >&2
    rm -f "$FILE.tmp"
    exit 0
  fi
  mv "$FILE.tmp" "$FILE"
fi

echo "Updating external entities from $FILE"
php /var/www/scripts/updateExternalEntities.php > /dev/null

EXISTING=$(php_run 'echo $c->getDb()->query("SELECT COUNT(*) FROM Entities")->fetchColumn();')
if [ "$EXISTING" != "0" ] && [ "${SEED_FORCE:-0}" != "1" ]; then
  echo "Entities table already has $EXISTING rows, skipping import (SEED_FORCE=1 to reimport)"
  exit 0
fi

WORK=$(mktemp -d)
trap 'rm -rf "$WORK"' EXIT
php /usr/local/share/seed/split-metadata.php "$FILE" "$WORK" "$AUTHORITY"

TOTAL=$(ls "$WORK" | wc -l)
N=0
for f in "$WORK"/*.xml; do
  N=$((N + 1))
  php /var/www/scripts/importAndValidateXML.php "$f" "$FEED" > /dev/null 2>&1 || echo "Failed to import $f" >&2
  if [ $((N % 50)) -eq 0 ]; then echo "Imported $N/$TOTAL"; fi
done
echo "Done: imported $N entities into feed '$FEED'"
