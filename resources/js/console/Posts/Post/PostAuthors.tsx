import React from 'react';
import {useActions, useValues} from "kea";
import subdomainLogic from "../../logic/subdomainLogic";
import AsyncSelect from "react-select/async";
import api from "../../lib/api";
import {Post, User} from "../../types";
import usersLogic, {IDKeyedUsers} from "../../logic/usersLogic";
import {getPrimaryLanguage} from "../../lib/blog-helpers";
import getSubdomain from "../../logic-helpers/subdomain";
import {OnChangeValue} from "react-select";

interface SelectOption {
    value: number;
    label: string | null;
}

export default function PostAuthors({ post, updatePostValue } : { post: Post, updatePostValue: Function }) {

    const subdomain = getSubdomain()

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

    function handleChange(options: OnChangeValue<SelectOption, true>) {

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
        className="react-select react-select-normal react-select-multi"
        onChange={handleChange}
    />

}