<script lang="ts">
	import { FormControl, Modal, SplitControl, TextInput, Validation } from "@hyvor/design/components";
	import type { Route } from "../../../lib/types";

    export let show = false;
    export let route: null | Route = null;

    let name = route?.name || '';
    let match = route?.match || '';
    let template = route?.template || '';
    let postsFilter = route?.posts_filter || '';
    let contentType = route?.content_type || '';

    let nameError : null | string = null;
    let matchError : null | string = null;
    let templateError : null | string = null;
    let postsFilterError : null | string = null;
    let contentTypeError : null | string = null;

    function validate() {
        let isValid = true;

        if (!name) {
            nameError = "Name is required";
            isValid = false;
        }

        if (!match) {
            matchError = "Match is required";
            isValid = false;
        }

        if (!template) {
            templateError = "Template is required";
            isValid = false;
        }

        if (!postsFilter) {
            postsFilterError = "Posts Filter is required";
            isValid = false;
        }

        if (!contentType) {
            contentTypeError = "Content Type is required";
            isValid = false;
        }

        return isValid;
    }

    function handleCreate() {
        if (!validate())
            return;

        
    }

    function handleUpdate() {

    }
    
</script>

<Modal
    title={route ? "Update Route" : "Create Route"}
    bind:show={show}
    footer={{
        confirm: {
            text: route ? "Update" : "Create",
        },
    }}
    on:confirm={() => {
        if (route) {
            handleUpdate();
        } else {
            handleCreate();
        }
    }}
>

    <SplitControl
        flex={[2,3]}
        label="Name"
        caption="Just for your reference"
    >
        <FormControl>
            <TextInput 
                block
                placeholder="New Route"
                bind:value={name}
                state={nameError ? "error" : undefined}
            />
            {#if nameError}
                <Validation state="error">{nameError}</Validation>
            {/if}
        </FormControl>
    </SplitControl>

    <SplitControl
        flex={[2,3]}
        label="Match"
        caption="The path or pattern to match"
    >
        <FormControl>
            <TextInput 
                block
                placeholder="/path"
                bind:value={match}
                state={matchError ? "error" : undefined}
            />
            {#if matchError}
                <Validation state="error">{matchError}</Validation>
            {/if}
        </FormControl>
    </SplitControl>

    <SplitControl
        flex={[2,3]}
        label="Template"
        caption="The Twig template to render when the route matches. Add fallbacks by separating with comma."
    >
        <FormControl>
            <TextInput 
                block
                placeholder="new,index"
                bind:value={template}
                state={templateError ? "error" : undefined}
            />
            {#if templateError}
                <Validation state="error">{templateError}</Validation>
            {/if}
        </FormControl>
    </SplitControl>

    <SplitControl
        flex={[2,3]}
        label="Posts Filter"
        caption="A FilterQ expression to filter posts for the _posts array."
    >
        <FormControl>
            <TextInput 
                block
                placeholder="tag.slug=new"
                bind:value={postsFilter}
                state={postsFilterError ? "error" : undefined}
            />
            {#if postsFilterError}
                <Validation state="error">{postsFilterError}</Validation>
            {/if}
        </FormControl>
    </SplitControl>

    <SplitControl
        flex={[2,3]}
        label="Content Type"
        caption="The content type header to send with the response"
    >
        <FormControl>
            <TextInput 
                block
                placeholder="text/html"
                bind:value={contentType}
                state={contentTypeError ? "error" : undefined}
            />
            {#if contentTypeError}
                <Validation state="error">{contentTypeError}</Validation>
            {/if}
        </FormControl>
    </SplitControl>

</Modal>

