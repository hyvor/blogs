import React from 'react';
import ReactDOM from 'react-dom';
import OnBoarding from './OnBoarding'

import {
    BrowserRouter as Router,
    Switch,
    Route,
    Link
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
                        <a className="active">Your Blog</a>

                        <div className="left-divider"></div>

                        <a>Posts</a>
                        <a>Pages</a>

                        <div className="left-divider"></div>

                        <a>Theme</a>
                        <a>Team</a>
                        <a>Billing</a>
                        <a>Settings</a>
                    </div>
                </div>
                <div id="middle" className="box">

                </div>
            </Route>

        </Switch>
    </Router>
}

ReactDOM.render(<App />, document.getElementById("app"));