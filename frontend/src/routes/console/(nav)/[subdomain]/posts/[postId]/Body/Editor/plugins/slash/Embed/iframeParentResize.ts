let added = false;

export function addParentResizeEvent() {

    if (added) {
        return;
    }

    window.addEventListener('message', function (event) {
        const source = event.source;
        const iframes = document.querySelectorAll('iframe');
        for (const iframe in iframes) {
            if (iframes[iframe]?.contentWindow === source) {
                if (event.data.type === 'unfold-iframe-resize') {
                    iframes[iframe].style.height = `${event.data.height}px`;
                }
            }
        }
    });


    added = true;

}
