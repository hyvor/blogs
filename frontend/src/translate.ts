/**
 * Translates the English source files of the marketing site into every
 * other configured language using the Anthropic API.
 *
 * Usage (run from the `frontend` directory):
 *
 *   npx tsx src/translate.ts
 *   npx tsx src/translate.ts --langs fr,es
 *   npx tsx src/translate.ts --force src/routes/(marketing)/[[lang]]/locale/en.json
 *   npx tsx src/translate.ts --force-all
 *
 * Requires the ANTHROPIC_API_KEY environment variable to be set.
 */

import {
	readFileSync,
	writeFileSync,
	existsSync,
	mkdirSync,
	readdirSync,
	copyFileSync
} from 'node:fs';
import { createHash } from 'node:crypto';
import path from 'node:path';
import { fileURLToPath } from 'node:url';
import Anthropic from '@anthropic-ai/sdk';
import { LANGUAGES_CONFIG } from './routes/(marketing)/[[lang]]/marketingLang';

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const FRONTEND_ROOT = path.resolve(__dirname, '..');
const CACHE_FILE = path.join(FRONTEND_ROOT, '.translate-cache.json');
const MODEL = process.env.ANTHROPIC_MODEL || 'claude-sonnet-5';

const TRANSLATABLE_LANGUAGES = LANGUAGES_CONFIG.filter((lang) => !lang.default);

type TranslatableSpec = {
	// path relative to FRONTEND_ROOT, may use `**` (any depth) and a single
	// `*` inside the last segment (e.g. `*.svelte`)
	source: string;
	// same shape as `source`, with `{{lang}}` substituted for the target language code
	target: string;
};

const TRANSLATABLES: TranslatableSpec[] = [
	{
		source: 'src/routes/(marketing)/[[lang]]/locale/en.json',
		target: 'src/routes/(marketing)/[[lang]]/locale/{{lang}}.json'
	},
	{
		source: 'src/routes/(marketing)/[[lang]]/docs/[[slug]]/en/**/*.svelte',
		target: 'src/routes/(marketing)/[[lang]]/docs/[[slug]]/{{lang}}/**/*.svelte'
	}
];

// ---------------------------------------------------------------------------
// Minimal glob-free path matcher.
//
// SvelteKit route folders use literal `[[bracket]]` names, which a real glob
// engine (`[...]` = character class) would misinterpret. We only ever need
// `**` (any depth) and a single `*` wildcard within one filename segment, so
// we walk the filesystem by hand instead of pulling in a glob dependency.
// ---------------------------------------------------------------------------

function splitStaticAndGlob(pattern: string) {
	const segments = pattern.split('/');
	const idx = segments.findIndex((seg) => seg.includes('*'));
	if (idx === -1) {
		return { staticSegments: segments, globSegments: [] as string[] };
	}
	return { staticSegments: segments.slice(0, idx), globSegments: segments.slice(idx) };
}

function segmentToRegex(segment: string): RegExp {
	const escaped = segment.replace(/[.+^${}()|[\]\\]/g, '\\$&').replace(/\*/g, '.*');
	return new RegExp(`^${escaped}$`);
}

/** Returns paths (posix-joined, relative to `baseDir`) of files matching `globSegments`. */
function walkMatch(baseDir: string, globSegments: string[]): string[] {
	if (globSegments.length === 0) {
		return existsSync(baseDir) ? [''] : [];
	}

	const segment = globSegments[0]!;
	const rest = globSegments.slice(1);

	if (segment === '**') {
		const results: string[] = [];
		const recurse = (dir: string, relPrefix: string) => {
			if (!existsSync(dir)) return;
			for (const rel of walkMatch(dir, rest)) {
				results.push(relPrefix && rel ? `${relPrefix}/${rel}` : relPrefix || rel);
			}
			for (const entry of readdirSync(dir, { withFileTypes: true })) {
				if (entry.isDirectory()) {
					recurse(
						path.join(dir, entry.name),
						relPrefix ? `${relPrefix}/${entry.name}` : entry.name
					);
				}
			}
		};
		recurse(baseDir, '');
		return results;
	}

	if (!existsSync(baseDir)) return [];

	const regex = segmentToRegex(segment);
	const results: string[] = [];
	for (const entry of readdirSync(baseDir, { withFileTypes: true })) {
		if (!regex.test(entry.name)) continue;
		if (rest.length === 0) {
			if (entry.isFile()) results.push(entry.name);
		} else if (entry.isDirectory()) {
			for (const rel of walkMatch(path.join(baseDir, entry.name), rest)) {
				results.push(`${entry.name}/${rel}`);
			}
		}
	}
	return results;
}

type ResolvedPair = { source: string; target: string; nested: boolean };

