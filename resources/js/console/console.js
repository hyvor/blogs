import React, { useState } from 'react';
import ReactDOM from 'react-dom';
import OnBoarding from './OnBoarding'
import TheEditor from './editor';
import Select from './ReusableComponents/Select';

import {Filter} from 'react-bootstrap-icons';

import {
    BrowserRouter as Router,
    Switch,
    Route,
    NavLink
  } from "react-router-dom";

function App() {

    return <Router basename="/console">
        <Switch>
            <Route path="/onboarding" exact>
                <OnBoarding />
            </Route>

            <Route>
                <div id="left" className="box">
                    <div id="left-header">
                        <img src="/logo.jpeg" id="left-header-image-1" className="round-image-40"></img>
                        <div id="left-header-image-2" className="round-image-40"></div>
                    </div>
                    <div id="left-nav">
                        <NavLink to="/" exact>Your Blog</NavLink>

                        <div className="left-divider"></div>

                        <NavLink to="/posts">Posts</NavLink>
                        <NavLink to="/pages">Pages</NavLink>

                        <div className="left-divider"></div>

                        <a>Theme</a>
                        <a>Team</a>
                        <a>Billing</a>
                        <a>Settings</a>
                    </div>
                </div>
                <div id="middle">
                    <Middle />
                </div>
            </Route>

        </Switch>
    </Router>
}

function Middle() {

    return (
        <Switch>
            <Route path="/posts">
                <Posts />
            </Route>
            <Route>
                <TheEditor />
            </Route>
        </Switch>
    )

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
                            <div className="post-title">Hello World, Welcome to Hyvor Blogs!</div>
                            
                            <div className="post-data">
                                <div className="post-date">
                                    2021-02-03 12:46pm { 
                                        status !== 'published' ? 
                                        <span>&#8729; <span className={`post-status ${status}`}>{status}</span>
                                        </span>
                                        : null}
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
        <div className="box box-right"></div>
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