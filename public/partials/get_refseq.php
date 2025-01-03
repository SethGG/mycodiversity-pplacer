<?php

require_once '../../src/database.php';
require_once '../../src/get_refseq.php';

$db = new Database();

$refseq = $_POST['refseq'] ?? '';
$results = modelGetRefseq($db, $refseq);
$view = viewGetRefseq($refseq, $results);

echo $view;


?>