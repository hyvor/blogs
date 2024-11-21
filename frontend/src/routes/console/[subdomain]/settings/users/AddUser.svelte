<script lang="ts">
	import { FormControl, InputGroup, Link, Modal, Radio, SplitControl, TextInput, Validation, toast } from "@hyvor/design/components";
	import type { User, UserRole } from "../../../lib/types";
	import { IconBoxArrowUpRight } from "@hyvor/icons";
	import { createGuestUser, createHyvorUser } from "./userActions";
	import { createEventDispatcher } from "svelte";

    export let show: boolean

    let type: 'hyvor' | 'guest' = 'hyvor';

    let hyvorUsernameOrEmail: string = '';
    let hyvorUsernameOrEmailError: null | string = null;
    let hyvorUsernameOrEmailEl: HTMLInputElement;

    let role: UserRole = 'admin';
    
    let guestName = '';
    let guestNameError: null | string = null;
    let guestNameEl: HTMLInputElement;

    let isLoading = false;

    const dispatch = createEventDispatcher<{add: User}>()

    function handleAdd() {
        type === 'hyvor' ? addHyvor() : addGuest();
    }

    function addHyvor() {
        hyvorUsernameOrEmailError = null;

        if (hyvorUsernameOrEmail.trim() === '') {
            hyvorUsernameOrEmailError = 'Username or email is required';
            hyvorUsernameOrEmailEl.focus();
            return;
        }

        isLoading = true;


        createHyvorUser(hyvorUsernameOrEmail, role)
            .then(res => {
                dispatch('add', res);
                show = false;
                toast.success('User invited successfully');
            })
            .catch(e => {
                toast.error(e.message || 'Failed to invite user');
            })
            .finally(() => {
                isLoading = false;
            })


    }
    
    function addGuest() {
        guestNameError = null;

        if (guestName.trim() === '') {
            guestNameError = 'Name is required';
            guestNameEl.focus();
            return;
        }

        isLoading = true;

        createGuestUser(guestName)
            .then(res => {
                dispatch('add', res);
                show = false;
                toast.success('User added successfully');
            })
            .catch(e => {
                toast.error(e.message || 'Failed to add user');
            })
            .finally(() => {
                isLoading = false;
            })

    }

</script>

<Modal
    bind:show={show}
    title="Add User"
    footer={{
        confirm: {
            text: type === 'hyvor' ? 'Invite User' : 'Add User'
        }
    }}
    loading={isLoading}
    on:confirm={handleAdd}
>

    <SplitControl
        label="User Type"
        caption="Guest users cannot access the console"
    >

        <InputGroup>
            <Radio bind:group={type} value="hyvor">HYVOR</Radio>
            <Radio bind:group={type} value="guest">Guest</Radio>
        </InputGroup>

    </SplitControl>

    {#if type === 'hyvor'}
        <SplitControl 
            label="Username or Email"
            caption="The username or email of the HYVOR user"
        >   
            <FormControl>
                <TextInput 
                    bind:value={hyvorUsernameOrEmail}
                    state={hyvorUsernameOrEmailError ? 'error' : undefined}
                    bind:input={hyvorUsernameOrEmailEl}
                />
                {#if hyvorUsernameOrEmailError}
                    <Validation state="error">
                        {hyvorUsernameOrEmailError}
                    </Validation>
                {/if}
                <div class="signup-note">
                    Ask the user to <Link 
                        href="https://hyvor.com/signup"
                        target="_blank"
                    >signup for HYVOR</Link> if they haven't already.
                </div>
            </FormControl>
        </SplitControl>
        <SplitControl 
            label="Role"
            caption="The role of the user"
        >
            <div class="roles">
                <div>
                    <Radio bind:group={role} value="admin">Admin</Radio>
                    <span>
                        all permissions, except blog deletion
                    </span>
                </div>
                <div>
                    <Radio bind:group={role} value="editor">Editor</Radio>
                    <span>
                        can edit everyone's posts
                    </span>
                </div>
                <div>
                    <Radio bind:group={role} value="writer">Writer</Radio>
                    <span>
                        can create and edit their own posts
                    </span>
                </div>
                <div>
                    <Radio bind:group={role} value="contributor">Contributor</Radio>
                    <span>
                        can write posts, but cannot publish
                    </span>
                </div>
                <div>
                    <Radio bind:group={role} value="finance">Finance</Radio>
                    <span>
                        access to billing only
                    </span>
                </div>
            </div>
            <div class="signup-note">
                Learn more about <Link 
                    href="/docs/users#roles"
                    target="_blank"
                >
                    roles
                    <IconBoxArrowUpRight size={12} slot="end" />
                </Link>
            </div>
        </SplitControl>
    {:else}
        <SplitControl label="Name">
            <FormControl>
                <TextInput 
                    bind:value={guestName}
                    state={guestNameError ? 'error' : undefined}
                    bind:input={guestNameEl}
                />
                {#if guestNameError}
                    <Validation state="error">
                        {guestNameError}
                    </Validation>
                {/if}
            </FormControl>
        </SplitControl>
    {/if}

</Modal>

<style lang="scss">
    .signup-note {
        font-size:14px;
        color:var(--text-light);
        margin-top:10px;
    }

    .roles {
        display: flex;
        flex-direction: column;
        gap: 6px;
        div {
            display: flex;
            align-items: center;
            span {
                flex: 1;
                text-align: right;
                font-size:12px;
                color:var(--text-light);
            }
        }
    }

</style>