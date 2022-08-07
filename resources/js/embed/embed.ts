/*
class HyvorBlog extends HTMLElement {

    constructor() {
        super();
    }

    connectedCallback() {

        this.attachShadow({mode: "open"});

        const root = document.createElement("div");
        root.id = "inside-root";
        this.shadowRoot?.append(root);

        fetch('https://9eb3-77-140-61-8.ngrok.io/api/delivery/v0/testing-store-for-hb-myshopify-com')
            .then(response => response.json())
            .then(json => {
                root.innerHTML = atob(json.content);
                replaceScripts()
            })

        function replaceScripts() {
            var scripts = root.getElementsByTagName("script");
            for (var i = 0; i < scripts.length; i++) {
                var script = scripts[i];
                if (script.hasAttribute('data-flashload-skip-replacing')) continue;
                script.parentNode?.replaceChild(cloneScript(script), script);
            }
            function cloneScript(node: HTMLScriptElement){
                var script  = document.createElement("script");
                script.text = node.innerHTML;

                var i = -1, attrs = node.attributes, attr;
                while ( ++i < attrs.length ) {
                    script.setAttribute( (attr = attrs[i]).name, attr.value );
                }
                return script;
            }
        }

    }

}

customElements.define('hyvor-blog', HyvorBlog)*/

const wrap = document.getElementById("hyvor-blogs-embed-wrap");

const iframe = document.createElement("iframe")
iframe.src = "https://9eb3-77-140-61-8.ngrok.io/embed/testing-store-for-hb-myshopify-com";

iframe.style.width = '1px';
iframe.style.minWidth = '100%';
iframe.style.border = 'none';
iframe.style.userSelect = 'none';
iframe.style.height = '750px';


window.addEventListener("message", function(e) {

    console.log("MESSAGE RECEIVED");
    const json = JSON.parse(e.data)

    if (json.type === 'resize') {
        iframe.style.height = json.height + "px";
    }

});

wrap?.append(iframe)