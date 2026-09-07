import { Node } from 'prosemirror-model';
import { getDocFromContent, getTextFromContent } from '../prosemirror/helpers';
import { type Link, getLinksFromContent } from '../links/links';
import { getOccurrencesOfKeywordInContent, getWords, getWordsCount } from './words';

export interface Input {
	primaryKeyword: string | null;
	secondaryKeywords: string[];
	title: string;
	description: string;
	slug: string;
	content: string | null;
	blogUrl: string;
	languageCode: string;
}

export interface Output {
	average: number;
	tests: TestResult[];
}

export class SeoAnalyzer {
	constructor(private input: Input) {}

	public analyze(): Output {
		const tests = [
			new PrimaryKeywordInTitleTest(this.input),
			new PrimaryKeywordInDescriptionTest(this.input),
			new PrimaryKeywordInSlugTest(this.input),
			new PrimaryKeywordInBeginningOfContentTest(this.input),
			new ContentLengthTest(this.input),
			new AllKeywordsInContentTest(this.input),
			new AllKeywordsInSubHeadingsTest(this.input),
			new AllKeywordsInImgAltTest(this.input),
			new KeywordDensityTest(this.input),
			new SlugLengthTest(this.input),
			new ExternalLinksTest(this.input),
			new InternalLinksTest(this.input),
			new ImagesCountTest(this.input),
			new ImageAltTest(this.input)
		] as Test[];

		let totalScore = 0;
		let totalTests = 0;

		let results: TestResult[] = [];

		for (let test of tests) {
			const result = test.run();

			if (!result.ignore) totalScore += result.score;

			totalTests++;
			results.push(result);
		}

		const averageScore = totalScore / totalTests;

		return {
			average: averageScore,
			tests: results
		};
	}
}

export type TestMessageParams = Record<string, string | number>;

export interface TestResult {
	result: any;
	results: any;
	name: string;
	score: number; // 0 - 100
	/** i18n key under `console.postEditor.seo.checks.*`, resolved by the UI */
	messageKey: string;
	messageParams?: TestMessageParams;
	ignore: boolean;
}

// i18n key prefix for all SEO check messages
const K = 'console.postEditor.seo.checks';

export class Test {
	constructor(protected input: Input) {}
	public run(): TestResult {
		throw new Error('Not implemented');
	}

	protected defaultResult(messageKey: string, messageParams?: TestMessageParams): TestResult {
		return {
			name: this.constructor.name,
			score: 0,
			ignore: false,
			messageKey,
			messageParams,
			result: null,
			results: null
		};
	}

	protected contentNode(): Node {
		return getDocFromContent(this.input.content);
	}
	protected contentText(): string {
		return getTextFromContent(this.input.content);
	}

	protected links(): Link[] {
		return getLinksFromContent(this.input.content, this.input.blogUrl);
	}

	protected allKeywords(): string[] | null {
		const keywords = [
			...(this.input.primaryKeyword ? [this.input.primaryKeyword] : []),
			...this.input.secondaryKeywords
		];
		return keywords.length ? keywords : null;
	}

	protected keywordInString(keyword: string, str: string): boolean {
		if (!keyword) return false;

		return getOccurrencesOfKeywordInContent(keyword, str, this.input.languageCode) > 0;
	}
}

export class PrimaryKeywordInTitleTest extends Test {
	public run() {
		let result = this.defaultResult(`${K}.primaryKeywordInTitle.notFound`);

		if (!this.input.primaryKeyword) {
			result.ignore = true;
			result.messageKey = `${K}.primaryKeywordInTitle.ignored`;
			return result;
		}

		const title = this.input.title.toLowerCase().trim();
		const primaryKeyword = this.input.primaryKeyword.toLowerCase();

		// primary keyword within first 50 characters
		const primaryKeywordInFirst50Chars = title.substring(0, 50).includes(primaryKeyword);

		if (title.startsWith(primaryKeyword)) {
			result.score = 100;
			result.messageKey = `${K}.primaryKeywordInTitle.found`;
		} else if (primaryKeywordInFirst50Chars) {
			result.score = 75;
			result.messageKey = `${K}.primaryKeywordInTitle.foundNotBeginning`;
		} else if (title.includes(primaryKeyword)) {
			result.score = 49;
			result.messageKey = `${K}.primaryKeywordInTitle.foundNotFirst50`;
		}

		return result;
	}
}

