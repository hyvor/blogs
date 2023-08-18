import {actions, events, kea, key, listeners, path, props, reducers, selectors} from "kea";
import api from "../lib/api";
import postsLogic from "./postsLogic";
import type { postLogicType } from "./postLogicType";
import {Language, Post, PostVariant} from "../types";
import {ajax} from "kea-ajax";
import getSubdomain from "../logic-helpers/subdomain";
import {diff} from "deep-object-diff";
import languagesLogic from "./languagesLogic";
import {PostEditorState} from "../states";
import merge from "deepmerge";
import userBlogsLogic from "./userBlogsLogic";
import { SeoAnalyzer } from "../Posts/Post/New/Seo/seo-analyzer";

export async function updatePost(post: Post, diff: Partial<Post>) {

    if (diff.variants) {
        for (let variant of diff.variants) {
            await api.patch(
                getSubdomain(), `/post/${post.id}/variant`,
                variant
            )
        }
        delete diff.variants;
    }

    if (diff.authors) {
        await api.patch(getSubdomain(), `/post/${post.id}/authors`, {
            ids: post.authors.map(author => author.id)
        });
        delete diff.authors
    }
    if (diff.tags) {
        await api.patch(getSubdomain(), `/post/${post.id}/tags`, {
            ids: post.tags.map(author => author.id)
        });
        delete diff.tags
    }

    return await api.patch<Post>(getSubdomain(), `/post/${post.id}`, diff)

}


