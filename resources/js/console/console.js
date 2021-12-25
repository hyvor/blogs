import React, { useState } from 'react';
import ReactDOM from 'react-dom';
import OnBoarding from './OnBoarding'
import Select from './ReusableComponents/Select';

import {
    BrowserRouter,
    Routes,
    Route,
    Outlet,
    useNavigate,
    Redirect,
    Navigate,
} from "react-router-dom";



import PostEditor from './PostEditor';
import Nav from './Nav/Nav';
import useBlogsState from './state/useBlogsState';
import useActiveSubdomain from './state/useActiveSubdomain';
import { BoxArrowUpRight, Laptop, Phone } from 'react-bootstrap-icons';

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
            <iframe 
                src={"https://blogs.hyvor.test/theme" /* `https://${subdomain}.hyvorblogs.io` */} 
                style={{
                    width: type === 'laptop' ? "100%" : 360,
                    height: type === 'laptop' ? "100%" : 740
                }}
            />
        </div>
    </div>;

}

function Posts() {

    const statusOptions = [
        { value: 'all', label: 'All' },
        { value: 'published', label: 'Published (43)' },
        { value: 'draft', label: 'Draft (50)' },
        { value: 'deleted', label: 'Deleted (2)' }
    ]
    const ownershipOptions = [
        { value: 'all', label: 'All' },
        { value: 'you', label: 'You (45)' },
    ];
    const tagsOptions = [
        { value: 'all', label: 'All' },
        { value: 'creative', label: '#creative'}
    ]
    const dateOptions = [
        { value: 'all', label: 'All' },
        { value: 'today', label: 'Today'}
    ];

    var postStatuses = ['published', 'draft', 'scheduled', 'deleted'];

    return <div className="posts-view">
        <div className="box box-left">
            <div className="middle-heading">
                <div>
                    Posts
                </div>
                <button className="button small">+ New</button>
            </div>
            <div className="posts-filtering">
                <div className="post-filters">
                    <PostsFilter name="Status" defaultValue={statusOptions[0]} options={statusOptions} />
                    <PostsFilter name="Author" defaultValue={ownershipOptions[0]} options={ownershipOptions} />
                    <PostsFilter name="Tags" defaultValue={tagsOptions[0]} options={tagsOptions} />
                    <PostsFilter name="Date" defaultValue={dateOptions[0]} options={dateOptions} />
                </div>
                <div className="post-search">
                    <input className="input" placeholder="Search..."></input>
                </div>
            </div>
            <div className="posts-list">
                {
                    [...Array(15).keys()].map(i => {
                        var status = postStatuses[Math.floor(Math.random() * postStatuses.length)];

                        return <div key={i} className={"posts-list-item" + (i === 1 ? " active" : "") + ` ${status}` }>
                            <div className="post-title">{ 
                                        status !== 'published' ? 
                                        <span className={`post-status ${status}`}>{status}</span>
                                        : null}Hello World, Welcome to Hyvor Blogs!</div>
                            
                            <div className="post-data">
                                <div className="post-date">
                                    2021-02-03 12:46pm 
                                </div>
                                <div className="post-author">by Ishini Avindya</div>
                            </div>


                            <div className="post-tags">
                                <span className="post-tag">#creative</span>
                            </div>
                        </div>
                    })
                }
            </div>
        </div>
        <div className="box box-right">
            <PostEditor />
        </div>
    </div>
}

function PostsFilter(props) {
    return <div className="posts-filter">
        <div className="posts-filter-name">{props.name}</div>
        <Select 
            defaultValue={props.defaultValue} 
            type="small" 
            options={props.options}
        />
    </div>   
}

ReactDOM.render(<App />, document.getElementById("app"));