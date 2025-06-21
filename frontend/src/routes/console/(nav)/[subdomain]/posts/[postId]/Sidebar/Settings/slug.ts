
export const SLUG_INVALID_CHARACTERS = [
    ":", 
    "/", 
    "?", 
    "#", 
    "[", 
    "]", 
    "@", 
    "!", 
    "$", 
    "&", 
    "'", 
    "(", 
    ")", 
    "*", 
    "+", 
    ",", 
    ";", 
    "=",
    "%",
];

export function slugGetInvalidCharater(slug: string): string|null {

    const invalidCharacters = SLUG_INVALID_CHARACTERS.filter((char) => slug.includes(char));

    if (invalidCharacters.length > 0) {
        return invalidCharacters[0]!;
    }

    return null;

}