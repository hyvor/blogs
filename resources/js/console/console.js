import React from 'react';
import ReactDOM from 'react-dom';
import { resetContext, Provider } from 'kea'
import { routerPlugin } from 'kea-router'
import Scene from './Scene';
import { loadersPlugin } from 'kea-loaders';

resetContext({
    plugins: [
        routerPlugin({
            pathFromRoutesToWindow: (path) => "/console" + path,
            pathFromWindowToRoutes: (path) => path.replace(/^\/console/, ''),
        }),
        loadersPlugin()
    ]
});

function App() {

    return <Provider>
        <Scene />
    </Provider>

}


ReactDOM.render(<App />, document.getElementById("app"));