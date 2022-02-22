

export default class Heading {

    constructor(node, view, getPos) {
        const _self = this

        this.dom = document.createElement("div");
        this.dom.className = "heading-wrap";

        this.contentDOM = document.createElement("h" + node.attrs.level)
        const id = node.attrs.id || "";
        this.contentDOM.id = id
        this.dom.appendChild(this.contentDOM);

        this.inputWrap = document.createElement("div");
        this.inputWrap.contentEditable = false;
        this.dom.appendChild(this.inputWrap)

        // id input
        this.input = document.createElement("input");
        this.input.value = id;

        this.input.oninput = function(e) {
            view.dispatch(
                view.state.tr.setNodeMarkup(
                    getPos(),
                    null,
                    {...node.attrs, id: e.target.value }
                )
            )
        }

        this.inputWrap.appendChild(this.input);
    }

    update(node) {
        if (node.type.name === 'heading') {
            this.contentDOM.id = node.attrs.id;
            this.input.value = node.attrs.id;
            return true;
        }
    }

    stopEvent(e) {
        return e.target.isEqualNode(this.input)
    }

}