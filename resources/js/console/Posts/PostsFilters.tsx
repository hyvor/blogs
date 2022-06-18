// @ts-nocheck
// TODO: FIXXXXXX

import React, {ReactNode, useEffect, useState} from 'react';
import Select from '../ReusableComponents/Select';
import {components, GroupBase, SingleValueProps} from 'react-select';
import { useValues } from 'kea';
import postsLogic from '../logic/postsLogic';
import subdomainLogic from '../logic/subdomainLogic';
import blogsLogic from '../logic/blogsLogic';
import numberFormatter from '../../helpers/numberFormatter';
import languagesLogic from '../logic/languagesLogic';
import { Calendar } from 'react-bootstrap-icons';
import dayjs from 'dayjs';
import onOutsideClick from '../../helpers/onOutsideClick';
import ReactDatePicker from 'react-datepicker';
import {UserBlog} from "../objects/userblog";
import {Filters} from "../types";
import getSubdomain from "../logic-helpers/subdomain";

interface PostsFiltersProps {
    filters: Filters,
    changeFilter: Function
}

interface SelectOption {
    value: any,
    label: ReactNode
}

export default function PostsFilters({ filters, changeFilter } : PostsFiltersProps) {

    const subdomain = getSubdomain()
    const { counts } = useValues(postsLogic({subdomain}))
    const { findBlogBySubdomain } = useValues(blogsLogic)

    const blog: UserBlog = findBlogBySubdomain(subdomain)

    const statusOptions = [
        { value: 'all', label: <FilterLabel name="All" count={blog.blog.posts_count} /> },
        { value: 'published', label: <FilterLabel name="Published" count={counts && counts.status.published} />},
        { value: 'draft', label: <FilterLabel name="Draft" count={counts && counts.status.draft} />},
        { value: 'scheduled', label: <FilterLabel name="Scheduled" count={counts && counts.status.scheduled} />},
        { value: 'featured', label: <FilterLabel name="Featured" count={counts && counts.status.featured} />},
    ]

    const [authorsOptions, setAuthorsOptions] = useState<Array<SelectOption>>([
        { value: 'all', label: <FilterLabel name="All" count={blog.blog.posts_count} /> },
        { value: 'you', label: <FilterLabel name="You" count={blog.user.posts_count} /> },
    ])
    const [tagsOptions, setTagsOptions] = useState<Array<SelectOption>>([
        { value: 'all', label: <FilterLabel name="All" count={blog.blog.posts_count} /> },
    ]);

    const dateOptions = [
        { value: 'all', label: 'All' },
        { value: 'today', label: 'Today' },
        { value: 'last-week', label: 'Last Week' },
        { value: 'last-month', label: 'Last Month' },
        { value: 'last-year', label: 'Last Year' },
        { value: 'custom', label: 'Custom' },
    ];
    const [currentDateOption, setCurrentDateOption] = useState('all');

    // use effect is required because counts are loaded lazily
    useEffect(() => {
        if (!counts) return;

        const authorsCopy = [...authorsOptions];
        counts.authors.forEach(({id, name, posts_count}) => {
            if (id === blog.user.id) return;
            authorsCopy.push({
                value: id,
                label: <FilterLabel name={name} count={posts_count} />
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

    }, [counts]);

    function handleChange(name: string, v: SelectOption) {
        changeFilter(name, v.value);
    }
    function handleDateChange(start: any, end: any) {
        if (start === 'date') {
            setCurrentDateOption(end.value);
            const text = end.value; // something like last-7

            start = dayjs().subtract(7, 'day')
            end = dayjs()
            if (text === 'today') {
                start = dayjs().startOf('day')
                end = dayjs();
            } else if (text === 'last-week') {
                end = dayjs().startOf('week');
                start = end.subtract(7, 'day');
            } else if (text === 'last-month') {
                end = dayjs().startOf('month');
                start = end.subtract(1, 'month');
            } else if (text === 'last-year') {
                end = dayjs().startOf('year');
                start = end.subtract(1, 'year')
            } else if (text === 'all') {
                start = null
                end = null
            }
        }

        changeFilter({
            startDate: start,
            endDate: end
        });
    }
    function updateSearch(e: any) {
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
            <PostsFilter name="date" value={currentDateOption} options={dateOptions} onChange={handleDateChange} />
        </div>
        <div className="post-search">
            <input
                className="input"
                value={search}
                onChange={(e) => setSearch(e.target.value)}
                onKeyDown={(e) => e.key === 'Enter' && updateSearch(e)}
                onBlur={updateSearch}
                placeholder="Search..."
            />
        </div>
    </div>

}

function FilterLabel( {name, count} : {name: string, count: number} ) {

    return <span className="filter-label">
        <span className="name">{name}</span>
        <span className="count">{ numberFormatter(count) }</span>
    </span>

}


interface PostsFilterProps {
    name: string,
    value: string | number,
    options: Array<{
        value: string | number;
        label: string | ReactNode
    }>,
    onChange: Function, //(name: string, value: string | number) => {}
}

function PostsFilter( { name, value, options, onChange } : PostsFilterProps ) {

    const SingleValue = (p : any) => {
        const name = p.data.label.props ? p.data.label.props.name : p.data.label;
        return <components.SingleValue {...p}>
          {name}
        </components.SingleValue>
    };

    const valueCalculated = options.find(i => i.value === value) || options[0];

    return <div className="posts-filter">
        <div className="posts-filter-name">{name}</div>
        <div className="posts-filter-select-wrap">

            <Select 
                value={valueCalculated}
                type="small"
                options={options}
                onChange={(v: SelectOption) => onChange(name, v)}

                // for testing
                defaultMenuIsOpen={false}

                

                // https://stackoverflow.com/a/52484756/9059939
                // to remove number
                components={{ SingleValue }}
            />

            {
                name === 'date' && value === 'custom' ?
                <CustomDate onChange={onChange} />
                : null
            }

        </div>
    </div>   
}

function CustomDate({ onChange }: {onChange: Function}) {

    const [ isOpened, setIsOpened ] = useState(true);

    const [ startDate, setStartDate ] = useState(dayjs().subtract(7, 'day').toDate());
    const [ endDate, setEndDate ] = useState(dayjs().toDate());

    function handleChange([start, end]: [Date, Date]) {
        setStartDate(start)
        setEndDate(end)

        if (end) {
            onChange(start, end)
            setIsOpened(false)
        }
    }

    return <span className="custom-date">
        <span className="custom-date-icon" onClick={() => setIsOpened(!isOpened)}>
            <Calendar />
        </span>
        {
            isOpened ?
            <ReactDatePicker
                selected={startDate}
                startDate={startDate}
                endDate={endDate}
                onChange={handleChange}
                maxDate={dayjs().toDate()}
                selectsRange
                inline 
                disabledKeyboardNavigation
            /> : null
        }
    </span>

}