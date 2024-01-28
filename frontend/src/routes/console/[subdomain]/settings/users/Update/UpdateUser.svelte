<script lang="ts">
	import { Modal, SplitControl, TextInput, toast } from "@hyvor/design/components";
	import ImageSetting from "../../@components/ImageSetting.svelte";
	import type { User, UserVariant } from "../../../../lib/types";
	import VariantInput from "../../@components/VariantInput/VariantInput.svelte";
	import UserSlug from "./UserSlug.svelte";
	import { updateUser, updateUserVariant } from "../userActions";
	import { createEventDispatcher } from "svelte";

    export let user: User;
    export let show: boolean;

    let pictureUrl: string | null = user.picture_url;
    let slug = user.slug;
    let email = user.email;
    let websiteUrl = user.website_url;

    let socialFacebook = user.social_facebook;
    let socialTwitter = user.social_twitter;
    let socialLinkedin = user.social_linkedin;
    let socialYoutube = user.social_youtube;
    let socialTiktok = user.social_tiktok;
    let socialInstagram = user.social_instagram;
    let socialGithub = user.social_github;

    const variantChanges : Record<number, Partial<UserVariant>> = {};

    function handleVariantChange(key: keyof UserVariant, e: CustomEvent<{languageId: number, value: string}>) {
        variantChanges[e.detail.languageId] = {
            ...(variantChanges[e.detail.languageId] || {}),
            [key]: e.detail.value,
        }
    }

    let isUpdating = false;

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
        />
    </SplitControl>

    <SplitControl
        label="Social Links"
        caption="Add absolute URLs with protocol (https://)"
    >

        <div slot="nested">

            <SplitControl
                label="Facebook"
            >
                <TextInput 
                    block
                    bind:value={socialFacebook}
                />
            </SplitControl>

            <SplitControl
                label="Twitter (X)"
            >
                <TextInput 
                    block
                    bind:value={socialTwitter}
                />
            </SplitControl>

            <!-- Linkedin -->
            <SplitControl
                label="Linkedin"
            >
                <TextInput 
                    block
                    bind:value={socialLinkedin}
                />
            </SplitControl>

            <!-- Youtube -->
            <SplitControl
                label="Youtube"
            >
                <TextInput 
                    block
                    bind:value={socialYoutube}
                />
            </SplitControl>

            <!-- TikTok -->
            <SplitControl
                label="TikTok"
            >
                <TextInput 
                    block
                    bind:value={socialTiktok}
                />
            </SplitControl>
           
            <!-- Instagram -->
            <SplitControl
                label="Instagram"
            >
                <TextInput 
                    block
                    bind:value={socialInstagram}
                />
            </SplitControl>

            <!-- Github -->
            <SplitControl
                label="Github"
            >
                <TextInput 
                    block
                    bind:value={socialGithub}
                />
            </SplitControl>

        </div>

    </SplitControl>


</Modal>