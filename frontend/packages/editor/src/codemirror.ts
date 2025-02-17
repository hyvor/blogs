let loaded = false;

export async function importCodemirrorAll() {

    if (loaded)
        return;

    // @ts-ignore
    const CodeMirror = await import('codemirror');

    (window as any).CodeMirror = CodeMirror.default;

    // @ts-ignore
    await import('codemirror/addon/display/autorefresh'); 
    // @ts-ignore
    await import('codemirror/addon/comment/comment');
    // @ts-ignore
    await import('codemirror/addon/edit/matchbrackets');
    // @ts-ignore
    await import('codemirror/addon/edit/matchtags');
    // @ts-ignore
    await import('codemirror/addon/edit/closebrackets');
    // @ts-ignore
    await import('codemirror/addon/edit/closetag');
    // @ts-ignore
    await import('codemirror/keymap/sublime');
    // @ts-ignore
    await import('codemirror/lib/codemirror.css');
    // @ts-ignore
    await import('codemirror/theme/solarized.css');
    
    
    // default languages

    // @ts-ignore
    await import('codemirror/mode/twig/twig'); // twig
    // @ts-ignore
    await import('codemirror/mode/htmlmixed/htmlmixed'); // html
    // @ts-ignore
    await import('codemirror/mode/css/css'); // css|scss

    loaded = true;

}