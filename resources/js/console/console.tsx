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


const echo = new Echo({
  broadcaster: 'pusher',
  key: process.env.VITE_PUSHER_APP_KEY,
  wsHost: process.env.VITE_PUSHER_HOST,
  wsPort: process.env.VITE_PUSHER_PORT,
  wssPort: process.env.VITE_PUSHER_PORT,
  forceTLS: false,
  encrypted: true,
  disableStats: true,
  enabledTransports: ['ws', 'wss'],
});

echo.channel("testNotif").listen("TestEvent", (e: any) => {
    console.log(e);
});

const root = createRoot(document.getElementById("app")!)
root.render(<App />);
