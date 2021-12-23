import React from 'react'
import { useRecoilValue } from 'recoil'
import activeBlogIdState from '../state/activeBlogIdState'
import blogsState from '../state/blogsState'


import {ChevronExpand} from 'react-bootstrap-icons';

export default function BlogsSelector() {

    const blogs = useRecoilValue(blogsState)
    const activeBlogId = useRecoilValue(activeBlogIdState)

    return <div className="blog-selector">
        <div className="name">Supun's Blog</div>
        <div><ChevronExpand /></div>
    </div>

}