export class PrimaryKeywordInDescriptionTest extends Test {
	public run() {
		let result = this.defaultResult(`${K}.primaryKeywordInDescription.notFound`);

		if (!this.input.primaryKeyword) {
			result.ignore = true;
			result.messageKey = `${K}.primaryKeywordInDescription.ignored`;
			return result;
		}

		const description = this.input.description.toLowerCase().trim();

		if (description.includes(this.input.primaryKeyword.toLowerCase())) {
			result.score = 100;
			result.messageKey = `${K}.primaryKeywordInDescription.found`;
		}

		return result;
	}
}

export class PrimaryKeywordInSlugTest extends Test {
	public run() {
		let result = this.defaultResult(`${K}.primaryKeywordInSlug.notFound`);

		if (!this.input.primaryKeyword) {
			result.ignore = true;
			result.messageKey = `${K}.primaryKeywordInSlug.ignored`;
			return result;
		}

		const slug = this.input.slug.toLowerCase().trim();
		const primaryKeyword = this.input.primaryKeyword.toLowerCase();
		const slugifiedPrimaryKeyword = primaryKeyword.replace(/ /g, '-');

		if (slug === '') {
			result.score = 0;
			result.messageKey = `${K}.primaryKeywordInSlug.slugEmpty`;
			return result;
		}

		if (slug == slugifiedPrimaryKeyword) {
			result.score = 100;
			result.messageKey = `${K}.primaryKeywordInSlug.found`;
		} else if (slug.includes(slugifiedPrimaryKeyword)) {
			result.score = 75;
			result.messageKey = `${K}.primaryKeywordInSlug.foundWithOthers`;
		}

		return result;
	}
}

/**
 * If the content is less than 300 words, check if the primary keyword is in the content.
 * If the content is more than 300 words, check if the primary keyword is in the first 10% of the content.
 */
export class PrimaryKeywordInBeginningOfContentTest extends Test {
	public run() {
		let result = this.defaultResult(`${K}.primaryKeywordInBeginning.notFound`);

		if (!this.input.primaryKeyword) {
			result.ignore = true;
			result.messageKey = `${K}.primaryKeywordInBeginning.ignored`;
			return result;
		}

		const content = this.contentText().toLowerCase().trim();
		const primaryKeyword = this.input.primaryKeyword.toLowerCase();
		const words = content.split(/\s+/);
		const contentToCheck =
			words.length < 300 ? content : words.slice(0, Math.floor(words.length * 0.1)).join(' ');

		if (this.keywordInString(primaryKeyword, contentToCheck)) {
			result.score = 100;
			result.messageKey = `${K}.primaryKeywordInBeginning.found`;
		}

		return result;
	}
}

export class ContentLengthTest extends Test {
	public run() {
		const content = this.contentText().toLowerCase().trim();
		const wordsCount = getWordsCount(content, this.input.languageCode);

		let result = this.defaultResult(`${K}.contentLength.short`, { count: wordsCount });

		if (wordsCount >= 400) {
			result.messageKey = `${K}.contentLength.ok`;
			result.messageParams = { count: wordsCount };

			const score = Math.floor(wordsCount / 25);
			result.score = score > 100 ? 100 : score;
		}

		return result;
	}
}

