<script lang="ts">
    import { Callout } from "@hyvor/design/components";
    import { DocsImage } from "@hyvor/design/marketing";
    import analyticsImg from "./analytics.png";
    import webAnalyticsAddSiteImg from "./web-analytics-add-a-site.png";
    import existingDomainAddImg from "./existing-domain-done.png"
    import viewAnalyticsImg from "./view-analytics.png";
    import addNewSiteImg from "./add-new-site.png";
    import copyJSCodeImg from "./copy-js-code.png";
    import pasteJSCodeImg from "./paste-js-code.png";
    import analyticsOverviewImg from "./analytics-overview.png";
</script>


<div class="guide-content">
    <Callout type="info">
        <p><b>Requirements: </b>You should have a <a href="https://www.cloudflare.com">Cloudflare account</a> to use the Cloudflare Analytics tools.</p>
    </Callout>

    <p>Two different processes are carried out if you are,</p>
        <ul>
            <li><a href="#not-using-cloudflare">already using Cloudflare CDN for your blog</a>,</li>
            <li><a href="#already-use-cloudflare">using Cloudflare CDN</a></li>
        </ul>

    
    <h2 id="not-using-cloudflare">1. If you are already using Cloudflare CDN,</h2>
    <p>This process is called "Automatic Setup". To proceed with it, you should have  the "Orange cloud" turned on.</p>
    <ol>
        <li>Login to your Cloudflare account</li>
        <li>Click "<b>Analytics & Logs</b> -&gt;<b>Web Analytics</b>"</li>
        <DocsImage src={analyticsImg} alt="Cloudflare Analytics" width={300} />
        <li>Click "<b>Add a Site</b>"</li>
        <DocsImage src={webAnalyticsAddSiteImg} alt="Add a site" />
        <li>Once you click on input space, it will show your domain (in a dropdown) that is already managed by Cloudflare. Click on your blog domain and proceed by clicking "<b>Done</b>". This is called "<b>Automatic Setup</b>".</li>
        <DocsImage src={existingDomainAddImg} alt="Add a site" />
        <p>Now you can see your site has been added.You can check it by going to "<b>Analytics & Logs</b> -&gt;<b>Web Analytics</b>".</p>
        <DocsImage src={viewAnalyticsImg} alt="View Analytics" />
    </ol>

    <h2 id="already-use-cloudflare">2. If you do not use Cloudflare CDN,</h2>    
    <h3 id="add-your-blog">2.1. Adding Your Blog to Cloudflare Analytics</h3>
        <p>After you signed in to your Cloudflare account, you can add your blog to Cloudflare Analytics.</p>
        <ol>
            <li>Click "<b>Analytics & Logs</b> -&gt;<b>Web Analytics</b>"</li>
            <DocsImage src={analyticsImg} alt="Cloudflare Analytics" width={300} />
            <li>Click "<b>Add a Site</b>"</li>
            <DocsImage src={webAnalyticsAddSiteImg} alt="Add a site" />
            <li>Enter your blog URL (ex:<code>myawesomeblog.hyvorblogs.io</code> or <code>myawesomeblog.com</code>).
                <ol>
                    <li>Click the dropdown which says "<b>Click here to use my-awesome-blog.hyvorblogs.io which does not belong to Cloudflare websites</b>"</li>
                    <li>Click "<b>Done</b>"</li>
                </ol>
                <DocsImage src={addNewSiteImg} alt="Add new site" />
            </li>
            <li>Copy the given code</li>
            <DocsImage src={copyJSCodeImg} alt="Copy JS code" />   
        </ol>

    <h3 id="add-the-code">2.2. Adding the Code to Your Blog</h3>
        <ol>
            <li>In your <a href="/console">blog console</a> go to <b>Settings → Custom code</b>.</li>
            <li>Paste the code you copied from Cloudflare Analytics into the "<b>Footer Code</b>" section.</li>
            <li>Click "<b>Save</b>"</li>
            <DocsImage src={pasteJSCodeImg} alt="Paste JS code" />

            <p>This way, Cloudflare Analytics will be implemented on all the pages of your blog.</p>

        </ol>

    <h3 id="verify">2.3. Verify</h3>
        <p>After you added the code, you can verify it by going to "<b>Analytics & Logs</b> -&gt;<b>Web Analytics</b>".</p>
        <DocsImage src={analyticsOverviewImg} alt="View Analytics" />

</div>
