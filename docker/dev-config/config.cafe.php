<?php

# DEVELOPMENT configuration for CAFe (Comunidade Acadêmica Federada, RNP).
# Values come from CAFe's published metadata (https://ds.cafe.rnp.br/metadata/cafe-metadata.xml) and
# RNP's help center (https://ajuda.rnp.br/cafe).

$db = array(
  'name'        => getenv('DB_NAME') ?: 'metadata',
  'servername'  => getenv('DB_HOST') ?: 'mariadb',
  'username'    => getenv('DB_USER') ?: 'metadata_admin',
  'password'    => getenv('DB_PASSWORD') ?: 'adminpwd', # NOSONAR
);

# Development: mail goes to the smtp4dev container (web UI on http://localhost:8025), never leaves the machine
$smtp = array(
  'host'        => getenv('SMTP_HOST') ?: 'smtp4dev',
  'from'        => 'metadata@example.org',               # dev sender, use a real RNP address in production
  'fromName'    => 'CAFe - Metadata',
  'replyTo'     => 'atendimento@rnp.br',                 # RNP Service Desk
  'replyName'   => 'RNP Service Desk',
  'sendOut'     => true,
);

$mode = getenv('APP_MODE') ?: 'Lab';
$baseURL = getenv('BASE_URL') ?: 'http://localhost:8080/';

# ADMIN_USERS: comma separated list of eduPersonPrincipalNames that get admin level (20)
$userLevels = array();
foreach (array_filter(array_map('trim', explode(',', getenv('ADMIN_USERS') ?: ''))) as $user) {
  $userLevels[$user] = 20;
}

$federation = array(
  'displayName' => 'CAFe',
  'longName' => 'Comunidade Acadêmica Federada (CAFe)',
  'htmlIcons' => '
      <link rel="icon" type="image/png" sizes="32x32" href="/images/favicon-32x32.png">
      <link rel="icon" type="image/png" sizes="16x16" href="/images/favicon-16x16.png">
      <link rel="shortcut icon" href="/images/favicon.ico">',
  'noAccountHtml' => 'contact the identity provider of your institution',
  'localFeed' => 'cafe',
  'eduGAINFeed' => 'cafe-edugain',
  'aboutURL' => 'https://www.rnp.br/servicos/cafe',
  'contactURL' => 'https://www.rnp.br/contato/',
  'toolName' => 'CAFe SAML WebSSO metadata administration tool',
  'teamName' => 'RNP Service Desk',
  'teamMail' => 'atendimento@rnp.br',
  'logoURL' => '/swamid-logo-2-100x115.png',             # TODO: replace with the CAFe logo (add file to web/html)
  'logoWidth' => 55,
  'logoHeight' => 63,
  'languages' => array('en'),                            # the UI only has sv and en texts

  # Where docker/seed/seed.sh downloads the federation aggregate from (docker only, not read by the app)
  'metadata_url' => 'https://ds.cafe.rnp.br/metadata/cafe-metadata.xml',
  # Local copy of the aggregate (used by updateExternalEntities.php)
  'metadata_main_path' => '/opt/metadata/cafe-metadata.xml',
  'metadata_registration_authority_exclude' => array(),
  'metadata_registration_authority' => 'http://cafe.rnp.br',       # NOSONAR Must match registrationAuthority in the metadata
  'metadata_registration_policy' => 'https://svn.cafe.rnp.br/repos/CAFe/termos_politicas_CAFe/mrps.pdf',

  'rulesName' => 'Política da Comunidade Acadêmica Federada (CAFe)',
  'rulesURL' => 'https://ajuda.rnp.br/cafe/cafe/politicas-do-servico/politica-da-comunidade-academica-federada-cafe',
  'roloverDocURL' => 'https://ajuda.rnp.br/cafe/idp-cafe/faq/gerar-novo-certificado-para-o-metadado',

  'rulesSectsBoth' => '',
  'rulesSectsIdP' => '',
  'rulesSectsSP' => '',
  'rulesInfoBoth' => '',
  'rulesInfoIdP' => '',
  'rulesInfoSP' => '',

  # SWAMID specific assurance (al1-al3) is not used by CAFe
  'swamid_assurance' => false,
  'checkOrganization' => false,
  'populateMemberSince' => false,
  'cleanAttribuesFromIDPSSODescriptor' => true,
  'storeServiceInfo' => false,
  # Empty string = not used
  'releaseCheckResultsURL' => '',
  'eduPersonAffiliationLink' => '',
  'eduPersonAffiliationExemptIdPs' => array(),
  'mdqBaseURL' => '',
  # SQLite DB with MDS statistics; created empty by docker/entrypoint.sh (in production it is filled by the MDS service)
  'mdsDbPath' => '/opt/metadata/MDS.db',
  'urlCheckUA' => 'CAFe metadata validator',
  'urlCheckDataEnabled' => false,
  'urlCheckPlainHTTPEnabled' => false,
  'urlCheckMaxSize' => 16 * 1024 * 1024, // 16MB
);
