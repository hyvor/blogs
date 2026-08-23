import { LANGUAGES_CONFIG } from "./routes/(marketing)/[[lang]]/marketingLang";

const LANGUAGES_TO_TRANSLATE_TO = LANGUAGES_CONFIG.filter(lang => !lang.default).map(lang => lang.code);

const TRANSLATABLES = [

    {
        source: 'src/routes/(marketing)/[[lang]]/locale/en.json',
        target: 'src/routes/(marketing)/[[lang]]/locale/{{lang}}.json',
    },

    {
        source: 'frontend/src/routes/(marketing)/[[lang]]/docs/[[slug]]/content/'
    }

];