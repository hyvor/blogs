

export default function onOutsideClick(element, func, onBlur = false)	{

    /**
     * Calling two times is a trick to avoid the outsideClick function being called the first time.
     */
    //window.addEventListener("click", addClickEventListerner, true)

    setTimeout(addClickEventListerner, 0);

    function addClickEventListerner() {
        window.addEventListener("click", checkOutsideClick, true);
        window.removeEventListener("click", addClickEventListerner, true);

        if (onBlur) window.addEventListener("blur", handleBlur, true)
    }

    function checkOutsideClick(event) {
        if (event.target !== element && !element.contains(event.target)) {
            removeListenerAndCall();
            event.stopPropagation();
        }
    }

    function removeListenerAndCall() {
        window.removeEventListener("click", checkOutsideClick, true);
        func()
    }

    /**
     * Close popups on blur.
     * Only use for popups that doesn't contain user input and importatnt state
     */
    function handleBlur() {
        window.removeEventListener("blur", handleBlur, true);
        removeListenerAndCall();
    }

    return removeListenerAndCall;


}