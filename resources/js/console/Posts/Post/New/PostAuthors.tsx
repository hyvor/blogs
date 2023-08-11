import React, { useState } from 'react';
import {useActions, useValues} from "kea";
import subdomainLogic from "../../../logic/subdomainLogic";
import AsyncSelect from "react-select/async";
import api from "../../../lib/api";
import {Post, User, UserVariant} from "../../../types";
import usersLogic, {IDKeyedUsers} from "../../../logic/usersLogic";
import {getPrimaryLanguage} from "../../../lib/blog-helpers";
import getSubdomain from "../../../logic-helpers/subdomain";
import {OnChangeValue} from "react-select";
import ProfilePicture from '../../../ReusableComponents/ProfilePicture';

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
            label: users[id].variants.find(v => v.language_id === languageId)?.name || ''
        })
    }

    const defaultValue = post.authors.map(author => (
        {
            value: author.id ,
            label: author.variants.find(v => v.language_id === languageId)?.name || ''
        }
    ))

    async function loader(input: string) : Promise<Array<SelectOption>> {

        const users : Array<User> = await api.get(subdomain, '/users/search', {
            search: input
        });

        addUsers(users)

        return users.map(user => ({
            value: user.id,
            label: user.variants.find(v => v.language_id === languageId)?.name || ''
        }))

    }

    function handleChange(options: OnChangeValue<SelectOption, true>) {

        const authors : Array<User> = [];
        options.forEach(({value}) => authors.push(users[value]))
        updatePostValue('authors', authors);

    }

    function handleRemove(id: number) {
        const authors = post.authors.filter(author => author.id !== id);
        updatePostValue('authors', authors);
    }

    return <div className="post-authors">

        {
            post.authors.map(author => {

                const variant = author.variants.find(v => v.language_id === languageId);

                return <span className="post-author">
                    <ProfilePicture user={author} size={16} /> 
                    <span className="name">
                        { variant?.name || 'Anonymous' }
                    </span>
                    <span className="remove" onClick={() => handleRemove(author.id)}>
                        &times;
                    </span>
                </span>

            })
        }

        <Adder post={post} />

    </div>

    /* return <AsyncSelect
        loadOptions={loader}
        cacheOptions
        defaultOptions={options}
        // defaultValue={defaultValue}
        value={defaultValue}
        isMulti
        classNamePrefix="react-select"
        className="react-select react-select-normal react-select-multi"
        onChange={handleChange}
    /> */

}

function Adder({post} : { post: Post}) {

    const subdomain = getSubdomain()

    const usersLogicInst = usersLogic({subdomain});
    const { users } = useValues(usersLogicInst) as { users: IDKeyedUsers }
    const { addUsers } = useActions(usersLogicInst)
    const languageId = getPrimaryLanguage(subdomain).id

    const [isAdding, setIsAdding] = useState(false);

    return <span className="author-adder">
        <span className="plus" onClick={() => setIsAdding(!isAdding)}>+</span>

        {
            isAdding && <div className="adder">

                <div className="search-wrap">
                    <input type="text" placeholder="Search..." />
                </div>

                <div className="users">

                    {
                        Object.values(users).map((user: User) => {

                            const variant = user.variants.find(v => v.language_id === languageId);

                            return <span className="user">
                                <ProfilePicture user={user} size={16} /> 
                                <span className="name">
                                    { variant?.name || 'Anonymous' }
                                </span>
                            </span>

                        })
                    }

                </div>

            </div>
        }
    </span>

}