import { Mark, Node } from 'prosemirror-model';
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

export interface TestResult {
	result: any;
	results: any;
	name: string;
	score: number; // 0 - 100
	message: string;
	ignore: boolean;
}

export class Test {
	constructor(protected input: Input) {}
	public run(): TestResult {
		throw new Error('Not implemented');
	}

	protected defaultResult(message: string = ''): TestResult {
		return {
			name: this.constructor.name,
			score: 0,
			ignore: false,
			message: message,
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
		let result = this.defaultResult('Primary keyword not found in title');

		if (!this.input.primaryKeyword) {
			result.ignore = true;
			result.message = 'Ignored primary keyword in title test. Add primary keyword';
			return result;
		}

		const title = this.input.title.toLowerCase().trim();
		const primaryKeyword = this.input.primaryKeyword.toLowerCase();

		// primary keyword within first 50 characters
		const primaryKeywordInFirst50Chars = title.substring(0, 50).includes(primaryKeyword);

		if (title.startsWith(primaryKeyword)) {
			result.score = 100;
			result.message = 'Primary keyword found in the title';
		} else if (primaryKeywordInFirst50Chars) {
			result.score = 75;
			result.message = 'Primary keyword found in the title, but not at the beginning';
		} else if (title.includes(primaryKeyword)) {
			result.score = 49;
			result.message = 'Primary keyword found in the title, but not within first 50 characters';
		}

		return result;
	}
}

export class PrimaryKeywordInDescriptionTest extends Test {
	public run() {
		let result = this.defaultResult('Primary keyword not found in the description');

		if (!this.input.primaryKeyword) {
			result.ignore = true;
			result.message = 'Ignored primary keyword in description test. Add primary keyword';
			return result;
		}

		const description = this.input.description.toLowerCase().trim();

		if (description.includes(this.input.primaryKeyword.toLowerCase())) {
			result.score = 100;
			result.message = 'Primary keyword found in the description';
		}

		return result;
	}
}

export class PrimaryKeywordInSlugTest extends Test {
	public run() {
		let result = this.defaultResult('Primary keyword not found in the slug');

		if (!this.input.primaryKeyword) {
			result.ignore = true;
			result.message = 'Ignored primary keyword in slug test. Add primary keyword';
			return result;
		}

		const slug = this.input.slug.toLowerCase().trim();
		const primaryKeyword = this.input.primaryKeyword.toLowerCase();
		const slugifiedPrimaryKeyword = primaryKeyword.replace(/ /g, '-');

		if (slug === '') {
			result.score = 0;
			result.message = 'Slug is empty';
			return result;
		}

		if (slug == slugifiedPrimaryKeyword) {
			result.score = 100;
			result.message = 'Primary keyword found in the slug';
		} else if (slug.includes(slugifiedPrimaryKeyword)) {
			result.score = 75;
			result.message = 'Primary keyword found in the slug with other words';
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
		let result = this.defaultResult('Primary keyword not found in the beginning of the content');

		if (!this.input.primaryKeyword) {
			result.ignore = true;
			result.message = 'Ignored primary keyword in beginning test. Add primary keyword';
			return result;
		}

		const content = this.contentText().toLowerCase().trim();
		const primaryKeyword = this.input.primaryKeyword.toLowerCase();
		const words = content.split(/\s+/);
		const contentToCheck =
			words.length < 300 ? content : words.slice(0, Math.floor(words.length * 0.1)).join(' ');

		if (this.keywordInString(primaryKeyword, contentToCheck)) {
			result.score = 100;
			result.message = 'Primary keyword found in the beginning of the content';
		}

		return result;
	}
}

export class ContentLengthTest extends Test {
	public run() {
		const content = this.contentText().toLowerCase().trim();
		const wordsCount = getWordsCount(content, this.input.languageCode);

		let result = this.defaultResult(
			`Content is ${wordsCount} word${wordsCount === 1 ? '' : 's'} long. Consider using at least 400 words.`
		);

		if (wordsCount >= 400) {
			result.message = `Content is ${wordsCount} words long`;

			const score = Math.floor(wordsCount / 25);
			result.score = score > 100 ? 100 : score;
		}

		return result;
	}
}

export class AllKeywordsInContentTest extends Test {
	public run() {
		const result = this.defaultResult();
		const keywords = this.allKeywords();

		const content = this.contentText().toLowerCase().trim();
		const missingKeywords = [];

		if (!keywords) {
			result.ignore = true;
			result.message = 'Ignored keywords in content test. Add keywords';
			return result;
		}

		for (let keyword of keywords) {
			if (!this.keywordInString(keyword, content)) {
				missingKeywords.push(keyword);
			}
		}

		result.score = 100 - (missingKeywords.length * 100) / keywords.length;
		result.message =
			result.score === 100
				? 'All keywords found in the content'
				: 'Some keywords are missing in the content: ' + missingKeywords.join(', ');

		return result;
	}
}

export class AllKeywordsInSubHeadingsTest extends Test {
	public run() {
		const result = this.defaultResult('No keywords found in subheadings');
		const keywords = this.allKeywords();

		if (!keywords) {
			result.ignore = true;
			result.message = 'Ignored keywords in subheadings test. Add keywords';
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
			result.message = 'All keywords found in subheadings';
		} else {
			result.message = 'Some keywords not found in subheadings: ' + notFound.join(', ');
		}

		return result;
	}
}

export class AllKeywordsInImgAltTest extends Test {
	public run() {
		const result = this.defaultResult('No keywords found in image alt attributes');
		const keywords = this.allKeywords();

		if (!keywords) {
			result.ignore = true;
			result.message = 'Ignored keywords in img alt test. Add keywords';
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
			result.message = 'All keywords found in image alt attributes';
		} else {
			result.message = 'Some keywords not found in image alt attributes: ' + notFound.join(', ');
		}

		return result;
	}
}

export class KeywordDensityTest extends Test {
	public run() {
		const result = this.defaultResult();

		const content = this.contentText().toLowerCase().trim();
		const keywords = this.allKeywords();

		if (!keywords) {
			result.ignore = true;
			result.message = 'Ignored keywords density test. Add keywords';
			return result;
		}

		const words = content.split(/\s+/);
		const wordsCount = words.length;

		const keywordsCount = keywords.reduce((count, keyword) => {
			return count + getOccurrencesOfKeywordInContent(keyword, content, this.input.languageCode);
		}, 0);

		const density = (keywordsCount * 100) / wordsCount;

		let message = '';
		let score = 0;

		if (density > 5) {
			message = ', which is too high';
			score = 0;
		} else if (density >= 2.5) {
			message = ', which may be too high';
			score = 50;
		} else if (density >= 0.5) {
			message = ', which is good';
			score = 100;
		} else if (density >= 0.1) {
			message = ', which may be too low';
			score = 50;
		} else {
			message = ', which is too low';
			score = 0;
		}

		result.message = `Keyword density is ${density.toFixed(2)}%${message}. ${keywordsCount} keyword${keywordsCount === 1 ? '' : 's'} found.`;
		result.score = score;

		return result;
	}
}

export class SlugLengthTest extends Test {
	public run(): TestResult {
		const result = this.defaultResult();

		const slug = this.input.slug.toLowerCase().trim();
		const slugMessage = `Slug is ${slug.length} characters long`;

		if (slug.length === 0) {
			result.ignore = true;
			result.message = 'Slug length test ignored because slug is empty';
		} else if (slug.length < 35) {
			result.score = 100;
			result.message = slugMessage;
		} else if (slug.length < 50) {
			result.score = 50;
			result.message = `${slugMessage}. Consider using a shorter slug`;
		} else {
			result.score = 0;
			result.message = `${slugMessage}. Consider using a shorter slug`;
		}

		return result;
	}
}

export class ExternalLinksTest extends Test {
	public run() {
		const result = this.defaultResult('No external links found');
		const externalLinksCount = this.links().filter((link) => link.type === 'external').length;

		if (externalLinksCount > 0) {
			result.score = 100;
			result.message = `${externalLinksCount} external link${externalLinksCount === 1 ? '' : 's'} found`;
		}

		return result;
	}
}

export class InternalLinksTest extends Test {
	public run() {
		const result = this.defaultResult('No internal links found');
		const internalLinksCount = this.links().filter(
			(link) =>
				link.type === 'internal-blog' ||
				link.type === 'internal-domain' ||
				link.type === 'internal-root-domain'
		).length;

		if (internalLinksCount > 0) {
			result.score = 100;
			result.message = `${internalLinksCount} internal link${internalLinksCount === 1 ? '' : 's'} found`;
		}

		return result;
	}
}

export class ImagesCountTest extends Test {
	public run() {
		const result = this.defaultResult('No images found');
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

		result.message = `${imagesCount} image${imagesCount === 1 ? '' : 's'} found`;

		return result;
	}
}

export class ImageAltTest extends Test {
	public run() {
		const result = this.defaultResult();
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
			result.message = 'Image alt test ignored because no images found';
		} else {
			result.message =
				imagesMissingAltsCount === 0
					? 'All images have alt attributes'
					: `${imagesMissingAltsCount} image${imagesMissingAltsCount === 1 ? '' : 's'} missing alt attributes`;

			result.score = imagesMissingAltsCount === 0 ? 100 : 0;
		}

		return result;
	}
}
