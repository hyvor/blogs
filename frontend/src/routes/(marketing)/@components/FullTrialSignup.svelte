<script>
	import { Button } from "@hyvor/design/components";
	import TrialChecks from "./TrialChecks.svelte";

</script>


<div class="wrap" {...$$restProps}>

    <div class="hds-container inner">

        <h3>
            Ready to start your fully-customizable, SEO-friendly, and blazing-fast blog?
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
        padding:80px;
        margin: 30px 0;
    }

    .inner {
        width: 700px;
        max-width: 100%;
        margin: auto;
    }

    h3 {
        text-align: center;
        font-size: 25px;
        font-weight: 600;
    }

    .button-wrap {
        padding: 20px;
        text-align: center;
    }

    .trial-checks {
        margin-top:15px;
    }

</style>