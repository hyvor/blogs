import {Plugin} from "prosemirror-state"
import { EditorView } from "prosemirror-view";
import { getWordsCount } from "../words";

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
        if (wordCount)
            wordCount.innerHTML = getWordsCount(view.state.doc.textContent) + " Words";
    }

}