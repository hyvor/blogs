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

resetContext({
    plugins: [
        routerPlugin(),
        ajaxPlugin()
    ]
});

import relativeTime from 'dayjs/plugin/relativeTime'
import { GlobalImageUploader } from './ReusableComponents/ImageUploader/ImageUploader';

dayjs.extend(relativeTime);

function App() {
    
    return <div>
        <Scene />

        <GlobalImageUploader />
        <ToastContainer />
    </div>

}

const root = createRoot(document.getElementById("app")!)
root.render(<App />);
