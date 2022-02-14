/**
 * Some helpers for blog plan
 * 
 * blog = array item in blogsLogic (which has both blog and user)
 */

export function isBlogInTeamPlan(blog) {
    return blog.blog.is_on_trial ||
        (
            blog.blog.subscribed && 
            ['team', 'enterprise'].indexOf(blog.blog.subscription.plan) >= 0
        )
}
export function isBlogInProPlan(blog) {
    return blog.blog.is_on_trial ||
        (
            blog.blog.subscribed && 
            ['pro', 'team', 'enterprise'].indexOf(blog.blog.subscription.plan) >= 0
        )
}