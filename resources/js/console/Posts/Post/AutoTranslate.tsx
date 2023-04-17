import {useActions, useValues} from "kea";
import languagesLogic from "../../logic/languagesLogic";
import getSubdomain from "../../logic-helpers/subdomain";
import {usePostActions, usePostValues} from "./helpers";
import {PopupConfirm} from "../../ReusableComponents/Popup";
import React, {useState} from "react";
import DualSetting from "../../ReusableComponents/DualSetting";
import api from "../../lib/api";
import {hasSubscription} from "../../lib/blog-helpers";
import billingLogic from "../../logic/billing/billingLogic";
import Select from "../../ReusableComponents/Select";
import {Language} from "../../types";

export default function AutoTranslate({id, onCancel}: { id: number, onCancel: Function}) {

    const subdomain = getSubdomain();
    const { languages } = useValues(languagesLogic({subdomain}))
    const { editorState, post } = usePostValues(id)
    const { updateCurrentPostVariantValue, changeEditorState, savePost } = usePostActions(id);
    const { navigateToBilling } = useActions(billingLogic({subdomain}));

    const currentLanguage = languages.find(l => l.id === editorState.languageId)
    const primaryLanguage = languages.find(l => l.is_primary) as Language;

    const [variantLanguageId, setVariantLanguageId] = useState(primaryLanguage.id);
    const [sourceLanguage, setSourceLanguage] = useState(findMatchingLanguage(primaryLanguage.code, SOURCE_LANGUAGES));
    const [targetLanguage, setTargetLanguage] = useState(
        findMatchingLanguage(currentLanguage?.code || primaryLanguage.code, TARGET_LANGUAGES)
    );

    const [isTranslating, setIsTranslating] = useState(false);

    function handleTranslate() {

        if (!isBlogSubscribed) {
            return navigateToBilling();
        }

        setIsTranslating(true);

        api.post<{title: string, content: string}>(subdomain, `/ai/translate`, {
            title: post.variants[0].title,
            content: post.variants[0].content,
            source_lang: sourceLanguage,
            target_lang: targetLanguage
        }).then(data => {

            updateCurrentPostVariantValue('title', data.title);
            updateCurrentPostVariantValue('content', data.content);
            updateCurrentPostVariantValue('content_unsaved', data.content);

            changeEditorState('version', editorState.version + 1);

            savePost();

            onCancel();

        }).finally(() => {
            setIsTranslating(false);
        })

    }

    const isBlogSubscribed = hasSubscription(subdomain);

    const sourceLangOptions = Object.keys(SOURCE_LANGUAGES).map(key => {
        return {
            value: key,
            label: SOURCE_LANGUAGES[key as keyof typeof SOURCE_LANGUAGES]
        }
    });
    const targetLangOptions = Object.keys(TARGET_LANGUAGES).map(key => {
        return {
            value: key,
            label: TARGET_LANGUAGES[key as keyof typeof TARGET_LANGUAGES]
        }
    });

    return <PopupConfirm
        title={isBlogSubscribed ? "Auto-Translate" : "Upgrade Required"}
        text={
            isBlogSubscribed ?
                <div>
                    <DualSetting
                        title="Source Variant"
                        description="The variant you want to translate from"
                        right={
                            <div>
                                {
                                    languages.map(l => {
                                        if (l.id === currentLanguage?.id) {
                                            return null;
                                        }
                                        return <span
                                            className={"global-lang-tag" + (variantLanguageId === l.id ? " active" : "")}
                                            key={l.id}
                                            onClick={() => setVariantLanguageId(l.id)}
                                        >{l.code}</span>
                                    })
                                }
                            </div>
                        }
                    />
                    <DualSetting
                        title="Source Language"
                        description={
                            <div>
                                The language you want to translate from. { Object.keys(SOURCE_LANGUAGES).length } languages supported.
                            </div>
                        }
                        right={
                            <Select
                                value={sourceLangOptions.find(o => o.value === sourceLanguage)}
                                options={sourceLangOptions}
                                onChange={(v: any) => setSourceLanguage(v.value)}
                            />
                        }
                    />
                    <DualSetting
                        title="Target Language"
                        description={
                            <div>
                                The language you want to translate to. { Object.keys(TARGET_LANGUAGES).length } languages supported.
                            </div>
                        }
                        right={
                            <Select
                                value={targetLangOptions.find(o => o.value === targetLanguage)}
                                options={targetLangOptions}
                                onChange={(v: any) => setTargetLanguage(v.value)}
                            />
                        }
                    />
                </div> :
                <div>
                    Auto-translation is a premium feature available in all paid subscription plans. Upgrade now to easily translate your posts into multiple languages. See <a className="link" href="/pricing" target="_blank">pricing</a> for more details.
                </div>
        }
        name={isBlogSubscribed ? "Auto-Translate" : "Upgrade Now"}
        onClick={handleTranslate}
        onCancel={onCancel}
        isLoading={isTranslating}
        loadingName="Translating"
    />

}

function findMatchingLanguage(langCode: string, languages: Record<string, string>) {
    let matchingLang = Object.keys(languages).find(lang => lang === langCode.toUpperCase());

    if (!matchingLang) {
        // Try to find a matching language by the first two letters
        matchingLang = Object.keys(languages).find(
            lang => lang === langCode.toUpperCase().substring(0, 2)
        );
    }

    return matchingLang ? matchingLang : languages[Object.keys(languages)[0]];
}

const SOURCE_LANGUAGES = {
    'BG': 'Bulgarian',
    'CS': 'Czech',
    'DA': 'Danish',
    'DE': 'German',
    'EL': 'Greek',
    'EN': 'English',
    'ES': 'Spanish',
    'ET': 'Estonian',
    'FI': 'Finnish',
    'FR': 'French',
    'HU': 'Hungarian',
    'ID': 'Indonesian',
    'IT': 'Italian',
    'JA': 'Japanese',
    'KO': 'Korean',
    'LT': 'Lithuanian',
    'LV': 'Latvian',
    'NB': 'Norwegian',
    'NL': 'Dutch',
    'PL': 'Polish',
    'PT': 'Portuguese',
    'RO': 'Romanian',
    'RU': 'Russian',
    'SK': 'Slovak',
    'SL': 'Slovenian',
    'SV': 'Swedish',
    'TR': 'Turkish',
    'UK': 'Ukrainian',
    'ZH': 'Chinese'
}

const TARGET_LANGUAGES = {
    'BG': 'Bulgarian',
    'CS': 'Czech',
    'DA': 'Danish',
    'DE': 'German',
    'EL': 'Greek',
    'EN-US': 'English (US)',
    'EN-GB': 'English (UK)',
    'ES': 'Spanish',
    'ET': 'Estonian',
    'FI': 'Finnish',
    'FR': 'French',
    'HU': 'Hungarian',
    'ID': 'Indonesian',
    'IT': 'Italian',
    'JA': 'Japanese',
    'KO': 'Korean',
    'LT': 'Lithuanian',
    'LV': 'Latvian',
    'NB': 'Norwegian',
    'NL': 'Dutch',
    'PL': 'Polish',
    'PT-BR': 'Portuguese (Brazil)',
    'PT-PT': 'Portuguese (Portugal)',
    'RO': 'Romanian',
    'RU': 'Russian',
    'SK': 'Slovak',
    'SL': 'Slovenian',
    'SV': 'Swedish',
    'TR': 'Turkish',
    'UK': 'Ukrainian',
    'ZH': 'Chinese'
}