export class AllKeywordsInContentTest extends Test {
	public run() {
		const result = this.defaultResult(`${K}.allKeywordsInContent.allFound`);
		const keywords = this.allKeywords();

		const content = this.contentText().toLowerCase().trim();
		const missingKeywords = [];

		if (!keywords) {
			result.ignore = true;
			result.messageKey = `${K}.allKeywordsInContent.ignored`;
			return result;
		}

		for (let keyword of keywords) {
			if (!this.keywordInString(keyword, content)) {
				missingKeywords.push(keyword);
			}
		}

		result.score = 100 - (missingKeywords.length * 100) / keywords.length;

		if (result.score === 100) {
			result.messageKey = `${K}.allKeywordsInContent.allFound`;
		} else {
			result.messageKey = `${K}.allKeywordsInContent.someMissing`;
			result.messageParams = { keywords: missingKeywords.join(', ') };
		}

		return result;
	}
}

export class AllKeywordsInSubHeadingsTest extends Test {
	public run() {
		const result = this.defaultResult(`${K}.allKeywordsInSubHeadings.noneFound`);
		const keywords = this.allKeywords();

		if (!keywords) {
			result.ignore = true;
			result.messageKey = `${K}.allKeywordsInSubHeadings.ignored`;
			return result;
		}

		const content = this.contentNode();
		const subHeadings: Node[] = [];
		content.descendants((node) => {
			if (node.type.name === 'heading' && node.attrs.level > 1) subHeadings.push(node);
		});
		const foundKeywords: string[] = [];

		for (let subHeading of subHeadings) {
			const text = subHeading.textContent.toLowerCase().trim();

			for (let keyword of keywords) {
				if (this.keywordInString(keyword, text) && !foundKeywords.includes(keyword)) {
					foundKeywords.push(keyword);
				}
			}
		}

		result.score = (foundKeywords.length * 100) / keywords.length;

		const notFound = keywords.filter((keyword) => !foundKeywords.includes(keyword));

		if (notFound.length === 0) {
			result.messageKey = `${K}.allKeywordsInSubHeadings.allFound`;
		} else {
			result.messageKey = `${K}.allKeywordsInSubHeadings.someMissing`;
			result.messageParams = { keywords: notFound.join(', ') };
		}

		return result;
	}
}

export class AllKeywordsInImgAltTest extends Test {
	public run() {
		const result = this.defaultResult(`${K}.allKeywordsInImgAlt.noneFound`);
		const keywords = this.allKeywords();

		if (!keywords) {
			result.ignore = true;
			result.messageKey = `${K}.allKeywordsInImgAlt.ignored`;
			return result;
		}

		const content = this.contentNode();
		const images: Node[] = [];
		content.descendants((node) => {
			if (node.type.name === 'image') images.push(node);
		});
		const foundKeywords: string[] = [];

		for (let image of images) {
			const alt = image.attrs.alt?.toLowerCase()?.trim() || '';

			if (!alt) continue;

			for (let keyword of keywords) {
				if (this.keywordInString(keyword, alt) && !foundKeywords.includes(keyword)) {
					foundKeywords.push(keyword);
				}
			}
		}

		result.score = (foundKeywords.length * 100) / keywords.length;

		const notFound = keywords.filter((keyword) => !foundKeywords.includes(keyword));

		if (notFound.length === 0) {
			result.messageKey = `${K}.allKeywordsInImgAlt.allFound`;
		} else {
			result.messageKey = `${K}.allKeywordsInImgAlt.someMissing`;
			result.messageParams = { keywords: notFound.join(', ') };
		}

		return result;
	}
}

export class KeywordDensityTest extends Test {
	public run() {
		const result = this.defaultResult(`${K}.keywordDensity.good`);

		const content = this.contentText().toLowerCase().trim();
		const keywords = this.allKeywords();

		if (!keywords) {
			result.ignore = true;
			result.messageKey = `${K}.keywordDensity.ignored`;
			return result;
		}

		const words = content.split(/\s+/);
		const wordsCount = words.length;

		const keywordsCount = keywords.reduce((count, keyword) => {
			return count + getOccurrencesOfKeywordInContent(keyword, content, this.input.languageCode);
		}, 0);

		const density = (keywordsCount * 100) / wordsCount;

		let variant: 'tooHigh' | 'mayBeTooHigh' | 'good' | 'mayBeTooLow' | 'tooLow';
		let score = 0;

		if (density > 5) {
			variant = 'tooHigh';
			score = 0;
		} else if (density >= 2.5) {
			variant = 'mayBeTooHigh';
			score = 50;
		} else if (density >= 0.5) {
			variant = 'good';
			score = 100;
		} else if (density >= 0.1) {
			variant = 'mayBeTooLow';
			score = 50;
		} else {
			variant = 'tooLow';
			score = 0;
		}

		result.messageKey = `${K}.keywordDensity.${variant}`;
		result.messageParams = { density: density.toFixed(2), count: keywordsCount };
		result.score = score;

		return result;
	}
}