const postLogic = kea<postLogicType>([

    props({} as {id: number, data?: Post}),
    key(props => props.id),
    path(key => ['post', key]),

    actions(({values}) => ({
        set: (obj: Post) => ({obj}),
        setOriginal: (obj: Post) => ({obj}),
        setVariant: (variant: PostVariant) => ({variant}),
        updatePostValue: (key: keyof Post, value: any) => ({key, value}),
        updatePost: (update: Partial<Post>) => ({update}),
        updateCurrentPostVariantValue: (key: keyof PostVariant, value: any) => ({
            key,
            value,
            languageId: values.editorState.languageId
        }),
        updateCurrentPostVariant: (update: Partial<PostVariant> & {language_id: number}) => ({update}),
        addVariant: (variant: PostVariant) => ({variant}),
        removeVariant: (languageId: number) => ({languageId}),
        changeEditorState: (key: keyof PostEditorState, value: any) => ({key, value})
    })),

    ajax(({actions, selectors, props, values}) => ({
 
        loadPost: async () => {
            const response = await api.get<Post>(getSubdomain(), `/post/${props.id}`);
            actions.set(response);
        },

        deletePost: async () => {
            // remove from posts list
            const subdomain = getSubdomain()
            const postsLogicInst = postsLogic({subdomain});
            postsLogicInst.actions.setPostsList(postsLogicInst.values.postsList.filter(pId => pId !== props.id));
            postsLogicInst.actions.navigateToPosts();
            await api.delete(subdomain, `/post/${props.id}`);
        },

        /**
         * @deprecated use savePostDiff instead
         */
        savePost: async () => {
            const diff = values.diff
            
            if (Object.keys(diff).length === 0) {
                return false;
            }

            const response = await updatePost(values.post, diff);
            actions.setOriginal(response);
        },
        
        /**
         * @deprecated use savePostDiff instead
         */
        forceSavePost: async ({onSave, update} : { update: Partial<Post>, onSave: (post: Post) => void}) => {

            const diff = mergePostWithUpdate(values.diff, update)

            const response = await updatePost(values.post, diff);
            actions.set(response)

            typeof onSave === 'function' && onSave(response);
        },

        savePostDiff: async({diff, onSave} : {diff: Partial<Post>, onSave: Function}) => {
            const response = await updatePost(values.post, diff);
            actions.set(response)
            typeof onSave === 'function' && onSave(response);
        },

        saveCurrentVariantDiff: async({diff, onSave}: {diff: Partial<PostVariant>, onSave?: Function}) => {
            const currentVariant = values.currentVariant;
            const response = await api.patch<PostVariant>(getSubdomain(), `/post/${values.post.id}/variant`, {
                language_id: currentVariant.language_id,
                ...diff
            });
            actions.setVariant(response);
            typeof onSave === 'function' && onSave(response);
        },

        createVariant: async ({languageId, onCreate} : {languageId: number, onCreate: Function}) => {

            const variant = await api.post<PostVariant>(getSubdomain(),
                `/post/${props.id}/variant`,
                {
                    language_id: languageId
                }
            );

            actions.addVariant(variant);
            onCreate && onCreate();

        },

        deleteVariant: async ({languageId} : {languageId: number}) => {

            actions.removeVariant(languageId);

            await api.delete(getSubdomain(), `/post/${props.id}/variant`, {
                language_id: languageId
            });

        }

    })),

    listeners(({actions, values}) => ({

        updateCurrentPostVariantValue: ({key, languageId, value}) => {

            // auto update slug when updating title 
            // if the original value is null
            // commented to let the user select the slug
            /*if (key === 'title' && values.postOriginal.slug === null) {
                actions.updatePostValue("slug", slugify(value))
            }*/

        },

    })),


    reducers(({values}) => ({

        // post's current state in the front-end
        post: [
            {} as Post,
            {
                set: (_, {obj}) => obj,
                setVariant: (state, {variant}) => {
                    const copy = {...state}
                    copy.variants = copy.variants.map(
                        v => v.language_id === variant.language_id ?
                            variant :
                            v
                    );
                    return copy;
                },
                updatePostValue: (state, {key, value}) => ({...state, ...{[key]: value}} as Post),
                updatePost: (state, {update}) => ({...state, ...update} as Post),
                updateCurrentPostVariantValue: (state, {key, value, languageId}) => {
                    const copy = {...state}
                    copy.variants = copy.variants.map(
                        variant => variant.language_id === languageId ?
                            {...variant, [key]: value || null} :
                            variant
                    );
                    return copy;
                },
                updateCurrentPostVariant: (state, {update}) => {
                    const copy = {...state}
                    copy.variants = copy.variants.map(
                        variant => variant.language_id === update.language_id ?
                            {...variant, ...update} :
                            variant
                    );
                    return copy;
                },
                addVariant: (state, {variant}) => {
                    const copy = {...state}
                    copy.variants.push(variant);
                    return copy;
                },
                removeVariant: (state, {languageId}) => {
                    const copy = {...state}
                    copy.variants = copy.variants.filter(v => v.language_id !== languageId)
                    return copy;
                }
            }
        ],

        // the really saved post in the back-end
        postOriginal: [
            {} as Post,
            {
                set: (_, {obj}) => obj,
                setVariant: (state, {variant}) => {
                    const copy = {...state}
                    copy.variants = copy.variants.map(
                        v => v.language_id === variant.language_id ?
                            variant :
                            v
                    );
                    return copy;
                },
                setOriginal: (_, {obj}) => obj,
                addVariant: (state, {variant}) => {
                    const copy = {...state}
                    copy.variants.push(variant);
                    return copy;
                },
                removeVariant: (state, {languageId}) => {
                    const copy = {...state}
                    copy.variants = copy.variants.filter(v => v.language_id !== languageId)
                    return copy;
                }
            }
        ],

        editorState: [
            {
                languageId: languagesLogic({subdomain: getSubdomain()}).values.primaryLanguage.id as number,
                isFullscreen: false,
                isPublishing: false,
                isUnpublishing: false,
                // just editing the post
                isNonDraftEditing: false,
                isDiscarding: false,
                
                isSaving: false,
                
                // updater opened
                isNonDraftUpdating: false,
                version: 1,
            } as PostEditorState,
            {
                changeEditorState: (state, {key, value}) => (
                    {...state, [key]: value} as PostEditorState
                )
            }
        ]

    })),

    selectors({

        // diff of orignal and state
        diff: [
            s => [s.post, s.postOriginal],
            (post, postOriginal) => {
                const d = diff(postOriginal, post) as Partial<Post>

                if (d.preview_id) delete d.preview_id;
                if (d.updated_at) delete d.updated_at;

                /**
                 * diff() function does not return correct diffs for arrays
                 * So, fixing is manually here
                 */
                if (d.variants) {

                    d.variants = [];

                    for (let variant of post.variants) {
                        let variantOriginal = postOriginal.variants.find(v => v.language_id === variant.language_id)

                        if (!variantOriginal)
                            continue;

                        let variantDiff = diff(variantOriginal, variant) as Partial<PostVariant>

                        if (variantDiff.url) delete variantDiff.url;

                        if (Object.keys(variantDiff).length > 0) {
                            variantDiff.language_id = variant.language_id;
                            d.variants.push(<PostVariant>variantDiff)
                        }
                    }

                    if (d.variants.length === 0) {
                        delete d.variants;
                    }

                }

                return d;
            }
        ],

        currentVariant: [
            s => [s.post, s.editorState],
            (post, editorState) : PostVariant =>
                post.variants.find(v => v.language_id === editorState.languageId) as PostVariant
        ],

        currentVariantDiff: [
            s => [s.currentVariant, s.postOriginal],
            (currentVariant, postOriginal) : Partial<PostVariant> => {
                const variantOriginal = postOriginal.variants
                    .find(v => v.language_id === currentVariant.language_id) as PostVariant;
                const d = diff(variantOriginal, currentVariant) as Partial<PostVariant>
                if (d.url) delete d.url;
                return d;
            }
        ],

        currentVariantOriginal: [
            s => [s.currentVariant, s.postOriginal],
            (currentVariant, postOriginal) : PostVariant => {
                return postOriginal.variants
                    .find(v => v.language_id === currentVariant.language_id) as PostVariant;
            }
        ],

        currentLanguage: [
            s => [s.editorState, languagesLogic({subdomain: getSubdomain()}).selectors.getLanguageById],
            (editorState, getLang) : Language => getLang(editorState.languageId) as Language
        ],

        currentVariantSeoResults: [
            s => [s.currentVariant],
            (currentVariant) => {
                const userBlog = userBlogsLogic().values.findBlogBySubdomain(getSubdomain());
                const blogUrl = userBlog.blog.base_url;
                const analyzer = new SeoAnalyzer({
                    primaryKeyword: currentVariant.seo_primary_keyword,
                    secondaryKeywords: currentVariant.seo_secondary_keywords,
                    title: currentVariant.title || '',
                    slug: currentVariant.slug || '',
                    description: currentVariant.description || '',
                    content: currentVariant.content_unsaved || currentVariant.content,
                    blogUrl,
                });
                return analyzer.analyze();
            }
        ]

    }),

    events(({actions, values, props}) => ({
        afterMount: () =>  {
            if (values.post.id)
                return;

            if (props.data) {
                actions.set(props.data);
            } else {
                actions.loadPost();
            }
        }
    }))

])

/**
 * Merges diff with update (for forced save) in a variant-safe manner
 * (deepmerge does not work with the variants array)
 */
function mergePostWithUpdate(diff: Partial<Post>, update: Partial<Post>) : Partial<Post> {

    let diffCopy = {...diff};
    let updateCopy = {...update}

    if (updateCopy.variants) {
        diffCopy.variants = diffCopy.variants || [];
        for (let variant of updateCopy.variants) {

            if (!diffCopy.variants.find(diffV => diffV.language_id === variant.language_id)) {
                diffCopy.variants.push(variant);
            } else {

                diffCopy.variants = diffCopy.variants.map(
                    v => v.language_id === variant.language_id ?
                        merge(v, variant) :
                        v
                );

            }

        }
        delete updateCopy.variants
    }

    diffCopy = merge(diffCopy, updateCopy);

    return diffCopy;
}

export default postLogic;
