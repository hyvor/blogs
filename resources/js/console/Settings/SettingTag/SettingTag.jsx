import React, {useState} from 'react';
import { useActions, useValues } from 'kea';
import subdomainLogic from '../../logic/subdomainLogic';
import tagsLogic from '../../logic/tagsLogic';
import Loader from '../../ReusableComponents/Loader';
import Toast from '../../ReusableComponents/Toast';
import NoResults from '../../ReusableComponents/NoResults';
import CreateTag from './CreateTag';
import Tags from './TagsTable';


//  Remaining 
/*
*
* Create tag Variant in language select if a variant is not selected.
* Update tag data (according to an language if required)
* Delete tag according to an condition.
* 
*/

export default function SettingTag(props) {
    const subdomain = subdomainLogic.values.subdomain;
    const tagsLogicBuilt = tagsLogic({subdomain})
    const { tag, loadAjax, createAjax, tagListHasMore, loadTagsListMoreAjax } = useValues(tagsLogicBuilt)
    const { loadTagsListMore, load} = useActions(tagsLogicBuilt)

    function handleScroll(e) {
        var el = e.target;
        if (
            loadTagsListMoreAjax.status !== 'loading' &&  
            tagListHasMore &&
            el.scrollTop + el.clientHeight >= el.scrollHeight
        ) 
        {
            loadTagsListMore({ offset: tag.length })
        }
    }

    return <div className="settingsTag">
        <div className="tag-title-bar">
            <div className="tag-title">
                Tags
            </div>
            <div>
                <CreateTag/>
                {
                    createAjax.status === 'error' ?
                    <Toast 
                        x={console.log(createAjax.error)}
                        text={createAjax.error}
                        type="error"
                    /> : null
                }
            </div>
        </div>

        <div>
            { 
                loadAjax.status === 'loading' ?
                    <Loader padding={40}/> 
                :
                    <div>
                        {
                            tag.length ?
                                <div className="global-table-view">
                                    <div className="global-table-header-six">      
                                        <div className="table-head-item">Name</div> 
                                        <div className="table-head-item">Slug</div>
                                        <div className="table-head-item">Description</div>
                                        <div className="table-head-item">Posts</div> 
                                        <div className="table-head-item">Code</div>
                                        <div></div>
                                    </div>

                                    <div>
                                        {
                                            tag.map(tag => (
                                                <div className="global-table-body">
                                                    <Tags key = {tag} tag={tag} subdomain ={subdomain}/>           
                                                </div>
                                            ))
                                        }
                                        {
                                            tagListHasMore == true ?
                                                <div>
                                                    <button type='button' className ="loadMore" onClick={handleScroll}>Load More</button>
                                                </div>
                                            : null
                                        }
                                    </div>
                                
                                </div>
                            :
                                <NoResults 
                                    text="There are no tags"
                                    padding={40}
                                    imageWidth={250}
                                />
                        }
                    </div>
            }
        </div>
    </div> 
}