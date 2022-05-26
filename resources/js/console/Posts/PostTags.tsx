import React from 'react';
import {Post} from "../objects/post";
import {useActions, useValues} from "kea";
import subdomainLogic from "../logic/subdomainLogic";
import AsyncSelect from "react-select/async";
import api from "../lib/api";
import {Tag, User} from "../types";
import {getPrimaryLanguage} from "../lib/blog-helpers";
import tagsLogic, {IDKeyedTags} from "../logic/tagsLogic";

interface SelectOption {
    value: number;
    label: string;
}

export default function PostTags({ post, updatePostValue } : { post: Post, updatePostValue: Function }) {

    const subdomain = subdomainLogic.values.subdomain

    const tagsLogicInst = tagsLogic({subdomain})
    const { tags } = useValues(tagsLogicInst) as { tags: IDKeyedTags }
    const { addTags } = useActions(tagsLogicInst)
    const languageId = getPrimaryLanguage(subdomain).id

    const options: Array<SelectOption> = [];

    for (let id in tags) {
        options.push({
            value: parseInt(id),
            label: tags[id].variants[languageId].name
        })
    }

    const defaultValue = post.tags.map(tag => (
        {value: tag.id , label: tag.variants[languageId].name }
    ))

    async function loader(input: string) : Promise<Array<SelectOption>> {

        const tags : Array<Tag> = await api.get(subdomain, '/tags/search', {
            search: input
        });

        addTags(tags)

        return tags.map(tag => ({
            value: tag.id,
            label: tag.variants[languageId].name
        }))

    }

    function handleChange(options: Array<SelectOption>) {
        const postTags : Array<Tag> = [];
        options.forEach(({value}) => postTags.push(tags[value]))
        updatePostValue('tags', postTags);
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