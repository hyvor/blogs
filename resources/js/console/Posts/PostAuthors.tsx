import React from 'react';
import {Post} from "../objects/post";
import {useValues} from "kea";
import postsLogic from "../logic/postsLogic";
import subdomainLogic from "../logic/subdomainLogic";
import AsyncSelect from "react-select/async";
import api from "../lib/api";
import {User} from "../objects/user";

interface SelectOption {
    value: number;
    label: string;
}

export default function PostAuthors({ post, languageId } : { post: Post, languageId: number }) {

    const subdomain = subdomainLogic.values.subdomain
    const { counts } = useValues(postsLogic({subdomain})) as { counts: PostCounts }

    const options: Array<SelectOption> = [];

    counts.authors.forEach(author => {
        options.push({
            value: author.id,
            label: author.name
        })
    });

    const defaultValue = post.authors.map(author => (
        {value: author.id , label: author.variants[languageId].name }
    ))

    async function loader(input: string) : Promise<Array<SelectOption>> {

        const users : Array<User> = await api.get(subdomain, '/users/search', {
            search: input
        });

        return users.map(user => ({
            value: user.id,
            label: user.variants[languageId].name
        }))

    }

    function handleChange(e: any) {
        console.log(e)
    }

    return <AsyncSelect
        loadOptions={loader}
        cacheOptions
        defaultOptions={options}
        defaultValue={defaultValue}
        isMulti
        classNamePrefix="react-select"
        className="react-select react-select-normal"
        onChange={handleChange}
    />

}