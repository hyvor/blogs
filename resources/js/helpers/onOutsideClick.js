

export default function onOutsideClick(element, func, onBlur = false, autoRemove = true, preventDefault = true) {

    /**
     * Calling two times is a trick to avoid the outsideClick function being called the first time.
     */
    //window.addEventListener("mouseup", addClickEventListerner, true)

    setTimeout(addClickEventListerner, 0);

    function addClickEventListerner() {
        window.addEventListener("click", checkOutsideClick, true);
        window.removeEventListener("click", addClickEventListerner, true);

        if (onBlur) window.addEventListener("blur", handleBlur, true)
    }

    function checkOutsideClick(event) {
        if (event.target !== element && !element.contains(event.target)) {
            removeListenerAndCall(event);

            if (preventDefault) {
                event.stopPropagation();
                event.preventDefault();
            }
        }
    }

    function removeListenerAndCall(e, forced, call = true) {
        autoRemove || forced ? window.removeEventListener("click", checkOutsideClick, true) : null;
        call && func(e)
    }

    /**
     * Close popups on blur.
     * Only use for popups that doesn't contain user input and importatnt state
     */
    function handleBlur(e) {
        window.removeEventListener("blur", handleBlur, true);
        removeListenerAndCall(e);
    }

    return (call) => removeListenerAndCall(null, true, call ? true : false);


}