##  Structure

Folder | Contents
-------|---------
`app` |
`app/Console` | Laravel Console (Terminal) functions. We currently don't use it.
`app/Exceptions` | Laravel Error Handling. Currently used to show a different JSON error response for API endpoints.
`app/Http` | Laravel HTTP folder
`app/Http/Controllers` | Laravel HTTP Controllers. All our controllers resides here seperated by each API
`app/Http/Middleware` | Laravel HTTP Middlewares for controllers.
`app/Http/Middleware/App` | Our middlewares reside here (just to seperate it from Laravel middleware)
`app/Models` | Application database models
`app/Providers` | Laravel and our service providers resides here. Usually, you want to edit `DomainsServiceProvider.php` when adding new repos.
`app/Domains` | All our repos reside here, namespaced by the domains (Blog, User, etc.).
`app/Repositores/{Domain}/Types` | All input and output types of that repository reside here. Our APIs return responses as objects or arrays of objects. The schema of these objects resides in this folder. Think of these objects as interfaces in TypeScript. We do not return Models directly in API responses. All Models go through Types to be converted to an "API-friendly" output. We define each key value pair in a public attribute with a default value (`json_encode` only convert `public` attributes).
`database/migrations` | Database migrations. We only store **table create** statements here - not updates
`database/seeders` | Add seeders here
`resources` | Front-end files
`resources/css` | SCSS files. Converted to CSS (`public/css` folder) when building
`resources/js` | JS/JSX/TS/TSX files. Converted to JS (`public/js` folder)  when building
`resources/views` | Contains some blade files for console and landing pages. Note that console only contains a little code to initiate Single Page app. Most of the UI is built using React.
`routes` | Laravel Routes
`routes/app` | Our rotes. Each API gets a single route file.
`tests` | Tests go here. Yet to be written.

## How it works

Hyvor Blogs is headless, which means back-end and front-end are completely seperated (in 99.9% cases). Most of front-end is written in React (except landing pages). The only thing back-end has is APIs (except views that renders the initial console page, etc.)

Let's see how our APIs are structured. 

All app's main functionalities (business logic) is written inside **Domains** seperated by domains like Post, Blog, etc. Usually, repos get a typed input and returns a typed output. Middlewares and Controllers are the middlemans between the external world and the repos. Middlewares are used for authentications, input sanitizing, etc. Controllers receive user input, call one or more Domains (with typed input), and return the results to the front-end in JSON.

## APIs

### 1. Console API
* Routes: `/routes/app/api-console.php`
* Controllers: `app/Http/Controllers/ConsoleAPI`

The console API handles all the functionalities in the console like post editing, changing blog settings, etc.

### 2. Data API
* Routes: `/routes/app/api-data.php`
* Controllers: `/app/Http/Controllers/DataAPI`

Returns public data of a blog. Can be access via the Http API or within Twig themes to render UIs.

### 3. Delivery API
* Routes: `/routes/app/api-delivery.php`
* Controllers: `app/Http/Controllers/DeliveryAPI`

This API gets two inputs (subdomain and path) and returns a special JSON containing information on how to "deliver" the file to an end-user. This API is internally used by subdomain routes to deliver the blogs. It can also be used by external developers to self-serve a blog in a subdirectory using a custom build reverse proxy.

```json
{
    "path": "/hello-world", // the path you requested (should start with /)
    "content": "Hello World", // text or base64 encoded string (for binaries)
    "content_type": "text/html", // content-type header value
    "code": 200, // http status code
    "is_binary": false, // if this is true, content is a base64 encoded string
}
```

## Other Routes
### 1. Subdomain
* Routes: `/routes/app/subdomain.php`
* Controllers: `app/Http/Controllers/Subdomain`

Serves blogs of the subdomain. These controllers calls the Delivery API and returns output in its real format with correct headers.

### 2. Landing Pages
* Routes: `routes/app/pages.php`

These are landing/marketing pages of the application. Usually, most websites seperate these types of pages from the application, but we keep them for simplicity and we don't usually have a lot of pages - just a few that explains what exactly our service does.