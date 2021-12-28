import { useEffect } from "react";
import { useLocation, useNavigate, useParams } from "react-router-dom";
import useBlogsState from "./useBlogsState";
import _globalState from "./_globalState";

/**
 * 
 * This is used to manage the active subdomain
 * This state is synced with 
 * 
 * The default subdomain is the subdomain of the first blog
 */
export default function useActiveSubdomain() {

    const blogs = useBlogsState().get();

    const routeSubdomain = useParams().subdomain;
    const loation = useLocation();
    const navigate = useNavigate();
    const state = _globalState('activeSubdomain', routeSubdomain || findDefaultActiveSubdomain(), {
        // when deleting the current one
        resetToFirst: (state, setState) => {
            setState(findDefaultActiveSubdomain());
        },

        change: (subdomain) => {
            var path;
            state.update((old) => {
                path = location.pathname.replace('/console/' + old, '');
                old = subdomain

                navigate("/" + subdomain + path);

                return old;
            });

        }
    });

    function findDefaultActiveSubdomain() {
        return blogs.length ? blogs[0].subdomain : null;
    }

    return state;

}
