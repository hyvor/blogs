<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{$title}}</title>
    <style>
        * {
            box-sizing: border-box;
        }
        .middle {
            position:absolute;
            top:50%;
            left:50%;
            transform:translate(-50%,-50%);
            text-align: center;
        }
        body {
            background-color: #fffaf8;
        }
    </style>

    @include('landing.meta')
</head>
<body>

<div class="middle">
    <h1>{{$title}}</h1>

    @if (isset($imageUrl))
        <img src="{{ $imageUrl }}" width="300" />
    @endif
    <div style="padding:30px">
        <p>
            {!! $text !!}
        </p>
        <a href="{{ $buttonUrl }}" class="button" data-flashload-skip-link>{{$buttonText}}</a>
    </div>
</div>

</body>
</html>
