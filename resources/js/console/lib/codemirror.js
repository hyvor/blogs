import CodeMirror from 'codemirror';

window.CodeMirror = CodeMirror;

// codemirror
/*import 'codemirror/addon/display/autorefresh';
import 'codemirror/addon/comment/comment';
import 'codemirror/addon/edit/matchbrackets';
import 'codemirror/addon/edit/matchtags';
import 'codemirror/addon/edit/closebrackets';
import 'codemirror/addon/edit/closetag'*/
import 'codemirror/keymap/sublime';
import 'codemirror/lib/codemirror.css';
import 'codemirror/theme/solarized.css';
// default languages
import 'codemirror/mode/javascript/javascript'; // js
import 'codemirror/mode/twig/twig'; // twig
import 'codemirror/mode/htmlmixed/htmlmixed'; // html
import 'codemirror/mode/css/css'; // css|scss
import 'codemirror/mode/yaml/yaml'; // yaml
