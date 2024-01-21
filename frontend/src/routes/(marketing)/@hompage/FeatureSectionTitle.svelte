<script lang="ts">
    export let title: string;
    export let subtitle: string;
</script>

<h2>{title}</h2>
<h3>{subtitle}</h3>

<style>
     h2 {
        font-size: 38px;
        font-weight: 700;
        margin-bottom: 10px;
        margin-top: 40px;
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
</style>