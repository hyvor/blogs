import React, { useEffect, useState } from 'react';
import ReactDOM from 'react-dom';
import OnBoarding from './OnBoarding'

import {
    BrowserRouter,
    Routes,
    Route,
    Outlet,
    useNavigate,
    Redirect,
    Navigate,
} from "react-router-dom";



import Nav from './Nav/Nav';
import useActiveSubdomain from './state/useActiveSubdomain';
import { BoxArrowUpRight, Laptop, Phone } from 'react-bootstrap-icons';
import Loader from './ReusableComponents/Loader';
import Posts from './Posts/Posts';

function App() {

    return <BrowserRouter basename="console">
        <Routes>
            <Route path="onboarding" exact element={<OnBoarding />} />

            <Route path="" element={<BlogConsole />} >

                <Route path=":subdomain">
                    <Route index element={<BlogPreview />} />
                    <Route path="posts" element={<Posts />} />
                    <Route path="pages" element={<Posts />} />
                    <Route path="theme" element={<Theme />} />
                    <Route path="settings" element={<Theme />} />
                </Route>

            </Route>
            

        </Routes>
    </BrowserRouter>

}

function Theme() {
    return <div style={{height: "100%"}} className="box"></div>
}

function BlogConsole() {

    return <div>
        <Nav />
        <div id="middle">
            <Outlet />
        </div>
    </div>

}

function BlogPreview() {

    const subdomain = useActiveSubdomain().get();
    const [type, setType] = useState('laptop');

    const [isLoading, setIsLoading] = useState(true);

    function closeLoading() {
        setIsLoading(false);
    }

    return <div className="box blog-preview-view">
        <div className="navi">
            <div className="left">
                <a 
                    href={ `https://${subdomain}.hyvorblogs.io` }
                    target="_blank"
                >{subdomain}.hyvorblogs.io &nbsp;<BoxArrowUpRight /></a>
            </div>
            <div className="right">
                <span onClick={() => setType('laptop')} className={type == 'laptop' ? "active" : ""}><Laptop /></span>
                <span onClick={() => setType('phone')} className={type == 'phone' ? "active" : ""}><Phone /></span>
            </div>
        </div>
        <div 
            className="iframe"
            style={{
                padding: type === 'laptop' ? 0 : 15
            }}
        >
            {
                isLoading ?
                <Loader /> : null 
            }
            <iframe
                id="preview-iframe"
                src={"https://blogs.hyvor.test/theme" /* `https://${subdomain}.hyvorblogs.io` */} 
                style={{
                    width: type === 'laptop' ? "100%" : 360,
                    height: type === 'laptop' ? "100%" : 740,
                    display: isLoading ? "none" : "block"
                }}
                onLoad={closeLoading}
            />
        </div>
    </div>;

}

ReactDOM.render(<App />, document.getElementById("app"));