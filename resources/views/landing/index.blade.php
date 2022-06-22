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
                    Hyvor Blogs is a simple but powerful platform to start a blog that you own.
                </h2>
                <a data-flashload-skip href="/console?signup=1" class="button big">
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
                <img src="/img/landing/home-main.svg" alt="Cover image" />
            </div>

        </div>
    </div>
</section>

<div class="wave">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 250"><path fill="#fffaf8" fill-opacity="1" d="M0,128L60,138.7C120,149,240,171,360,170.7C480,171,600,149,720,149.3C840,149,960,171,1080,170.7C1200,171,1320,149,1380,138.7L1440,128L1440,0L1380,0C1320,0,1200,0,1080,0C960,0,840,0,720,0C600,0,480,0,360,0C240,0,120,0,60,0L0,0Z"></path></svg></div>

<section class="details-list container">

    <h3>Main Features</h3>

    <div class="details-table-wrap">
        <div class="details-table">
            <div class="description">
                <h4><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-window" viewBox="0 0 16 16">
                        <path d="M2.5 4a.5.5 0 1 0 0-1 .5.5 0 0 0 0 1zm2-.5a.5.5 0 1 1-1 0 .5.5 0 0 1 1 0zm1 .5a.5.5 0 1 0 0-1 .5.5 0 0 0 0 1z"/>
                        <path d="M2 1a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V3a2 2 0 0 0-2-2H2zm13 2v2H1V3a1 1 0 0 1 1-1h12a1 1 0 0 1 1 1zM2 14a1 1 0 0 1-1-1V6h14v7a1 1 0 0 1-1 1H2z"/>
                    </svg> Powerful Console</h4>
                <p>
                    Hyvor Blogs provides a Console to manage multiple blogs at the same time. It also includes tools to publish and manage your posts, change your blog settings, edit the theme, add team members, and manage billing. Our Rich Text editor is easy-to-use, and supports all required text styling, image uploading, embedding, and link previews.
                </p>
                <div class="image-wrap">
                    <img src="/img/landing/home-console.png" />
                </div>
            </div>
            <div class="links">
                <a href="/docs/writing">Writing & Publishing</a>
                <a href="/docs/posts-pages">Posts & Pages</a>
            </div>
        </div>

        <div class="details-table">
            <div class="description">
                <h4><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-palette" viewBox="0 0 16 16">
                        <path d="M8 5a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3zm4 3a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3zM5.5 7a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0zm.5 6a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3z"/>
                        <path d="M16 8c0 3.15-1.866 2.585-3.567 2.07C11.42 9.763 10.465 9.473 10 10c-.603.683-.475 1.819-.351 2.92C9.826 14.495 9.996 16 8 16a8 8 0 1 1 8-8zm-8 7c.611 0 .654-.171.655-.176.078-.146.124-.464.07-1.119-.014-.168-.037-.37-.061-.591-.052-.464-.112-1.005-.118-1.462-.01-.707.083-1.61.704-2.314.369-.417.845-.578 1.272-.618.404-.038.812.026 1.16.104.343.077.702.186 1.025.284l.028.008c.346.105.658.199.953.266.653.148.904.083.991.024C14.717 9.38 15 9.161 15 8a7 7 0 1 0-7 7z"/>
                    </svg> Custom Themes</h4>
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
            <div class="links">
                <a href="/themes" class="themes-preview">Themes</a>
                <a href="/docs/theme">Theme</a>
                <a href="/docs/themes-overview">Themes Development</a>
            </div>
        </div>

        <div class="details-table">
            <div class="description">
                <h4><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-globe" viewBox="0 0 16 16">
                        <path d="M0 8a8 8 0 1 1 16 0A8 8 0 0 1 0 8zm7.5-6.923c-.67.204-1.335.82-1.887 1.855A7.97 7.97 0 0 0 5.145 4H7.5V1.077zM4.09 4a9.267 9.267 0 0 1 .64-1.539 6.7 6.7 0 0 1 .597-.933A7.025 7.025 0 0 0 2.255 4H4.09zm-.582 3.5c.03-.877.138-1.718.312-2.5H1.674a6.958 6.958 0 0 0-.656 2.5h2.49zM4.847 5a12.5 12.5 0 0 0-.338 2.5H7.5V5H4.847zM8.5 5v2.5h2.99a12.495 12.495 0 0 0-.337-2.5H8.5zM4.51 8.5a12.5 12.5 0 0 0 .337 2.5H7.5V8.5H4.51zm3.99 0V11h2.653c.187-.765.306-1.608.338-2.5H8.5zM5.145 12c.138.386.295.744.468 1.068.552 1.035 1.218 1.65 1.887 1.855V12H5.145zm.182 2.472a6.696 6.696 0 0 1-.597-.933A9.268 9.268 0 0 1 4.09 12H2.255a7.024 7.024 0 0 0 3.072 2.472zM3.82 11a13.652 13.652 0 0 1-.312-2.5h-2.49c.062.89.291 1.733.656 2.5H3.82zm6.853 3.472A7.024 7.024 0 0 0 13.745 12H11.91a9.27 9.27 0 0 1-.64 1.539 6.688 6.688 0 0 1-.597.933zM8.5 12v2.923c.67-.204 1.335-.82 1.887-1.855.173-.324.33-.682.468-1.068H8.5zm3.68-1h2.146c.365-.767.594-1.61.656-2.5h-2.49a13.65 13.65 0 0 1-.312 2.5zm2.802-3.5a6.959 6.959 0 0 0-.656-2.5H12.18c.174.782.282 1.623.312 2.5h2.49zM11.27 2.461c.247.464.462.98.64 1.539h1.835a7.024 7.024 0 0 0-3.072-2.472c.218.284.418.598.597.933zM10.855 4a7.966 7.966 0 0 0-.468-1.068C9.835 1.897 9.17 1.282 8.5 1.077V4h2.355z"/>
                    </svg> Host Anywhere</h4>
                <p>  
                When you create a blog, you get a subdomain of <b>hyvorblogs.io</b> for your blog, so that you can your friends, family, and audience can visit your blog on the internet. Optionally, you can also connect a custom domain such as <b>myblog.com</b> or <b>blog.mycompany.com</b> in a few steps (Don't worry we have guides for that).
                </p>
                <p class="note">
                    Important: You have to buy a custom domain from a third-party domain name registar, which will involve seperate payments/subscriptions.
                </p>
            </div>
            <div class="links">
                <a href="/docs/custom-domain">Custom Domain</a>
            </div>
        </div>

        <div class="details-table">
            <div class="description">
                <h4><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-search-heart" viewBox="0 0 16 16">
                        <path d="M6.5 4.482c1.664-1.673 5.825 1.254 0 5.018-5.825-3.764-1.664-6.69 0-5.018Z"/>
                        <path d="M13 6.5a6.471 6.471 0 0 1-1.258 3.844c.04.03.078.062.115.098l3.85 3.85a1 1 0 0 1-1.414 1.415l-3.85-3.85a1.007 1.007 0 0 1-.1-.115h.002A6.5 6.5 0 1 1 13 6.5ZM6.5 12a5.5 5.5 0 1 0 0-11 5.5 5.5 0 0 0 0 11Z"/>
                    </svg> In-built SEO</h4>
                <p>
                    We take care of the technical SEO part of the blog, such as setting HTML title and meta tags, social media tags, canonical URLs, generating sitemaps, handling redirect of old URLs, etc.
                </p>
            </div>
            <div class="links">
                <a href="/docs/seo">SEO</a>
            </div>
        </div>

        <div class="details-table">
            <div class="description">
                <h4><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-code" viewBox="0 0 16 16">
                        <path d="M5.854 4.854a.5.5 0 1 0-.708-.708l-3.5 3.5a.5.5 0 0 0 0 .708l3.5 3.5a.5.5 0 0 0 .708-.708L2.707 8l3.147-3.146zm4.292 0a.5.5 0 0 1 .708-.708l3.5 3.5a.5.5 0 0 1 0 .708l-3.5 3.5a.5.5 0 0 1-.708-.708L13.293 8l-3.147-3.146z"/>
                    </svg> Custom Code</h4>
                <p>
                   Want to add Analytics or other tracking code to your website? Code Injecting can be used for that. You can add custom HTML code to for the whole blog or for a single page.
                </p>
                
                <p>
                    
                </p>
            </div>
            <div class="links">
                <a href="/docs/custom-code">Custom Code</a>
                <br>
                <a href="/docs/comments">Comments</a>
                <a href="/docs/newsletter">Newsletter</a>
                <a href="/docs/analytics">Analytics</a>
                <a href="/docs/memberships">Memberships</a>
                <a href="/docs/forms">Forms</a>
            </div>
        </div>

        <div class="details-table">
            <div class="description">
                <h4><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-translate" viewBox="0 0 16 16">
                        <path d="M4.545 6.714 4.11 8H3l1.862-5h1.284L8 8H6.833l-.435-1.286H4.545zm1.634-.736L5.5 3.956h-.049l-.679 2.022H6.18z"/>
                        <path d="M0 2a2 2 0 0 1 2-2h7a2 2 0 0 1 2 2v3h3a2 2 0 0 1 2 2v7a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2v-3H2a2 2 0 0 1-2-2V2zm2-1a1 1 0 0 0-1 1v7a1 1 0 0 0 1 1h7a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1H2zm7.138 9.995c.193.301.402.583.63.846-.748.575-1.673 1.001-2.768 1.292.178.217.451.635.555.867 1.125-.359 2.08-.844 2.886-1.494.777.665 1.739 1.165 2.93 1.472.133-.254.414-.673.629-.89-1.125-.253-2.057-.694-2.82-1.284.681-.747 1.222-1.651 1.621-2.757H14V8h-3v1.047h.765c-.318.844-.74 1.546-1.272 2.13a6.066 6.066 0 0 1-.415-.492 1.988 1.988 0 0 1-.94.31z"/>
                    </svg> Multi-Language</h4>
                <p>
                   There is no other blogging platform that makes managing a multi-language blog easier than Hyvor Blogs. Period.
                </p>
            </div>
            <div class="links">
                <a href="/docs/languages">Languages</a>
            </div>
        </div>
        
        <div class="details-table">
            <div class="description">
                <h4><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-people" viewBox="0 0 16 16">
                        <path d="M15 14s1 0 1-1-1-4-5-4-5 3-5 4 1 1 1 1h8zm-7.978-1A.261.261 0 0 1 7 12.996c.001-.264.167-1.03.76-1.72C8.312 10.629 9.282 10 11 10c1.717 0 2.687.63 3.24 1.276.593.69.758 1.457.76 1.72l-.008.002a.274.274 0 0 1-.014.002H7.022zM11 7a2 2 0 1 0 0-4 2 2 0 0 0 0 4zm3-2a3 3 0 1 1-6 0 3 3 0 0 1 6 0zM6.936 9.28a5.88 5.88 0 0 0-1.23-.247A7.35 7.35 0 0 0 5 9c-4 0-5 3-5 4 0 .667.333 1 1 1h4.216A2.238 2.238 0 0 1 5 13c0-1.01.377-2.042 1.09-2.904.243-.294.526-.569.846-.816zM4.92 10A5.493 5.493 0 0 0 4 13H1c0-.26.164-1.03.76-1.724.545-.636 1.492-1.256 3.16-1.275zM1.5 5.5a3 3 0 1 1 6 0 3 3 0 0 1-6 0zm3-2a2 2 0 1 0 0 4 2 2 0 0 0 0-4z"/>
                    </svg> Team Members</h4>
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
            <div class="links">
                <a href="/docs/users">Users</a>
            </div>
        </div>

    </div>

</section>


<section class="details-list container">

    <h3>Advantages Over Competitors</h3>

    <div class="details-table-wrap">
        <div class="details-table">
            <div class="description">
                <h4><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-lightning-charge" viewBox="0 0 16 16">
                        <path d="M11.251.068a.5.5 0 0 1 .227.58L9.677 6.5H13a.5.5 0 0 1 .364.843l-8 8.5a.5.5 0 0 1-.842-.49L6.323 9.5H3a.5.5 0 0 1-.364-.843l8-8.5a.5.5 0 0 1 .615-.09zM4.157 8.5H7a.5.5 0 0 1 .478.647L6.11 13.59l5.732-6.09H9a.5.5 0 0 1-.478-.647L9.89 2.41 4.157 8.5z"/>
                    </svg> Fast</h4>
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
            <div class="links">
                <a href="/docs/how">How it works</a>
            </div>
        </div>

        <div class="details-table">
            <div class="description">
                <h4><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-shield" viewBox="0 0 16 16">
                        <path d="M5.338 1.59a61.44 61.44 0 0 0-2.837.856.481.481 0 0 0-.328.39c-.554 4.157.726 7.19 2.253 9.188a10.725 10.725 0 0 0 2.287 2.233c.346.244.652.42.893.533.12.057.218.095.293.118a.55.55 0 0 0 .101.025.615.615 0 0 0 .1-.025c.076-.023.174-.061.294-.118.24-.113.547-.29.893-.533a10.726 10.726 0 0 0 2.287-2.233c1.527-1.997 2.807-5.031 2.253-9.188a.48.48 0 0 0-.328-.39c-.651-.213-1.75-.56-2.837-.855C9.552 1.29 8.531 1.067 8 1.067c-.53 0-1.552.223-2.662.524zM5.072.56C6.157.265 7.31 0 8 0s1.843.265 2.928.56c1.11.3 2.229.655 2.887.87a1.54 1.54 0 0 1 1.044 1.262c.596 4.477-.787 7.795-2.465 9.99a11.775 11.775 0 0 1-2.517 2.453 7.159 7.159 0 0 1-1.048.625c-.28.132-.581.24-.829.24s-.548-.108-.829-.24a7.158 7.158 0 0 1-1.048-.625 11.777 11.777 0 0 1-2.517-2.453C1.928 10.487.545 7.169 1.141 2.692A1.54 1.54 0 0 1 2.185 1.43 62.456 62.456 0 0 1 5.072.56z"/>
                    </svg> Data Ownership & Privacy</h4>
                <p>
                    When using Hyvor Blogs, you own your content. It is <b>YOUR BLOG</b> that is hosted on our platform. We do not use your data for anything else than serving the blog. We never sell your data to anyone. Unlike platforms like Medium, we do not use your content to generate revenue nor hide your content behind paywalls.
                </p>
                <p>
                    You can also migrate to another platform at anytime. We support exporting content in WordPress format, making it easier to migrate to almost any platform.
                </p>
            </div>
            <div class="links">
                <a href="/docs/policy-privacy" class="legal">Privacy Policy</a>
                <a href="/docs/compliance-gdpr" class="legal">GDPR Compliance</a>
            </div>
        </div>

        <div class="details-table">
            <div class="description">
                <h4><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-key" viewBox="0 0 16 16">
                        <path d="M0 8a4 4 0 0 1 7.465-2H14a.5.5 0 0 1 .354.146l1.5 1.5a.5.5 0 0 1 0 .708l-1.5 1.5a.5.5 0 0 1-.708 0L13 9.207l-.646.647a.5.5 0 0 1-.708 0L11 9.207l-.646.647a.5.5 0 0 1-.708 0L9 9.207l-.646.647A.5.5 0 0 1 8 10h-.535A4 4 0 0 1 0 8zm4-3a3 3 0 1 0 2.712 4.285A.5.5 0 0 1 7.163 9h.63l.853-.854a.5.5 0 0 1 .708 0l.646.647.646-.647a.5.5 0 0 1 .708 0l.646.647.646-.647a.5.5 0 0 1 .708 0l.646.647.793-.793-1-1h-6.63a.5.5 0 0 1-.451-.285A3 3 0 0 0 4 5z"/>
                        <path d="M4 8a1 1 0 1 1-2 0 1 1 0 0 1 2 0z"/>
                    </svg> Security</h4>

                <p>
                    Hyvor Blogs is designed using latest technologies. We use industry-standard security practices do regular security checks to make our platform safe. Unlike self-hosted platforms like WordPress, Drupal, etc. you do not have to worry about updating breaking your blog on updates - we will take care of that. You can focus on writing content.
                </p>
            </div>
            <div class="links">
                <a href="/docs/policy-security" class="legal">Security Policy</a>
            </div>
        </div>

    </div>

</section>

<section class="details-list container">

    <h3>For Developers</h3>

    <div class="details-table-wrap">

        {{--<div class="details-table">
            <div class="description">
                <h4>Theme Development</h4>
                <p>Develop a theme, publish it to our marketplace, and earn a side income. There is only small learning curve to start building themes. We provide a "developer plan" for developers to use the platform for free forever.</p>
            </div>
        </div>--}}

        <div class="details-table">
            <div class="description">
                <h4><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-hdd-stack" viewBox="0 0 16 16">
                        <path d="M14 10a1 1 0 0 1 1 1v1a1 1 0 0 1-1 1H2a1 1 0 0 1-1-1v-1a1 1 0 0 1 1-1h12zM2 9a2 2 0 0 0-2 2v1a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-1a2 2 0 0 0-2-2H2z"/>
                        <path d="M5 11.5a.5.5 0 1 1-1 0 .5.5 0 0 1 1 0zm-2 0a.5.5 0 1 1-1 0 .5.5 0 0 1 1 0zM14 3a1 1 0 0 1 1 1v1a1 1 0 0 1-1 1H2a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1h12zM2 2a2 2 0 0 0-2 2v1a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2H2z"/>
                        <path d="M5 4.5a.5.5 0 1 1-1 0 .5.5 0 0 1 1 0zm-2 0a.5.5 0 1 1-1 0 .5.5 0 0 1 1 0z"/>
                    </svg> Data API</h4>
                <p>
                    Access public data of your blog in JSON format.
                </p>
            </div>
            <div class="links">
                <a href="/docs/api-data">Data API</a>
            </div>
        </div>

        <div class="details-table">
            <div class="description">
                <h4><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-gear" viewBox="0 0 16 16">
                        <path d="M8 4.754a3.246 3.246 0 1 0 0 6.492 3.246 3.246 0 0 0 0-6.492zM5.754 8a2.246 2.246 0 1 1 4.492 0 2.246 2.246 0 0 1-4.492 0z"/>
                        <path d="M9.796 1.343c-.527-1.79-3.065-1.79-3.592 0l-.094.319a.873.873 0 0 1-1.255.52l-.292-.16c-1.64-.892-3.433.902-2.54 2.541l.159.292a.873.873 0 0 1-.52 1.255l-.319.094c-1.79.527-1.79 3.065 0 3.592l.319.094a.873.873 0 0 1 .52 1.255l-.16.292c-.892 1.64.901 3.434 2.541 2.54l.292-.159a.873.873 0 0 1 1.255.52l.094.319c.527 1.79 3.065 1.79 3.592 0l.094-.319a.873.873 0 0 1 1.255-.52l.292.16c1.64.893 3.434-.902 2.54-2.541l-.159-.292a.873.873 0 0 1 .52-1.255l.319-.094c1.79-.527 1.79-3.065 0-3.592l-.319-.094a.873.873 0 0 1-.52-1.255l.16-.292c.893-1.64-.902-3.433-2.541-2.54l-.292.159a.873.873 0 0 1-1.255-.52l-.094-.319zm-2.633.283c.246-.835 1.428-.835 1.674 0l.094.319a1.873 1.873 0 0 0 2.693 1.115l.291-.16c.764-.415 1.6.42 1.184 1.185l-.159.292a1.873 1.873 0 0 0 1.116 2.692l.318.094c.835.246.835 1.428 0 1.674l-.319.094a1.873 1.873 0 0 0-1.115 2.693l.16.291c.415.764-.42 1.6-1.185 1.184l-.291-.159a1.873 1.873 0 0 0-2.693 1.116l-.094.318c-.246.835-1.428.835-1.674 0l-.094-.319a1.873 1.873 0 0 0-2.692-1.115l-.292.16c-.764.415-1.6-.42-1.184-1.185l.159-.291A1.873 1.873 0 0 0 1.945 8.93l-.319-.094c-.835-.246-.835-1.428 0-1.674l.319-.094A1.873 1.873 0 0 0 3.06 4.377l-.16-.292c-.415-.764.42-1.6 1.185-1.184l.292.159a1.873 1.873 0 0 0 2.692-1.115l.094-.319z"/>
                    </svg> Console API</h4>
                <p>
                    The same API we use in the Console.
                </p>
            </div>
            <div class="links">
                <a href="/docs/api-console">Console API</a>
            </div>
        </div>

        <div class="details-table">
            <div class="description">
                <h4><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-truck" viewBox="0 0 16 16">
                        <path d="M0 3.5A1.5 1.5 0 0 1 1.5 2h9A1.5 1.5 0 0 1 12 3.5V5h1.02a1.5 1.5 0 0 1 1.17.563l1.481 1.85a1.5 1.5 0 0 1 .329.938V10.5a1.5 1.5 0 0 1-1.5 1.5H14a2 2 0 1 1-4 0H5a2 2 0 1 1-3.998-.085A1.5 1.5 0 0 1 0 10.5v-7zm1.294 7.456A1.999 1.999 0 0 1 4.732 11h5.536a2.01 2.01 0 0 1 .732-.732V3.5a.5.5 0 0 0-.5-.5h-9a.5.5 0 0 0-.5.5v7a.5.5 0 0 0 .294.456zM12 10a2 2 0 0 1 1.732 1h.768a.5.5 0 0 0 .5-.5V8.35a.5.5 0 0 0-.11-.312l-1.48-1.85A.5.5 0 0 0 13.02 6H12v4zm-9 1a1 1 0 1 0 0 2 1 1 0 0 0 0-2zm9 0a1 1 0 1 0 0 2 1 1 0 0 0 0-2z"/>
                    </svg> Delivery API</h4>
                <p>
                    Delivery API can be used for self-hosting a blog within an application. This is specially useful if you want to host your blog on a subdirectory within a subdirectory (<b>example.com/blog</b>) of your application.
                </p>
                <p>
                    You can also use it for hosting your blog on the Edge.
                </p>
            </div>
            <div class="links">
                <a href="/docs/api-delivery">Delivery API</a>
                <a href="/docs/self-hosting-delivery-api">Self-hosting</a>
                <br />
                <a href="/blog/laravel-blog" class="blog">Laravel</a>
                <a href="/docs/symfony-blog" class="blog">Symfony</a>
                <a href="/docs/flask-blog" class="blog">Flask</a>
                <br />
                <a href="/blog/self-cloudflare-edge" class="blog">Cloudflare Edge</a>
                <a href="/docs/self-fastly-compute-edge" class="blog">Fastly Compute@Edge</a>
            </div>
        </div>

        <div class="details-table">
            <div class="description">
                <h4><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-bell" viewBox="0 0 16 16">
                        <path d="M8 16a2 2 0 0 0 2-2H6a2 2 0 0 0 2 2zM8 1.918l-.797.161A4.002 4.002 0 0 0 4 6c0 .628-.134 2.197-.459 3.742-.16.767-.376 1.566-.663 2.258h10.244c-.287-.692-.502-1.49-.663-2.258C12.134 8.197 12 6.628 12 6a4.002 4.002 0 0 0-3.203-3.92L8 1.917zM14.22 12c.223.447.481.801.78 1H1c.299-.199.557-.553.78-1C2.68 10.2 3 6.88 3 6c0-2.42 1.72-4.44 4.005-4.901a1 1 0 1 1 1.99 0A5.002 5.002 0 0 1 13 6c0 .88.32 4.2 1.22 6z"/>
                    </svg> Webhooks</h4>
                <p>Ping a URL when there are event. We support almost every event possible in a blog.</p>
            </div>
            <div class="links">
                <a href="/docs/webhooks">Webhooks</a>
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
            <div class="description">
                <h4><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-megaphone" viewBox="0 0 16 16">
                        <path d="M13 2.5a1.5 1.5 0 0 1 3 0v11a1.5 1.5 0 0 1-3 0v-.214c-2.162-1.241-4.49-1.843-6.912-2.083l.405 2.712A1 1 0 0 1 5.51 15.1h-.548a1 1 0 0 1-.916-.599l-1.85-3.49a68.14 68.14 0 0 0-.202-.003A2.014 2.014 0 0 1 0 9V7a2.02 2.02 0 0 1 1.992-2.013 74.663 74.663 0 0 0 2.483-.075c3.043-.154 6.148-.849 8.525-2.199V2.5zm1 0v11a.5.5 0 0 0 1 0v-11a.5.5 0 0 0-1 0zm-1 1.35c-2.344 1.205-5.209 1.842-8 2.033v4.233c.18.01.359.022.537.036 2.568.189 5.093.744 7.463 1.993V3.85zm-9 6.215v-4.13a95.09 95.09 0 0 1-1.992.052A1.02 1.02 0 0 0 1 7v2c0 .55.448 1.002 1.006 1.009A60.49 60.49 0 0 1 4 10.065zm-.657.975 1.609 3.037.01.024h.548l-.002-.014-.443-2.966a68.019 68.019 0 0 0-1.722-.082z"/>
                    </svg> Free Speech</h4>
                <p>
                    We maintain a free speech policy, within the bounds of the law.
                </p>
            </div>
            <div class="links">
                <a href="/docs/policy-content" class="legal">Content Policy</a>
            </div>
        </div>

        <div class="details-table">
            <div class="description">
                <h4><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-balloon-heart" viewBox="0 0 16 16">
                        <path fill-rule="evenodd" d="m8 2.42-.717-.737c-1.13-1.161-3.243-.777-4.01.72-.35.685-.451 1.707.236 3.062C4.16 6.753 5.52 8.32 8 10.042c2.479-1.723 3.839-3.29 4.491-4.577.687-1.355.587-2.377.236-3.061-.767-1.498-2.88-1.882-4.01-.721L8 2.42Zm-.49 8.5c-10.78-7.44-3-13.155.359-10.063.045.041.089.084.132.129.043-.045.087-.088.132-.129 3.36-3.092 11.137 2.624.357 10.063l.235.468a.25.25 0 1 1-.448.224l-.008-.017c.008.11.02.202.037.29.054.27.161.488.419 1.003.288.578.235 1.15.076 1.629-.157.469-.422.867-.588 1.115l-.004.007a.25.25 0 1 1-.416-.278c.168-.252.4-.6.533-1.003.133-.396.163-.824-.049-1.246l-.013-.028c-.24-.48-.38-.758-.448-1.102a3.177 3.177 0 0 1-.052-.45l-.04.08a.25.25 0 1 1-.447-.224l.235-.468ZM6.013 2.06c-.649-.18-1.483.083-1.85.798-.131.258-.245.689-.08 1.335.063.244.414.198.487-.043.21-.697.627-1.447 1.359-1.692.217-.073.304-.337.084-.398Z"/>
                    </svg> Life Insurance</h4>
                <p>If you are a paid customer, we will host your blog forever after your death. Once your family/freinds notifies us, we will archive your blog, cancel your subscription, and host your blog for free forever. And, we are a bootstrapped company who focuses on the product and customers than our growth. Therefore, it is very likely Hyvor Blogs will be there until the end of the internet, so is your blog.</p>
                <p class="note">Note: We are also happy to host any deceased person's personal blog on our platform for a one-time charge. Contact us for more information.</p>
            </div>
            <div class="links">
                <a href="/docs/policy-life-insurance" class="legal">Life Insurance Policy</a>
            </div>
        </div>

        <div class="details-table">
            <div class="description">
                <h4><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-envelope" viewBox="0 0 16 16">
                        <path d="M0 4a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V4Zm2-1a1 1 0 0 0-1 1v.217l7 4.2 7-4.2V4a1 1 0 0 0-1-1H2Zm13 2.383-4.708 2.825L15 11.105V5.383Zm-.034 6.876-5.64-3.471L8 9.583l-1.326-.795-5.64 3.47A1 1 0 0 0 2 13h12a1 1 0 0 0 .966-.741ZM1 11.105l4.708-2.897L1 5.383v5.722Z"/>
                    </svg> Support</h4>
                <p>Everyone can get support at our forum from community members and the Hyvor Blogs team. Or, contact us at <b>blogs.support@hyvor.com</b>. Live chat support is available for Team and Enteprise customers.</p>
            </div>
            <div class="links">
                <a href="/docs/contact-us" class="none">Contact Us</a>
                <a href="https://community.blogs.hyvor.com" class="none" target="_blank">Community</a>
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