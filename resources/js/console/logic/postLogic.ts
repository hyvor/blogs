import {actions, defaults, events, kea, key, listeners, path, props, reducers, selectors} from "kea";
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
import { Output, SeoAnalyzer } from "../Posts/Post/New/Seo/seo-analyzer";
import { calculateLinkAnalysis, getLinksFromContent, Link } from "../Posts/Post/New/Links/links";
import { subscriptions } from 'kea-subscriptions'
import { getBlogBaseUrl, getBlogUrl } from "../lib/blog-helpers";

export type PostDiff = Omit<Post, 'authors' | 'tags'> & {
    authors: number[],
    tags: number[],
}

export async function updatePost(post: Post, diff: Partial<PostDiff>) {

    diff = {...diff};

    if (diff.variants) {
        throw new Error('Use savePostVariantDiff instead');
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

    defaults(({props}) => {
        return {
            post: props.data || {} as Post,
            postOriginal: props.data || {} as Post,
        }
    }),

    actions(({values}) => ({
        setBoth: (obj: Post) => ({obj}),
        set: (obj: Post) => ({obj}),
        setVariant: (variant: PostVariant) => ({variant}),
        setOriginal: (obj: Post) => ({obj}),
        setVariantOriginal: (variant: PostVariant) => ({variant}),
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
        changeEditorState: (key: keyof PostEditorState, value: any) => ({key, value}),

        setCurrentVariantSeoResults: (results: Output) => ({results}),
        setCurrentVariantLinks: (links: Link[]) => ({links})
    })),

    ajax(({actions, selectors, props, values}) => ({
 
        loadPost: async () => {
            const response = await api.get<Post>(getSubdomain(), `/post/${props.id}`);
            actions.setBoth(response);
        },

        deletePost: async () => {
            // remove from posts list
            const subdomain = getSubdomain()
            const postsLogicInst = postsLogic({subdomain});
            postsLogicInst.actions.setPostsList(postsLogicInst.values.postsList.filter(pId => pId !== props.id));
            postsLogicInst.actions.navigateToPosts();
            await api.delete(subdomain, `/post/${props.id}`);
        },

        savePostDiff: async(
            {diff, onSave} : 
            {diff: Partial<PostDiff>, onSave: Function, updateState?: boolean}
        ) => {
            const response = await updatePost(values.post, diff);
            actions.setOriginal(response);

            /**
             * This updates the state with the response from the server
             * No side effects
             */

            const updatedObject : Post = {...values.post};
            for (let key in diff) {
                // @ts-ignore
                updatedObject[key as keyof Post] = response[key as keyof Post];
            }
            actions.set(updatedObject);

            typeof onSave === 'function' && onSave(response);
        },

        saveCurrentVariantDiff: async(
            {diff, onSave}: 
            {diff: Partial<PostVariant>, onSave?: (variant: PostVariant) => void, updateState?: boolean}
        ) => {
            const currentVariant = values.currentVariant;
            const response = await api.patch<PostVariant>(getSubdomain(), `/post/${values.post.id}/variant`, {
                language_id: currentVariant.language_id,
                ...diff
            });
            actions.setVariantOriginal(response);

            /**
             * This updates the state with the response from the server
             * Has these side effects:
             *  - slug of variant can change when the status is updated
             */
            const updatedObject : any = {...currentVariant};
            const keys = Object.keys(diff) as (keyof PostVariant)[];
            keys.push('url');
            if (diff.status) {
                keys.push('slug');
            }
            keys.forEach(key => updatedObject[key] = response[key]);
            actions.setVariant(updatedObject);

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
                setBoth: (_, {obj}) => obj,
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
                setBoth: (_, {obj}) => obj,
                setVariantOriginal: (state, {variant}) => {
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
                // isPublishing: false,
                // isUnpublishing: false,
                // just editing the post
                // isNonDraftEditing: false,
                isDiscarding: false,
                
                isSaving: false,
                
                // updater opened
                isNonDraftUpdating: false,
                version: 1,
                editorView: null,

                settingsSection: 'settings'

            } as PostEditorState,
            {
                changeEditorState: (state, {key, value}) => (
                    {...state, [key]: value} as PostEditorState
                )
            }
        ],

        currentVariantSeoResults: [
            {
                average: 0,
                tests: []
            } as Output,
            {
                setCurrentVariantSeoResults: (_, {results}) => results
            }
        ],

        currentVariantLinks: [
            [] as Link[],
            {
                setCurrentVariantLinks: (_, {links}) => links
            }
        ]

    })),

    selectors({

        // diff of orignal and state
        diff: [
            s => [s.post, s.postOriginal],
            (post, postOriginal) => {

                const postDiffCheck : PostDiff = {
                    ...post,
                    authors: post.authors.map(a => a.id),
                    tags: post.tags.map(a => a.id),
                };

                const postOriginalDiffCheck : PostDiff = {
                    ...postOriginal,
                    authors: postOriginal.authors.map(a => a.id),
                    tags: postOriginal.tags.map(a => a.id),
                };

                const d = diff(postOriginalDiffCheck, postDiffCheck) as Partial<PostDiff>;

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
            (post, editorState) : PostVariant => {
                return post.variants.find(v => v.language_id === editorState.languageId) as PostVariant
            }
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

        /* currentVariantSeoResults: [
            s => [s.currentVariant],
            (currentVariant) => {
                console.log('calculating seo results', currentVariant.id)
                const userBlog = userBlogsLogic().values.findBlogBySubdomain(getSubdomain());
                const language = languagesLogic({subdomain: getSubdomain()}).values.getLanguageById(currentVariant.language_id);
                const blogUrl = userBlog.blog.base_url;
                const analyzer = new SeoAnalyzer({
                    ...getSeoResultsInput(currentVariant),
                    blogUrl,
                    languageCode: language?.code || 'en',
                });
                return analyzer.analyze();
            },
            {
                equalityCheck: (a, b) => {
                    return JSON.stringify(getSeoResultsInput(a)) === 
                        JSON.stringify(getSeoResultsInput(b));
                }
            }
        ], */

        currentVariantLinkAnalysis: [
            s => [s.currentVariant],
            (currentVariant) => calculateLinkAnalysis(currentVariant)
        ],

    }),

    subscriptions(({actions, values, props}) => ({
        currentVariant: (currentVariant: PostVariant, oldValue: PostVariant|undefined) => {

            // seo results
            (function handleSeo() {

                function analyzeSeo() {
                    const startTime = new Date().getTime();

                    const userBlog = userBlogsLogic().values.findBlogBySubdomain(getSubdomain());
                    const language = languagesLogic({subdomain: getSubdomain()}).values.getLanguageById(currentVariant.language_id);
                    const blogUrl = userBlog.blog.base_url;
                    const analyzer = new SeoAnalyzer({
                        ...getSeoResultsInput(currentVariant),
                        blogUrl,
                        languageCode: language?.code || 'en',
                    });
                    const results = analyzer.analyze();
                    actions.setCurrentVariantSeoResults(results);

                    const endTime = new Date().getTime();
                    console.log('seo analysis took', endTime - startTime, 'ms');
                }

                if (!oldValue) {
                    return analyzeSeo();
                }

                const currentVariantInput = getSeoResultsInput(currentVariant);
                const oldValueInput = getSeoResultsInput(oldValue);

                if (JSON.stringify(currentVariantInput) === JSON.stringify(oldValueInput)) {
                    return;
                }

                if (currentVariantInput.content !== oldValueInput.content) {
                    // wait 100ms before calculating seo results on content change
                    if (SEO_CALCULATION_TIMEOUTS[currentVariant.id]) {
                        clearTimeout(SEO_CALCULATION_TIMEOUTS[currentVariant.id]);
                    }
                    SEO_CALCULATION_TIMEOUTS[currentVariant.id] = setTimeout(analyzeSeo, 100);
                } else {
                    // for other changes calculate seo results immediately
                    analyzeSeo();
                }

            })();

            // links
            (function handleLinks() {

                if (LINK_CALCULATION_TIMEOUTS[currentVariant.id]) {
                    clearTimeout(LINK_CALCULATION_TIMEOUTS[currentVariant.id]);
                }

                LINK_CALCULATION_TIMEOUTS[currentVariant.id] = setTimeout(() => {
                        
                    const startTime = new Date().getTime();

                    const links = getLinksFromContent(
                        currentVariant.content_unsaved || currentVariant.content,
                        getBlogBaseUrl(getSubdomain())
                    );
                    actions.setCurrentVariantLinks(links);

                    const endTime = new Date().getTime();
                    console.log('links update took', endTime - startTime, 'ms');
    
                }, 100);

            })();


        }
    })),

    events(({actions, values, props}) => ({}))

])

// post variant id: date
const SEO_CALCULATION_TIMEOUTS = {} as {[key: number]: ReturnType<typeof setTimeout>};
const LINK_CALCULATION_TIMEOUTS = {} as {[key: number]: ReturnType<typeof setTimeout>};

function getSeoResultsInput(variant: PostVariant) {
    return {
        primaryKeyword: variant.seo_primary_keyword,
        secondaryKeywords: variant.seo_secondary_keywords,
        title: variant.title || '',
        slug: variant.slug || '',
        description: variant.description || '',
        content: variant.content_unsaved || variant.content
    };
}

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
