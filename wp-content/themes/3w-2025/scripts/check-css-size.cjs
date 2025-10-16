#!/usr/bin/env node
/**
 * Enforces the CSS file length guideline for the 3W theme.
 * Fails the build if any source stylesheet exceeds the configured line budget.
 */
const fs = require('fs/promises');
const path = require('path');
const { glob } = require('glob');

const THEME_ROOT = path.resolve(__dirname, '..');
const MAX_LINES = Number(process.env.CSS_MAX_LINES ?? 500);
const DEFAULT_PATTERNS = [
  'src/**/*.css',
  'assets/**/*.css',
  'patterns/**/*.css',
  'parts/**/*.css',
  'templates/**/*.css',
];
const IGNORE_GLOBS = ['**/node_modules/**', '**/build/**', '**/*.min.css'];

async function getMatches(patterns) {
  const results = await glob(patterns, {
    cwd: THEME_ROOT,
    ignore: IGNORE_GLOBS,
    nodir: true,
    absolute: false,
  });
  return Array.from(new Set(results)).sort();
}

async function countLines(filePath) {
  const absolutePath = path.join(THEME_ROOT, filePath);
  const contents = await fs.readFile(absolutePath, 'utf8');
  return contents.split(/\r?\n/).length;
}

async function main() {
  const patterns = process.argv.slice(2);
  const globsToUse = patterns.length ? patterns : DEFAULT_PATTERNS;
  const matches = await getMatches(globsToUse);

  if (!matches.length) {
    console.warn('CSS length check: no matching files found.');
    return;
  }

  const failures = [];

  for (const relativePath of matches) {
    const lineCount = await countLines(relativePath);
    if (lineCount > MAX_LINES) {
      failures.push({ relativePath, lineCount });
    }
  }

  if (failures.length) {
    console.error('CSS length check failed. The following files exceed the line budget:');
    failures.forEach(({ relativePath, lineCount }) => {
      console.error(`  • ${relativePath} contains ${lineCount} lines (limit ${MAX_LINES}).`);
    });
    console.error('Split oversized stylesheets into smaller, component-scoped files to comply with the CSS authoring rules.');
    process.exitCode = 1;
    return;
  }

  console.log(`CSS length check passed for ${matches.length} file(s). Line limit: ${MAX_LINES}.`);
}

main().catch((error) => {
  console.error('CSS length check encountered an unexpected error:');
  console.error(error);
  process.exitCode = 1;
});
