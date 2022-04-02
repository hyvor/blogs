const shiki = require('shiki');

const input = JSON.parse(process.argv[2]);

if (input.type === 'tokens') {

    shiki.getHighlighter({
        theme: input.theme
    }).then((highlighter) => {

        const tokens = highlighter.codeToThemedTokens(input.code, input.language)
        const theme = highlighter.getTheme();

        const output = JSON.stringify({
            tokens,
            theme
        });

        process.stdout.write(output);
    })

} else if (input.type === 'languages') {

    process.stdout.write(JSON.stringify(shiki.BUNDLED_LANGUAGES));

} else if (input.type === 'themes') {

    process.stdout.write(JSON.stringify(shiki.BUNDLED_THEMES));

}