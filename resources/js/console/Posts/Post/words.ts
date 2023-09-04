
function getIntlLanguageCode(languageCode?: string | undefined) : string | undefined {
    if (!languageCode)
        return undefined;
    try {
        // @ts-ignore
        const codes = Intl.getCanonicalLocales(languageCode);
        return codes[0];
    } catch (err) {
        return undefined;
    }
}


export function getWords(str: string, languageCode?: string | undefined) {
    str = str.trim();

    if (str === "")
        return [];

    if (typeof Intl.Segmenter === "function") {

        const segmenter = new Intl.Segmenter(getIntlLanguageCode(languageCode), {granularity: "word"});
        const segments = segmenter.segment(str)[Symbol.iterator]();

        let words = [];
        for (const segment of segments) {
            if (segment.isWordLike)
                words.push(segment.segment);
        }
        
        return words;

    } else {
        return str.split(/\s+/).filter(word => word !== "");
    }

}

export function getWordsCount(str: string, languageCode: string) {
    return getWords(str, languageCode).length;
}

export function getOccurrencesOfKeywordInContent(keyword: string, content: string, languageCode?: string | undefined) {

    if (typeof Intl.Segmenter !== "function") {
        return getOccurrencesOfKeywordInContentWithoutIntl(keyword, content);
    }

    const segmenter = new Intl.Segmenter(
        getIntlLanguageCode(languageCode), 
        {granularity: "word"}
    );
    
    let occurrences = 0;

    // the below problem is not occurred here because the keywords are short
    const keywordWords = [...segmenter.segment(keyword)]
        //.filter(segment => segment.isWordLike)
        .map(segment => segment.segment);
    
    /**
     * Bug fix: Directly converting segment to [] causes extremely high memory usage on large inputs
     * due to saving the input in each segment.
     * Therefore, we iterate and only save the words in an array
     */
    const contentSegmentsIterator = segmenter.segment(content)[Symbol.iterator]();


    const contentWords : string[] = [];
    for (const segment of contentSegmentsIterator) {
        contentWords.push(segment.segment);
    }

    contentWords.forEach((contentWord, i) => {

        if (
            contentWord.toLowerCase() !== 
            keywordWords[0].toLowerCase()
        )
            return;

        let found = true;

        for (let j = 1; j < keywordWords.length; j++) {
            if (
                contentWords[i + j].toLowerCase() !== 
                keywordWords[j].toLowerCase()
            ) {
                found = false;
                break;
            }
        }

        if (found)
            occurrences++;

    });

    return occurrences;

}

function getOccurrencesOfKeywordInContentWithoutIntl(keyword: string, content: string) {

    const regex = new RegExp(
        '(?<=' + // lookbehind
            /* 
            * a non-letter/number character
            * p{L} matches any kind of letter from any language
            * p{N} matches any kind of numeric character in any script
            * this is enough in most cases
            */
            '[^\\p{L}\\p{N}]' +
            
            '|' + // or

            // start of the string
            '^' +
        ')' +
        keyword +
        '(?=' + // lookahead
            '[^\\p{L}\\p{N}]' +
            '|' + // or
            '$' + // end of the string
        ')',
        'giu'
    );

    const matches = content.match(regex);

    if (!matches)
        return 0;

    return matches.length;

}