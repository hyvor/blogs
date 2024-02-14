<script>
	import { CodeBlock } from "@hyvor/design/components";

</script>
<h1>WordPress Migration</h1>

<p>
    This guide will help you migrate from WordPress to Hyvor Blogs and vice versa.
</p>

<h2 id="wordpress-to-hb">
    WordPress &rarr; Hyvor Blogs 😊
</h2>

<p>
    Work in progress. This section will be available soon.
</p>

<h2 id="hb-to-wp">
    Hyvor Blogs &rarr; WordPress 😢
</h2>

<p>
    We don't want you to lock into our platform. To ensure that, we provide an easy way to export your data from Hyvor Blogs to WordPress. You can export your posts, pages, users, tags, media, etc. and import them to any WordPress installation. There are also a <a href="#hb-to-wp-limitations">few limitations</a> to consider.
</p>

<h3 id="hb-to-wp-export">
    Exporting from Hyvor Blogs
</h3>

<ul>
    <li>
        Go to <strong>Tools &rarr; Export</strong> in the Console.
    </li>
    <li>
        Select <strong>WordPress</strong> as the <strong>Export Format</strong>.
    </li>
    <li>
        Click <strong>Export</strong> and confirm.
    </li>
</ul>

<p>
    Your export will take a couple of minutes to a few hours to complete depending on the size of your blog. Once it's done, you will be able to download the export file in the <strong>Settings &rarr; Exports &rarr; History</strong> tab.
</p>

<p>
    The export file will be a <code>.zip</code> file with the following structure:
</p>

<CodeBlock code={`
    - export.xml
    - media/
        - 2024/
            - 01/
                - image1.jpg
                - image2.jpg
                - ...

`} />

<ul>
    <li>
        <code>export.xml</code> - This is a WordPress-compatible XML file that contains the following data:
        <ul>
            <li>Posts</li>
            <li>Pages</li>
            <li>Authors (Users in Hyvor Blogs)</li>
            <li>Categories (Tags in Hyvor Blogs)</li>
        </ul>
    </li>
    <li>
        <code>media</code> - This directory contains all the media files used in your blog. The media files are organized by year and month.
    </li>
</ul>

<h3 id="hb-to-wordpress-import">
    Importing to WordPress
</h3>

<p>
    First, import <code>export.xml</code> to WordPress using the built-in importer.
</p>

<ul>
    <li>
        Go to <strong>Tools &rarr; Import</strong> in the WordPress admin panel.
    </li>
    <li>
        Click on <strong>WordPress</strong> and install the importer if you haven't already.
    </li>
</ul>

<p>
    Next, copy all the folders in the <code>media</code> directory to the <code>wp-content/uploads</code> directory in your WordPress installation.
</p>

<ul>
    <li>
        Access your WordPress installation using an FTP client or a file manager.
    </li>
    <li>
        Navigate to the <code>wp-content/uploads</code> directory.
    </li>
    <li>
        Upload the <code>media</code> directory to the <code>wp-content/uploads</code> directory. The final structure should look like this:

        <CodeBlock code={`
            - wp-content/
                - uploads/
                    - 2024/
                        - 01/
                            - image1.jpg
                            - image2.jpg
                            - ...
        `} />
    </li>
</ul>


<h3 id="hb-to-wp-important-details">
    Important Details
</h3>

<p>
    To ensure a smooth migration, we handle the following things for you under the hood:
</p>

<ul>
    <li>
        <strong>Media URLs in Posts</strong>: 
        In Hyvor Blogs, media have the URL structure <code>/media/[name]</code>. In the exported XML, we automatically replace the image URLs in your posts with the new WordPress media URLs (<code>/wp-content/uploads/[year]/[month]/[name]</code>).
    </li>
</ul>

<h3 id="hb-to-wp-limitations">
    Limitations/Compatibility Issues
</h3>

<ul>
    <li>
        <strong>Multi-language support</strong>: 
        Hyvor Blogs is multi-language by default. However, WordPress doesn't have built-in multi-language support. Therefore, when you export your data, you can only export data of a single language. For multiple languages, you need to export each language separately.
    </li>
    <li>
        <strong>Multi-author</strong>: 
        In Hyvor Blogs, a post can have multiple authors. However, WordPress doesn't support multiple authors for a single post. Therefore, when you import your data, only the first author will be set as the author of the post. The other authors will be ignored.
    </li>
    <li>
        <strong>Tags</strong>:
        WordPress has a concept of categories and tags. In Hyvor Blogs, we only have tags. When importing to WordPress, all tags will be imported as <strong>categories</strong>.
    </li>
    <li>
        <strong>Comments</strong>: 
        Comments are not managed by Hyvor Blogs, and therefore, they are not exported. If you are using Hyvor Talk on your blog, see <a href="https://talk.hyvor.com/docs/export" target="_blank">Hyvor Talk docs</a> for exporting comments. Other comment systems should have their own export tools.
    </li>
</ul>