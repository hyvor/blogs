@include('errors.template', [
    'title' => "Server Error",
    'text' => 'Seems like there are some issues with our servers. We are working to fix them as soon as possible. Please try again in a moment. Visit our <a class="link" href="https://status.hyvor.com">Status Page</a> for more updates.',
    'buttonText' => 'Refresh Page',
    'buttonUrl' => '',
    'imageUrl' => '/img/landing/500.svg'
])