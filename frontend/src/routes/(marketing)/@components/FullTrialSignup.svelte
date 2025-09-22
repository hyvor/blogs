<script lang="ts">
	import { Button } from "@hyvor/design/components";
	import TrialChecks from "./TrialChecks.svelte";
    import Browser from "../@homepage/new-home/Browser.svelte";
    import image from '../@homepage/new-home/img/image.png';

    interface Props {
        title?: string;
        [key: string]: any
    }

    let { title = "Ready to start your fully-customizable, SEO-friendly, and blazing-fast blog?", ...rest }: Props = $props();

</script>


<div class="wrap" {...rest}>

    <div class="hds-container inner">

        <h3>
            {title}
        </h3>

        <div class="button-wrap">
            <Button 
                as="a"
                size="large"
                href="/console?signup"
            >
                Start your blog
            </Button>
        </div>

        <div class="trial-checks">
            <TrialChecks style="display:flex;justify-content:space-around" />
        </div>

    </div>
</div>

<style>

    .wrap {
        background-color: var(--accent-light-mid);
        padding:80px 15px;
        margin: 30px 0;
    }

    .inner {
        width: 700px;
        max-width: 100%;
        margin: auto;
    }

    h3 {
        text-align: center;
        font-size: 30px;
        font-weight: 600;
    }

    .button-wrap {
        padding: 20px;
        text-align: center;
        font-size: 20px;
    }

    .trial-checks {
        margin-top:15px;
        font-size: 20px;
    }

</style>
