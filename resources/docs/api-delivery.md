# Delivery API

Delivery API tells you information about how to "serve a request". This API is the backbone of [self-hosting on a subdirectory](self-hosting).

**API Endpoint**: `https://blogs.hyvor.com/api/delivery/v0/{subdomain}`

Replace `{subdomain}` with the subdomain of your blog. Only HTTP `GET` method is supported.

## Request {#request}

The Delivery API only supports two query parameters:

* `api_key` - (string) Create a Delivery API key at **Console &rarr; Settings &rarr; API Keys**
* `path` - (string) A path within your blog. The delivery API will tell you how to serve a response for this path

## Response {#response}

A success response of the Delivery API is always an object of one of the following types.

### 1. File 

```json
{
    "type": "file",
    "at": 1661590503,
    "cache": true,
    "status": 200,
    "file_type": "template",
    "content": "SGVsbG8gV29ybGQ=",
    "mime_type": "text/html"
}
```

### 2. Redirect

```json
{
    "type": "redirect",
    "at": 1661590503,
    "cache": true,
    "status": 301,
    "to": "https://example.com"
}
```

Common:

| Key | Type      | Description |
| --- |-----------| --- |
| `type` | `string`  | `file` or `redirect`
| `at` | `integer` | UNIX timestamp when the object was created
| `cache` | `boolean` | Whether the response object should be cached. `false` for post preview [routes](routes).
| `status` | `integer` | HTTP Status. Can be `200`, `301`, or `302`, or `404`

File:

| Key | Type      | Description |
| --- |-----------| --- |
| `file_type` | `string` | `template`, `asset`, or `media`
| `content` | `string`  | Base-64 encoded content of the file
| `mime_type` | `string`  | HTTP Mime Type of the file (For Content-Type header)

Redirect:

| Key | Type      | Description |
| --- |-----------| --- |
| `to` | `string`  | URL of the redirect