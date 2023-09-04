import {Plugin} from "prosemirror-state"
import { EditorView } from "prosemirror-view";
import { getWordsCount } from "../words";
import { getCurrentPostValues } from "../helpers";
import { getTextFromContent, getTextFromDoc } from "./helpers";

export default function wordCountPlugin() {
    return new Plugin({
        view(editorView) { return new WordCountPlugin(editorView) }
    })
}

class WordCountPlugin {

    constructor(private view: EditorView) {
        this.updateCount(view);
    }
  
    update(view: EditorView) {
        this.updateCount(view);
    }

    updateCount(view: EditorView) {
        const wordCount = document.getElementById("pm-word-count");

        const postValues = getCurrentPostValues();

        if (!postValues) {
            return;
        }

        const languageCode = postValues.currentLanguage.code;
        const text = getTextFromDoc(view.state.doc);

        if (wordCount)
            wordCount.innerHTML = 
                getWordsCount(text, languageCode) + 
                " Words";
    }

}