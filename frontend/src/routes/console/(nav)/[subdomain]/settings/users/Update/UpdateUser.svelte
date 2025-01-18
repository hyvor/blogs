<script lang="ts">
	import { Modal, SplitControl, TextInput, toast } from "@hyvor/design/components";
	import ImageSetting from "../../@components/ImageSetting.svelte";
	import type { User, UserVariant } from "../../../../../lib/types";
	import VariantInput from "../../@components/VariantInput/VariantInput.svelte";
	import UserSlug from "./UserSlug.svelte";
	import { updateUser, updateUserVariant } from "../userActions";
	import { createEventDispatcher } from "svelte";

    interface Props {
        user: User;
        show: boolean;
    }

    let { user, show = $bindable() }: Props = $props();

    let pictureUrl: string | null = $state(user.picture_url);
    let slug = $state(user.slug);
    let email = $state(user.email);
    let websiteUrl = $state(user.website_url);

    let socialFacebook = $state(user.social_facebook);
    let socialTwitter = $state(user.social_twitter);
    let socialLinkedin = $state(user.social_linkedin);
    let socialYoutube = $state(user.social_youtube);
    let socialTiktok = $state(user.social_tiktok);
    let socialInstagram = $state(user.social_instagram);
    let socialGithub = $state(user.social_github);

    const variantChanges : Record<number, Partial<UserVariant>> = {};

    function handleVariantChange(key: keyof UserVariant, e: CustomEvent<{languageId: number, value: string}>) {
        variantChanges[e.detail.languageId] = {
            ...(variantChanges[e.detail.languageId] || {}),
            [key]: e.detail.value,
        }
    }

    let isUpdating = $state(false);

    const dispatch = createEventDispatcher<{update: User}>();

    async function handleUpdate() {

        isUpdating = true;

        if (Object.keys(variantChanges).length > 0) {
            for (const [languageId, changes] of Object.entries(variantChanges)) {
                try {
                    await updateUserVariant(user.id, Number(languageId), changes);
                } catch (e: any) {
                    toast.error(e.message);
                    isUpdating = false;
                    return;
                }
            }
        }

        const updates : Partial<User> = {};

        if (pictureUrl !== user.picture_url) {
            updates.picture_url = pictureUrl;
        }
        if (slug !== user.slug) {
            updates.slug = slug;
        }
        if (email !== user.email) {
            updates.email = email;
        }
        if (websiteUrl !== (user.website_url || '')) {
            updates.website_url = websiteUrl;
        }

        if (socialFacebook !== (user.social_facebook || '')) {
            updates.social_facebook = socialFacebook;
        }
        if (socialTwitter !== (user.social_twitter || '')) {
            updates.social_twitter = socialTwitter;
        }
        if (socialLinkedin !== (user.social_linkedin || '')) {
            updates.social_linkedin = socialLinkedin;
        }
        if (socialYoutube !== (user.social_youtube || '')) {
            updates.social_youtube = socialYoutube;
        }
        if (socialTiktok !== (user.social_tiktok || '')) {
            updates.social_tiktok = socialTiktok;
        }
        if (socialInstagram !== (user.social_instagram || '')) {
            updates.social_instagram = socialInstagram;
        }
        if (socialGithub !== (user.social_github || '')) {
            updates.social_github = socialGithub;
        }


        updateUser(user.id, updates)
            .then(res => {
                toast.success('User updated');
                show = false;
                dispatch('update', res);
            })
            .catch(e => {
                toast.error(e.message);
            })
            .finally(() => {
                isUpdating = false;
            });

        isUpdating = false;

    }

</script>

<Modal
    title="Update User"
    bind:show={show}
    closeOnOutsideClick={false}
    closeOnEscape={false}
    footer={{
        confirm: {
            text: 'Update',
        }
    }}
    on:confirm={handleUpdate}
    loading={isUpdating}
>

    <SplitControl
        label="Profile Picture"
        caption="Recommended to use a square image"
    >
        <ImageSetting 
            src={pictureUrl} 
            on:change={e => pictureUrl = e.detail}
        />
    </SplitControl>
    
    <UserSlug 
        id={user.id}
        bind:slug={slug}
        slugOriginal={user.slug}
    />

    <SplitControl
        label="Email"
        caption="User's email address"
    >
        <TextInput 
            bind:value={email}
            block
        />
    </SplitControl>

    <VariantInput
        label="Name"
        caption="User's name"
        type="user"
        obj={user}
        key="name"
        maxlength={160}
        on:variantCreate
        on:change={e => handleVariantChange('name', e)}
    />

    <VariantInput
        obj={user}
        type="user"
        key="bio"
        label="Bio"
        maxlength={255}
        caption="User's bio"
        on:variantCreate
        on:change={e => handleVariantChange('bio', e)}
    />

    <VariantInput
        obj={user}
        type="user"
        key="location"
        label="Location"
        caption="User's location"
        maxlength={255}
        on:variantCreate
        on:change={e => handleVariantChange('location', e)}
    />

    <SplitControl
        label="Website URL"
        caption="User's website URL"
    >
        <TextInput 
            bind:value={websiteUrl}
            block
            maxlength={255}
        />
    </SplitControl>

    <SplitControl
        label="Social Links"
        caption="Add absolute URLs with protocol (https://)"
    >

        {#snippet nested()}
                <div >

                <SplitControl
                    label="Facebook"
                >
                    <TextInput 
                        block
                        bind:value={socialFacebook}
                        maxlength={255}
                    />
                </SplitControl>

                <SplitControl
                    label="Twitter (X)"
                >
                    <TextInput 
                        block
                        bind:value={socialTwitter}
                        maxlength={255}
                    />
                </SplitControl>

                <!-- Linkedin -->
                <SplitControl
                    label="Linkedin"
                >
                    <TextInput 
                        block
                        bind:value={socialLinkedin}
                        maxlength={255}
                    />
                </SplitControl>

                <!-- Youtube -->
                <SplitControl
                    label="Youtube"
                >
                    <TextInput 
                        block
                        bind:value={socialYoutube}
                        maxlength={255}
                    />
                </SplitControl>

                <!-- TikTok -->
                <SplitControl
                    label="TikTok"
                >
                    <TextInput 
                        block
                        bind:value={socialTiktok}
                        maxlength={255}
                    />
                </SplitControl>
               
                <!-- Instagram -->
                <SplitControl
                    label="Instagram"
                >
                    <TextInput 
                        block
                        bind:value={socialInstagram}
                        maxlength={255}
                    />
                </SplitControl>

                <!-- Github -->
                <SplitControl
                    label="Github"
                >
                    <TextInput 
                        block
                        bind:value={socialGithub}
                        maxlength={255}
                    />
                </SplitControl>

            </div>
            {/snippet}

    </SplitControl>


</Modal>