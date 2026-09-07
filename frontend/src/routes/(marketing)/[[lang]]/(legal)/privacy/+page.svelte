<script>
	import { Callout } from '@hyvor/design/components';
	import { Document } from '@hyvor/design/marketing';
	import IconCardChecklist from '@hyvor/icons/IconCardChecklist';
</script>

<svelte:head>
	<title>Privacy Policy - Hyvor Blogs</title>
</svelte:head>

<Document title="Privacy Policy (Hyvor Blogs)" subtitle="Last Updated: 2024-09-21">
	<h2>Introduction</h2>

	<p>
		HYVOR, (us, we, our), operates the Hyvor Blogs (<a href="https://blogs.hyvor.com"
			>https://blogs.hyvor.com</a
		>) website and provides a blogging platform. This page covers how we collect and use the
		information you ("Blogger", "Customer") give us at Hyvor Blogs.
	</p>

	<Callout type="info">
		{#snippet title()}
			Non-binding Summary
		{/snippet}
		{#snippet icon()}
			<span style="display:inline-flex;align-items:center;margin-top:8px;">
				<IconCardChecklist size={30} />
			</span>
		{/snippet}
		<ul>
			<li>You own the content you publish.</li>
			<li>We do not use your content to make revenue.</li>
			<li>
				We do not add any external tracking codes, affiliate links, paywalls to your blog (though
				you have the options to manually do so).
			</li>
			<li>You can export your content and media anytime.</li>
			<li>We use cookies only for authentication.</li>
			<li>Your data is hosted in the EU (Nuremberg and Frankfurt, Germany).</li>
		</ul>
	</Callout>

	<h2>Information We Collect</h2>
	<h3>1. Signup</h3>
	<p>
		HYVOR signup data (user data) is collected at hyvor.com, please refer to our <a
			href="https://hyvor.com/privacy">main privacy page</a
		> to see how we use your signup information. Your Hyvor account can be used to access any HYVOR service
		including Hyvor Blogs.
	</p>

	<h3>2. Blog Data and Post</h3>
	<p>
		When you create a blog, we will collect information of the blog such as name and description.
		These data will be used to render the blog. Posts-related data such as post content, post
		metadata will also be saved.
	</p>

	<h3>3. User Data</h3>
	<p>
		When you add users (authors) to your blog, you have options to provide more details than
		contained in their Hyvor account, for example
	</p>

	<h3>4. Payment Information</h3>
	<p>
		For all the types of payments done when subscribing to paid plans, we use the secure service
		from Paddle.com. Paddle works as our merchant of record (a reseller of our products), and it
		stores your credit card information, etc. for recurring payments.
	</p>

	<h3>5. Technical Data</h3>
	<p>
		We collect technical data such as IP address for security purposes. These are mostly collected
		within server logs, which are deleted periodically.
	</p>

	<h2>Data Ownership</h2>
	<p>
		You (Blogger) are the sole owner of all the data related to the blog such as posts, post
		metadata, media, pages, tags, and authors. We will only save these data in our servers in the
		period we host your blog on our platform. You can export all data anytime. You can delete these
		data anytime.
	</p>

	<h2>Blog Control</h2>
	<p>
		You are the sole owner of your blog. You can completely customize the source code of your blog.
		By default, we do not add any third-party codes, affiliate links, or paywalls to your blog. Our
		official themes are also designed to behave this way. However, you have to options to add custom
		code manually or integrate other services.
	</p>

	<h2>Data Storage</h2>
	<p>
		Hyvor Blogs is hosted at <a href="https://www.hetzner.com/">Hetzner</a> data centers in
		Nuremberg, Germany. Some of your data will be shared with Hyvor's main servers hosted at
		<a href="https://www.digitalocean.com/">DigitalOcean</a> data centers in Frankfurt, Germany.
	</p>

	<h2>Data Security</h2>
	<p>
		We use HTTPS/TLS encryption to protect data transferred between you and our site. All of your
		data is stored safely on our databases and always backed up on external servers that we own.
		Therefore, we guarantee the safety of your stored data. However, the data transferred through
		the internet is not always completely safe. You are responsible for taking appropriate actions
		to secure your account.
	</p>

	<h2>Emails</h2>
	<p>
		We only send administrative emails to bloggers to inform them account, security, policy, and
		service updates through email. You can only unsubscribe from these emails by deleting the blog
		from the Console.
	</p>

	<h2 id="third-party-services">Third-Party Services</h2>

	<p>
		Please see our Sub-Processors page (<a href="https://hyvor.com/sub" target="_blank"
			>https://hyvor.com/sub</a
		>) for a list of third-party services we use.
	</p>

	<h2>Updates to Privacy Policy</h2>
	<p>
		We may update this privacy policy page from time to time. The last update time is shown at the
		top of this document. Any major updates to our privacy policy will be notified to all the users
		via email and through the website.
	</p>
</Document>
