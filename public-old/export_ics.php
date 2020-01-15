<?php

require_once dirname(__DIR__) . '/vendor/autoload.php';

$icsExport = new \App\Legacy\ICSExport($db, $_SESSION['user_id']);

$icsExport->setFilterClosed($display_filter_closed);
$icsExport->setFilterPriority($display_filter_priority);
$icsExport->setFilterAging($display_filter_aging);
$icsExport->setShowEstimate($display_show_estimate);
$icsExport->setShowInactive($display_show_inactive);
$icsExport->setShowSection($display_show_section);
$icsExport->setShowPriority($display_show_priority);

$headers = $icsExport->getHeaders();

foreach ($headers as $header) {
    header($header);
}

print($icsExport->getOutput());
