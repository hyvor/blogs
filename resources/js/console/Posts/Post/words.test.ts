import { bench, describe, expect, test } from "vitest";
import { getOccurrencesOfKeywordInContent, getWords } from "./words";


test('get words', () => {

    expect(getWords("")).toStrictEqual([])
    expect(getWords(" ")).toStrictEqual([])
    expect(getWords("word")).toStrictEqual(['word']);
    expect(getWords("word     word")).toStrictEqual(['word', 'word']);
    expect(getWords("word word")).toStrictEqual(['word', 'word']);
    expect(getWords("word,word")).toStrictEqual(['word', 'word']);
    expect(getWords("word, word")).toStrictEqual(['word', 'word']);
    expect(getWords("word!word.")).toStrictEqual(['word', 'word']);

    // emoji
    expect(getWords("Hello 👍")).toStrictEqual(['Hello']);
    expect(getWords("👍")).toStrictEqual([]);
    expect(getWords("👍 👍")).toStrictEqual([]);

    // Chinese
    expect(getWords("你好")).toStrictEqual(['你好']);
    expect(getWords("你好 你好", 'zh-cn')).toStrictEqual(['你好', '你好']);
    expect(getWords("你好，你好")).toStrictEqual(['你好', '你好']);
    expect(getWords('随着数字时代的到来')).toStrictEqual(["随着", "数字", "时代", "的", "到来"]);

    // Japanese
    expect(getWords("こんにちは")).toStrictEqual(['こんにちは']);
    expect(getWords("こんにちは こんにちは")).toStrictEqual(['こんにちは', 'こんにちは']);
    expect(getWords("こんにちは、こんにちは")).toStrictEqual(['こんにちは', 'こんにちは']);
    
    // Korean
    expect(getWords("안녕하세요")).toStrictEqual(['안녕하세요']);
    expect(getWords("안녕하세요 안녕하세요")).toStrictEqual(['안녕하세요', '안녕하세요']);
    expect(getWords("안녕하세요, 안녕하세요")).toStrictEqual(['안녕하세요', '안녕하세요']);

});


describe('occurrences of keyword', () => {

    test('test temp', () => {
        // 2 words keyword
        expect(getOccurrencesOfKeywordInContent('blogging platform', 'blogging platform')).toBe(1);
    })


    test('occurrences of keyword in content', () => {

        // empty keyword
        expect(getOccurrencesOfKeywordInContent("", "")).toBe(0);
        
        // 1 word keyword
        expect(getOccurrencesOfKeywordInContent("word", "")).toBe(0);
        expect(getOccurrencesOfKeywordInContent('word', 'word')).toBe(1);
        expect(getOccurrencesOfKeywordInContent('word', 'word word')).toBe(2);
        expect(getOccurrencesOfKeywordInContent('word', 'word word word')).toBe(3);
        expect(getOccurrencesOfKeywordInContent('keyword', 'word word')).toBe(0);
        expect(getOccurrencesOfKeywordInContent('word', 'keyword word')).toBe(1);
        expect(getOccurrencesOfKeywordInContent('word', 'word keyword')).toBe(1);

        // case insensitive
        expect(getOccurrencesOfKeywordInContent('word', 'Word')).toBe(1);
        expect(getOccurrencesOfKeywordInContent('word', 'second Word')).toBe(1);
        expect(getOccurrencesOfKeywordInContent('Word', 'Word not Word and word')).toBe(3);

        // 2 words keyword
        expect(getOccurrencesOfKeywordInContent('blogging platform', 'blogging platform')).toBe(1);
        expect(getOccurrencesOfKeywordInContent('blogging platform', 'blogging platform blogging platform')).toBe(2);
        expect(getOccurrencesOfKeywordInContent('blogging platform', 'Hyvor Blogs is a blogging platform')).toBe(1);

        // 4 words keyword
        expect(getOccurrencesOfKeywordInContent('blogging platform for developers', 'blogging platform for developers')).toBe(1);
        expect(getOccurrencesOfKeywordInContent(
            'blogging platform for developers', 
            'Hyvor Blogs is a blogging platform for developers'
        )).toBe(1);

        // with break
        expect(getOccurrencesOfKeywordInContent('blogging platform', 'blogging platform.')).toBe(1);
        expect(getOccurrencesOfKeywordInContent('blogging platform', 'blogging| platform')).toBe(0);
        expect(getOccurrencesOfKeywordInContent('blogging platform', 'blogging. platform')).toBe(0);
        expect(getOccurrencesOfKeywordInContent('blogging platform', 'blog gingplatform')).toBe(0);

        // chinese
        expect(getOccurrencesOfKeywordInContent('你好', '你好')).toBe(1);
        expect(getOccurrencesOfKeywordInContent('你好', '你好 你好')).toBe(2);
        expect(getOccurrencesOfKeywordInContent('你好', '你好 随着数字时代的到来')).toBe(1);

        // japanese
        expect(getOccurrencesOfKeywordInContent('こんにちは', 'こんにちは')).toBe(1);
        expect(getOccurrencesOfKeywordInContent('こんにちは', 'こんにちは こんにちは')).toBe(2);

    });

    test('occurrence performance', () => {

        // performance
        const content = Array(10000).fill('word').join(' ');
        expect(getOccurrencesOfKeywordInContent('keyword', content)).toBe(0); 

    });

})