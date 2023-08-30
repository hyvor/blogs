import { describe, expect, test } from 'vitest'
import { AllKeywordsInContentTest, ContentLengthTest, Input, PrimaryKeywordInBeginningOfContentTest, PrimaryKeywordInDescriptionTest, PrimaryKeywordInSlugTest, PrimaryKeywordInTitleTest } from './seo-analyzer';

function getInput(input: Partial<Input>) {
    return {
        ...{
            primaryKeyword: '',
            secondaryKeywords: [],
            title: '',
            slug: '',
            description: '',
            content: '',
            blogUrl: '',
        },
        ...input,
    }
}

describe('seo tests', () => {

    describe('primary keyword in title', () => {

        test('when keyword is contained in the first 50', () => {
            const result = new PrimaryKeywordInTitleTest(getInput({
                primaryKeyword: 'keyword',
                title: 'keyword this is a title'
            })).run();
            expect(result.score).toBe(100);
            expect(result.message).toBe('Primary keyword found in the title');
        })

        test('when keyword is not contained in the first 50', () => {
            const result = new PrimaryKeywordInTitleTest(getInput({
                primaryKeyword: 'keyword',
                title: 'this is a title with this is a title with this is a title with keyword'
            })).run();
            expect(result.score).toBe(49);
            expect(result.message).toBe('Primary keyword found in the title, but not within first 50 characters');
        });

        test('when keyword is not found', () => {
            const result = new PrimaryKeywordInTitleTest(getInput({
                primaryKeyword: 'keyword',
                title: 'this is a title'
            })).run();
            expect(result.score).toBe(0);
            expect(result.message).toBe('Primary keyword not found in title');
        });

    });

    describe('primary keyword in description', () => {

        test('when keyword is found', () => {
            const result = new PrimaryKeywordInDescriptionTest(getInput({
                primaryKeyword: 'keyword',
                description: 'this is a description with keyword'
            })).run();
            expect(result.score).toBe(100);
            expect(result.message).toBe('Primary keyword found in the description');
        });

        test('when keyword is not found', () => {
            const result = new PrimaryKeywordInDescriptionTest(getInput({
                primaryKeyword: 'keyword',
                description: 'this is a description'
            })).run();
            expect(result.score).toBe(0);
            expect(result.message).toBe('Primary keyword not found in the description');
        });
        
    });

    describe('primary keyword in slug', () => {
        
        test('when keyword is found', () => {
            const result = new PrimaryKeywordInSlugTest(getInput({
                primaryKeyword: 'keyword',
                slug: 'this-is-a-slug-with-keyword'
            })).run();
            expect(result.score).toBe(100);
            expect(result.message).toBe('Primary keyword found in the slug');
        });

        test('multi word keyword', () => {
            const result = new PrimaryKeywordInSlugTest(getInput({
                primaryKeyword: 'Multi Word Keyword',
                slug: 'this-is-a-slug-with-multi-word-keyword',
            })).run();
            expect(result.score).toBe(100);
            expect(result.message).toBe('Primary keyword found in the slug');
        });

        test('when keyword is not found', () => {
            const result = new PrimaryKeywordInSlugTest(getInput({
                primaryKeyword: 'keyword',
                slug: 'this-is-a-slug'
            })).run();
            expect(result.score).toBe(0);
            expect(result.message).toBe('Primary keyword not found in the slug');
        });
        
    });

    describe('primary keyword in beginning of content', () => {

        test('when keyword is not found', () => {
            const result = new PrimaryKeywordInBeginningOfContentTest(getInput({
                primaryKeyword: 'keyword',
                content: null
            })).run();
            expect(result.score).toBe(0);
            expect(result.message).toBe('Primary keyword not found in the beginning of the content');
        });

        test('when keyword is found in the beginning', () => {
            const result = new PrimaryKeywordInBeginningOfContentTest(getInput({
                primaryKeyword: 'keyword',
                content: JSON.stringify({
                    type: 'doc',
                    content: [
                        {type: 'paragraph', content: [{type: 'text', text: 'keyword this is a content'}]
                    }]
                })
            })).run();
            expect(result.score).toBe(100);
            expect(result.message).toBe('Primary keyword found in the beginning of the content');
        });

        test('checks in first 10% when content is more than 300 words', () => {

            const words300 = Array.from({length: 300}, () => 'word').join(' ');

            const result = new PrimaryKeywordInBeginningOfContentTest(getInput({
                primaryKeyword: 'keyword',
                content: JSON.stringify({
                    type: 'doc',
                    content: [
                        {
                            type: 'paragraph', 
                            content: [
                                {
                                    type: 'text', 
                                    text: 'this is a content keyword ' + words300
                                }
                            ]
                        }
                    ]
                })
            })).run();
            expect(result.score).toBe(100);
            expect(result.message).toBe('Primary keyword found in the beginning of the content');

            const result2 = new PrimaryKeywordInBeginningOfContentTest(getInput({
                primaryKeyword: 'keyword',
                content: JSON.stringify({
                    type: 'doc',
                    content: [
                        {
                            type: 'paragraph', 
                            content: [
                                {
                                    type: 'text', 
                                    text: words300 + 'this is a content keyword ' + words300
                                }
                            ]
                        }
                    ]
                })
            })).run();
            expect(result2.score).toBe(0);
            expect(result2.message).toBe('Primary keyword not found in the beginning of the content');

        });

    });

    describe('all keywords in content', () => {

        test('runs for each keyword separately', () => {

            const result = new AllKeywordsInContentTest(getInput({
                primaryKeyword: 'keyword',
                secondaryKeywords: ['keyword2', 'keyword3'],
                content: JSON.stringify({
                    type: 'doc',
                    content: [
                        {
                            type: 'paragraph', 
                            content: [
                                {
                                    type: 'text', 
                                    text: 'keyword this is a content keyword2 and keyword3'
                                }
                            ]
                        }
                    ]
                })
            })).run();

            expect(result.message).toBe('All keywords found in the content');
            expect(result.score).toBe(100);

        });

    });


    describe('content length', () => {

        test('for each length', () => {

            function testWithWordCount(count: number) {
                const words = Array.from({length: count}, () => 'word').join(' ');
                const result = new ContentLengthTest(getInput({
                    content: JSON.stringify({
                        type: 'doc',
                        content: [
                            {
                                type: 'paragraph', 
                                content: [
                                    {
                                        type: 'text', 
                                        text: words
                                    }
                                ]
                            }
                        ]
                    })
                })).run();
                return result;
            }

            expect(testWithWordCount(1).score).toBe(0);
            expect(testWithWordCount(1).message).toBe('Content is 1 word long. Consider using at least 400 words.');

            expect(testWithWordCount(100).score).toBe(0);
            expect(testWithWordCount(400).score).toBe(16);
            expect(testWithWordCount(1000).score).toBe(40);
            expect(testWithWordCount(1500).score).toBe(60);
            expect(testWithWordCount(2000).score).toBe(80);
            expect(testWithWordCount(2500).score).toBe(100);
            expect(testWithWordCount(3000).score).toBe(100);

            expect(testWithWordCount(500).message).toBe('Content is 500 words long');

        });

    });

});