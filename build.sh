#!/usr/bin/env bash
set -euo pipefail

ROOT="$(cd "$(dirname "$0")" && pwd)"
DIST="$ROOT/dist"
OUTPUT="$DIST/VivaArcade.phar"
WORK="$(mktemp -d)"
trap 'rm -rf "$WORK"' EXIT
STAGE="$WORK/package"

command -v php >/dev/null || { echo "PHP CLI is required" >&2; exit 1; }
command -v sha256sum >/dev/null || { echo "sha256sum is required" >&2; exit 1; }
php -r 'foreach (["Phar", "json", "hash", "zlib"] as $ext) { if (!extension_loaded($ext)) { fwrite(STDERR, "Missing PHP extension: {$ext}\n"); exit(1); } }'

mkdir -p "$STAGE/src" "$DIST"
cp "$ROOT/plugin.yml" "$STAGE/plugin.yml"
cp -a "$ROOT/src/VivaArcade/." "$STAGE/src/"
if [[ -d "$ROOT/resources" ]]; then
  cp -a "$ROOT/resources/." "$STAGE/resources/"
fi

echo "Checking PHP syntax..."
while IFS= read -r -d '' file; do
  php -l "$file"
done < <(find "$STAGE" "$ROOT/tests" -type f -name '*.php' -print0)

echo "Running dependency-light core regressions..."
php "$ROOT/tests/run.php"

echo "Running UUID storage and idempotent reward tests..."
php "$ROOT/tests/storage.php"

echo "Running match snapshot codec tests..."
php "$ROOT/tests/snapshot.php"

echo "Running district directory and Finglish layout tests..."
php "$ROOT/tests/world-layout.php"

echo "Building PHAR..."
rm -f "$OUTPUT"
php -d phar.readonly=0 -r '
$stage = $argv[1];
$output = $argv[2];
$phar = new Phar($output);
$phar->startBuffering();
$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($stage, FilesystemIterator::SKIP_DOTS));
foreach ($iterator as $file) {
    if (!$file->isFile()) { continue; }
    $relative = str_replace(DIRECTORY_SEPARATOR, "/", substr($file->getPathname(), strlen($stage) + 1));
    if ($relative === "plugin.yml" || str_starts_with($relative, "src/") || str_starts_with($relative, "resources/")) {
        $phar->addFile($file->getPathname(), $relative);
    }
}
if (!$phar->offsetExists("plugin.yml") || !$phar->offsetExists("src/Main.php") || !$phar->offsetExists("resources/config.yml")) {
    throw new RuntimeException("Required plugin files are missing from the PHAR");
}
$phar->setStub($phar->createDefaultStub());
$phar->stopBuffering();
$check = new Phar($output);
$manifest = file_get_contents("phar://" . $output . "/plugin.yml");
if (!is_string($manifest) || strpos($manifest, "main: VivaArcade\\Main") === false) {
    throw new RuntimeException("PHAR plugin manifest validation failed");
}
echo "PHAR validated: {$output}\n";
' "$STAGE" "$OUTPUT"

(
  cd "$DIST"
  sha256sum VivaArcade.phar > SHA256SUMS.txt
)

echo "Build completed: $OUTPUT"
echo "SHA256:"
cat "$DIST/SHA256SUMS.txt"
