@extends('emails/@base')

@section('title', 'Trial Ended')
@section('heading', 'Trial Ended')

@section('content')
    <div>
        <p>
            Hi {{ $user->name }} 👋,
        </p>

        <p>
            The trial of your blog "<b>{{ $blog->subdomain }}</b>" has ended. We hope you have explored our product. If you are satisfied with it, you can upgrade your blog to a paid plan from the Console. See the plans and their features <a href="https://blogs.hyvor.com/pricing" target="_blank" style="text-decoration:underline;color:inherit;">here</a>.
        </p>

        <div style="text-align:center;padding:10px">
            <a
                    style="display:inline-block;padding:10px 20px;background-color:#896c6b;color:white;border-radius:20px;text-decoration: none"
                    href="https://blogs.hyvor.com/console/{{ $blog->subdomain  }}/billing"
            >
                Upgrade Now
            </a>
        </div>

        <p style="font-size: 14px;color:#999;">
            If you have any questions, feel free to reply to this email.
        </p>
    </div>
@endsection