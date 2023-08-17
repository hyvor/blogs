import { Mark, Node } from "prosemirror-model";
import schema from "../../ProseMirror/schema";

export interface Input {
    primaryKeyword: string,
    secondaryKeywords: string[],
    title: string,
    description: string,
    slug: string,
    content: string | null,
    blogUrl: string,
}

interface Output {
    average: number,
    tests: TestResult[],
}

export class SeoAnalyzer {

    constructor(private input : Input) {}

    public analyze() : Output {

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
        ] as Test[];

        let totalScore = 0;
        let totalTests = 0;

        let results : TestResult[] = [];

        for (let test of tests) {
            const result = test.run();

            totalScore += result.score;

            totalTests++;
            results.push(result);
        }

        const averageScore = totalScore / totalTests;

        return {
            average: averageScore,
            tests: results,
        };
    }

}

export interface TestResult {
    score: number, // 0 - 100
    message: string,
    ignore: boolean,
}


class Test {
    constructor(protected input : Input) {}
    public run() : TestResult {
        throw new Error('Not implemented');
    }

    protected defaultResult(message: string = '') : TestResult {
        return {
            score: 0,
            ignore: false,
            message: message,
        }
    }

    protected contentNode() : Node {
        const json = this.input.content ?
            JSON.parse(this.input.content) :
            null;

        return json ? Node.fromJSON(schema, json) : schema.nodes.doc.createAndFill()!;
    }
    protected contentText() : string {
        const doc = this.contentNode();
        let text = '';

        doc.descendants(node => {
            const acceptedNodes = ['paragraph', 'figcaption'];
            if (acceptedNodes.includes(node.type.name)) {
                if (text.length > 0) text += "\n";
                text += node.textContent;
            }
        });

        return text;
    }

    protected links(type: null | 'internal' | 'external') : Mark[] {
        const doc = this.contentNode();
        let links : Mark[] = [];

        doc.descendants(node => {

            const marks = node.marks;
            if (marks.length === 0) return;

            node.marks.forEach(mark => {

                if (mark.type.name !== 'link') return;

                const href = mark.attrs.href;
                const isInternal = href.startsWith(this.input.blogUrl);
                const isExternal = href.startsWith('http');

                if (type === 'internal' && isInternal) {
                    links.push(mark);
                } else if (type === 'external' && isExternal) {
                    links.push(mark);
                } else if (type === null) {
                    links.push(mark);
                }

            });

        });

        return links;
    }

    protected allKeywords() : string[] {
        return [this.input.primaryKeyword, ...this.input.secondaryKeywords];
    }

    protected keywordInString(keyword: string, str: string) : boolean {
        console.log(str);
        return new RegExp(`\\b${keyword}\\b`, 'i').test(str);
    }

}

export class PrimaryKeywordInTitleTest extends Test {

    public run() {
        let result = this.defaultResult('Primary keyword not found in title');

        const title = this.input.title.toLowerCase().trim();
        const primaryKeyword = this.input.primaryKeyword.toLowerCase();

        // primary keyword within first 50 characters
        const primaryKeywordInFirst50Chars = title
            .substring(0, 50)
            .includes(primaryKeyword);

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

        const slug = this.input.slug.toLowerCase().trim();
        const primaryKeyword = this.input.primaryKeyword.toLowerCase();
        const slugifiedPrimaryKeyword = primaryKeyword.replace(/ /g, '-');

        if (slug === '') {
            result.score = 0;
            result.message = 'Slug is empty';
            return result;
        }

        if (
            slug.includes(primaryKeyword) ||
            slug.includes(slugifiedPrimaryKeyword)
        ) {
            result.score = 100;
            result.message = 'Primary keyword found in the slug';
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

        const content = this.contentText().toLowerCase().trim();
        const primaryKeyword = this.input.primaryKeyword.toLowerCase();
        const words = content.split(/\s+/);
        const contentToCheck =  words.length < 300 ? 
            content : 
            words.slice(0, Math.floor(words.length * 0.1)).join(' ');

        if (this.keywordInString(primaryKeyword, contentToCheck)) {
            result.score = 100;
            result.message = 'Primary keyword found in the beginning of the content';
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

        for (let keyword of keywords) {
            if (!this.keywordInString(keyword, content)) {
                missingKeywords.push(keyword);
            }
        }
        
        result.score = 100 - (missingKeywords.length * 100 / keywords.length);
        result.message = result.score === 100 ?
            'All keywords found in the content' :
            'Some keywords are missing in the content: ' + missingKeywords.join(', ');

        return result;

    }

}

export class ContentLengthTest extends Test {

    public run() {
        const content = this.contentText().toLowerCase().trim();
        const words = content.split(/\s+/);
        const wordsCount = words.length;

        let result = this.defaultResult(`Content is ${wordsCount} words long. Consider using at least 400 words.`);

        if (wordsCount >= 400) {
            result.message = `Content is ${wordsCount} words long`;

            const score = Math.floor(wordsCount / 25);
            result.score = score > 100 ? 100 : score;
        }

        return result;
    }
}

export class AllKeywordsInSubHeadingsTest extends Test {

    public run() {
        const result = this.defaultResult('No keywords found in subheadings');
        const keywords = this.allKeywords();

        const content = this.contentNode();
        const subHeadings : Node[] = [];
        content.descendants(node => {
            if (node.type.name === 'heading' && node.attrs.level > 1)
                subHeadings.push(node);
        });
        const foundKeywords : string[] = [];

        for (let subHeading of subHeadings) {
            const text = subHeading.textContent.toLowerCase().trim();

            for (let keyword of keywords) {
                if (this.keywordInString(keyword, text) && !foundKeywords.includes(keyword)) {
                    foundKeywords.push(keyword);
                }
            }
        }

        result.score = foundKeywords.length * 100 / keywords.length;

        const notFound = keywords.filter(keyword => !foundKeywords.includes(keyword));

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

        const content = this.contentNode();
        const images : Node[] = [];
        content.descendants(node => {
            if (node.type.name === 'image')
                images.push(node);
        });
        const foundKeywords : string[] = [];

        for (let image of images) {
            const alt = image.attrs.alt?.toLowerCase()?.trim() || '';

            if (!alt) continue;

            for (let keyword of keywords) {
                if (this.keywordInString(keyword, alt) && !foundKeywords.includes(keyword)) {
                    foundKeywords.push(keyword);
                }
            }
        }

        result.score = foundKeywords.length * 100 / keywords.length;

        const notFound = keywords.filter(keyword => !foundKeywords.includes(keyword));

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

        const words = content.split(/\s+/);
        const wordsCount = words.length;

        const keywordsCount = keywords.reduce((count, keyword) => {
            return count + (content.match(new RegExp(`\\b${keyword}\\b`, 'gi')) || []).length;
        }, 0);

        const density = keywordsCount * 100 / wordsCount;

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

        if (slug.length < 35) {
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

        const result = this.defaultResult();
        const content = this.contentNode();

        const externalLinks : Node[] = [];

        content.descendants(node => {
            if (node.type.name === 'link' && node.attrs.href.startsWith('http'))
                externalLinks.push(node);
        }
        
    }

}