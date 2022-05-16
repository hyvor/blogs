import React from 'react'
import {useValues} from "kea";
import blogsLogic from "../logic/blogsLogic";
import {UserBlog} from '../objects/userblog';
import NavLink from "../ReusableComponents/NavLink";
import {BlogType} from "../enums";

export default function BlogsList() {

    const { blogs } = useValues(blogsLogic());

    return <div className="blogs-list">

        <div className="blogs-list-title">Your Blogs</div>
        <div className="blogs-list-list">
            {
                blogs.map(({ blog, user } : UserBlog) => {

                    return <NavLink
                        key={blog.id}
                        href={"/console/" + blog.subdomain}
                        className={"blog-card" + (blog.type === BlogType.DEV ? " dev" : "")}
                    >

                        <div className="top-row">
                            <div className="icon">
                                {
                                    blog.logo_url ?
                                    <img src={blog.logo_url} alt="Blog Logo" /> :
                                    <span className="icon-placeholder" />
                                }
                            </div>
                            <div className="name-url">
                                <div className="name">
                                    { blog.name }
                                    {
                                        blog.type === BlogType.DEV ?
                                        <span className="global-tag blue">DEV</span> : null
                                    }
                                </div>
                                <div className="url" title={blog.subdomain}>{ blog.subdomain }</div>
                            </div>
                        </div>

                        <div className="data-cards">
                            <DataCard name="Role" value={ blog.type === BlogType.DEV ? "DEV" : user.role } />
                            <DataCard name="Plan" value={
                                blog.type === BlogType.DEV ?
                                    "DEV" :
                                    (
                                        blog.is_on_trial ?
                                            "Trial" :
                                            blog.subscription?.plan
                                    )
                            } />
                        </div>
                        <div className="data-cards" style={{marginTop: 10}}>
                            <DataCard name="Posts" value={ blog.posts_count } />
                            <DataCard name="Users" value={ blog.posts_count } />
                        </div>

                    </NavLink>
                })
            }
        </div>

        <div className="blog-create">
            <NavLink href="/console/new" className="button medium">
                Create New Blog
            </NavLink>
        </div>

    </div>

}

function DataCard({ name, value } : { name: string, value: number | string }) {

    return <div className="data-card">
        <div className="card-name">{ name }</div>
        <div className="card-value">{ value }</div>
    </div>

}