import React, { useState } from 'react';
import ReactDOM from 'react-dom';
import OnBoarding from './OnBoarding'
import TheEditor from './PostEditor';
import Select from './ReusableComponents/Select';


import {Filter} from 'react-bootstrap-icons';

import {
    BrowserRouter as Router,
    Switch,
    Route,
    NavLink
} from "react-router-dom";

function App() {

    const isLoggedIn = true;

    return <Router basename="/console">
        <Switch>
            <Route path="/onboarding" exact>
                <OnBoarding />
            </Route>

            <Route>
                {
                    isLoggedIn ?
                    <div>
                        <Left />
                        <div id="middle">
                            <Middle />
                        </div>
                    </div> : 
                    <NotLoggedHomepage />
                }
            </Route>

        </Switch>
    </Router>
}

import { CheckCircleFill, HourglassSplit } from 'react-bootstrap-icons';
import ReactSwitch from "./ReusableComponents/Switch";
import PostEditor from './PostEditor';
function NotLoggedHomepage() {

    const [isYearlyBilling, setIsYearlyBilling] = useState(true);

    return <div id="not-logged-homepage" className="box">

        <div className="first-row">
            <h1>Hyvor Blogs</h1>
            <p>A simple blogging platform for individuals and teams</p>

            <div>
                <button className="button">Sign up</button>
                <span className="or">or</span>
                <button className="button">Log in</button>
            </div>
        </div>
        

        <div className="second-row">

            <div className="plans-title">Pricing Plans</div>
            <div className="plans-wrap">
                <div className="plan">
                    <div className="plan-title">Personal</div>
                    <div className="plan-desc">For starters & hobby bloggers</div>
                    <div className="plan-features">
                        <div><CheckCircleFill /> 1 user</div>
                        <div><CheckCircleFill /> 1GB media storage</div>
                        <div><CheckCircleFill /> hyvorblogs.io subdomain</div>
                        <div><CheckCircleFill /> In-built SEO</div>
                        <div><CheckCircleFill /> Own your data, export anytime in WordPress format</div>
                        <div><HourglassSplit /> Import from WordPress, Blogger, Medium, Ghost, etc.</div>
                    </div>
                    <div className="plan-price">Free</div>
                </div>
                <div className="plan">
                    <div className="plan-title">Personal Pro</div>
                    <div className="plan-desc">For serious bloggers</div>
                    <div className="plan-features">        
                        <div><CheckCircleFill /> All Personal features</div>
                        <div><CheckCircleFill /> 1 user</div>
                        <div><CheckCircleFill /> 5GB media storage</div>
                        <div><CheckCircleFill /> Code injecting</div>
                        <div><CheckCircleFill /> Custom domain</div>
                        <div><HourglassSplit /> Custom themes</div>
                        <div><HourglassSplit /> Data API</div>
                    </div>
                    <div className="plan-price">$20/year</div>
                </div>
                <div className="plan">
                    <div className="plan-title">Team</div>
                    <div className="plan-desc">For you and your partner</div>
                    <div className="plan-features">
                        <div><CheckCircleFill /> All Personal Pro Features</div>
                        <div><HourglassSplit /> Delivery API (Self-hosting on a subdirectory)</div>

                        <div className="plan-package">
                            <div className="plan-package-include">One Package Includes: </div>
                            <div><CheckCircleFill /> 2 Team Members</div>
                            <div><CheckCircleFill /> 20 Contributors</div>
                            <div><CheckCircleFill /> 1000 Posts</div>
                            <div><CheckCircleFill /> 5GB Media Storage</div>
                        </div>
                    </div>
                    <div className="plan-switch">
                        <span>Billed Annually</span>
                        <ReactSwitch
                            checked={isYearlyBilling} 
                            onChange={(c) => setIsYearlyBilling(c)} />
                    </div>
                    <div className="plan-price">
                        <div>${isYearlyBilling ? "10" : "15"}/month/package</div>
                    </div> 
                </div>
            </div>
        </div>

    </div>;

}

function Left() {
    return <div id="left" className="box">
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