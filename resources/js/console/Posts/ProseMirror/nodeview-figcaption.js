

export default class Figcaption {

    constructor(node) {
        this.dom = this.contentDOM = document.createElement("figcaption")
        if (node.content.size == 0) this.dom.classList.add("empty")

        this.dom.addEventListener("click", function() {
            
        })
    }

    update(node) {
        if (node.type.name != "figcaption") return false
        if (node.content.size > 0) this.dom.classList.remove("empty")
        else this.dom.classList.add("empty")
        return true
    }

}