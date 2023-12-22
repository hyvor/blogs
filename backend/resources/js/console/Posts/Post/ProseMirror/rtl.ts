

export function isEditorRtl() {
    const wrap = document.querySelector('.post-editor-wrap')

    if (!wrap)
        return false;

    return wrap.getAttribute('dir') === 'rtl';
}