export class SlugLengthTest extends Test {
	public run(): TestResult {
		const result = this.defaultResult(`${K}.slugLength.ok`);

		const slug = this.input.slug.toLowerCase().trim();

		if (slug.length === 0) {
			result.ignore = true;
			result.messageKey = `${K}.slugLength.ignoredEmpty`;
		} else if (slug.length < 35) {
			result.score = 100;
			result.messageKey = `${K}.slugLength.ok`;
			result.messageParams = { count: slug.length };
		} else if (slug.length < 50) {
			result.score = 50;
			result.messageKey = `${K}.slugLength.tooLong`;
			result.messageParams = { count: slug.length };
		} else {
			result.score = 0;
			result.messageKey = `${K}.slugLength.tooLong`;
			result.messageParams = { count: slug.length };
		}

		return result;
	}
}

export class ExternalLinksTest extends Test {
	public run() {
		const result = this.defaultResult(`${K}.externalLinks.none`);
		const externalLinksCount = this.links().filter((link) => link.type === 'external').length;

		if (externalLinksCount > 0) {
			result.score = 100;
			result.messageKey = `${K}.externalLinks.found`;
			result.messageParams = { count: externalLinksCount };
		}

		return result;
	}
}

export class InternalLinksTest extends Test {
	public run() {
		const result = this.defaultResult(`${K}.internalLinks.none`);
		const internalLinksCount = this.links().filter(
			(link) =>
				link.type === 'internal-blog' ||
				link.type === 'internal-domain' ||
				link.type === 'internal-root-domain'
		).length;

		if (internalLinksCount > 0) {
			result.score = 100;
			result.messageKey = `${K}.internalLinks.found`;
			result.messageParams = { count: internalLinksCount };
		}

		return result;
	}
}

export class ImagesCountTest extends Test {
	public run() {
		const result = this.defaultResult(`${K}.imagesCount.found`);
		const doc = this.contentNode();

		let imagesCount = 0;

		doc.descendants((node) => {
			if (node.type.name === 'image' && node.attrs.src) {
				imagesCount++;
			}
		});

		if (imagesCount >= 4) {
			result.score = 100;
		} else if (imagesCount === 3) {
			result.score = 90;
		} else if (imagesCount === 2) {
			result.score = 80;
		} else if (imagesCount === 1) {
			result.score = 70;
		}

		result.messageKey = `${K}.imagesCount.found`;
		result.messageParams = { count: imagesCount };

		return result;
	}
}

export class ImageAltTest extends Test {
	public run() {
		const result = this.defaultResult(`${K}.imageAlt.allHaveAlt`);
		const doc = this.contentNode();

		let imagesMissingAltsCount = 0;
		let imagesCount = 0;

		doc.descendants((node) => {
			if (node.type.name === 'image' && node.attrs.src) {
				imagesCount++;
				if (!node.attrs.alt) imagesMissingAltsCount++;
			}
		});

		if (imagesCount === 0) {
			result.ignore = true;
			result.messageKey = `${K}.imageAlt.ignoredNoImages`;
		} else if (imagesMissingAltsCount === 0) {
			result.messageKey = `${K}.imageAlt.allHaveAlt`;
			result.score = 100;
		} else {
			result.messageKey = `${K}.imageAlt.someMissingAlt`;
			result.messageParams = { count: imagesMissingAltsCount };
			result.score = 0;
		}

		return result;
	}
}
