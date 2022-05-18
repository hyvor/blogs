import React from 'react';
import {Post} from "../objects/post";
import Select from "../ReusableComponents/Select";
import {useValues} from "kea";
import postsLogic from "../logic/postsLogic";
import subdomainLogic from "../logic/subdomainLogic";

interface SelectOption {
    value: number;
    label: string;
}

export default function PostAuthors({ post, languageId } : { post: Post, languageId: number }) {

   /* const subdomain = subdomainLogic.values.subdomain;
    const tagsLogicBuilt = tagsLogic({subdomain})
    const { tag, getSelectedTags } = useValues(tagsLogicBuilt)
    const { saveId } = useActions(tagsLogicBuilt)  */

    const { counts } =
        useValues(postsLogic({subdomain: subdomainLogic.values.subdomain})) as { counts: PostCounts }


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

    return <Select
        options={options}
        defaultValue={defaultValue}
        isMulti
    />

}