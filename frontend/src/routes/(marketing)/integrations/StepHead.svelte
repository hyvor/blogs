<script lang="ts">
    export let title: string;
    export let number: number;
</script>

<div class="step-head">
<!-- <h2>Step</h2> -->
<div class="number"><h2 class="number-sub">{number}</h2></div>
<div class="line"></div>

</div>
<h2 id="account"> {title}</h2>

<style lang="scss">
    .step-head {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-top: 20px;

        /* styles for {number} and {title} */
        .number {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: var(--accent-light);
            color: var(--accent);
            font-size: 15px;
            

            .number-sub {
                margin: 0;
                background-color: #ac9084;
                width: 30px;
                height: 30px;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
            }

            
        }

        .line {
                /*take full width of step-head */
                flex-grow: 1;
                height: 2px;
                background-color: var(--accent-light);
                margin: 0 10px;
            }

    }
</style>