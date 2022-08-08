<script>
    window.HYVOR_BLOGS_APP_URL = "{{ $domain }}";
    window.HYVOR_BLOGS_EMBED_SUBDOMAIN = "{{ $subdomain }}";
    {!!  file_get_contents(resource_path('js/embed/embed.js')) !!}
</script>