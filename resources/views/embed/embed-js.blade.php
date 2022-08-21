window.HYVOR_BLOGS_APP_URL = "{{ config('app.url') }}";
window.HYVOR_BLOGS_EMBED_SUBDOMAIN = "{{ $subdomain }}";
window.HYVOR_BLOGS_PATH_STYLE = {{ isset($pathStyle) && $pathStyle ? 'true' : 'false'  }};
window.HYVOR_BLOGS_PATH = {!! isset($pathStyle) && $pathStyle ? "\"$path\"" : 'null'  !!};
{!!  file_get_contents(resource_path('js/embed/embed.js')) !!}