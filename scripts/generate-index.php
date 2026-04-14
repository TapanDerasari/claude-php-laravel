<?php
// scripts/generate-index.php
declare(strict_types=1);

function parseFrontmatter(string $content): ?array
{
    if (!str_starts_with($content, "---\n")) {
        return null;
    }
    $end = strpos($content, "\n---", 4);
    if ($end === false) {
        return null;
    }
    $block = substr($content, 4, $end - 4);
    $out = [];
    foreach (explode("\n", $block) as $line) {
        if (!preg_match('/^([\w\-]+):\s*(.*)$/', $line, $m)) {
            continue;
        }
        $out[$m[1]] = trim($m[2], " \"'");
    }
    return $out;
}

$options = getopt('', ['root::', 'readme::', 'check']);
$root = $options['root'] ?? getcwd();
$readme = $options['readme'] ?? ($root . '/README.md');
$checkMode = isset($options['check']);

$rows = [];
foreach (['php', 'laravel'] as $stack) {
    $stackDir = "$root/$stack";
    if (!is_dir($stackDir)) {
        continue;
    }
    $it = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($stackDir, FilesystemIterator::SKIP_DOTS)
    );
    foreach ($it as $file) {
        if (!$file->isFile() || $file->getExtension() !== 'md') {
            continue;
        }
        $fm = parseFrontmatter(file_get_contents($file->getPathname()));
        if ($fm === null || empty($fm['name'])) {
            continue;
        }
        $rows[] = $fm;
    }
}

usort($rows, function ($a, $b) {
    return [$a['stack'] ?? '', $a['type'] ?? '', $a['name'] ?? ''] <=> [$b['stack'] ?? '', $b['type'] ?? '', $b['name'] ?? ''];
});

$table = "| Stack | Type | Name | Description | Paste into |\n";
$table .= "|-------|------|------|-------------|------------|\n";
foreach ($rows as $r) {
    $table .= sprintf(
        "| %s | %s | %s | %s | `%s` |\n",
        $r['stack'] ?? '',
        $r['type'] ?? '',
        $r['name'] ?? '',
        $r['description'] ?? '',
        $r['paste-into'] ?? ''
    );
}
$table = rtrim($table);

$current = file_get_contents($readme);
if ($current === false) {
    fwrite(STDERR, "Could not read $readme\n");
    exit(2);
}

$pattern = '/(<!-- INDEX:START -->)(.*)(<!-- INDEX:END -->)/s';
if (!preg_match($pattern, $current)) {
    fwrite(STDERR, "$readme is missing INDEX markers\n");
    exit(2);
}

$replacement = "$1\n$table\n$3";
$updated = preg_replace($pattern, $replacement, $current);

if ($checkMode) {
    if ($updated !== $current) {
        fwrite(STDERR, "README index is stale. Run: php scripts/generate-index.php\n");
        exit(1);
    }
    echo "README index is up to date.\n";
    exit(0);
}

file_put_contents($readme, $updated);
echo "Updated $readme\n";
exit(0);
