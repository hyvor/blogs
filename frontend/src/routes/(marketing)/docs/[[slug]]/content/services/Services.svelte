<script lang="ts">
    import { Callout, TabNav, TabNavItem, Table, TableRow, Link } from "@hyvor/design/components";
	import { IconBoxArrowUpRight } from '@hyvor/icons';
    let active = $state('comments');
</script>
<h1>Services</h1>

<TabNav bind:active={active}>
    <TabNavItem name="comments">
        Comments
    </TabNavItem>

    <TabNavItem name="newsletter">
        Newsletter
    </TabNavItem>

    <TabNavItem name="analytics">
        Analytics
    </TabNavItem>

    <TabNavItem name="memberships">
        Memberships
    </TabNavItem>

    <TabNavItem name="forms">
        Forms
    </TabNavItem>

</TabNav>

{#if active === 'comments'}
    <div>
        <p>Adding a commenting system to your blog means your readers have a way to share their opinions and participate in conversations in the blog. It can increase the authority of your blog.</p>
        <p><a href="https://talk.hyvor.com">Hyvor Talk</a> is our own commenting platform. Hyvor Blogs integrates with Hyvor Talk directly to allow comments on your blog. You can use Hyvor Talk for <b>FREE</b> starting from <a href="/pricing">Growth Plan</a>.</p>
        
       <!--  <Callout type="info">
            <ul>
                <li>Connect your blog to Hyvor Talk, It's easy and fast</li>
                <li>Use the same HYVOR account (for the owner)</li>
                <li>Completely free</li>
                <li>Fast, secure, and privacy-focused</li>
                </ul>
        </Callout> -->
        <p>Comments Settings: <b>Console → Integrations → Hyvor Talk</b>.</p>

        <h2>Adding Comments</h2>
            <p>To add comments to your blog, you need to add the code given by the commenting system in the "Comments Embed Code" field. This will add the comments to the bottom of each post. You can find the code in the commenting system's dashboard.</p>
            <p>Here are some popular commenting systems:</p>
            <ul>
                <li><a href="https://talk.hyvor.com" target="_blank" rel="nofollow">Hyvor Talk</a></li>
                <li><a href="https://disqus.com" target="_blank" rel="nofollow">Disqus</a></li>
                <li><a href="https://commento.io" target="_blank" rel="nofollow">Commento</a></li>
                <li><a href="https://getreplybox.com/" rel="nofollow">GetReplyBox</a></li>
            </ul>
            <p>You can also customize the theme to add the embed code manually.</p>
    </div>

{:else if active === 'newsletter'}
    <div>
        <p>A newsletter is a great way to build an audience and share valuable information.</p>

        <h2>Adding</h2>
        <p>To add an email signup form to your blog,</p>
            <ul>
                <li>First, signup for a <a href="/docs/newsletter#services">newsletter service</a>.</li>
                <li>Copy the given HTML code.</li>
                <li>Paste it in <b>Console → Settings → Comments & Newsletter → Newsletter Signup Form Code</b>.</li>
            </ul>

        <h2>Positioning</h2>
        <p>
            All Hyvor Blogs <a href="/themes">themes</a> are designed to have a place for the newsletter signup form. If you want to change this position, you will need to <a href="/docs/theme#editing">edit your theme</a>, and change the position of the newsletter <a href="/docs/themes-templates#placeholders">placeholder</a>. In your template files, find and cut this code <code>{`{{ _newsletter | template }}`}</code>, and paste it in the place you need the signup form to be.
        </p>

        <h2>Newsletter Services</h2>

        <Table columns= "1fr 1fr" hover>
            <TableRow head>
                <div>Service</div>
                <div>Integration Tutorial</div>
            </TableRow>

            <TableRow>
                <div><a href="https://convertkit.com/" rel="nofollow" target="_blank">ConvertKit</a></div>
                <div><Link href="https://hyvor.com/blog/add-convertkit" target="_blank">{#snippet end()}
                                                        <IconBoxArrowUpRight  />
                                                    {/snippet}View</Link></div>
            </TableRow>
            <TableRow>
                <div><a href="https://mailchimp.com/en-gb/" rel="nofollow" target="_blank">MailChimp</a></div>
                <div><Link href="https://hyvor.com/blog/how-to-add-mailchimp" target="_blank">{#snippet end()}
                                                        <IconBoxArrowUpRight  />
                                                    {/snippet}View</Link></div>
            </TableRow>
            <TableRow>
                <div><a href="https://emailoctopus.com/" rel="nofollow" target="_blank">EmailOctopus</a></div>
                <!-- <div><Link href="https://hyvor.com/blog/how-to-add-mailchimp"><IconBoxArrowInUpRight slot="end" />View</Link></div> -->
            </TableRow>
            <TableRow><div><a href="https://moosend.com/" rel="nofollow" target="_blank">Mossend</a></div></TableRow>
            <TableRow><div><a href="https://www.mailerlite.com/" rel="nofollow" target="_blank">MailerLite</a></div></TableRow>

        </Table>
           
            <p>We are not affiliated with any of these services. There are plenty of other newsletter services not mentioned here.</p>
    </div>

{:else if active === 'analytics'}
    <div>
        <p>It is cool to know some metrics about how much traffic your blog gets. You can easily integrate analytic services to the blog using custom code.</p>

        <h2>How to Add</h2>
            <ul>
                <li>First, signup for an <a href="/docs/analytics#services">analytic service</a>.</li>
                <li>Copy the given HTML code.</li>
                <li>Paste it in <b>Console → Settings → Custom Code → Foot Code</b>.</li>
            </ul>

        <h2>Analytics Services</h2>
        <Table columns= "1fr 1fr" hover>
            <TableRow head>
                <div>Service</div>
                <div>Blog Tutorial</div>
            </TableRow>

            <TableRow>
                <div><a href="https://analytics.google.com/analytics" rel="nofollow" target="_blank">Google Analytics</a></div>
                <div><Link href="https://hyvor.com/blog/add-google-analytics-to-your-blog" target="_blank">{#snippet end()}
                                                                <IconBoxArrowUpRight  />
                                                            {/snippet}View</Link></div>
            </TableRow>
            <TableRow>
                <div><a href="https://www.cloudflare.com/analytics" rel="nofollow" target="_blank">Cloudflare Analytics</a></div>
            </TableRow>
            <TableRow>
                <div><a href="https://matomo.org/" rel="nofollow" target="_blank">Matomo</a></div>
            </TableRow>
            <TableRow>
                <div><a href="https://usefathom.com/" rel="nofollow" target="_blank">Fathom</a></div>
            </TableRow>
            <TableRow><div><a href="https://plausible.io/" rel="nofollow" target="_blank">Plausible Analytics</a></div></TableRow>
            <TableRow><div><a href="https://simpleanalytics.io/" rel="nofollow" target="_blank">Simple Analytics</a></div></TableRow>
            <TableRow><div><a href="https://posthog.com/" rel="nofollow" target="_blank">Posthog</a></div></TableRow>
        </Table>

        <p>We are not affiliated with any of these services. There are plenty of other analytics tools not mentioned here.</p>

        <h2>Privacy</h2>
        <p>Some of those platforms track <b>users</b> while others only track <b>visits</b>. Before tracking users, you will need the user's consent (Using a <b>cookie banner</b>). Please consult the documentations of each service to learn more about how they handle personal data.</p>
    </div>

{:else if active === 'memberships'}
    <div>
        <p>Adding memberships (login, signup, subscriptions, etc.) is an easy way to allow your readers to log in to your blog, as well as for you to provide gated content and earn a subscription revenue. Because these feature are dynamic, Hyvor Blogs does not and will not support memberships natively, as we thrive to make your blog static.</p>

        <h2>Membership Platforms</h2>

            <Table columns="1fr 1fr" hover>
                <TableRow head>
                    <div>Service</div>
                    <div>Blog Tutorial</div>
                </TableRow>

                <TableRow>
                    <div><a href="https://memberstack.com/" rel="nofollow" target="_blank">Memberstack</a></div>
                    <div><Link href="https://hyvor.com/blog/add-memberstack" target="_blank">{#snippet end()}
                                                                        <IconBoxArrowUpRight  />
                                                                    {/snippet}View</Link></div>
                </TableRow>

                <TableRow>
                    <div><a href="https://www.memberspace.com/" rel="nofollow" target="_blank">Memberspace</a></div>
                    <!-- <div><Link href="https://hyvor.com/blog/add-memberspace"><IconBoxArrowInUpRight slot="end" />View</Link></div> -->
                </TableRow>
            </Table>

            <Callout type="info"><p>Note that integrating these platforms, in most cases, requires you to write Javascript code. If you just want to connect with your users, embedding a <a href="https://blogs.hyvor.com/docs/newsletter">newsletter</a> signup form would be a better and easy option.</p></Callout>

    </div>

{:else}
    <div>
        <p>You may want to collect data from your visitors. Embedding a forms service is the easier way to do that.</p>

        <h2>How to Embed Forms</h2>
            <ul>
                <li>First, signup for a <a href="/docs/forms#forms">forms services</a>. They allow you to create custom forms as you like</li>
                <li>Then, add the form embed code to your blog:</li>
                    <ul>
                        <li>To add a form to a post or page, use <a href="/docs/writing#custom-html">custom HTML/Twig blocks</a>.</li>
                        <li>To add a form to a specific place in the blog, you will need to <a href="/docs/theme#editing">edit the theme</a>.</li>
                    </ul>
            </ul>

        <h2>Forms Services</h2>

            <Table columns="1fr 1fr">
                <TableRow head>
                    <div>Service</div>
                    <div>Blog Tutorial</div>
                </TableRow>

                <TableRow>
                    <div><a href="https://www.google.com/forms" rel="nofollow" target="_blank">Google Forms</a></div>
                    <div><Link href="https://hyvor.com/blog/add-google-forms" target="_blank">{#snippet end()}
                                                                        <IconBoxArrowUpRight  />
                                                                    {/snippet}View</Link></div>
                </TableRow>

                <TableRow>
                    <div><a href="https://forms.office.com/" rel="nofollow" target="_blank">Microsoft Forms</a></div>
                    <div><Link href="https://hyvor.com/blog/add-microsoft-forms" target="_blank">{#snippet end()}
                                                                        <IconBoxArrowUpRight  />
                                                                    {/snippet}View</Link></div>
                </TableRow>

                <TableRow>
                    <div><a href="https://www.typeform.com/" rel="nofollow" target="_blank">Typeform</a></div>
                    <div><Link href="https://hyvor.com/blog/add-microsoft-forms" target="_blank">{#snippet end()}
                                                                        <IconBoxArrowUpRight  />
                                                                    {/snippet}View</Link></div>
                </TableRow>

                <TableRow>
                    <div><a href="https://www.wufoo.com/" rel="nofollow" target="_blank">Wufoo</a></div>
                    <div><Link href="https://hyvor.com/blog/add-wufoo" target="_blank">{#snippet end()}
                                                                        <IconBoxArrowUpRight  />
                                                                    {/snippet}View</Link></div>
                </TableRow>

                <TableRow>
                    <div><a href="https://www.jotform.com/" rel="nofollow" target="_blank">JotForm</a></div>
                </TableRow>

                <TableRow>
                    <div><a href="https://www.formsite.com/" rel="nofollow" target="_blank">Formsite</a></div>
                </TableRow>

                <TableRow>
                    <div><a href="https://paperform.co/" rel="nofollow" target="_blank">Paperform</a></div>
                </TableRow>

                <TableRow>
                    <div><a href="https://www.zoho.com/forms/" rel="nofollow" target="_blank">Zoho Forms</a></div>
                </TableRow>
            </Table>

            <p>We are not affiliated with any of these services. There are plenty of other forms services not mentioned here.</p>
    </div>
{/if}




