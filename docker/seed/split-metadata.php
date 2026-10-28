<?php

# Split a metadata aggregate into one XML file per EntityDescriptor, as importAndValidateXML.php expects.
# Usage: php split-metadata.php <aggregate.xml> <output-dir> [registrationAuthority]
# With a registrationAuthority only entities registered by it (or without any registration info) are written.

const MD_NS = 'urn:oasis:names:tc:SAML:2.0:metadata';
const MDRPI_NS = 'urn:oasis:names:tc:SAML:metadata:rpi';

if ($argc < 3) {
  fwrite(STDERR, "Usage: split-metadata.php <aggregate.xml> <output-dir>\n");
  exit(1);
}

$source = new DOMDocument();
$source->preserveWhiteSpace = false;
if (!$source->load($argv[1])) {
  fwrite(STDERR, "Can not parse $argv[1]\n");
  exit(1);
}
if (!is_dir($argv[2]) && !mkdir($argv[2], 0775, true)) {
  fwrite(STDERR, "Can not create $argv[2]\n");
  exit(1);
}

$authority = $argv[3] ?? '';
$count = 0;
foreach ($source->getElementsByTagNameNS(MD_NS, 'EntityDescriptor') as $entity) {
  if ($authority !== '') {
    $info = $entity->getElementsByTagNameNS(MDRPI_NS, 'RegistrationInfo')->item(0);
    if ($info && $info->getAttribute('registrationAuthority') !== $authority) {
      continue;
    }
  }
  $doc = new DOMDocument('1.0', 'UTF-8');
  $doc->appendChild($doc->importNode($entity, true));
  file_put_contents(sprintf('%s/%05d.xml', $argv[2], ++$count), $doc->saveXML());
}
printf("%d entities written to %s\n", $count, $argv[2]);
