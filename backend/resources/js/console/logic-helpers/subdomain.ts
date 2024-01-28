import {useValues} from "kea";
import subdomainLogic from "../logic/subdomainLogic";

/**
 * These functions ensure that subdomain is a string
 * When used in components that are rendered after the subdomain check
 */

export function useSubdomain() : string {
    const { subdomain } = useValues(subdomainLogic)
    return subdomain as string;
}

export default function getSubdomain() : string {
    return subdomainLogic.values.subdomain as string;
}