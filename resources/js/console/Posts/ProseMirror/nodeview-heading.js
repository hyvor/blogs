

export default class Heading {

    constructor(node) {
        this.dom = this.contentDOM = document.createElement("div");
        this.dom.className = "heading-wrap";

        const h = document.createElement("h" + node.attrs.level);
        this.dom = this.contentDOM = document.createElement("h" + node.attrs.level)
        if (node.content.size == 0) this.dom.classList.add("empty")

        this.dom.addEventListener("click", function() {
            console.log("I am focused")
        })
    }

    update(node) {
        if (node.type.name != "figcaption") return false
        if (node.content.size > 0) this.dom.classList.remove("empty")
        else this.dom.classList.add("empty")
        return true
    }

}