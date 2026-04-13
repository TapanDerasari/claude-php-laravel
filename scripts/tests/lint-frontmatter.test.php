<?php
// scripts/tests/lint-frontmatter.test.php
declare(strict_types=1);

$script = __DIR__ . '/../lint-frontmatter.php';
$fixtures = __DIR__ . '/fixtures';

$cases = [
    // [label, target path, expected exit code, expected substring in output (or null)]
    ['valid rule passes', "$fixtures/valid-rule.md", 0, null],
    ['valid skill passes', "$fixtures/valid-skill", 0, null],
    ['valid agent passes', "$fixtures/valid-agent.md", 0, null],
    ['missing paste-into fails', "$fixtures/invalid-missing-paste-into.md", 1, 'paste-into'],
    ['missing description fails', "$fixtures/invalid-missing-description.md", 1, 'description'],
    ['unknown type fails', "$fixtures/invalid-unknown-type.md", 1, 'type'],
];

$failures = 0;
foreach ($cases as [$label, $path, $expectedCode, $expectedOutputSubstring]) {
    $output = [];
    $code = 0;
    exec("php " . escapeshellarg($script) . " " . escapeshellarg($path) . " 2>&1", $output, $code);
    $joined = implode("\n", $output);

    if ($code !== $expectedCode) {
        echo "FAIL: $label — expected exit $expectedCode, got $code\n$joined\n\n";
        $failures++;
        continue;
    }
    if ($expectedOutputSubstring !== null && !str_contains($joined, $expectedOutputSubstring)) {
        echo "FAIL: $label — expected output to contain '$expectedOutputSubstring'\n$joined\n\n";
        $failures++;
        continue;
    }
    echo "PASS: $label\n";
}

exit($failures === 0 ? 0 : 1);
