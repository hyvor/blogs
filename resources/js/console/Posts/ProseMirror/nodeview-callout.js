

export default class Callout {

    constructor(node) {
        this.dom = document.createElement("aside")

        this.contentDOM = document.createElement("div")

        const emoji = document.createElement("span")
        emoji.innerHTML = node.attrs.emoji

        this.dom.appendChild(emoji)
        this.dom.appendChild(this.contentDOM)
    }

    update(node) {
        /* if (node.type.name != "figcaption") return false
        if (node.content.size > 0) this.dom.classList.remove("empty")
        else this.dom.classList.add("empty")
        return true */
    }

}