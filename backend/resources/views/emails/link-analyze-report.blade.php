@extends('emails/@base')

@section('title', $title)
@section('heading', 'Link Analysis Report')

@php
    $linkAnalysisUrl = "https://blogs.hyvor.com/console/$blog->subdomain/tools/link-analysis";
@endphp

@section('content')
    <div>
        <p>
            Hi 👋,
        </p>

        <p>
            Here's the link analysis report for <b>{{ $blog->urlWithoutProtocol() }}</b>.
        </p>

        <div style="width: 250px;margin: auto;">

            <div style="padding:5px;display:flex;">
                <span style="flex:1;">Total</span>
                <span style="font-weight:600">{{ $analyzer->linksCount  }}</span>
            </div>
            <div style="padding:5px;display:flex;">
                <span style="flex:1;">OK</span>
                <span style="font-weight:600;color:#5d995d">{{ $analyzer->linksOkCount  }}</span>
            </div>
            <div style="padding:5px;display:flex;">
                <span style="flex:1;">Broken</span>
                <span style="font-weight:600;color:#db7474">{{ $analyzer->linksBrokenCount  }}</span>
            </div>
            <div style="padding:5px;display:flex;">
                <span style="flex:1;">Risky</span>
                <span style="font-weight:600;color:#f1c40f">{{ $analyzer->linksRiskyCount  }}</span>
            </div>
            <div style="padding:5px;display:flex;">
                <span style="flex:1;">Redirect</span>
                <span style="font-weight:600;color:#5875b9">{{ $analyzer->linksRedirectCount  }}</span>
            </div>
            <div style="padding:5px;display:flex;">
                <span style="flex:1;">Ignored</span>
                <span style="font-weight:600;color:#999">{{ $analyzer->linksIgnoredCount  }}</span>
            </div>

        </div>

        <p>
            We analyzed <b>{{ $analyzer->postsCount }}</b> posts on your blog.
        </p>

        <div style="text-align:center;padding:10px">
            <a
                    style="display:inline-block;padding:10px 20px;background-color:#896c6b;color:white;border-radius:20px;text-decoration: none"
                    href="{{ $linkAnalysisUrl }}"
            >
                View Links
            </a>
        </div>

        <p style="font-size: 14px;color:#999;">
            You can disable email reports in <a
                    href="{{ $linkAnalysisUrl }}"
            >Link Analysis</a> &rarr; Settings.
        </p>
    </div>
@endsection