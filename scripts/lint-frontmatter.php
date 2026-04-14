<?php
// scripts/lint-frontmatter.php
declare(strict_types=1);

const REQUIRED_FIELDS = ['name', 'description', 'paste-into', 'stack', 'type'];
const ALLOWED_STACKS = ['php', 'laravel'];
const ALLOWED_TYPES = ['rule', 'skill', 'agent', 'command', 'hook'];

const PASTE_INTO_RULES = [
    'rule'    => ['exact'  => 'CLAUDE.md'],
    'skill'   => ['prefix' => '.claude/skills/', 'suffix' => '/'],
    'agent'   => ['prefix' => '.claude/agents/', 'suffix' => '.md'],
    'command' => ['prefix' => '.claude/commands/', 'suffix' => '.md'],
    'hook'    => ['prefix' => '.claude/settings.json'],
];

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

function validateFile(string $path): array
{
    $errors = [];
    $content = file_get_contents($path);
    if ($content === false) {
        return ["$path: could not read file"];
    }
    // Plain markdown files (navigation docs, READMEs) have no frontmatter header.
    // Skip them silently. Files that START with --- but are missing the closing ---
    // are still flagged as malformed.
    if (!str_starts_with($content, "---\n")) {
        return [];
    }
    $fm = parseFrontmatter($content);
    if ($fm === null) {
        return ["$path: malformed frontmatter (missing closing ---)"];
    }
    foreach (REQUIRED_FIELDS as $field) {
        if (empty($fm[$field])) {
            $errors[] = "$path: missing required field '$field'";
        }
    }
    if (isset($fm['stack']) && !in_array($fm['stack'], ALLOWED_STACKS, true)) {
        $errors[] = "$path: invalid stack '{$fm['stack']}', must be one of " . implode(',', ALLOWED_STACKS);
    }
    if (isset($fm['type']) && !in_array($fm['type'], ALLOWED_TYPES, true)) {
        $errors[] = "$path: invalid type '{$fm['type']}', must be one of " . implode(',', ALLOWED_TYPES);
    }
    if (
        isset($fm['type'], $fm['paste-into'])
        && in_array($fm['type'], ALLOWED_TYPES, true)
        && isset(PASTE_INTO_RULES[$fm['type']])
    ) {
        $rule = PASTE_INTO_RULES[$fm['type']];
        $pi = $fm['paste-into'];
        $ok = true;
        if (isset($rule['exact']) && $pi !== $rule['exact']) {
            $ok = false;
        }
        if (isset($rule['prefix']) && !str_starts_with($pi, $rule['prefix'])) {
            $ok = false;
        }
        if (isset($rule['suffix']) && !str_ends_with($pi, $rule['suffix'])) {
            $ok = false;
        }
        if (!$ok) {
            $errors[] = "$path: paste-into '$pi' does not match the expected pattern for type '{$fm['type']}'";
        }
    }
    $hasCurated = !empty($fm['source']) && !empty($fm['license']);
    $hasOriginal = !empty($fm['author']);
    if (!$hasCurated && !$hasOriginal) {
        $errors[] = "$path: must have either 'source' + 'license' (curated) or 'author' (original)";
    }
    if (isset($fm['type']) && $fm['type'] === 'skill' && basename($path) !== 'SKILL.md') {
        $errors[] = "$path: skill files must be named SKILL.md";
    }
    return $errors;
}

function walk(string $target): array
{
    $errors = [];
    if (is_file($target)) {
        return str_ends_with($target, '.md') ? validateFile($target) : [];
    }
    $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($target, FilesystemIterator::SKIP_DOTS));
    foreach ($it as $file) {
        if ($file->isFile() && $file->getExtension() === 'md') {
            $errors = array_merge($errors, validateFile($file->getPathname()));
        }
    }
    return $errors;
}

$target = $argv[1] ?? null;
if ($target === null) {
    fwrite(STDERR, "Usage: php lint-frontmatter.php <file-or-dir>\n");
    exit(2);
}

if (!file_exists($target)) {
    fwrite(STDERR, "ERROR: $target: path does not exist\n");
    exit(2);
}

$errors = walk($target);
foreach ($errors as $e) {
    fwrite(STDERR, "ERROR: $e\n");
}
exit($errors === [] ? 0 : 1);
