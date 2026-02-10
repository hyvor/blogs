<script>
	import { CodeBlock } from '@hyvor/design/components';
</script>

<h1>Import from WordPress</h1>

<p>
	Currently, WordPress importing is only available through support. Please contact us at
	<a href="mailto:blogs.support@hyvor.com"> blogs.support@hyvor.com </a> to import your WordPress blog.
</p>

<p>Follow the steps below to export your WordPress site:</p>

<ul>
	<li>
		Export your posts at <strong
			>WordPress admin &rarr; Tools &rarr; Export &rarr; All Content &rarr; Download Export
			File</strong
		>
	</li>
	<li>
		Download your <code>wp-content/uploads</code> folder from your WordPress site. This folder contains
		all the images and other media files used in your posts. You may need to use an FTP client or
		ask your hosting provider to download this folder.
	</li>
</ul>

<p>Once you have the files, create a ZIP file with the following structure:</p>

<CodeBlock
	code={`
wordpress-export.zip
    ├── export.xml
    └── uploads
        └── 2024
            ├── 01
            │   ├── image1.jpg
            │   └── image2.jpg
            └── 02
                └── image3.jpg
`}
/>

<p>
	Send us the ZIP file and we will handle the rest. Feel free to contact us via live chat if you
	have any questions.
</p>
