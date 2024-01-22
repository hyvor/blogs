<script lang="ts">
    export let name: string;
    export let price: number;
</script>

<div class="plan hds-box">
    <div class="name">{name}</div>
    <div class="price-wrap">
        <span class="price">${price}</span><span class="freq">/month</span>
    </div>
</div>

<style>

    .plan {
        flex: 1;
        margin-left: 10px;
        padding: 15px 25px;
        min-width: 0;
    }

    .name {
        font-weight: 600;
        font-size: 18px;
    }

    .price-wrap {
        margin-top: 4px;
    }

    .price {
        font-weight: 600;
        font-size: 28px;
    }

    .freq {
        color: var(--text-light);
    }

</style>