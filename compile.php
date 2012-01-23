<?php
$inFile = $_SERVER['argv'][1];

if (!file_exists($inFile) || !is_readable($inFile)) {
    die("Can't read infile");
}

$outFile = $_SERVER['argv'][2];

if (!$outFile) {
    die("Can't write to outfile");
}

$codingFiles = array(
                    );

$x = file_get_contents($codingFiles[0]);

for($i=0;$i<256;$i++) {
    if (strpos($x, chr($i)) === false) {
        die($i." not found");
    }
}

$doc = new DOMDocument('1.0');
$doc->formatOutput = true;

$root = $doc->createElement('mfd');
$doc->appendChild($root);

$bfRoot = $doc->createElement('basefiles');
$root->appendChild($bfRoot);

foreach ($codingFiles as $index => $file) {
    $bf = $doc->createElement('basefile', $file);
    $bf->setAttribute('id', $index);
    $bfRoot->appendChild($bf);
}

$description = $doc->createElement('description');
$root->appendChild($description);

$len = 1;

$data = file_get_contents($inFile);

$inData = array();
foreach ($codingFiles as $index => $codingFile) {
    $inData[$index] = file_get_contents($codingFile);
}
$cache=array();
$start = microtime(true);

$offset = 0;
$strlen = strlen($data);
$harxx = 1000;
while ($strlen) {
    if ($harxx++ % 1000 == 0) {
        var_dump($strlen);
    }
    $best = null;
    $len = 0;
    
    $best = array('index' => null,
                  'offset' => 0,
                  'length' => 0,
                 );
    for ($i=0;$i<count($codingFiles);$i++) {
        $lastFound = null;
        while (true) {
            $len++;
            if ($strlen < $len) {
                break;
            }
            $toAnalyze = substr($data,$offset, $len);
            if (isset($cache[$i][$toAnalyze])) {
                $found = $cache[$i][$toAnalyze];
            } else {
                $found = strpos($inData[$i], $toAnalyze);
                $cache[$i][$toAnalyze] = $found;
            }
            if ($found === false) {
                break;
            }

            $lastFound = $found;
        }

        $len--;
        if ($len>$best['length']) {
            $best['index'] = $i;
            $best['offset'] = $lastFound;
            $best['length'] = $len;
        }
    }
    
    $offset += $best['length'];
    $strlen -= $best['length'];
    $sequence = $doc->createElement('sequence');
    $sequence->setAttribute('i',$best['index']);
    $sequence->setAttribute('o', $best['offset']);
    $sequence->setAttribute('l', $best['length']);
    $description->appendChild($sequence);
}
file_put_contents('compress.zlib://'.$outFile, $doc->saveXml());
var_dump(microtime(true) - $start);
