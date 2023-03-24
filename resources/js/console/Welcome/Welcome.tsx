import React from "react";
import BlogsList from './BlogsList'

export default function Welcome() {

    return <div className="box welcome-view">
        <h1>Welcome to Hyvor Blogs</h1>
        <div className="content-wrap">
            <BlogsList />
            <div className="links">
                <div className="links-title">Product</div>
                <div className="links-list">
                    <Link name="Pricing" href="/pricing" />
                    <Link name="Docs" href="/docs" />
                    <Link name="Blog" href="/blog" />
                    <Link name="Roadmap" href="https://community.blogs.hyvor.com/roadmap" />
                    <Link name="Changelog" href="https://community.blogs.hyvor.com/changelog" />
                </div>
                <div className="links-title">Support</div>
                <div className="links-list">
                    <Link name="Join our Discord" href="https://discord.com/invite/2WRJxQB" />
                    <Link name="Community" href="https://community.blogs.hyvor.com" />
                    <Link
                        name="blogs.support@hyvor.com"
                        href="mailto:blogs.support@hyvor.com"
                    />
                </div>
            </div>
        </div>
    </div>

}

type LinkProps = {
    name: string;
    href: string;
};

function Link({ name, href }: LinkProps) {

    return <a href={href} target="_blank">
        {name}
    </a>;

}