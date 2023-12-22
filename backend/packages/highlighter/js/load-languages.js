import shiki from 'shiki';

const allLanguages = shiki.BUNDLED_LANGUAGES

export function getLanguagesToLoad(language) {

    const languagesToLoad = [language];

    addEmbeddedLanguages(language);

    function addEmbeddedLanguages(language) {
        const embeddedLangs = language.embeddedLangs || [];

        embeddedLangs.forEach(embeddedLang => {

            if (findLanguage(embeddedLang, languagesToLoad)) {
                return
            }

            languagesToLoad.push(findLanguage(embeddedLang))

            addEmbeddedLanguages(embeddedLangs)

        })
    }

    return languagesToLoad;

}

export function findLanguage(languageKey, findIn = allLanguages) {

    return findIn.find(
        lang => 
            lang.id === languageKey || 
            (   
                lang.aliases && 
                lang.aliases.includes(languageKey)
            )
    );

}