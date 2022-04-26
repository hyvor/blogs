<!DOCTYPE html>
<html>
<head>
    @include('landing.meta', [
        'title' => 'Hyvor Blogs - The simplest blogging platform',
        'description' => 'Hyvor Blogs is the simplest blogging platform',
        'image' => '',
        'canonical' => 'https://blogs.hyvor.com',
    ])
</head>

<body class="index">


@include('landing.nav')

<section id="hero-home">

    <div class="container">

        <div class="title-and-svg">

            <div class="hero-title">
                <h1>
                    Start Your Blog Today!
                </h1>
                <h2>
                    Hyvor Blogs is a simple but powerful platform to start a blog with a custom theme and domain.
                </h2>
                <a data-no-instant href="/console?signup=1" class="button big">
                    Start a Blog
                </a>

                {{-- <div class="hero-message-wrap">
                    <a 
                        class="hero-message"
                        href="/blog/v2"
                        target="_blank"
                    >
                        Hyvor Talk v2 is now generally available &rsaquo;&rsaquo;
                    </a>
                </div> --}}
            </div>

            <div class="hero-svg">



            </div>

        </div>
    </div>
</section>

<div class="wave">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 250"><path fill="#f1e8e8" fill-opacity="1" d="M0,128L60,138.7C120,149,240,171,360,170.7C480,171,600,149,720,149.3C840,149,960,171,1080,170.7C1200,171,1320,149,1380,138.7L1440,128L1440,0L1380,0C1320,0,1200,0,1080,0C960,0,840,0,720,0C600,0,480,0,360,0C240,0,120,0,60,0L0,0Z"></path></svg></div>

