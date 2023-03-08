import React, { ReactNode, useEffect, useState } from 'react';
import Select from '../ReusableComponents/Select';
import { components } from 'react-select';
import { useValues } from 'kea';
import postsLogic from '../logic/postsLogic';
import userBlogsLogic from '../logic/userBlogsLogic';
import numberFormatter from '../../helpers/numberFormatter';
import { Calendar } from 'react-bootstrap-icons';
import dayjs from 'dayjs';
import ReactDatePicker from 'react-datepicker';
import { Filters, Tag, User } from "../types";
import getSubdomain from "../logic-helpers/subdomain";
import usersLogic from "../logic/usersLogic";
import tagsLogic from "../logic/tagsLogic";

interface PostsFiltersProps {
    filters: Filters,
    changeFilter: Function
}

interface SelectOption {
    value: any,
    label: ReactNode
}

export default function PostsFilters({ filters, changeFilter }: PostsFiltersProps) {

    const subdomain = getSubdomain()
    const { counts } = useValues(postsLogic({ subdomain }))
    const { findBlogBySubdomain } = useValues(userBlogsLogic)
    const { users } = useValues(usersLogic({ subdomain }))
    const { tags } = useValues(tagsLogic({ subdomain }))

    const blog = findBlogBySubdomain(subdomain)

    const statusOptions = [
        { value: 'all', label: <FilterLabel name="All" count={blog.blog.posts_count} /> },
        { value: 'published', label: <FilterLabel name="Published" count={counts?.published || 0} /> },
        { value: 'draft', label: <FilterLabel name="Draft" count={counts?.draft || 0} /> },
        { value: 'scheduled', label: <FilterLabel name="Scheduled" count={counts?.scheduled || 0} /> },
        { value: 'featured', label: <FilterLabel name="Featured" count={counts?.featured || 0} /> },
    ]

    const defaultAuthorOptions = [
        { value: 'all', label: <FilterLabel name="All" count={blog.blog.posts_count} /> },
        { value: blog.user.id, label: <FilterLabel name="You" count={blog.user.posts_count} /> },
    ] as SelectOption[];
    const [authorsOptions, setAuthorsOptions] = useState(defaultAuthorOptions);

    const defaultTagsOptions = [
        { value: 'all', label: <FilterLabel name="All" count={blog.blog.posts_count} /> },
    ] as SelectOption[];
    const [tagsOptions, setTagsOptions] = useState(defaultTagsOptions);

    const dateOptions = [
        { value: 'all', label: 'All' },
        { value: 'today', label: 'Today' },
        { value: 'last-week', label: 'Last Week' },
        { value: 'last-month', label: 'Last Month' },
        { value: 'last-year', label: 'Last Year' },
        { value: 'custom', label: 'Custom' },
    ];
    const [currentDateOption, setCurrentDateOption] = useState('all');

    useEffect(() => {

        const authorsCopy = [...defaultAuthorOptions];
        Object.values(users).forEach((user: User) => {
            if (user.id === blog.user.id) return;

            authorsCopy.push({
                value: user.id,
                label: <FilterLabel name={user.variants[0]?.name || 'Anonymous'} count={user.posts_count} />
            })

        })
        setAuthorsOptions(authorsCopy);

        const tagsCopy = [...defaultTagsOptions]
        Object.values(tags).forEach((tag: Tag) => {
            tagsCopy.push({
                value: tag.id,
                label: <FilterLabel name={tag.variants[0]?.name || 'Anonymous'} count={tag.posts_count} />
            })
        })
        setTagsOptions(tagsCopy);

    }, [users, tags]);

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


    const searchTimeout = React.useRef<null | ReturnType<typeof setTimeout>>(null);

    // search is only updated when blur or enterClick
    const [search, setSearch] = useState(filters.search);

    function updateSearchValue(s: string) {
        setSearch(s);

        if (searchTimeout.current) {
            clearTimeout(searchTimeout.current);
        }

        searchTimeout.current = setTimeout(() => {
            updateSearch(s);
        }, 200);
    }
    function updateSearch(s: string) {
        if (filters.search !== s)
            changeFilter('search', s)
    }

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
                onChange={e => updateSearchValue(e.target.value)}
                onKeyDown={e => e.key === 'Enter' && updateSearch((e.target as HTMLInputElement).value)}
                onBlur={e => updateSearch(e.target.value)}
                placeholder="Search..."
            />
        </div>
    </div>

}

function FilterLabel({ name, count }: { name: string, count: number }) {

    return <span className="filter-label">
        <span className="name">{name}</span>
        <span className="count">{numberFormatter(count)}</span>
    </span>

}


interface PostsFilterProps {
    name: string,
    value: string | number | null,
    options: {
        value: string | number;
        label: string | ReactNode
    }[],
    onChange: Function, //(name: string, value: string | number) => {}
}

function PostsFilter({ name, value, options, onChange }: PostsFilterProps) {

    const SingleValue = (p: any) => {
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
                onChange={v => onChange(name, v as SelectOption)}

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

function CustomDate({ onChange }: { onChange: Function }) {

    const [isOpened, setIsOpened] = useState(true);

    const [startDate, setStartDate] = useState(dayjs().subtract(7, 'day').toDate());
    const [endDate, setEndDate] = useState(dayjs().toDate());

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