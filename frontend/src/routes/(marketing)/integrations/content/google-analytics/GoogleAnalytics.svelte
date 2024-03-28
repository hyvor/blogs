<script lang="ts">
	import { Callout } from '@hyvor/design/components';
	import { DocsImage } from '@hyvor/design/marketing';
	import gaStartImg from './ga-start.png';
	import createAccImg from './create-account.png';
	import createPropertyImg from './create-property.png';
	import businessDetailsImg from './business-details.png';
	import businessObjImg from './business-obj.png';
	import dataStreamImg from './data-stream.png';
	import installationImg from './installation.png';
	import headCodeImg from './head-code.png';
	import realtimeImg from './realtime.png';
</script>

<div class="guide-content">
	<Callout type="info">
		<p><b>Requirements: </b></p>
		<ul>
			<li>
				a <a href="https://myaccount.google.com">Google account</a> to use the Google Analytics tools.
			</li>
			<li>a blog on <a href="/console">Hyvor Blogs</a></li>
		</ul>
	</Callout>

	<h2 id="google-analytics-account-creation">Step 1 - Google Analytics account setup</h2>

	<p>
		If you already have a Google Analytics account, skip to <a href="#create-data-stream">Step 2</a
		>.
	</p>

	<ol>
		<li>
			Sign in to your Google account and go to <a href="https://analytics.google.com/analytics"
				>Google Analytics</a
			>. Then click <b>Start Measuring</b>.
		</li>
		<DocsImage src={gaStartImg} alt="Google Analytics start measuring" />

		<li>Choose a name for your account, you can use the same one as your Hyvor account.</li>
		<DocsImage src={createAccImg} alt="Create account" />

		<li>
			Select or unselect the data-sharing parameters according to your needs. Then click "<b>Next</b
			>".
		</li>

		<li>
			Choose a name for your Google Analytics property. Since a property represents a business's web
			and/or app data you can use the name of your blog.
		</li>
		<li>Select the time zone and the currency of your country. Click <b>Next</b>.</li>
		<DocsImage src={createPropertyImg} alt="Create property" />

		<li>
			Fill in your business information according to your blog. Then click <b>Create</b>.
		</li>
		<DocsImage src={businessDetailsImg} alt="Business details" />

		<li>
			Choose the business objectives that you want to achieve with your blog. Then click <b
				>Create</b
			>. After that you will see Google Analytics Terms of Service Agreement, make sure to accept
			that.
		</li>
		<DocsImage src={businessObjImg} alt="Business objectives" />
	</ol>

	<h2 id="create-data-stream">Step 2 - Create a data stream</h2>
	<p>
		After you accepted Google Analytics Terms of Searvice Agreement, you will be directed to
		creating a data stream step.
	</p>
	<ol>
		<li>Choose <b>Web</b> among the given data streams.</li>
		<li>
			Enter the URL of your website, and use your blog URL (ex:<code
				>myawesomeblog.hyvorblogs.io</code
			>
			or <code>myawesomeblog.com</code>), and the name for the stream, and use the name of your blog
			(My Awesome Blog).
		</li>
		<li>Click <b>Create stream</b>.</li>
		<DocsImage src={dataStreamImg} alt="Data stream" />
	</ol>

	<h2 id="link-google-analytics-data-stream-with-your-blog">
		Step 3 - Linking the Google Analytics data stream with your blog
	</h2>
	<p>Now the data stream creating is finished, you'll need to link it to your blog.</p>
	<ol>
		<li>
			A popup should open with the installation instructions (if not click on <b
				>View tag instructions</b
			>).
		</li>
		<DocsImage src={installationImg} alt="Installation" />
		<li>
			Copy the code snippet provided by clicking on the <b>Copy icon</b> in the top right corner.
		</li>

		<li>In your blog console go to <b>Settings &rarr; Custom code</b>.</li>
		<li>Paste the code snippet in the Head Code section and hit <b>Save</b>.</li>
		<DocsImage src={headCodeImg} alt="Adding copied code to Head code" />
		<p>This way, Google Analytics will be implemented on all the pages of your blog.</p>

		<Callout type="info">
			<p>It may take up to 24 hours for Google Analytics to start showing data.</p>
		</Callout>
	</ol>

	<h2 id="very-installation">Step 4 - Verify the installation</h2>
	<p>
		After you've installed the code snippet, you can verify the installation by going to the <a
			href="https://analytics.google.com/analytics">Google Analytics</a
		>
		and clicking on the "Realtime" tab. Then go to your blog and you should see a user in the
		<b>Realtime</b> tab.
	</p>
	<DocsImage src={realtimeImg} alt="Realtime" />
</div>
