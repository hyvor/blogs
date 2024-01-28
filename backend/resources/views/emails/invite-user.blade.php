@extends('emails/@base')

@section('title', 'Invitation to join ' . $user->blog->subdomain)
@section('heading', 'Invitation to Join')

@section('content')
    <div>
        <p style="font-weight:600;font-size:20px;">Hi {{ $hyvorUser->name }},</p>

        <p>You are invited to join <b>{{ $user->blog->subdomain }}</b> as {{
            in_array(strtolower($user->role->value[0]), ['a', 'e', 'i', 'o', 'u']) ? "an" : "a"
        }} {{ $user->role->value }}. Click the button below to accept the invitation.</p>

        <div style="text-align:center;padding:10px">
            <a
                style="display:inline-block;padding:10px 20px;background-color:#896c6b;color:white;border-radius:20px;text-decoration: none"
                href="{{ $link  }}"
            >
                Accept Invitation
            </a>
        </div>

        <p>The link expires in 24 hours. If you have any questions, please contact us at <b>blogs.support@hyvor.com</b>.</p>
    </div>
@endsection