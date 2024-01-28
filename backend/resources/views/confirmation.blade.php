<!DOCTYPE html>
<html>
<head>
    <title>{{ $title }}</title>
    <style>
        @import url('https://fonts.bunny.net/css?family=nunito:400,700');
        * {
            box-sizing: border-box;
        }
        body {
            font-family: 'Nunito', sans-serif;
            background:#fffaf8;
            margin:0;
            font-size:14px;
        }

        a {
            color: inherit;
            text-decoration: none;
        }
        .link {
            color: #886c6b;
            font-weight: 600;
            text-decoration: underline;
            cursor: pointer;
        }
        header {
            width: 100%;
            height: 80px;
            padding: 30px;
            font-weight: 700;
            text-align: center;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        img.logo {
            width: 35px;
            height: 35px;
            vertical-align: middle;
            margin-right: 4px;
        }
        header .name {
            vertical-align: middle;
        }
        #confirmation-box {
            margin: 40px auto auto;
            background: #fff;
            box-shadow: 0 0 30px rgb(0 0 0 / 5%);
            padding: 40px;
            border-radius: 20px;
            width:550px;
            max-width: 100%;
        }
        #confirmation-box .confirm-title {
            font-size:20px;
            font-weight: 600;
            margin-bottom: 20px;
            text-align: center;
        }
        #confirmation-box .confirm-title i {
            margin-right:5px;
            vertical-align: middle;
        }
        #confirmation-box .close-button {
            margin-top: 20px;
            text-align: center;
        }
        #confirmation-box .confirm-desc {
            padding: 10px;
            text-align: center;
        }
        .button {
            background:#886c6b;
            color:#fff;
            border-radius: 20px;
            border:none;
            font-weight: 600;
            font-family: inherit;
            cursor: pointer;
            width: 140px;
            height: 36px;
        }
    </style>
</head>
<body>

<header>
    <a href="/">
        <img class="logo" src="/img/logo.png">
        <span class="name">HYVOR</span>
    </a>
</header>

<div id="confirmation-box" class="{{ $type ?? 'normal' }}">
    <div class="confirm-title"><i>
            @if (isset($type) && $type === 'error')
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="#db7474" class="bi bi-x-circle-fill" viewBox="0 0 16 16">
                    <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zM5.354 4.646a.5.5 0 1 0-.708.708L7.293 8l-2.647 2.646a.5.5 0 0 0 .708.708L8 8.707l2.646 2.647a.5.5 0 0 0 .708-.708L8.707 8l2.647-2.646a.5.5 0 0 0-.708-.708L8 7.293 5.354 4.646z"/>
                </svg>
            @else
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="#886c6b" class="bi bi-check-circle-fill" viewBox="0 0 16 16">
                    <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/>
                </svg>
            @endif
        </i>{{ $title }}</div>
    <div class="confirm-desc">{!! $description !!}</div>
    <div class="close-button"><button onclick="window.close()" class="button">Close</button></div>
</div>
</body>
</html>