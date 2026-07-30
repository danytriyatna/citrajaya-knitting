<?php
header('Content-Type: text/plain');

$repoDir = realpath(__DIR__ . '/..');
echo "Repo Directory: " . $repoDir . "\n";

// Execute git pull origin dev
$cmd = "cd " . escapeshellarg($repoDir) . " && git pull origin dev 2>&1";
echo "Executing: " . $cmd . "\n\n";

exec($cmd, $output, $returnCode);
echo implode("\n", $output) . "\n";
echo "\nStatus Code: " . $returnCode . "\n";
