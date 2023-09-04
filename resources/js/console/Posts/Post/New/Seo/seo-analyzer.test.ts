import { describe, expect, test } from 'vitest'
import { AllKeywordsInContentTest, AllKeywordsInImgAltTest, AllKeywordsInSubHeadingsTest, ContentLengthTest, ExternalLinksTest, ImageAltTest, ImagesCountTest, Input, InternalLinksTest, KeywordDensityTest, PrimaryKeywordInBeginningOfContentTest, PrimaryKeywordInDescriptionTest, PrimaryKeywordInSlugTest, PrimaryKeywordInTitleTest, SlugLengthTest, Test } from './seo-analyzer';
import { pmc } from "../../../../../../../e2e/helpers/prosemirror-test-helper";

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
            languageCode: 'en',
        },
        ...input,
    }
}

describe('helpers', () => {

    test('keywords in content', () => {
        
        class FinalTest extends Test {
            t(content: string, keyword: string) {
                return this.keywordInString(keyword, content);
            }
        }

        function t(content: string, keyword: string, expected: boolean) {
            expect(new FinalTest({} as any).t(content, keyword)).toBe(expected);
        }

        t('keyword', 'keyword', true);
        t('this keyword', 'keyword', true);
        t('keyword this', 'keyword', true);
        t('this keyword this', 'keyword', true);
        t('keyword2', 'keyword', false);
        t('2keyword', 'keyword', false);
        t('keyword.', 'keyword', true);
        t('.keyword', 'keyword', true);
        t('keyword?', 'keyword', true);
        t('keyword!', 'keyword', true);
        t('keyword❤️', 'keyword', true);
        t('keywords', 'keyword', false);
        t('Keyword', 'keyword', true);
        t('kEyWoRd', 'keyword', true);
        t('keyword keyword', 'keyword', true);
        t('keyword keyword keyword', 'keyword', true);

        // non-ascii
        t('යතුර', 'යතුර', true);
        t('යතුරයතුර', 'යතුර', false);
        t('this is යතුර', 'යතුර', true);
        t('යතුර this is', 'යතුර', true);
        t('යතුර2', 'යතුර', false);
        t('යතුර.', 'යතුර', true);
        t('.යතුර', 'යතුර', true);
        t('යතුර?', 'යතුර', true);
        t('යතුර!', 'යතුර', true);

        // chinese
        t('这是关键字', '关键字', true);
        t('关键字', '这', false);

        // japanese
        t('これはキーワードです', 'キーワード', true);
        t('キーワード', 'これ', false);

    });

})

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

        test('when exactly matched', () => {
            const result = new PrimaryKeywordInSlugTest(getInput({
                primaryKeyword: 'keyword',
                slug: 'keyword'
            })).run();
            expect(result.score).toBe(100);
            expect(result.message).toBe('Primary keyword found in the slug');
        });
        
        test('when keyword is found', () => {
            const result = new PrimaryKeywordInSlugTest(getInput({
                primaryKeyword: 'keyword',
                slug: 'this-is-a-slug-with-keyword'
            })).run();
            expect(result.score).toBe(75);
            expect(result.message).toBe('Primary keyword found in the slug with other words');
        });

        test('multi word keyword', () => {
            const result = new PrimaryKeywordInSlugTest(getInput({
                primaryKeyword: 'Multi Word Keyword',
                slug: 'this-is-a-slug-with-multi-word-keyword',
            })).run();
            expect(result.score).toBe(75);
            expect(result.message).toBe('Primary keyword found in the slug with other words');
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

        test('non-ascci', () => {

            const result = new PrimaryKeywordInBeginningOfContentTest(getInput({
                primaryKeyword: 'යතුර',
                content: pmc.docP('යතුර is here')
            })).run();
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

        test('when some keywords are missing', () => {

            const result = new AllKeywordsInContentTest(getInput({
                primaryKeyword: 'keyword',
                secondaryKeywords: ['keyword2', 'keyword3'],
                content: pmc.docP('keyword this is a content keyword2'),
            })).run();

            expect(result.message).toBe('Some keywords are missing in the content: keyword3');
            expect(Math.floor(result.score)).toBe(66);

        });

        test('when all are missing', () => {
                
            const result = new AllKeywordsInContentTest(getInput({
                primaryKeyword: 'keyword',
                secondaryKeywords: ['keyword2', 'keyword3'],
                content: pmc.docP('this is a content'),
            })).run();

            expect(result.message).toBe('Some keywords are missing in the content: keyword, keyword2, keyword3');
            expect(result.score).toBe(0);
    
        })

    });

    describe('all keywords in headings', () => {
        
        test('when all keywords are found', () => {

            const result = new AllKeywordsInSubHeadingsTest(getInput({
                primaryKeyword: 'keyword',
                secondaryKeywords: ['keyword2', 'keyword3'],
                content: pmc.doc([
                    pmc.h('keyword'),
                    pmc.h('keyword2'),
                    pmc.h('keyword3'),
                ]),
            })).run();

            expect(result.message).toBe('All keywords found in subheadings');
            expect(result.score).toBe(100);

        });

        test('when some keywords are missing', () => {

            const result = new AllKeywordsInSubHeadingsTest(getInput({
                primaryKeyword: 'keyword',
                secondaryKeywords: ['keyword2', 'keyword3'],
                content: pmc.doc([
                    pmc.h('keyword'),
                    pmc.h('keyword2'),
                    pmc.h('keyword4'),
                ]),
            })).run();

            expect(result.message).toBe('Some keywords not found in subheadings: keyword3');
            expect(Math.floor(result.score)).toBe(66);

        });

        test('when all keywords are missing', () => {

            const result = new AllKeywordsInSubHeadingsTest(getInput({
                primaryKeyword: 'keyword',
                secondaryKeywords: ['keyword2', 'keyword3'],
                content: pmc.doc([
                    pmc.h('keyword4'),
                    pmc.h('keyword5'),
                    pmc.h('keyword6'),
                ]),
            })).run();

            expect(result.message).toBe('Some keywords not found in subheadings: keyword, keyword2, keyword3');
            expect(result.score).toBe(0);

        });

    });

    describe('all keywords in image alt', () => {

        test('when all keywords are found', () => {

            const result = new AllKeywordsInImgAltTest(getInput({
                primaryKeyword: 'keyword',
                secondaryKeywords: ['keyword2', 'keyword3'],
                content: pmc.doc([
                    {
                        type: 'image',
                        attrs: {
                            alt: 'keyword keyword2 keyword3',
                        },
                    },
                ]),
            })).run();

            expect(result.message).toBe('All keywords found in image alt attributes');
            expect(result.score).toBe(100);

        });

        test('when some keywords are missing', () => {

            const result = new AllKeywordsInImgAltTest(getInput({
                primaryKeyword: 'keyword',
                secondaryKeywords: ['keyword2', 'keyword3'],
                content: pmc.doc([
                    {
                        type: 'image',
                        attrs: {
                            alt: 'keyword keyword2 keyword4',
                        },
                    },
                ]),
            })).run();

            expect(result.message).toBe('Some keywords not found in image alt attributes: keyword3');
            expect(Math.floor(result.score)).toBe(66);

        });

        test('when all keywords are missing', () => {

            const result = new AllKeywordsInImgAltTest(getInput({
                primaryKeyword: 'keyword',
                secondaryKeywords: ['keyword2', 'keyword3'],
                content: pmc.doc([
                    {
                        type: 'image',
                        attrs: {
                            alt: 'keyword4 keyword5 keyword6',
                        },
                    },
                ]),
            })).run();

            expect(result.message).toBe('Some keywords not found in image alt attributes: keyword, keyword2, keyword3');
            expect(result.score).toBe(0);

        });

    });

    describe('keyword density', () => {

        test('when ignored', () => {
            const result = new KeywordDensityTest(getInput({
                primaryKeyword: null,
                secondaryKeywords: [],
                content: ''
            })).run();

            expect(result.message).toBe('Ignored keywords density test. Add keywords');
            expect(result.ignore).toBe(true);
        });

        test('with each density', () => {

            function getScoreForDensity(density: number) {

                const keywordCount = density * 100;
                const words = Array.from({length: (100 - density) * 100}, () => 'other').join(' ');
                const keywords = Array.from({length: keywordCount}, () => 'keyword').join(' ');

                const result = new KeywordDensityTest(getInput({
                    primaryKeyword: 'keyword',
                    secondaryKeywords: [],
                    content: pmc.docP(keywords + ' ' + words)
                })).run();
                
                return {
                    score: result.score,
                    message: result.message,
                }
            }

            expect(getScoreForDensity(10)).toContain({
                score: 0,
                message: "Keyword density is 10.00%, which is too high. 1000 keywords found."
            })

            expect(getScoreForDensity(3)).toContain({
                score: 50,
                message: "Keyword density is 3.00%, which may be too high. 300 keywords found."
            })

            expect(getScoreForDensity(1)).toContain({
                score: 100,
                message: "Keyword density is 1.00%, which is good. 100 keywords found."
            })

            expect(getScoreForDensity(0.4)).toContain({
                score: 50,
                message: "Keyword density is 0.40%, which may be too low. 40 keywords found."
            })

            expect(getScoreForDensity(0.09)).toContain({
                score: 0,
                message: "Keyword density is 0.09%, which is too low. 9 keywords found."
            })

        });

    });


    describe('slug length', () => {

        test('slug for different lengths', () => {

            function runForSlugLength(length: number) {
                return new SlugLengthTest(getInput({
                    primaryKeyword: 'keyword',
                    secondaryKeywords: [],
                    content: '',
                    slug: Array.from({length}, () => 'a').join(''),
                })).run();
            }

            expect(runForSlugLength(0)).toContain({
                ignore: true,
                message: 'Slug length test ignored because slug is empty'
            });

            expect(runForSlugLength(10)).toContain({
                score: 100,
                message: 'Slug is 10 characters long'
            });

            expect(runForSlugLength(36)).toContain({
                score: 50,
                message: 'Slug is 36 characters long. Consider using a shorter slug'
            });

            expect(runForSlugLength(51)).toContain({
                score: 0,
                message: 'Slug is 51 characters long. Consider using a shorter slug'
            });

        });

    });

    describe('external links', () => {

        test('when no external links', () => {

            const result = new ExternalLinksTest(getInput({
                primaryKeyword: 'keyword',
                secondaryKeywords: [],
                content: pmc.docP('this is a content'),
            })).run();

            expect(result.message).toBe('No external links found');
            expect(result.score).toBe(0);

        }); 
        
        test('when has 1 external link', () => {

            const result = new ExternalLinksTest(getInput({
                primaryKeyword: 'keyword',
                secondaryKeywords: [],
                content: pmc.docP([
                    pmc.link('https://example.com', 'example')
                ]),
                blogUrl: 'https://hyvor.com/blog'
            })).run();

            expect(result.message).toBe('1 external link found');
            expect(result.score).toBe(100);

        })

        test('when has multiple external links', () => {

            const result = new ExternalLinksTest(getInput({
                content: pmc.docP([
                    pmc.link('https://example.com', 'example'),
                    pmc.link('https://example.com', 'example'),
                ]),
                blogUrl: 'https://hyvor.com/blog'
            })).run();

            expect(result.message).toBe('2 external links found');
            expect(result.score).toBe(100);

        })

    });

    describe('internal links', () => {

        test('when no internal links', () => {

            const result = new InternalLinksTest(getInput({
                primaryKeyword: 'keyword',
                secondaryKeywords: [],
                content: pmc.docP('this is a content'),
            })).run();

            expect(result.message).toBe('No internal links found');
            expect(result.score).toBe(0);

        }); 

        test('when has 1 internal link', () => {

            const result = new InternalLinksTest(getInput({
                primaryKeyword: 'keyword',
                secondaryKeywords: [],
                content: pmc.docP([
                    pmc.link('https://hyvor.com', 'HYVOR')
                ]),
                blogUrl: 'https://hyvor.com/blog'
            })).run();

            expect(result.message).toBe('1 internal link found');
            expect(result.score).toBe(100);

        })

        test('when has multiple external links', () => {

            const result = new InternalLinksTest(getInput({
                content: pmc.docP([
                    pmc.link('https://hyvor.com', 'example'),
                    pmc.link('https://hyvor.com', 'example'),
                ]),
                blogUrl: 'https://hyvor.com/blog'
            })).run();

            expect(result.message).toBe('2 internal links found');
            expect(result.score).toBe(100);

        })

    });

    describe('Images count test', () => {

        test('by count', () => {

            function getByCount(count: number) {
                return new ImagesCountTest(getInput({
                    content: pmc.doc([
                        ...Array.from({length: count}, () =>
                            pmc.img('https://example.com/image1.jpg', 'image1'),
                        )
                    ]),
                })).run();
            }

            expect(getByCount(0)).toContain({
                score: 0,
                message: '0 images found'
            });

            expect(getByCount(1)).toContain({
                score: 70,
                message: '1 image found'
            });

            expect(getByCount(2)).toContain({
                score: 80,
                message: '2 images found'
            });

            expect(getByCount(3)).toContain({
                score: 90,
                message: '3 images found'
            });

            expect(getByCount(4)).toContain({
                score: 100,
                message: '4 images found'
            });

            expect(getByCount(5)).toContain({
                score: 100,
                message: '5 images found'
            });

        });

    });

    describe('all images have alt', () => {

        test('when no images', () => {

            const result = new ImageAltTest(getInput({
                content: pmc.doc([]),
            })).run();

            expect(result.ignore).toBe(true);
            expect(result.message).toBe('Image alt test ignored because no images found');

        });

        test('when some missing alt', () => {

            const result = new ImageAltTest(getInput({
                content: pmc.doc([
                    pmc.img('https://example.com/image1.jpg'),
                    pmc.img('https://example.com/image2.jpg', 'image2'),
                ]),
            })).run();

            expect(result.score).toBe(0);
            expect(result.message).toBe('1 image missing alt attributes');

        });
        
        test('when all has alt', () => {

            const result = new ImageAltTest(getInput({
                content: pmc.doc([
                    pmc.img('https://example.com/image1.jpg', 'image1'),
                    pmc.img('https://example.com/image2.jpg', 'image2'),
                ]),
            })).run();

            expect(result.score).toBe(100);
            expect(result.message).toBe('All images have alt attributes');

        });

    });



});