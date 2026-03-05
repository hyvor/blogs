<script lang="ts">
	import { Callout, CodeBlock } from '@hyvor/design/components';
	import { Docs, DocsImage } from '@hyvor/design/marketing';
	import copyCodeImg from './copy-code.png';
	import pasteCodeImg from './paste-code.png';
	import integrationImg from './integrated.png';
	import welcomeImg from './welcome.png';
	import campaignImg from './email-campaign.png';
	import finalImg from './final.png';
</script>

<div class="guide-content">
	<Callout type="info">
		<p><b>Requirements</b>:</p>
		<ul>
			<li>a Mailchimp account</li>
			<li>a form created using Mailchimp</li>
			<li>a blog on <a href="/console">Hyvor Blogs</a></li>
		</ul>
	</Callout>

	<p>
		If you do not have completed any of the above, refer this <a
			href="https://mailchimp.com/help/getting-started-with-mailchimp/"
			rel="nofollow">documentation</a
		> (by Mailchimp) to complete all of them.
	</p>

	<h2>Step 1: Get the Mailchimp Form Code</h2>
	<ol>
		<li>
			Choose the Newsletter Signup Form you created (in the Form Editor) and click <b>Continue</b>.
		</li>
		<li><b>Copy the Code</b> given.</li>
		<DocsImage src={copyCodeImg} alt="Copy the code" />
	</ol>

	<h2>Step 2: Integration</h2>
	<ol>
		<li>Go to <a href="/console">Hyvor Blogs Console</a>.</li>
		<li>
			Then go to, <b>Settings &rarr; Comments & Newsletters &rarr; Newsletter Sign-up Form Code</b>
			and <b>paste</b> the copied code.
		</li>
		<li><b>Save.</b></li>
		<DocsImage src={pasteCodeImg} alt="Paste the code" />
	</ol>

	<h2>Step 3: Verify</h2>
	<p>
		Go to your blog URL and check whether the newsletter sign up form is integrated correctly. It
		should look like this.
	</p>
	<DocsImage src={integrationImg} alt="Integrated" />

	<Callout type="danger">
		<p>
			<b>Warning</b>: Please note that the location of the form may vary depending on the template
			employed by your blog. For instance, the form appears on the post pages in the Hello theme. To
			specify the desired placement for the subscription form, kindly refer to the section below.
		</p>
	</Callout>

	<h2 id="edit-theme">Note: Editing the blog's theme to add the subscription form</h2>
	<p>
		If the signup form location used by your blog's <a href="/docs/themes-templates">template</a> does
		not fit your need, you can edit the theme to customize the location.
	</p>
	<ol>
		<li>Go to the <b>Theme → templates</b> section.</li>
		<li>Choose the file where you want to display the form, for example,</li>
		<ul>
			<li><code>index.twig</code>: The form will be displayed on the home page.</li>

			<li>
				<code>_footer.twig</code>: The form will be displayed in the footers of all pages.
			</li>
		</ul>
		<li>
			<b>Copy</b> the following code snippet in your chosen file
			<code>MAILCHIMP_CODE_SNIPPET</code>
			with the code snipped provided by Mailchimp when editing your form.
		</li>
		<CodeBlock
			language="html"
			code={`
        <center>
	        MAILCHIMP_CODE_SNIPPET
        </center> 
         `}
		/>

		<p>
			This way, you can customize where to put the subscription form. Note that you can put it in
			multiple places.
		</p>
	</ol>
</div>
