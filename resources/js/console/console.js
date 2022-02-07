import React from 'react';
import ReactDOM from 'react-dom';
import { resetContext, Provider } from 'kea'
import { routerPlugin } from 'kea-router'
import { loadersPlugin } from 'kea-loaders'
import Scene from './Scene';
import { loadersWithHasMorePlugin } from './lib/kea-plugins/loadersWithHasMore';
import { ajaxPlugin } from './lib/kea-plugins/ajax';
import { ToastContainer } from 'react-toastify'

import 'react-toastify/dist/ReactToastify.css';

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