import React from 'react';
import NavLink from '../ReusableComponents/NavLink';
import postLogic from '../logic/postLogic';
import { useValues } from 'kea';
import dayjs from 'dayjs';
import languagesLogic from '../logic/languagesLogic';
import LangTag from '../ReusableComponents/LangTag';
import { getLangTagIconByPostStatus } from './Post/PostLanguageSelector';
import { PostVariant } from "../types";
import UserPermissions from "../services/UserPermissions";
import { CheckCircleFill, ExclamationCircleFill, QuestionCircleFill, XCircleFill } from "react-bootstrap-icons";
import { SeoScoreTag } from "./Post/New/Seo/Seo";
import { getCountsByStatus } from "./Post/New/Links/links";
import Tooltip from "../ReusableComponents/Tooltip";

export default function PostsListRow({ id, subdomain }: { id: number, subdomain: string }) {

    const { languages, getLanguageById } = useValues(languagesLogic({ subdomain }))
    const { 
        post, 
        postOriginal, 
        currentVariantSeoResults,
        currentVariantLinkAnalysis
    } = useValues(postLogic({ id }))

    const postsLink = `/console/${subdomain}/` + (post.is_page ? 'pages' : 'posts')
    const toLink = `${postsLink}/${post.id}`

    const languageId = languages[0].id

    const variant = post.variants.find(v => v.language_id === languageId) as PostVariant;

    const permClass = UserPermissions.canEditPost(postOriginal) ? '' : 'global-no-permissions';


    function LinkTag() {

        const counts = getCountsByStatus(currentVariantLinkAnalysis);

        if (counts.loading > 0) {
            return <Tooltip tooltip="Link analysis outdated. Open the post to analyze again">
                <span className="global-seo-score-tag link-tag ignore"><QuestionCircleFill /></span>
            </Tooltip>
        }

        if (counts.broken > 0) {
            return <Tooltip tooltip="Some broken links found">
                <span className="global-seo-score-tag link-tag red"><XCircleFill /></span>
            </Tooltip>
        }

        if (counts.redirect > 0) {
            return <Tooltip tooltip="Some redirects found">
                <span className="global-seo-score-tag link-tag orange"><ExclamationCircleFill /></span>
            </Tooltip>
        }

        return <Tooltip tooltip="All links are healthy">
            <span className="global-seo-score-tag link-tag green"><CheckCircleFill /></span>
        </Tooltip>

    }

    return <NavLink
        key={post.id}
        href={location.pathname === toLink ? postsLink : toLink}
        className={"posts-list-item" + ` ${variant.status} ${permClass}`}>

        <div>
            <div className="post-title">{variant.title || '(Untitled)'}</div>

            <div className="post-data">
                <div className="post-date">
                    {dayjs.unix(post.published_at || post.created_at).format('MMM D, YYYY')}
                </div>
            </div>

        </div>

        <div className="post-languages">
            {
                post.variants.map(variant => {
                    const lang = getLanguageById(variant.language_id)

                    return lang ?
                        <LangTag
                            key={lang.id}
                            code={lang.code}
                            icon={getLangTagIconByPostStatus(variant.status)}
                        /> : null
                })
            }
        </div>

        {
            !post.is_page ?
                <div className="post-authors">
                    {
                        post.authors.map(author => {
                            return <div className="post-author" key={author.id}>
                                <img
                                    src={author.picture_url || undefined}
                                    className="round-image-40 post-author-image "
                                    alt="Profile Picture"
                                />
                                <span className="post-author-name">
                                        {author.variants[0]?.name}
                                    </span>
                            </div>
                        })
                    }
                </div>
                : null
        }

        <div className="post-tags-wrap">

            <div className="post-tags">
                {
                    !post.is_page ?
                        post.tags.map(tag => {
                            return <span
                                key={tag.id}
                                className="post-tag"
                            >
                                {
                                    tag.variants.find(v => v.language_id === languageId)?.name
                                }
                            </span>
                        })
                        : null
                }
            </div>
        </div>

        <div className="post-health-wrap">
            <div className="seo">
                <span className="name">SEO</span>
                <SeoScoreTag score={currentVariantSeoResults.average} percentage={true} />
            </div>
            <div className="links">
                <span className="name">Links</span>
                <LinkTag />
            </div>
        </div>

        <div className="post-status-wrap">
            <span className={`global-post-status ${variant.status}`}>{variant.status}</span>
        </div>


    </NavLink>

}