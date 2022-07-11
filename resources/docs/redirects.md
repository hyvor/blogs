# Redirects

Redirects allow you to redirect a visitor from one path within your blog to another path or an external URL.

Redirects: **Console &rarr; Settings &rarr; Redirects**.

A redirect has three attributes.

| Attribute | Description                                                                                                |
|-----------|------------------------------------------------------------------------------------------------------------|
| From      | The path to match. For example, `/old-slug`. Currently, we do not support wildcards or regex in the match. |
| To        | Where to redirect. Use an absolute URL here.                                                               |
| Type      | Permanent (HTTP 301) or Temporary (HTTP 302). It determines the [HTTP response code](https://developer.mozilla.org/en-US/docs/Web/HTTP/Status#redirection_messages).