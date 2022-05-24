import React from 'react';
import {Post} from "../objects/post";
import {useActions, useValues} from "kea";
import subdomainLogic from "../logic/subdomainLogic";
import AsyncSelect from "react-select/async";
import api from "../lib/api";
import {User} from "../types";
import usersLogic, {IDKeyedUsers} from "../logic/usersLogic";
import {getPrimaryLanguage} from "../lib/blog-helpers";

interface SelectOption {
    value: number;
    label: string;
}

export default function PostAuthors({ post, updatePostValue } : { post: Post, updatePostValue: Function }) {

    const subdomain = subdomainLogic.values.subdomain

    const usersLogicInst = usersLogic({subdomain});
    const { users } = useValues(usersLogicInst) as { users: IDKeyedUsers }
    const { addUsers } = useActions(usersLogicInst)
    const languageId = getPrimaryLanguage(subdomain).id

    const options: Array<SelectOption> = [];

    for (let id in users) {
        options.push({
            value: parseInt(id),
            label: users[id].variants[languageId].name
        })
    }

    const defaultValue = post.authors.map(author => (
        {value: author.id , label: author.variants[languageId].name }
    ))

    async function loader(input: string) : Promise<Array<SelectOption>> {

        const users : Array<User> = await api.get(subdomain, '/users/search', {
            search: input
        });

        addUsers(users)

        return users.map(user => ({
            value: user.id,
            label: user.variants[languageId].name
        }))

    }

    function handleChange(options: Array<SelectOption>) {

        const authors : Array<User> = [];
        options.forEach(({value}) => authors.push(users[value]))
        updatePostValue('authors', authors);

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