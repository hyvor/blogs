<script lang="ts">
	import { FormControl, Modal, SplitControl, TextInput, Validation, toast } from "@hyvor/design/components";
	import type { Route } from "../../../lib/types";
	import { createRoute, updateRoute } from "./routeActions";
	import { createEventDispatcher } from "svelte";

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

    let isCreating = false;

    function validate() {
        let isValid = true;

        if (name.trim().length === 0) {
            nameError = "Name is required";
            isValid = false;
        }

        if (match.trim().length === 0) {
            matchError = "Match is required";
            isValid = false;
        }

        if (match[0] !== '/') {
            matchError = "Match must start with /";
            isValid = false;
        }

        if (!template) {
            templateError = "Template is required";
            isValid = false;
        }

        return isValid;
    }

    const dispatch = createEventDispatcher();

    function handleCreate() {
        if (!validate())
            return;

        isCreating = true;

        createRoute({
            name,
            match,
            template,
            posts_filter: postsFilter,
            content_type: contentType,
        })
            .then(res => {
                toast.success("Route created successfully");
                dispatch('create', res)
            })
            .catch(err => {
                toast.error(err.message);
            })
            .finally(() => {
                show = false;
            });

    }

    function handleUpdate() {

        if (!validate())
            return;

        isCreating = true;

        updateRoute(route!.id, {
            name,
            match,
            template,
            posts_filter: postsFilter,
            content_type: contentType,
        })
            .then(res => {
                toast.success("Route updated successfully");
                dispatch('update', res)
            })
            .catch(err => {
                toast.error(err.message);
            })
            .finally(() => {
                show = false;
            });

    }

    const flex = [2,3];
    
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
    loading={isCreating}
>

    <SplitControl
        flex={flex}
        label="Name"
        caption="Just for your reference"
    >
        <FormControl>
            <TextInput 
                block
                placeholder="New Route"
                bind:value={name}
                state={nameError ? "error" : undefined}
                autofocus
            />
            {#if nameError}
                <Validation state="error">{nameError}</Validation>
            {/if}
        </FormControl>
    </SplitControl>

    <SplitControl
        flex={flex}
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
        flex={flex}
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
        flex={flex}
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
        flex={flex}
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

