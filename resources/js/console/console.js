import React from 'react';
import ReactDOM from 'react-dom';
import { resetContext, Provider } from 'kea'
import { routerPlugin } from 'kea-router'
import Scene from './Scene';
import { ajaxPlugin } from './lib/kea-plugins/ajax';
import { ToastContainer } from 'react-toastify'

import 'react-toastify/dist/ReactToastify.css';

// codemirror
import 'codemirror/addon/display/autorefresh';
import 'codemirror/addon/comment/comment';
import 'codemirror/addon/edit/matchbrackets';
import 'codemirror/keymap/sublime';
import 'codemirror/lib/codemirror.css';
import 'codemirror/theme/solarized.css';
// languages
import 'codemirror/mode/javascript/javascript'; // js
import 'codemirror/mode/twig/twig'; // twig
import 'codemirror/mode/htmlmixed/htmlmixed'; // html
import 'codemirror/mode/css/css'; // css|scss

resetContext({
    plugins: [
        routerPlugin(),
        ajaxPlugin()
    ]
});

function App() {


    return <Provider>
        <Scene />
        <ToastContainer />
    </Provider>

}


ReactDOM.render(<App />, document.getElementById("app"));