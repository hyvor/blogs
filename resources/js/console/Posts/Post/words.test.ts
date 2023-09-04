import { expect, test } from "vitest";
import { getWords } from "./words";



test('count words', () => {

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
    expect(getWords("你好")).toStrictEqual(['你', '好']);
    expect(getWords("你好 你好")).toStrictEqual(['你', '好', '你', '好']);
    expect(getWords("你好，你好")).toStrictEqual(['你', '好', '你', '好']);

    // Japanese (normal)
    expect(getWords("こんにちは")).toStrictEqual(['こんにちは']);
    expect(getWords("こんにちは こんにちは")).toStrictEqual(['こんにちは', 'こんにちは']);
    expect(getWords("こんにちは、こんにちは")).toStrictEqual(['こんにちは', 'こんにちは']);
    
    // japanese (kanji)
    expect(getWords("今日は")).toStrictEqual(['今日', 'は']);
    expect(getWords("今日は 今日は")).toStrictEqual(['今日', 'は', '今日', 'は']);
    
    // Korean
    expect(getWords("안녕하세요")).toStrictEqual(['안녕하세요']);
    expect(getWords("안녕하세요 안녕하세요")).toStrictEqual(['안녕하세요', '안녕하세요']);
    expect(getWords("안녕하세요, 안녕하세요")).toStrictEqual(['안녕하세요', '안녕하세요']);

});