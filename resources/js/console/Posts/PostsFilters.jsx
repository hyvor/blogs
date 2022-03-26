import React, { useEffect, useState } from 'react';
import Select from '../ReusableComponents/Select';
import { components } from 'react-select';
import { useValues } from 'kea';
import postsLogic from '../logic/postsLogic';
import subdomainLogic from '../logic/subdomainLogic';
import blogsLogic from '../logic/blogsLogic';
import numberFormatter from '../../helpers/numberFormatter';
import languagesLogic from '../logic/languagesLogic';

export default function PostsFilters({ filters, changeFilter }) {

    const { subdomain } = useValues(subdomainLogic)
    const { counts } = useValues(postsLogic({subdomain}))
    const { findBlogBySubdomain } = useValues(blogsLogic)

    const blog = findBlogBySubdomain(subdomain)

    const statusOptions = [
        { value: 'all', label: <FilterLabel name="All" count={blog.blog.posts_count} /> },
        { value: 'published', label: <FilterLabel name="Published" count={counts && counts.status.published} />},
        { value: 'draft', label: <FilterLabel name="Draft" count={counts && counts.status.draft} />},
        { value: 'scheduled', label: <FilterLabel name="Scheduled" count={counts && counts.status.scheduled} />},
        { value: 'featured', label: <FilterLabel name="Featured" count={counts && counts.status.featured} />},
    ]

    const [authorsOptions, setAuthorsOptions] = useState([
        { value: 'all', label: <FilterLabel name="All" count={blog.blog.posts_count} /> },
        { value: 'you', label: <FilterLabel name="You" count={blog.user.posts_count} /> },
    ])
    const [tagsOptions, setTagsOptions] = useState([
        { value: 'all', label: <FilterLabel name="All" count={blog.blog.posts_count} /> },
    ]);

    const dateOptions = [
        { value: 'all', label: 'All' },
        { value: 'today', label: 'Today'}
    ];

    // use effect is required because counts are loaded lazily
    useEffect(() => {
        if (!counts) return;

        const authorsCopy = [...authorsOptions];
        counts.authors.forEach(({id, slug, posts_count}) => {
            if (id === blog.user.id) return;
            authorsCopy.push({
                value: id,
                label: <FilterLabel name={slug} count={posts_count} />
            })
        })
        setAuthorsOptions(authorsCopy);

        const tagsCopy = [...tagsOptions]
        counts.tags.forEach(({id, name, posts_count}) => {
            tagsCopy.push({
                value: id,
                label:  <FilterLabel name={name} count={posts_count} />,
            })
        })
        setTagsOptions(tagsCopy);

        /* const languagesCopy = []
        counts.languages.forEach(({id, code}) => {
            languagesCopy.push({
                value: id,
                label: code
            });
        });
        setLanguageOptions(languagesCopy); */

    }, [counts]);

    function handleChange(name, v) {
        changeFilter(name, v.value);
    }
    function updateSearch(e) {
        if (filters.search !== e.target.value)
            changeFilter('search', e.target.value)
    }

    // search is only updated when blur or enterClick
    const [search, setSearch] = useState(filters.search);


    return <div className="posts-filtering">
        <div className="post-filters">
            <PostsFilter name="status" value={filters.status} options={statusOptions} onChange={handleChange} />
            <PostsFilter name="author" value={filters.author} options={authorsOptions} onChange={handleChange} />
            <PostsFilter name="tag" value={filters.tag} options={tagsOptions} onChange={handleChange} />
            <PostsFilter name="date" value={filters.date} options={dateOptions} onChange={handleChange} />
        </div>
        <div className="post-search-and-lang">
            <div className="post-search">
                <input 
                    className="input" 
                    value={search} 
                    onChange={(e) => setSearch(e.target.value)}
                    onKeyDown={(e) => e.key === 'Enter' && updateSearch(e)}
                    onBlur={updateSearch}
                    placeholder="Search..."
                ></input>
            </div>
        </div>
    </div>

}

function FilterLabel( {name, count} ) {

    return <span className="filter-label">
        <span className="name">{name}</span>
        <span className="count">{ numberFormatter(count || 0) }</span>
    </span>

}
 

function PostsFilter( { name, value, options, onChange } ) {

    const SingleValue = p => {
        const name = p.data.label.props ? p.data.label.props.name : p.data.label;
        return <components.SingleValue {...p}>
          {name}
        </components.SingleValue>
    };

    value = options.find(i => i.value === value) || options[0];

    return <div className="posts-filter">
        <div className="posts-filter-name">{name}</div>
        <Select 
            value={value} 
            type="small" 
            options={options}
            onChange={(v) => onChange(name, v)}

            defaultMenuIsOpen={false}

            

            // https://stackoverflow.com/a/52484756/9059939
            // to remove number
            components={{ SingleValue }}
        />
    </div>   
}