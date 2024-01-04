<script>
	import { Button, Radio, SplitControl, Switch, TextInput } from "@hyvor/design/components";

    let importFrom = 'sitemap';

    let sitemapUrl = '';
    
    let titleCssSelector = '';
    let descriptionCssSelector = '';
    let contentCssSelector = '';
    let contentExcludeCssSelector = '';
    let publishedDateCssSelector = '';

    let importImages = true;
    let slugExclude = '';
</script>

<SplitControl 
    label="Import From"
    caption="Select a method to import data from"
>

    <Radio 
        name="import-from"
        value="sitemap"
        bind:group={importFrom}
    >
        Sitemap (Live Website)
    </Radio>

</SplitControl>

<SplitControl 
    label="Sitemap URL"
    caption="XML or TXT sitemap that contains links to all your posts."
>

    <TextInput 
        placeholder="https://..."
        block
        bind:value={sitemapUrl}
    />

</SplitControl>

<SplitControl 
    label="CSS Selectors"
    caption="Add CSS selectors to find data in your HTML pages."
>

    <div slot="nested">

        <SplitControl 
            label="Post Content"
            caption="Required"
        >
            <TextInput 
                block
                bind:value={contentCssSelector}
            />
        </SplitControl>

        <SplitControl label="Post Title">
            <TextInput 
                block
                bind:value={titleCssSelector}
            />
        </SplitControl>

        <SplitControl label="Post Description">
            <TextInput 
                block
                bind:value={descriptionCssSelector}
            />
        </SplitControl>

        <SplitControl 
            label="Post Content Exclude"
            caption="To exclude elements from post content"
        >
            <TextInput 
                block
                bind:value={contentExcludeCssSelector}
            />
        </SplitControl>

        <SplitControl label="Post Published Date">
            <TextInput 
                block
                bind:value={publishedDateCssSelector}
            />
        </SplitControl>

    </div>

</SplitControl>

<SplitControl 
    label="Import Images"
    caption="Copy images into blog media (recommended)"
>

    <Switch 
        bind:checked={importImages}
    />

</SplitControl>


<SplitControl 
    label="Slug Exclude"
    caption="Exclude a part of the URL from the slug (ex: /blog/)"
>

    <TextInput 
        block
        bind:value={slugExclude}
    />

</SplitControl>

<SplitControl 
    label="Test"
    caption="Test a single page before importing the sitemap (recommended)"
>

    <TextInput 
        block
        bind:value={slugExclude}
        placeholder="URL to test"
    />

</SplitControl>

<div class="footer">

    <div 
        class="note"
        style="margin-bottom: 20px;"
    >
        Please test a few pages before importing the sitemap. If you need help, feel free to contact us.
    </div>

    <Button>
        Import
    </Button>

</div>


<style>

    .footer {
        padding: 20px 100px;
        text-align: center;
    }

</style>