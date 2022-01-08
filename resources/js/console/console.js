import React from 'react';
import ReactDOM from 'react-dom';
import { resetContext, Provider } from 'kea'
import { routerPlugin } from 'kea-router'
import { loadersPlugin } from 'kea-loaders'
import Scene from './Scene';
import { loadersWithHasMorePlugin } from './lib/kea-plugins/loadersWithHasMore';
import { ajaxPlugin } from './lib/kea-plugins/ajax';

resetContext({
    plugins: [
        routerPlugin({
            pathFromRoutesToWindow: (path) => "/console" + path,
            pathFromWindowToRoutes: (path) => path.replace(/^\/console/, ''),
        }),
        loadersPlugin(),
        loadersWithHasMorePlugin(),
        ajaxPlugin()
    ]
});

function App() {

    return <Provider>
        <Scene />
    </Provider>

}


ReactDOM.render(<App />, document.getElementById("app"));