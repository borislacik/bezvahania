<?php

/**
 * Documentation gate (docs/brief.md, section 10.4). Run: composer docs:check
 *
 * On a feature branch it compares the branch with main:
 *  - a new or changed file in docs/changes/ is required,
 *  - changed code requires a CHANGELOG.md change,
 *  - changed composer.json / package.json / .env.example / scripts / routes / config / compose / docker
 *    requires a README.md change, unless the change record contains "README: bez zmeny – <reason>".
 * Always:
 *  - every .claude/skills/<name>/SKILL.md has frontmatter with name and description,
 *  - relative links between .md files resolve.
 */
$root = dirname(__DIR__);
chdir($root);

$errors = [];

function run(string $command): ?string
{
    $output = [];
    exec($command.' 2>/dev/null', $output, $code);

    return $code === 0 ? implode("\n", $output) : null;
}

$branch = trim((string) run('git rev-parse --abbrev-ref HEAD'));
$base = getenv('DOCS_CHECK_BASE') ?: 'main';
$onMain = $branch === $base || $branch === 'HEAD' || $branch === '';

if (! $onMain && run('git rev-parse --verify --quiet '.escapeshellarg($base)) !== null) {
    $diff = run('git diff --name-only '.escapeshellarg($base).'...HEAD') ?? '';
    $uncommitted = run('git status --porcelain --untracked-files=all') ?? '';
    $files = array_filter(explode("\n", $diff));

    foreach (array_filter(explode("\n", $uncommitted)) as $line) {
        $files[] = trim(substr($line, 3));
    }

    $files = array_values(array_unique($files));
    $changed = fn (string $pattern): array => array_values(array_filter($files, fn (string $f): bool => preg_match($pattern, $f) === 1));

    $changeRecords = $changed('#^docs/changes/.+\.md$#');
    if ($changeRecords === []) {
        $errors[] = 'Chýba záznam o zmene v docs/changes/ (RRRR-MM-DD-<vetva>.md).';
    }

    $code = $changed('#^(app|bootstrap|config|database|routes|resources|lang|scripts|docker|tests|public)/|^(composer\.json|package\.json|compose\.yaml|vite\.config\.js)$#');
    if ($code !== [] && $changed('#^CHANGELOG\.md$#') === []) {
        $errors[] = 'Zmenil sa kód, ale CHANGELOG.md (sekcia [Unreleased]) nie.';
    }

    $readmeTriggers = $changed('#^(composer\.json|package\.json|\.env\.example|compose\.yaml)$|^(scripts|routes|config|docker)/#');
    if ($readmeTriggers !== [] && $changed('#^README\.md$#') === []) {
        $exempt = false;
        foreach ($changeRecords as $record) {
            if (is_file($record) && preg_match('/^README: bez zmeny – .+/mu', (string) file_get_contents($record)) === 1) {
                $exempt = true;
            }
        }
        if (! $exempt) {
            $errors[] = 'Zmenili sa '.implode(', ', array_slice($readmeTriggers, 0, 5)).', ale README.md nie '
                .'(výnimka: riadok „README: bez zmeny – <dôvod>“ v zázname v docs/changes/).';
        }
    }
}

foreach (glob('.claude/skills/*', GLOB_ONLYDIR) ?: [] as $dir) {
    $file = $dir.'/SKILL.md';
    $content = is_file($file) ? (string) file_get_contents($file) : '';
    if (preg_match('/\A---\R(.*?)\R---/s', $content, $m) !== 1
        || preg_match('/^name:\s*\S+/m', $m[1]) !== 1
        || preg_match('/^description:\s*\S+/m', $m[1]) !== 1) {
        $errors[] = "Skill {$dir} nemá SKILL.md s platným frontmatterom (name, description).";
    }
}

$markdown = array_filter(
    explode("\n", run('git ls-files --cached --others --exclude-standard -- "*.md"') ?? ''),
    fn (string $f): bool => $f !== '' && is_file($f),
);

foreach ($markdown as $file) {
    $content = (string) file_get_contents($file);
    $content = (string) preg_replace('/```.*?```/s', '', $content);
    $content = (string) preg_replace('/`[^`\n]*`/', '', $content);
    preg_match_all('/\[[^\]]*\]\(([^)\s]+)(?:\s+"[^"]*")?\)/', $content, $links);

    foreach ($links[1] as $link) {
        if (preg_match('#^([a-z]+:|\#|/)#i', $link) === 1) {
            continue;
        }
        $path = explode('#', $link)[0];
        $path = (string) preg_replace('/:\d+$/', '', rawurldecode($path));
        if ($path !== '' && ! file_exists(dirname($file).'/'.$path)) {
            $errors[] = "Nefunkčný odkaz v {$file}: {$link}";
        }
    }
}

if ($errors !== []) {
    fwrite(STDERR, "docs:check zlyhal:\n - ".implode("\n - ", $errors)."\n");
    exit(1);
}

echo 'docs:check OK'.($onMain ? ' (main: iba odkazy a skills)' : " (vetva {$branch} voči {$base})").PHP_EOL;
