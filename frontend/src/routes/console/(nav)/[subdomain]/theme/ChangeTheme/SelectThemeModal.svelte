<script lang="ts">
	import { Loader, Modal, Button, ActionList, ActionListItem, Text, confirm, Callout, toast } from "@hyvor/design/components";
	import { onMount } from "svelte";
	import { changeTheme, loadThemes } from "../themeActions";
	import type { Theme } from "../../../../lib/types";
	import { IconBoxArrowUpRight, IconExclamation, IconExclamationCircle } from "@hyvor/icons";
	import { setThemeFiles } from "../themeStore";

    interface Props {
        show?: boolean;
    }

    let { show = $bindable(false) }: Props = $props();

    let isLoading = $state(true);
    let themes : Theme[] = $state([]);

    onMount(() => {

        loadThemes()
            .then(res => {
                themes = res;
            })
            .finally(() => isLoading = false);

    });

    async function handleClick(name: string) {
        if (await confirm({
            title: 'Confirm change',
            content: 'Are you sure you want to change the theme? Any changes you made to the current theme will be lost.',
            confirmText: 'Yes, Change',
        })) {

            show = false;

            const toastId = toast.loading('Changing theme...');

            changeTheme(name)
                .then(files => {
                    toast.success('Theme changed successfully', {id: toastId});
                    setThemeFiles(files);
                })
                .catch(e => toast.error(e.message, {id: toastId}));

        }
    }
    
</script>

<Modal
    bind:show={show}
    footer={{
        cancel: {
            text: 'Close',
        },
        confirm: false,
    }}
    closeOnOutsideClick={false}
    size="small"
>
    {#snippet title()}
        <div  class="title">
            Choose a theme <Button
                as="a"
                href="/themes"
                target="_blank"
                size="small"
            >
                Preview Themes {#snippet end()}
                        <IconBoxArrowUpRight  size={12} />
                    {/snippet}
            </Button>
        </div>
    {/snippet}

    {#if isLoading}
        <Loader block padding={150} />
    {:else}

        <Callout type="warning" style="text-align:initial;margin-bottom:20px;font-size:14px">
            {#snippet icon()}
                        <IconExclamationCircle  size={16} />
                    {/snippet}
            Changing the theme will reset any changes you made to the current theme.
        </Callout>

        <ActionList>
            {#each themes as theme (theme.id)}
                <ActionListItem
                    on:click={() => handleClick(theme.name)}
                >
                    {theme.name}

                    {#snippet end()}
                                        <Text light >
                            v{theme.latest_version}
                        </Text>
                                    {/snippet}
                </ActionListItem>
            {/each}
        </ActionList>


    {/if}
</Modal>

<style>
    .title {
        font-weight: 600;
        text-align: left;
        font-size: 18px;
    }
</style>