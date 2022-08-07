
function sendResizeSignal() {

    const height = document.body.offsetHeight;
    window.parent.postMessage(JSON.stringify({
        type: "resize",
        height
    }), '*');

}

sendResizeSignal();

window.addEventListener('click', function(e) {

    e.preventDefault();
    e.stopPropagation();

}, true);