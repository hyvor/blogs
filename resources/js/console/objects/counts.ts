
interface AuthorCounts {
    id: number;
    name: string;
    posts_count: number;
}
interface TagCounts {
    id: number;
    name: string;
    posts_count: number;
}

interface PostCounts {

    status: {
        published: number,
        draft: number,
        scheduled: number,
        featured: number
    },

    authors: Array<AuthorCounts>,
    tags: Array<TagCounts>

}