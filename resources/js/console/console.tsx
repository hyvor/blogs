// css
import '../../css/console/console.scss';

import React from 'react';
import { resetContext } from 'kea'
import { routerPlugin } from 'kea-router'
import Scene from './Scene';
import { ToastContainer } from 'react-toastify'

import './lib/codemirror';

import 'react-toastify/dist/ReactToastify.css';
import 'prosemirror-codemark/dist/codemark.css';
import {createRoot} from "react-dom/client";
import { ajaxPlugin } from 'kea-ajax'
import dayjs from "dayjs";
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';



resetContext({
    plugins: [
        routerPlugin(),
        ajaxPlugin()
    ]
});

import relativeTime from 'dayjs/plugin/relativeTime'

dayjs.extend(relativeTime);

function App() {
    
    return <div>
        <Scene />
        <ToastContainer />
    </div>

}

window.Pusher = Pusher;

const echo = new Echo({
    broadcaster: 'pusher',
    key: 'app-key',//process.env.VITE_PUSHER_APP_KEY,
    wsHost: 'localhost',//process.env.VITE_PUSHER_HOST,
    wsPort: '6001',//process.env.VITE_PUSHER_PORT,
    wssPort: '6001',//process.env.VITE_PUSHER_PORT,
    forceTLS: false,
    encrypted: true,
    disableStats: true,
    enabledTransports: ['ws', 'wss'],
    cluster: 'eu',
  });

echo.channel("testNotif").listen("TestEvent", (e: any) => {
    console.log(e);
});

const root = createRoot(document.getElementById("app")!)
root.render(<App />);
