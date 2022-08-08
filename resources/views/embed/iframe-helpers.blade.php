<script>
    window.EMBEDDING_URL = "{{ $embeddingUrl }}";
</script>

{{-- Script for communicating --}}
@vite('resources/js/embed/iframe.ts')

{{-- Fix CSS --}}
<style>
    html, body {overflow: hidden}
</style>