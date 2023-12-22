import shiki from 'shiki';
import { getLanguagesToLoad, findLanguage } from './load-languages.js';

const input = JSON.parse(process.argv[2]);

if (input.type === 'tokens') {

    let language = findLanguage(input.language)

    if (!language)
        language = findLanguage('html'); // set default to HTML

    const languagesToLoad = getLanguagesToLoad(language)

    const startTime = performance.now()

    shiki.getHighlighter({
        theme: input.theme,
        langs: languagesToLoad
    }).then((highlighter) => {
        const tokens = highlighter.codeToThemedTokens(input.code, language.id)

        const endTime = performance.now()

        const theme = highlighter.getTheme();

        const output = JSON.stringify({
            tokens,
            theme,
            time_seconds: (endTime - startTime) / 1000
        });

        process.stdout.write(output);
    })

} else if (input.type === 'languages') {

    process.stdout.write(JSON.stringify(shiki.BUNDLED_LANGUAGES));

} else if (input.type === 'themes') {

    process.stdout.write(JSON.stringify(shiki.BUNDLED_THEMES.filter(x => x !== 'css-variables')));

}
