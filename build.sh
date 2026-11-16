#!/usr/bin/env bash
set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
SOURCE_ARCHIVE="$ROOT/dist/source.zip"
DIST="$ROOT/dist"

command -v php >/dev/null || { echo "PHP CLI is required" >&2; exit 1; }
command -v unzip >/dev/null || { echo "unzip is required" >&2; exit 1; }
[[ -f "$SOURCE_ARCHIVE" ]] || { echo "Missing dist/source.zip; refusing to build an incomplete plugin" >&2; exit 1; }

php -r 'foreach (["Phar", "json", "hash", "zlib"] as $ext) { if (!extension_loaded($ext)) { fwrite(STDERR, "Missing PHP extension: {$ext}\n"); exit(1); } }'
if ! unzip -t "$SOURCE_ARCHIVE"; then
  echo "ERROR: dist/source.zip is not a readable ZIP archive. Check the committed binary bundle before packaging." >&2
  if command -v file >/dev/null; then file "$SOURCE_ARCHIVE" >&2; fi
  exit 1
fi

WORK="$(mktemp -d)"
cleanup(){ rm -rf "$WORK"; }
trap cleanup EXIT
STAGE="$WORK/package"
mkdir -p "$STAGE"
unzip -q "$SOURCE_ARCHIVE" -d "$STAGE"

# Overlay first-class repository files without deleting or replacing unrelated
# files from the original source archive.
cp "$ROOT/plugin.yml" "$STAGE/plugin.yml"
if [[ -d "$ROOT/src" ]]; then
  mkdir -p "$STAGE/src"
  cp -a "$ROOT/src/." "$STAGE/src/"
fi
if [[ -d "$ROOT/resources" ]]; then
  mkdir -p "$STAGE/resources"
  cp -a "$ROOT/resources/." "$STAGE/resources/"
fi

while IFS= read -r -d '' file; do
  php -l "$file" >/dev/null
done < <(find "$STAGE" -type f -name '*.php' -print0)
php "$ROOT/tests/run.php"

OUTPUT="$DIST/VivaArcade.phar"
mkdir -p "$DIST"
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
    if ($relative === "plugin.yml" || str_starts_with($relative, "src/") || str_starts_with($relative, "resources/") || $relative === "LICENSE") {
        $phar->addFile($file->getPathname(), $relative);
    }
}
if (!$phar->offsetExists("plugin.yml")) { throw new RuntimeException("plugin.yml missing from PHAR"); }
$phar->setStub($phar->createDefaultStub());
$phar->stopBuffering();
$check = new Phar($output);
if (!$check->offsetExists("plugin.yml")) { throw new RuntimeException("PHAR validation failed"); }
echo "PHAR OK: {$output}\n";
' "$STAGE" "$OUTPUT"

if command -v sha256sum >/dev/null; then
  (cd "$DIST" && sha256sum VivaArcade.phar source.zip > SHA256SUMS.txt)
else
  php -r 'foreach (array_slice($argv, 1) as $file) { echo hash_file("sha256", $file), "  ", basename($file), "\n"; }' "$OUTPUT" "$SOURCE_ARCHIVE" > "$DIST/SHA256SUMS.txt"
fi

echo "Build completed: $OUTPUT"
