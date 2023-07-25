@include('errors.template', [
    'title' => "Blog Trial Ended",
    'text' => 'Your free trial has ended. Please upgrade to a paid plan to continue using the blog.',
    'buttonText' => 'Upgrade now',
    'buttonUrl' => 'https://blogs.hyvor.com/console/' . $subdomain . '/billing',
])