import { useEffect } from "react";
import { useLocation, useParams } from "react-router-dom";
import useBlogsState from "./useBlogsState";
import _globalState from "./_globalState";

/**
 * 
 * This is used to manage the active subdomain
 * This state is synced with 
 * 
 * The default subdomain is the subdomain of the first blog
 * 
 * 
 * 
 */
export default function useActiveSubdomain() {

    const blogs = useBlogsState().get();

    const { subdomain } = useParams();
    const loation = useLocation();
    const state = _globalState('activeSubdomain', subdomain || findDefaultActiveSubdomain(), {
        // when deleting the current one
        resetToFirst: (state, setState) => {
            setState(findDefaultActiveSubdomain());
        }
    });

    useEffect(() => {
        if (subdomain) {
            state.set(subdomain);
        }
        console.log(location);
    }, [subdomain]);

    useEffect(() => {
        
    }, [state.get()])

    function findDefaultActiveSubdomain() {
        return blogs.length ? blogs[0].subdomain : null;
    }

    return state;

}
