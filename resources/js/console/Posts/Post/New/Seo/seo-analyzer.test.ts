import { describe, expect, test } from 'vitest'
import { Input, PrimaryKeywordInBeginningOfContentTest, PrimaryKeywordInDescriptionTest, PrimaryKeywordInSlugTest, PrimaryKeywordInTitleTest } from './seo-analyzer';

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
            expect(result.message).toBe('Primary keyword found in title');
        })

        test('when keyword is not contained in the first 50', () => {
            const result = new PrimaryKeywordInTitleTest(getInput({
                primaryKeyword: 'keyword',
                title: 'this is a title with this is a title with this is a title with keyword'
            })).run();
            expect(result.score).toBe(49);
            expect(result.message).toBe('Primary keyword found in title, but not within first 50 characters');
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
            expect(result.message).toBe('Primary keyword found in description');
        });

        test('when keyword is not found', () => {
            const result = new PrimaryKeywordInDescriptionTest(getInput({
                primaryKeyword: 'keyword',
                description: 'this is a description'
            })).run();
            expect(result.score).toBe(0);
            expect(result.message).toBe('Primary keyword not found in description');
        });
        
    });

    describe('primary keyword in slug', () => {
        
        test('when keyword is found', () => {
            const result = new PrimaryKeywordInSlugTest(getInput({
                primaryKeyword: 'keyword',
                slug: 'this-is-a-slug-with-keyword'
            })).run();
            expect(result.score).toBe(100);
            expect(result.message).toBe('Primary keyword found in slug');
        });

        test('multi word keyword', () => {
            const result = new PrimaryKeywordInSlugTest(getInput({
                primaryKeyword: 'Multi Word Keyword',
                slug: 'this-is-a-slug-with-multi-word-keyword',
            })).run();
            expect(result.score).toBe(100);
            expect(result.message).toBe('Primary keyword found in slug');
        });

        test('when keyword is not found', () => {
            const result = new PrimaryKeywordInSlugTest(getInput({
                primaryKeyword: 'keyword',
                slug: 'this-is-a-slug'
            })).run();
            expect(result.score).toBe(0);
            expect(result.message).toBe('Primary keyword not found in slug');
        });
        
    });

    describe('primary keyword in beginning of content', () => {

        test('when keyword is not found', () => {
            const result = new PrimaryKeywordInBeginningOfContentTest(getInput({
                primaryKeyword: 'keyword',
                content: null
            })).run();
            expect(result.score).toBe(0);
            expect(result.message).toBe('Primary keyword not found in the beginning of content');
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
            expect(result.message).toBe('Primary keyword found in the beginning of content');
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
            expect(result.message).toBe('Primary keyword found in the beginning of content');

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
            expect(result2.message).toBe('Primary keyword not found in the beginning of content');

        });

    });

});