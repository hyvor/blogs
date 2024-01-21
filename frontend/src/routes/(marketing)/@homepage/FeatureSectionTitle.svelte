<script lang="ts">
    export let title: string;
    export let subtitle: string;
    export let icon: null | string = null;
    export let h2Style: string | undefined = undefined;
</script>

<div class="wrap">

    {#if icon}
        <div class="img-wrap">
            <img src={icon} alt={title} />
        </div>
    {/if}


    <h2 style={h2Style}>{title}</h2>

    <h3>{@html subtitle}</h3>

</div>


<style>
    .wrap {
        margin-top: 100px;
    }
     h2 {
        font-size: 38px;
        font-weight: 700;
        margin-bottom: 10px;
        margin-top: 20px;
        text-align: center;
    }
    h3 {
        font-size: 20px;
        width: 650px;
        margin: 0 auto;
        font-weight: normal;
        line-height: 1.4;
        text-align: center;
    }
    .img-wrap {
        text-align: center;
    }
    img {
        width: 140px;
        height: 140px;
    }

</style>