<section class="details-list container">

    <h3>Main Features</h3>

    <div class="details-table-wrap">
        <div class="details-table">
            <div class="title">
                <h4>A Powerful Console</h4>
                <div class="title-description">For managing multiple blogs and writing posts</div>
            </div>
            <div class="description">
                <p>
                    Hyvor Blogs provides a Console to manage multiple blogs at the same time. It also includes tools to publish and manage your posts, change your blog settings, edit the theme, add team members, and manage billing. Our Rich Text editor is easy-to-use, and supports all required text styling, image uploading, embedding, and link previews.
                </p>
                <div class="image-wrap">
                    <img src="/img/landing/home-console.png" />
                </div>
            </div>
        </div>

        <div class="details-table">
            <div class="title">
                <h4>Custom Themes & Marketplace</h4>
                <div class="title-description">Customize themes or build your own one</div>
            </div>
            <div class="description">
                <p>
                    Installing any theme from our marketplace just takes a few clicks. There are free and paid themes available. If you are familiar with HTML and CSS, you can even build your own theme from scratch using the in-built file editor and our theme development guides. Because we use technologies most web developers are already familiar with, you can ask any professional web developer to build a theme for you.
                </p>
                <div class="image-wrap">
                    <img src="/img/landing/home-console.png" />
                </div>
                <p class="note">
                    Note: Hyvor Blogs is not a drag-and-drop website building software.
                </p>
            </div>
        </div>

        <div class="details-table">
            <div class="title">
                <h4>Subdomain & Custom Domain</h4>
                <div class="title-description">Publishing your blog to the internet</div>
            </div>
            <div class="description">
                <p>  
                When you create a blog, you get a subdomain of <b>hyvorblogs.io</b> for your blog, so that you can your friends, family, and audience can visit your blog on the internet. Optionally, you can also connect a custom domain such as <b>myblog.com</b> or <b>blog.mycompany.com</b> in a few steps (Don't worry we have guides for that).
                </p>
                <p class="note">
                    Important: You have to buy a custom domain from a third-party domain name registar, which will involve seperate payments/subscriptions.
                </p>
            </div>
        </div>

        <div class="details-table">
            <div class="title">
                <h4>In-built SEO</h4>
                <div class="title-description">Publishing your blog to the internet</div>
            </div>
            <div class="description">
                <p>  
                    We take care of the technical SEO part of the blog, such as setting HTML title and meta tags, social media tags, canonical URLs, generating sitemaps, handling redirect of old URLs, etc.
                </p>
            </div>
        </div>

        <div class="details-table">
            <div class="title">
                <h4>Code Injecting</h4>
                <div class="title-description">For analytics, comments, and newsletter forms</div>
            </div>
            <div class="description">
                <p>
                   Want to add Analytics or other tracking code to your website? Code Injecting can be used for that. You can add custom HTML code to for the whole blog or for a single page.
                </p>
                
                <p>
                    
                </p>
            </div>
        </div>

        <div class="details-table">
            <div class="title">
                <h4>Comments & Newsletter</h4>
                <div class="title-description">Building an audience</div>
            </div>
            <div class="description">
                <p>
                   Hyvor Blogs partners with its sister product, <a class="link" href="https://talk.hyvor.com" target="_blank">Hyvor Talk</a> to provide commenting for your blog. Good news is, even Hyvor Talk is a premium product, it is free up to 100,000 pageviews/months for Hyvor Blogs customers.
                </p>
                <p>
                    Hyvor Blogs does not (yet) support newsletters natively. But, embedding a newsletter sign up form from a third-party service is easy. All Hyvor Blogs themes have a place to embed the newsletter form. So, you just have to copy and paste the embed code in the Console. 
                </p>
            </div>
        </div>
        
        <div class="details-table">
            <div class="title">
                <h4>Team Members</h4>
                <div class="title-description">Invite your colleagues</div>
            </div>
            <div class="description">
                <p>
                   You can invite team members (users) for 6 different roles.
                    <ul>
                        <li><b>Owner</b> - who created the blog, can access everything</li>
                        <li><b>Admin</b> - can access everything</li>
                        <li><b>Editor</b> - can publish and manage everyone's posts.</li>
                        <li><b>Writer</b> - can publish and manage their posts but not others'.</li>
                        <li><b>Contributors</b> - can write but not publish. An editor has to publish their posts.</li>
                        <li><b>Finance</b> - can only access billing settings.</li>
                    </ul>
                </p>
                
            </div>
        </div>

    </div>

</section>


<section class="details-list container">

    <h3>Advantages Over Competitors</h3>

    <div class="details-table-wrap">
        <div class="details-table">
            <div class="title">
                <h4>Fast</h4>
                <div class="title-description">It is hard to make a faster blog than yours!</div>
            </div>
            <div class="description">
                <p>When you update your posts, we convert
                <p>We serve your blog through our global CDN in plain HTML.</p>
                <p>
                    Let us explain that without technical jargon. We save "copies" of your blog in servers located in multiple location around the world (that is global CDN). When a user visits your blog, the server nearest to the user send the response. Simply, your blog is fast for anyone around the world.
                </p>
                <p>So, why "plain HTML"? All browsers understand HTML. Some competitors serve content in other types such as Javascript, then convert it to HTML (called rendering) in the browser. This process takes time. Plain HTML takes has the lowest processing time.</p>
                <p>
                    And, We use InstantClick.js under the hood to make the navigation between pages faster. It is the same technology we use in our landing pages (Try clicking the Pricing link above to see how fast it loads without reloading the browser).
                </p>
                <p>Altogether, these optimizations makes your blog extremely faster than competitors like WordPress.</p>
            </div>
        </div>

        <div class="details-table">
            <div class="title">
                <h4>Data Ownership & Privacy</h4>
                <div class="title-description">You own your content</div>
            </div>
            <div class="description">
                <p>
                    When using Hyvor Blogs, you own your content. It is <b>YOUR BLOG</b> that is hosted on our platform. We do not use your data for anything else than serving the blog. We never sell your data to anyone. Unlike platforms like Medium, we do not use your content to generate revenue nor hide your content behind paywalls.
                </p>
                <p>
                    You can also migrate to another platform at anytime. We support exporting content in WordPress format, making it easier to migrate to almost any platform.
                </p>
            </div>
        </div>  

        <div class="details-table">
            <div class="title">
                <h4>Security</h4>
                <div class="title-description">We will take care of secuirty</div>
            </div>
            <div class="description">
                <p>
                    Hyvor Blogs is designed using latest technologies. We use industry-standard security practices do regular security checks to make our platform safe. Unlike self-hosted platforms like WordPress, Drupal, etc. you do not have to worry about updating breaking your blog on updates - we will take care of that. You can focus on writing content.
                </p>
            </div>
        </div>

    </div>

</section>

<section class="details-list container">

    <h3>For Developers</h3>

    <div class="details-table-wrap">

        <div class="details-table">
            <div class="title">
                <h4>Theme Development</h4>
            </div>
            <div class="description">
                <p>Develop a theme, publish it to our marketplace, and earn a side income. There is only small learning curve to start building themes. We provide a "developer plan" for developers to use the platform for free forever.</p>
            </div>
        </div>
                    
        <div class="details-table">
            <div class="title">
                <h4>API</h4>
            </div>
            <div class="description">
                <p>There are 3 APIs.</p>
                <ul>
                    <li><b>Data API</b> - Get public data of the blog in JSON</li>
                    <li><b>Delivery API</b> - For self-serving the blog and hosting on a subdirectory (company.com/blog)</li>
                    <li><b>Console API</b> (Coming soon) - For console tasks, access private data, create posts, etc.</li>
                </ul>
            </div>
        </div>

        <div class="details-table">
            <div class="title">
                <h4>Webhooks</h4>
            </div>
            <div class="description">
                <p>Ping a URL when there are event. We support almost every event possible in a blog.</p>
            </div>
        </div>

        <div class="details-table">
            <div class="title">
                <h4>Host Images on S3</h4>
            </div>
            <div class="description">
                <p>Develop a theme, publish it to our marketplace, and earn a side income. There is only small learning curve to start building themes. We provide a "developer plan" for developers to use the platform for free forever.</p>
            </div>
        </div>

    </div>

</section>

<!-- <section class="details-list container">

    <h3>For Enterprises</h3>

    <div class="details-table-wrap">

        <div class="details-table">
            <div class="title">
                <h4>SAML Login</h4>
            </div>
            <div class="description">
                <p>Allow your organization members to log into the Console using SAML with your authentication provider</p>
            </div>
        </div>

        <div class="details-table">
            <div class="title">
                <h4>Custom Console</h4>
            </div>
            <div class="description">
                <p>Enterprise blogs can set up our Console on a custom domain (<b>blogconsole.company.com</b>) with a custom logo (A custom console is required for SAML Login).</p>
            </div>
        </div>
                    
    </div>

</section> -->

<section class="details-list container">

    <h3>Other</h3>

    <div class="details-table-wrap">

        <div class="details-table">
            <div class="title">
                <h4>Death Insurance</h4>
                <div class="title-description">Don't let your blog die with you</div>
            </div>
            <div class="description">
                <p>If you are a paid customer, we will host your blog forever after your death. Once your family/freinds notifies us, we will archive your blog, cancel your subscription, and host your blog for free forever. And, we are a bootstrapped company who focuses on the product and customers than our growth. Therefore, it is very likely Hyvor Blogs will be there until the end of the internet, so is your blog.</p>
                <p class="note">Note: We are also happy to host any deceased person's personal blog on our platform for a one-time charge. Contact us for more information.</p>
            </div>
        </div>

        <div class="details-table">
            <div class="title">
                <h4>Support</h4>
                <div class="title-description">Get Human Support</div>
            </div>
            <div class="description">
                <p>Everyone can get support at our forum from community members and the Hyvor Blogs team. Or, contact us at <b>blogs.support@hyvor.com</b>. Live chat support is available for Team and Enteprise customers.</p>
            </div>
        </div>
                    
    </div>

</section>

<section class="faq container">

    <h3>FAQ</h3>

    <div class="details-table-wrap">

        <div class="faq">
            <h5>Should I choose Hyvor Blogs?</h5>
            </div>
            <p>
                Our targetted audiences are personal bloggers and business blogs. If you like to create a blog that you own and that you can customize as you want, Hyvor Blogs would be a good solution.
            </p>
        </div>

        <div class="faq">
            <h5>Will there be a subscriber/member login feature?</h5>
            </div>
            <p>
                One our main goals is to make the blog fast by making it "static". Login is a dynamic feature. We will not support any dynamic features except search. So, the answer is no. However, you can use platforms like Memberful, Memberstack or Memberspace to set up login and protected content pages for your blog. We may create direct integrations with one of these platforms in the future, but there will not be a native subscriber/member login feature.
            </p>
        </div>

        <div class="faq">
            <h5>Can I see usage/analytics of my blog (ex: Total Visitors)?</h5>
            </div>
            <p>
                Not natively. Because of how Hyvor Blogs works, most requests never even reach our servers - only our global CDN.  And, we do not place any tracking code on your blog. Therefore, we do not have a way to track pageviews internally. However, you can easily integrate a third-party analytics system to track usage.
            </p>
        </div>

        <div class="faq">
            <h5>Is there a trial?</h5>
            </div>
            <p>
                Yes, we provide a 30-days trial all features included. See our <a href="/pricing" class="link">Pricing</a> page for more details. You can also test Hyvor Blogs without signing up.
            </p>
        </div>
        
    </div>

</section>


<section id="spam" class="container">

    <div>
        <h3>Spam Policy</h3>

        <p>
            We do not allow blogs that are used for spamming or black hat SEO techniques. We do not also allow hosting machine-content generated. We have the right to ban such blogs. See <a target="_blank" class="link" rel="nofollow" href="/docs/terms">Terms</a> for more details.
        </p>

        <p>
            Other than that, we allow pretty much everything. It is your duty to make sure you follow copyright and other laws.
        </p>

    </div>

</section>

@include('landing.footer')

</body>
</html>