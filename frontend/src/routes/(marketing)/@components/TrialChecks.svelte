<script>
	import { IconCheckCircleFill } from "@hyvor/icons";
</script>

<div class="checks" {...$$restProps}>

    <div class="check">
        <IconCheckCircleFill />
        7-day free trial
    </div>

    <div class="check">
        <IconCheckCircleFill />
        No credit card required
    </div>

    <div class="check">
        <IconCheckCircleFill />
        Cancel anytime
    </div>

</div>

<style>

    .checks {
    }

    .check {
        display: flex;
        align-items: center;
        gap: 6px;
        margin-bottom:6px;
    }

    .check :global(svg) {
        color: var(--green);
        color: var(--accent);
    }

</style>