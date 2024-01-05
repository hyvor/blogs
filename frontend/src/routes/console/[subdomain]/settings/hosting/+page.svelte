<script>
	import { InputGroup, Link, Radio, SplitControl, TextInput } from "@hyvor/design/components";
	import { blogStore } from "../../../lib/stores/blogStore";
	import BlogSettingsSave from "../BlogSettingsSave.svelte";
	import { IconBoxArrowUpRight } from "@hyvor/icons";

    let subdomain = $blogStore.subdomain;
    let hostingAt = $blogStore.hosting_at;

</script>

<BlogSettingsSave
    keys={['subdomain', 'hosting_at', 'hosting_domain', 'hosting_url']}
/>

<div class="hosting">

    <SplitControl
        label="Subdomain"
        caption="The hyvorblogs.io subdomain. Uniquely identifies your blog within Hyvor Blogs."
    >
        <TextInput 
            bind:value={subdomain}
            block
        />
    </SplitControl>

    <SplitControl
        label="Hosted at"
        caption="Where do you like to host your blog?"
    >
        <InputGroup>
            <Radio
                value="subdomain"
                bind:group={hostingAt}
            >
                Subdomain (hyvorblogs.io)
            </Radio>
            <Radio
                value="domain"
                bind:group={hostingAt}
            >
                Custom Domain - &nbsp;<Link 
                    href="/docs/custom-domain" 
                    target="_blank"
                    style="font-size:14px;"
                >
                    Docs
                    <IconBoxArrowUpRight slot="end" size={10} />
                </Link>
            </Radio>
            <Radio
                value="self"
                bind:group={hostingAt}
            >
                Self-hosting - &nbsp;<Link 
                    href="/docs/self-hosting" 
                    target="_blank"
                    style="font-size:14px;"
                >
                    Docs
                    <IconBoxArrowUpRight slot="end" size={10} />
                </Link>
            </Radio>
        </InputGroup>
    </SplitControl>

</div>

<style>
    .hosting {
        padding: 20px 30px;
    }
</style>