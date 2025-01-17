<script lang="ts">
	import { Button, ButtonGroup, FormControl, InputGroup, Modal, Radio, SplitControl, TextInput, Validation, toast } from "@hyvor/design/components";
    import type { Language } from "../../../../lib/types";
	import { createLanguage, updateLanguage } from "./languageActions";
	import { languageStoreAdd, languageStoreUpdate, languagesStore } from "../../../../lib/stores/languagesStore";

    export let language: Language | null = null;
    export let show = false;

    const isCreating = language === null;

    let name = language ? language.name : '';
    let code = language ? language.code : '';
    let direction : 'ltr' | 'rtl' = language ? language.direction : 'ltr';

    let nameError : null | string = null;
    let codeError : null | string = null;

    function handleClick() {

        nameError = null;
        codeError = null;

        if (!name) {
            nameError = 'Name is required.';
            return;
        }

        if (!code) {
            codeError = 'Code is required.';
            return;
        }

        if (isCreating) {

            if ($languagesStore.find(l => l.code === code)) {
                codeError = 'Code is already in use.';
                return;
            }

            show = false;

            const toastId = toast.loading('Creating language...');

            createLanguage(name, code, direction)
                .then(res => {
                    toast.success('Language created.', {id: toastId});
                    languageStoreAdd(res);
                })
                .catch(err => {
                    toast.error(err.message, {id: toastId});
                });

        } else {

            if ($languagesStore.find(l => l.code === code && l.id !== language!.id)) {
                codeError = 'Code is already in use.';
                return;
            }

            show = false;

            const toastId = toast.loading('Updating language...');

            updateLanguage(language!.id, name, code, direction)
                .then(res => {
                    toast.success('Language updated.', {id: toastId});
                    languageStoreUpdate(res);
                    show = false;
                })
                .catch(err => {
                    toast.error(err.message, {id: toastId});
                });

        }

    }

    $: isButtonDisabled = !(isCreating ||
            (name !== language!.name || 
                code !== language!.code || 
                direction !== language!.direction))

</script>

<Modal 
    title={isCreating ? 'Add new language' : 'Edit language'}
    bind:show={show}
>

    <SplitControl
        label="Name"
        caption="The name of the language"
    >

        <FormControl>
            <TextInput 
                bind:value={name}
                placeholder="English"
                block
                state={nameError ? 'error' : undefined}
                autofocus
            />

            {#if nameError}
                <Validation state="error">
                    {nameError}
                </Validation>
            {/if}
        </FormControl>

    </SplitControl>

    <SplitControl
        label="Code"
        caption="A valid HTML language code"
    >
        <FormControl>
            <TextInput 
                bind:value={code}
                placeholder="en-US"
                block
                state={codeError ? 'error' : undefined}
            />

            {#if codeError}
                <Validation state="error">
                    {codeError}
                </Validation>
            {/if}
        </FormControl>
    </SplitControl>

    <SplitControl
        label="Direction"
    >
        <InputGroup>
            <Radio
                name="direction"
                value="ltr"
                bind:group={direction}
            >
                Left to Right (LTR)
            </Radio>
            <Radio
                name="direction"
                value="rtl"
                bind:group={direction}
            >
                Right to Left (RTL)
            </Radio>
        </InputGroup>
    </SplitControl>

    <svelte:fragment slot="footer">

        <ButtonGroup>

            <Button 
                variant="invisible"
                on:click={() => show = false}
            >
                Cancel
            </Button>

            <Button
                on:click={handleClick}
                disabled={isButtonDisabled}
            >
                {isCreating ? 'Add' : 'Save'}
            </Button>

        </ButtonGroup>

    </svelte:fragment>

</Modal>