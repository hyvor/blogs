@include('errors.template', [
    'title' => "Page Not Found",
    'text' => 'Sorry, the page you requested was not found.',
    'buttonText' => 'Go to Home Page',
    'buttonUrl' => '/',
    'imageUrl' => '/img/landing/404.svg'
])