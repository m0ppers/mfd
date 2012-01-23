<?php

$inFile = $_SERVER['argv'][1];

if (!file_exists($inFile) || !is_readable($inFile)) {
    die("Can't read infile");
}

$outFile = $_SERVER['argv'][2];

if (!$outFile) {
    die("Can't write to outfile");
}

$doc = new DOMDocument;
$data = file_get_contents('compress.zlib://'.$inFile);
$doc->load('compress.zlib://'.$inFile);

$xpath = new DOMXPath($doc);
var_dump($doc->firstChild);
$baseFiles = array();
foreach ($xpath->query('/mfd/basefiles/basefile') as $file) {
    $codedFiles[$file->getAttribute('id')] = file_get_contents($file->firstChild->data);
}

$fp = fopen($outFile,'w+');
foreach ($xpath->query('/mfd/description/sequence') as $s) {
    fwrite($fp, substr($codedFiles[$s->getAttribute('i')], $s->getAttribute('o'), $s->getAttribute('l')));
}
fclose($fp);
