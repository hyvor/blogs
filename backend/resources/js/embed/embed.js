const wrap = document.getElementById("hyvor-blogs-embed-wrap");
const iframe = document.createElement("iframe")

const hbDomain = window.HYVOR_BLOGS_APP_URL;
const currentSubdomain = window.HYVOR_BLOGS_EMBED_SUBDOMAIN;

const iframeUrl = new URL(hbDomain + "/embed/iframe/" + currentSubdomain);
const currentUrl = new URL(location.href)

const path = window.HYVOR_BLOGS_PATH || currentUrl.searchParams.get('p') || '';
iframeUrl.searchParams.append('path_style', window.HYVOR_BLOGS_PATH_STYLE ? 1 : 0);
iframeUrl.searchParams.append('path', path);

let embeddingUrl = currentUrl.href.replace(/\?.+/, '')
if (window.HYVOR_BLOGS_PATH_STYLE)
    embeddingUrl = embeddingUrl.replace(new RegExp(path + "$"), '');
iframeUrl.searchParams.append('url', embeddingUrl)
iframe.src = iframeUrl.toString();

iframe.style.width = '1px';
iframe.style.minWidth = '100%';
iframe.style.border = 'none';
iframe.style.userSelect = 'none';
iframe.style.height = '750px';

window.addEventListener("message", function(e) {

    try {
        const json = JSON.parse(e.data);

        if (json.type === 'resize') {
            iframe.style.height = json.height + "px";
        }

        if (json.type === 'init') {
            let elems = json.headElems;
            for (let i = 0; i < elems.length; i++) {
                const elData = elems[i]
                const el = document.createElement(elData.tag)
                for (let i in elData.attrs) {
                    el.setAttribute(i, elData.attrs[i]);
                }
                document.head.appendChild(el)
            }
            document.title = json.title;
        }

        if (json.type === 'navigation') {
            location.href = json.href;
        }

    } catch {}

});

wrap.append(iframe)