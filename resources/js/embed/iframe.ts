
function sendResizeSignal() {

    const height = document.body.offsetHeight;
    window.parent.postMessage(JSON.stringify({
        type: "resize",
        height
    }), '*');

}

sendResizeSignal();
setInterval(sendResizeSignal, 500);

// promote meta/link tags to the parent window
window.addEventListener('DOMContentLoaded', function() {

    const allowedMetaNames = [
        /^robots$/,
        /^description$/,
        /^twitter:.*/,
        /^generator$/,
    ];
    const allowedMetaProps = [
        /^og:.*/,
        /^article:.*/
    ];
    const allowedLinks = [
        /^canonical$/,
        /^alternate$/,
    ]

    function getProps(selector: string, attr: string, allowed: Array<RegExp>) {
        const elems = document.head.querySelectorAll(selector);
        const ret = [] as Array<{
            tag: string,
            attrs: Record<any, any>
        }>;
        for (let i = 0; i < elems.length; i++) {
            const elem = elems[i];

            const attrVal = elem.getAttribute(attr) || '';
            let matched = false;

            for (let regExp of allowed) {
                if (regExp.test(attrVal)) {
                    matched = true;
                    break;
                }
            }

            if (matched) {
                const attrs = {} as Record<any, any>
                for (let x = 0; x < elem.attributes.length; x++) {
                    const attr = elem.attributes[x];
                    attrs[attr.nodeName] = attr.nodeValue;
                }
                ret.push({
                    tag: elem.tagName.toLowerCase(),
                    attrs
                })
            }
        }

        return ret;
    }

    const headElems = [
        ...getProps("meta[name]", 'name', allowedMetaNames),
        ...getProps("meta[property]", 'property', allowedMetaProps),
        ...getProps("link[rel]", 'rel', allowedLinks)
    ];

    window.parent.postMessage(JSON.stringify({
        type: "init",
        headElems,
        title: document.title
    }));

});

// navigation
window.addEventListener('click', function(e) {

    const link = (e.target as HTMLElement).closest('a[href]') as null | HTMLAnchorElement;

    if (!link)
        return

    const baseUrl = (window as any).EMBEDDING_URL;

    if (!link.href.startsWith(baseUrl))
        return;

    e.preventDefault();
    e.stopPropagation();

    window.parent.postMessage(JSON.stringify({
        type: "navigation",
        href: link.href
    }), "*");


}, true);