export const DEEPL_SOURCE_LANGUAGES = {
  BG: "Bulgarian",
  CS: "Czech",
  DA: "Danish",
  DE: "German",
  EL: "Greek",
  EN: "English",
  ES: "Spanish",
  ET: "Estonian",
  FI: "Finnish",
  FR: "French",
  HU: "Hungarian",
  ID: "Indonesian",
  IT: "Italian",
  JA: "Japanese",
  KO: "Korean",
  LT: "Lithuanian",
  LV: "Latvian",
  NB: "Norwegian",
  NL: "Dutch",
  PL: "Polish",
  PT: "Portuguese",
  RO: "Romanian",
  RU: "Russian",
  SK: "Slovak",
  SL: "Slovenian",
  SV: "Swedish",
  TR: "Turkish",
  UK: "Ukrainian",
  ZH: "Chinese",
};

export const DEEPL_TARGET_LANGUAGES = {
  BG: "Bulgarian",
  CS: "Czech",
  DA: "Danish",
  DE: "German",
  EL: "Greek",
  "EN-US": "English (US)",
  "EN-GB": "English (UK)",
  ES: "Spanish",
  ET: "Estonian",
  FI: "Finnish",
  FR: "French",
  HU: "Hungarian",
  ID: "Indonesian",
  IT: "Italian",
  JA: "Japanese",
  KO: "Korean",
  LT: "Lithuanian",
  LV: "Latvian",
  NB: "Norwegian",
  NL: "Dutch",
  PL: "Polish",
  "PT-BR": "Portuguese (Brazil)",
  "PT-PT": "Portuguese (Portugal)",
  RO: "Romanian",
  RU: "Russian",
  SK: "Slovak",
  SL: "Slovenian",
  SV: "Swedish",
  TR: "Turkish",
  UK: "Ukrainian",
  ZH: "Chinese",
};

export function findMatchingLanguage<
  T extends typeof DEEPL_SOURCE_LANGUAGES | typeof DEEPL_TARGET_LANGUAGES,
>(langCode: string, languages: T): keyof T {
  let matchingLang = Object.keys(languages).find(
    (lang) => lang === langCode.toUpperCase(),
  );

  if (!matchingLang) {
    // Try to find a matching language by the first two letters
    matchingLang = Object.keys(languages).find(
      (lang) => lang === langCode.toUpperCase().substring(0, 2),
    );
  }

  return (matchingLang ? matchingLang : Object.keys(languages)[0]) as keyof T;
}
