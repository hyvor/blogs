import setInnerHTMLWithScripts from "../../../helpers/setInnerHTMLWithScripts";
import api from "../../lib/api";
import subdomainLogic from "../../logic/subdomainLogic";

export default class RichView {

    constructor(node, view, getPos) {
        this.node = node;
        this.view = view;
        this.getPos = getPos;

        this.dom = document.createElement("rich");
        // this.dom.classList.add("rich");


        const url = node.attrs.url;

        this.dom.dataset.url = url;

        this.dom.innerHTML = '<div class="embedding-placeholder">Loading embed...</div>';

        api.get(subdomainLogic.values.subdomain, '/url-data', {url})
            .then(response => {
                if (response.type === 'rich') {
                    setInnerHTMLWithScripts(this.dom, response.html);
                }
            })
            .catch(error => {

            })

    }

}