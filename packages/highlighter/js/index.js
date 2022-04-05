const shiki = require('shiki');
const { getLanguagesToLoad } = require('./load-languages');

const input = JSON.parse(process.argv[2]);

if (input.type === 'tokens') {

    const language = input.language

    const languagesToLoad = getLanguagesToLoad(language)

    const startTime = performance.now()

    shiki.getHighlighter({
        theme: input.theme,
        langs: languagesToLoad
    }).then((highlighter) => {
        const tokens = highlighter.codeToThemedTokens(input.code, language)

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

    process.stdout.write(JSON.stringify(shiki.BUNDLED_THEMES));

}