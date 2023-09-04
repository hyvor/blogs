
export function getOneCharPerWordScriptsRegexPart() {
    return '(?:' +
        '\\p{Unified_Ideograph}' + // Han (Chinese)
    ')';
}

export function getWords(str: string) {
    str = str.trim();

    if (str === "")
        return [];

    const regex = new RegExp(
        // split by non-letter characters
        '\\P{L}+' +

        // or
        '|' +

        /*
         * Split by zero or more whitespace characters
         * if they are preceded by a CJK character.
         */
        '(?:(?<=' +
            getOneCharPerWordScriptsRegexPart() +            
        ')\\s*)'
    
    , 'u');

    return str.split(regex).filter(word => word !== "");
}

export function getWordsCount(str: string) {
    return getWords(str).length;
}


export function getKeywordMatchingRegex(keyword: string, global: boolean = false) {

    const singleCharPart = getOneCharPerWordScriptsRegexPart();

    const regExp = new RegExp(
        '(?<=' + // lookbehind
            /* 
            * a non-letter/number character
            * p{L} matches any kind of letter from any language
            * p{N} matches any kind of numeric character in any script
            * this is enough in most cases
            */
            '[^\\p{L}\\p{N}]' +
            
            '|' + // or
            
            /*
            * a CJK (Chinese, Japanese, Korean) character
            * this is needed because, in CJK characters, word = character
            */
            singleCharPart + // a CJK (Chinese, Japanese, Korean) character
            
            '|' + // or

            // start of the string
            '^' +
        ')' +
        keyword +
        '(?=' + // lookahead
            '[^\\p{L}\\p{N}]' +
            '|' +
            singleCharPart +
            '|' +
            '$' + // end of the string
        ')',
        global ? 'giu' : 'iu'
    );

    console.log(regExp);

    return regExp;

}