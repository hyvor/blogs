import { Node } from "prosemirror-model";
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
        ];

        let totalScore = 0;
        let totalTests = 0;
        let totalIgnored = 0;

        let results : TestResult[] = [];

        for (let test of tests) {
            const result = test.run();
            totalScore += result.score;
            totalTests++;
            if (result.ignore) {
                totalIgnored++;
            }
            results.push(result);
        }

        const averageScore = totalScore / totalTests;

        return {
            average: averageScore,
            tests: results,
        };
    }

}



interface TestResult {
    testName: string,
    score: number, // 0 - 100
    message: string,
    ignore: boolean,
}

class Test {
    constructor(protected input : Input) {}
    public run() : TestResult {
        throw new Error('Not implemented');
    }

    protected defaultResult(message: string) : TestResult {
        return {
            testName: this.constructor.name,
            score: 0,
            message: message,
            ignore: false,
        };
    }

    protected contentNode() : Node {
        const json = this.input.content ?
            JSON.parse(this.input.content) :
            null;

        return json ? Node.fromJSON(schema, json) : schema.nodes.doc.createAndFill()!;
    }
    protected contentText() : string {
        return this.contentNode().textContent;
    }

}

export class PrimaryKeywordInTitleTest extends Test {

    public run() : TestResult {
        let result = this.defaultResult('Primary keyword not found in title');

        const title = this.input.title.toLowerCase().trim();

        // primary keyword within first 50 characters
        const primaryKeywordInFirst50Chars = title
            .substring(0, 50)
            .includes(this.input.primaryKeyword.toLowerCase());

        if (primaryKeywordInFirst50Chars) {
            result.score = 100;
            result.message = 'Primary keyword found in title';
        } else {

            if (title.includes(this.input.primaryKeyword.toLowerCase())) {
                result.score = 49;
                result.message = 'Primary keyword found in title, but not within first 50 characters';
            }

        }

        return result;
    }

}

export class PrimaryKeywordInDescriptionTest extends Test {

    public run() : TestResult {

        let result = this.defaultResult('Primary keyword not found in description');

        const description = this.input.description.toLowerCase().trim();

        if (description.includes(this.input.primaryKeyword.toLowerCase())) {
            result.score = 100;
            result.message = 'Primary keyword found in description';
        }

        return result;

    }

}

export class PrimaryKeywordInSlugTest extends Test {

    public run() : TestResult {

        let result = this.defaultResult('Primary keyword not found in slug');

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
            result.message = 'Primary keyword found in slug';
        }

        return result;

    }

}

/**
 * If the content is less than 300 words, check if the primary keyword is in the content.
 * If the content is more than 300 words, check if the primary keyword is in the first 10% of the content.
 */
export class PrimaryKeywordInBeginningOfContentTest extends Test {

    public run() : TestResult {

        let result = this.defaultResult('Primary keyword not found in the beginning of content');

        const content = this.contentText().toLowerCase().trim();
        const primaryKeyword = this.input.primaryKeyword.toLowerCase();
        const words = content.split(/\s+/);
        const contentToCheck =  words.length < 300 ? 
            content : 
            words.slice(0, Math.floor(words.length * 0.1)).join(' ');

        if (contentToCheck.includes(primaryKeyword)) {
            result.score = 100;
            result.message = 'Primary keyword found in the beginning of content';
        }

        return result;
    }

}