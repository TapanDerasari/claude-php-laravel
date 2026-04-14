<?php
declare(strict_types=1);

$script = __DIR__ . '/../generate-index.php';
$fixturesRoot = __DIR__ . '/fixtures/index-input';
$expected = trim(file_get_contents(__DIR__ . '/fixtures/index-expected.md'));

$tmpReadme = tempnam(sys_get_temp_dir(), 'idx') . '.md';
file_put_contents($tmpReadme, "# Test\n<!-- INDEX:START -->\nold content\n<!-- INDEX:END -->\n");

$code = 0;
$output = [];
exec("php " . escapeshellarg($script) . " --root=" . escapeshellarg($fixturesRoot) . " --readme=" . escapeshellarg($tmpReadme) . " 2>&1", $output, $code);

if ($code !== 0) {
    echo "FAIL: generator exited with $code\n" . implode("\n", $output) . "\n";
    unlink($tmpReadme);
    exit(1);
}

$generated = file_get_contents($tmpReadme);
$start = strpos($generated, '<!-- INDEX:START -->') + strlen('<!-- INDEX:START -->');
$end = strpos($generated, '<!-- INDEX:END -->');
$between = trim(substr($generated, $start, $end - $start));

unlink($tmpReadme);

if ($between !== $expected) {
    echo "FAIL: generated index does not match expected\n";
    echo "--- expected ---\n$expected\n";
    echo "--- got ---\n$between\n";
    exit(1);
}

echo "PASS: index generator produces expected output\n";

// Also verify --check mode exits 1 when README is stale
$staleReadme = tempnam(sys_get_temp_dir(), 'idx') . '.md';
file_put_contents($staleReadme, "# Test\n<!-- INDEX:START -->\nstale\n<!-- INDEX:END -->\n");
exec("php " . escapeshellarg($script) . " --check --root=" . escapeshellarg($fixturesRoot) . " --readme=" . escapeshellarg($staleReadme), $out2, $checkCode);
unlink($staleReadme);
if ($checkCode !== 1) {
    echo "FAIL: --check should exit 1 on stale README, got $checkCode\n";
    exit(1);
}
echo "PASS: --check exits 1 on stale README\n";
