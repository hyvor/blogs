import React from 'react';
import { useActions, useValues } from 'kea';
import ReactSelect, { components } from 'react-select';
import makeAnimated from 'react-select/animated';
import subdomainLogic from '../logic/subdomainLogic';
import postTagLogic from '../logic/posts/postTagLogic';

import tagsLogic from '../logic/tagsLogic';


// Create another function and test whether the 'loadSelectedTags' working without any issue.
// The selected tag data should be deleted when the user click on the cross icon.
// The post what are loaded in the dropdown should be loaded according to the post count.
// The selected tag should not be loaded in the dropdown menu.



// 1. get the post tags from the normal settings tag section. -- done
// 2. create a new function to get the post id and the tag id from the post_tag table (but will have to pass the postId from the url and the tag id as an request.)
// (2) this is  -- done but I didn't have to pass the tag Id.
// to create a new tag will have to pass the value to the array in the postLogic.js and then find a way to pass it to the back-end.
// most probably to delete also we can do the same by updating the array and passing it to the back-end.



export default function SelectTags({postId}) {

    const subdomain = subdomainLogic.values.subdomain;
    const postTagLogicBuilt = postTagLogic({subdomain})
    const { pt, selectedPT } = useValues(postTagLogicBuilt)
    const { createId, loadSelectedTags, load } = useActions(postTagLogicBuilt)  



    const tagsLogicBuilt = tagsLogic({subdomain})
    const { tag, loadAjax, createAjax, tagListHasMore, loadTagsListMoreAjax } = useValues(tagsLogicBuilt)
    const { loadTagsListMore} = useActions(tagsLogicBuilt)

    // console.log(selectedPT)
    // const options = pt.map(postTag => (
    //     {value: postTag.id , label: postTag.name}
    // )) 

    const options = tag.map(postTag => (
        {value: postTag.id , label: postTag.name}
    )) 

    // const defaultValue = { value: 0 , label: "Select Tag" } 
    const defaultValue = pt.map(postTag => (
        {value: postTag.name , label: postTag.name}
    )) 
    
    const customStyles = {

        control: (provided) => ({
            ...provided,
            width: '100%',
            fontSize: '12px',
            // padding: '3px 5px',
            borderRadius: '20px',
            border: 'none',
            background: '#f5f5f5',
            fontFamily: 'inherit',
            transition:' 0.3s box-shadow',
            alignItems: 'center',
            height: '20px',
            
            // whiteSpace: "nowrap",
            // textOverflow: "initial",
            // overflow: "hidden",
            overflowX: 'auto',
        }),

        valueContainer: (base, state) => ({
            ...base,
            fontFamily: 'Helvetica, sans-serif !important',
            fontSize: 12,
            fontWeight: 500,
            color: '#000',
            paddingLeft: '15px',
            paddingRight: '15px',
            display: 'flex',
            // paddingTop: '-2px',
            // paddingBottom: '-2px',

        }),

        multiValue: (base) => ({
            ...base,
            backgroundColor: "#896c6b",
            borderRadius: "20px"
        }),
        
        multiValueLabel: (base) => ({
            ...base,
            color: "#fff",
            fontWeight: "500"
        }),

        multiValueRemove: (base, state) => ({
            ...base,
            color: '#fff',
            borderRadius: '20px',
            backgroundColor: '896c6b',
           '&:hover': {
                backgroundColor: '#f1e8e8',
                color:'black',
            }
        }),

        dropdownIndicator: (base) => ({
            ...base,
            display:'none',
        }),

        clearIndicator: (base) => ({
            ...base,
            display:'none',
        }),

        indicatorSeparator:(base)=>({
            ...base,
            display:'none',
          }),

        option: (provided, state) => ({
            ...provided,
            color: '#000',
            backgroundColor: state.isSelected ? '#f1e8e8' : '#fff',
            width: '95%',
            display: 'flex',
            minHeight: 'initial',
            borderRadius: '20px',
            border: 'none',
            transition: '0.3s box-shadow',
            margin:'10px',
          }),

        singleValue: (provided, state) => {
            const opacity = state.isDisabled ? 0.5 : 1;
            const transition = 'opacity 300ms';
        
            return { ...provided, opacity, transition };
        }
      }

    function handleTag(data){
        
        const lastValue = data[data.length - 1];
        const tagId = lastValue.value
        // const postIdentity = postId.postId
        // console.log(postIdentity)
        
        // load({
        //     postId : postId,
        // })

        // loadSelectedTags({
        //     postId : postId,
        // })
    }
    
    const TagSelect = () => (
        <ReactSelect
            defaultValue={defaultValue}
            styles={customStyles}
            options={options}
            components={makeAnimated()}
            maxMenuHeight={150}
            // onChange={() => {}}
            onChange={handleTag}
            isMulti 
            // components={{ DropdownIndicator:() => null, IndicatorSeparator:() => null }}
        />
    );

    // loadSelectedTags({ postId : postId })
    // load({ postId : postId })


    return <TagSelect/>

}