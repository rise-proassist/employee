#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "$0")/.." && pwd)"
INPUT_PATH="${1:-$ROOT_DIR/context/backend-apis.adoc}"
OUTPUT_PATH="${2:-${INPUT_PATH%.adoc}.pdf}"
THEME_PATH="${3:-$ROOT_DIR/context/pdf-theme-ja.yml}"

if ! command -v asciidoctor-pdf >/dev/null 2>&1; then
  echo "[ERROR] asciidoctor-pdf not found. Run: source ~/.zshrc" >&2
  exit 1
fi

if [[ ! -f "$INPUT_PATH" ]]; then
  echo "[ERROR] Input file not found: $INPUT_PATH" >&2
  exit 1
fi

if [[ ! -f "$THEME_PATH" ]]; then
  echo "[ERROR] Theme file not found: $THEME_PATH" >&2
  exit 1
fi

FONT_DIRS=(
  "$HOME/Library/Fonts"
  "/Library/Fonts"
  "/System/Library/Fonts"
)

AVAILABLE_FONT_DIRS=()
for d in "${FONT_DIRS[@]}"; do
  if [[ -d "$d" ]]; then
    AVAILABLE_FONT_DIRS+=("$d")
  fi
done

FONT_DIRS_JOINED="$(IFS=';'; echo "${AVAILABLE_FONT_DIRS[*]}")"

echo "[INFO] Input : $INPUT_PATH"
echo "[INFO] Output: $OUTPUT_PATH"
echo "[INFO] Theme : $THEME_PATH"

asciidoctor-pdf \
  -a pdf-theme="$THEME_PATH" \
  -a "pdf-fontsdir=$FONT_DIRS_JOINED" \
  -o "$OUTPUT_PATH" \
  "$INPUT_PATH"

echo "[DONE] Generated: $OUTPUT_PATH"
