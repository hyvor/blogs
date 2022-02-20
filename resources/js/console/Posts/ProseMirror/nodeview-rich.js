import setInnerHTMLWithScripts from "../../../helpers/setInnerHTMLWithScripts";
import api from "../../lib/api";
import subdomainLogic from "../../logic/subdomainLogic";

export default class RichView {

    constructor(node, view, getPos) {
        this.node = node;
        this.view = view;
        this.getPos = getPos;

        this.dom = document.createElement("div");
        this.dom.classList.add("rich");


        const url = node.attrs.url;

        this.dom.innerHTML = '<div class="embedding-placeholder">Embedding...</div>';

        api.get(subdomainLogic.values.subdomain, '/embed', {url})
            .then(response => {
                if (response.type === 'rich') {
                    setInnerHTMLWithScripts(this.dom, response.html);
                }
            })
            .catch(error => {

            })

    }

}