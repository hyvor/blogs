<script lang="ts">
    let uploadInput: HTMLInputElement;

    function handleUpload() {

    }
</script>


<div class="file-creator">

    <input
        type="file"
        bind:this={uploadInput}
        style="display: none;"
        on:change={handleUpload}
    />

</div>