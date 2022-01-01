import React from 'react';
import ReactDOM from 'react-dom';
import { resetContext, Provider } from 'kea'
import { routerPlugin } from 'kea-router'
import Scene from './Scene';
import { loadersProPlugin } from './lib/kea-plugins/loaders-pro';

resetContext({
    plugins: [
        routerPlugin({
            pathFromRoutesToWindow: (path) => "/console" + path,
            pathFromWindowToRoutes: (path) => path.replace(/^\/console/, ''),
        }),
        loadersProPlugin()
    ]
});

function App() {

    return <Provider>
        <Scene />
    </Provider>

}


ReactDOM.render(<App />, document.getElementById("app"));