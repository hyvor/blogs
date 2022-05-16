import {UserBlog} from "./userblog";

export type appConfig = {

    hyvorUser: any;
    blogs: UserBlog[];

    domains: {
        app: string;
        delivery: string;
        hyvor: string;
    }

    syntax_themes: string[]
}