function resolvePairs(spec: TranslatableSpec, langCode: string): ResolvedPair[] {
	const targetPattern = spec.target.replaceAll('{{lang}}', langCode);

	const src = splitStaticAndGlob(spec.source);
	const tgt = splitStaticAndGlob(targetPattern);

	const srcBaseDir = path.join(FRONTEND_ROOT, ...src.staticSegments);
	const tgtBaseDir = path.join(FRONTEND_ROOT, ...tgt.staticSegments);

	const relMatches = walkMatch(srcBaseDir, src.globSegments);
	const nested = src.globSegments[0] === '**';

	return relMatches.map((rel) => ({
		source: src.globSegments.length === 0 ? srcBaseDir : path.join(srcBaseDir, rel),
		target: tgt.globSegments.length === 0 ? tgtBaseDir : path.join(tgtBaseDir, rel),
		nested
	}));
}

/**
 * Docs pages keep non-.svelte assets (images, gifs) next to the component
 * and import them with relative paths. Copy those siblings into the
 * translated directory too, otherwise the translated page fails to build.
 */
function copySiblingAssets(sourceFile: string, targetFile: string) {
	const srcDir = path.dirname(sourceFile);
	const tgtDir = path.dirname(targetFile);
	const translatedExt = path.extname(sourceFile);

	for (const entry of readdirSync(srcDir, { withFileTypes: true })) {
		if (!entry.isFile() || path.extname(entry.name) === translatedExt) continue;

		const destPath = path.join(tgtDir, entry.name);
		if (!existsSync(destPath)) {
			mkdirSync(tgtDir, { recursive: true });
			copyFileSync(path.join(srcDir, entry.name), destPath);
		}
	}
}

// ---------------------------------------------------------------------------
// Cache: relative source path -> lang code -> sha256 of the source content
// that was last translated for that language.
// ---------------------------------------------------------------------------

type Cache = Record<string, Record<string, string>>;

function loadCache(): Cache {
	if (!existsSync(CACHE_FILE)) return {};
	try {
		return JSON.parse(readFileSync(CACHE_FILE, 'utf-8'));
	} catch {
		console.warn(`Could not parse ${CACHE_FILE}, starting with an empty cache.`);
		return {};
	}
}

function saveCache(cache: Cache) {
	writeFileSync(CACHE_FILE, JSON.stringify(cache, null, '\t') + '\n', 'utf-8');
}

function hashContent(content: string): string {
	return createHash('sha256').update(content).digest('hex');
}

function relToFrontend(absPath: string): string {
	return path.relative(FRONTEND_ROOT, absPath).split(path.sep).join('/');
}

// ---------------------------------------------------------------------------
// CLI args
// ---------------------------------------------------------------------------

type Args = {
	forceFiles: string[];
	forceAll: boolean;
	langs: string[] | null;
};

function parseArgs(argv: string[]): Args {
	const args: Args = { forceFiles: [], forceAll: false, langs: null };

	for (let i = 0; i < argv.length; i++) {
		const arg = argv[i];
		if (arg === '--force') {
			const value = argv[++i];
			if (!value) throw new Error('--force requires a file path argument');
			args.forceFiles.push(value);
		} else if (arg === '--force-all') {
			args.forceAll = true;
		} else if (arg === '--langs') {
			const value = argv[++i];
			if (!value) throw new Error('--langs requires a comma-separated list of language codes');
			args.langs = value
				.split(',')
				.map((s) => s.trim())
				.filter(Boolean);
		} else {
			throw new Error(`Unknown argument: ${arg}`);
		}
	}

	return args;
}

function isForced(args: Args, relSourcePath: string, absSourcePath: string): boolean {
	if (args.forceAll) return true;
	return args.forceFiles.some((f) => {
		const normalized = f.split(path.sep).join('/');
		return (
			normalized === relSourcePath ||
			path.resolve(FRONTEND_ROOT, f) === absSourcePath ||
			relSourcePath.endsWith(normalized)
		);
	});
}

// ---------------------------------------------------------------------------
// Anthropic translation
// ---------------------------------------------------------------------------

const anthropic = new Anthropic(); // reads ANTHROPIC_API_KEY from env

function buildSystemPrompt(ext: string, langName: string, langCode: string): string {
	const shared = `You are a professional translator working on the source code of a documentation and marketing website. Translate the given file from English into ${langName} (${langCode}).

Rules:
- Translate only human-facing text: prose, headings, list items, labels, titles, descriptions, and attributes meant to be read by users (e.g. "title", "alt", "placeholder", "aria-label").
- Never translate or alter: object/property keys, code, HTML/component tag names, attribute names (only some attribute values), class names, ids, URLs, file paths, import statements, variable/prop names, i18n or translation keys, or anything inside code blocks, <code>, <pre>, or template literals meant to show code to the reader (e.g. CodeBlock's "code" prop).
- Preserve short inline pseudo-tags exactly as-is (e.g. <marker>...</marker>, <hl>...</hl>) — translate only the text between them, never the tag names.
- Preserve the exact file structure, syntax, formatting, indentation, and whitespace of the original. The output must remain a syntactically valid file of the same type.
- Do not add, remove, or reorder keys/elements/attributes. Do not add comments, notes, or explanations of your own.
- Output ONLY the fully translated file content and nothing else: no markdown code fences, no preamble, no trailing remarks.`;

	const jsonHint = `This is a JSON file of UI strings used for i18n. Translate every string VALUE naturally and idiomatically. Never translate, rename, add, or remove JSON keys. Keep the same nesting.`;

	const svelteHint = `This is a Svelte component containing documentation content. Translate the visible text content (paragraphs, headings, list items, link text, callouts, and human-facing attribute values like "title" or "alt"). Do NOT translate: the <script> block, Svelte syntax (e.g. {#if}, {#each}, {@html}, {expression}), component and prop names, class/style/href/id attribute values, and code shown to the reader inside <code>, <pre>, or CodeBlock's "code" prop.`;

	const hint = ext === '.json' ? jsonHint : ext === '.svelte' ? svelteHint : '';

	return [shared, hint].filter(Boolean).join('\n\n');
}

