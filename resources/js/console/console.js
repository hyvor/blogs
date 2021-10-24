import React from 'react';
import ReactDOM from 'react-dom';

function App() {

    return <div>
        <div id="left">
            <div id="left-header">
                <img src="/logo.jpeg" id="left-header-image-1" className="round-image-40"></img>
                <div id="left-header-image-2" className="round-image-40"></div>
            </div>
            <div id="left-nav">
                <a className="active">Overview</a>

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
        <div id="middle">

        </div>
    </div>
}

ReactDOM.render(<App />, document.getElementById("app"));