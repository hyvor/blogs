// @ts-ignore
import { diffWords } from 'diff';

export function getDiffWordsCount(oldText: string, newText: string) {
    const wordDiff = diffWords(oldText, newText);

    // Count the changed words
    let changedWordsCount = 0;
    wordDiff.forEach((part: any) => {
        if (part.removed || part.added) {
            // Word is either removed or added
            changedWordsCount += part.count;
        }
    });

    return changedWordsCount;
}