function stripCodeFence(text: string): string {
	const trimmed = text.trim();
	const lines = trimmed.split('\n');
	const firstLine = lines[0];
	const lastLine = lines[lines.length - 1];
	if (
		lines.length >= 2 &&
		firstLine !== undefined &&
		/^```/.test(firstLine) &&
		lastLine?.trim() === '```'
	) {
		return lines.slice(1, -1).join('\n');
	}
	return trimmed;
}

async function translateContent(
	filePath: string,
	langName: string,
	langCode: string,
	content: string
): Promise<string> {
	const ext = path.extname(filePath);
	const system = buildSystemPrompt(ext, langName, langCode);

	const maxAttempts = 3;

	for (let attempt = 1; attempt <= maxAttempts; attempt++) {
		try {
			const response = await anthropic.messages.create({
				model: MODEL,
				max_tokens: 16000,
				temperature: 0,
				system,
				messages: [{ role: 'user', content }]
			});

			const textBlock = response.content.find((block) => block.type === 'text');
			if (!textBlock || textBlock.type !== 'text') {
				throw new Error('Anthropic API returned no text content');
			}

			const translated = stripCodeFence(textBlock.text);
			return translated + (content.endsWith('\n') && !translated.endsWith('\n') ? '\n' : '');
		} catch (err) {
			const status = err instanceof Anthropic.APIError ? err.status : undefined;
			const retryable = status === undefined || status === 429 || status >= 500;
			if (!retryable || attempt >= maxAttempts) {
				throw err;
			}
			await new Promise((r) => setTimeout(r, attempt * 1000));
		}
	}

	throw new Error('unreachable');
}

// ---------------------------------------------------------------------------
// Main
// ---------------------------------------------------------------------------

async function main() {
	let args: Args;
	try {
		args = parseArgs(process.argv.slice(2));
	} catch (err) {
		console.error(err instanceof Error ? err.message : err);
		console.error('Usage: translate.ts [--force <file>] [--force-all] [--langs lang1,lang2,...]');
		process.exit(1);
	}

	if (!process.env.ANTHROPIC_API_KEY) {
		console.error('ANTHROPIC_API_KEY is not set.');
		process.exit(1);
	}

	let languages = TRANSLATABLE_LANGUAGES;
	if (args.langs) {
		const known = new Set(TRANSLATABLE_LANGUAGES.map((l) => l.code));
		for (const code of args.langs) {
			if (!known.has(code)) {
				console.error(`Unknown language code "${code}". Known languages: ${[...known].join(', ')}`);
				process.exit(1);
			}
		}
		languages = TRANSLATABLE_LANGUAGES.filter((l) => args.langs!.includes(l.code));
	}

	const cache = loadCache();
	let translatedCount = 0;
	let skippedCount = 0;
	let failedCount = 0;

	for (const spec of TRANSLATABLES) {
		for (const lang of languages) {
			const pairs = resolvePairs(spec, lang.code);

			for (const { source, target, nested } of pairs) {
				const relSource = relToFrontend(source);
				const content = readFileSync(source, 'utf-8');
				const hash = hashContent(content);

				const cached = cache[relSource]?.[lang.code];
				const targetExists = existsSync(target);
				const forced = isForced(args, relSource, source);

				if (nested) {
					copySiblingAssets(source, target);
				}

				if (!forced && targetExists && cached === hash) {
					skippedCount++;
					continue;
				}

				console.log(`Translating ${relSource} -> ${lang.code} (${relToFrontend(target)})`);

				try {
					const translated = await translateContent(source, lang.name, lang.code, content);
					mkdirSync(path.dirname(target), { recursive: true });
					writeFileSync(target, translated, 'utf-8');

					cache[relSource] ??= {};
					cache[relSource][lang.code] = hash;
					translatedCount++;
				} catch (err) {
					failedCount++;
					console.error(
						`Failed to translate ${relSource} -> ${lang.code}:`,
						err instanceof Error ? err.message : err
					);
				}
			}
		}
	}

	saveCache(cache);

	console.log(
		`Done. Translated ${translatedCount}, skipped ${skippedCount}, failed ${failedCount}.`
	);
	if (failedCount > 0) process.exit(1);
}

main();
