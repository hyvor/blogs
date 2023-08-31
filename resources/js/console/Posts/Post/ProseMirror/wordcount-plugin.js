import { Node } from "prosemirror-model";
import {Plugin} from "prosemirror-state"

export default function wordCountPlugin() {
    return new Plugin({
        view(editorView) { return new WordCountPlugin(editorView) }
    })
}

// https://stackoverflow.com/a/18679657/9059939
function countWords(str) {
    return str.trim().split(/\s+/).length;
}

class WordCountPlugin {
    constructor(view) {
        this.items = [];
        this.view = view;

        this.updateCount(view);
    }
  
    update(view) {
        this.updateCount(view);
    }

    updateCount(view) {
        const wordCount = document.getElementById("pm-word-count");
        if (wordCount)
            wordCount.innerHTML = countWords(view.state.doc.textContent) + " Words";
    }

}