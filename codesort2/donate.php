<?php

session_start();

require_once('codes-config.php');
require_once('functions.php');

$cs = CodeSort::GetInstance();
$cs->AddOptFromDb();

$cs->GetHeader('Donate');

echo "<h2>Donation</h2>\n";

$form_action = $cs->GetOpt('install_url') . '/donate.php';
require_once('do-donate.php');

$cs->GetFooter(false);
