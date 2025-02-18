<!DOCTYPE html>
<html lang="en">
<head>
    <title>@yield('title')</title>
    <style>
        * {
            box-sizing: border-box;
        }
    </style>
</head>
<body style="font-family: 'Segoe UI', Arial, sans-serif; background-color:#fffaf8;margin:0;font-size:16px;">

<div style="text-align:left;padding:20px 0;width:500px;max-width:100%;margin:auto;">
    <img
            src="https://hyvor.com/api/public/logo/blogs.svg"
            style="width:35px;vertical-align:middle;margin-right:10px;border-radius:50%;"
            alt="Hyvor Blogs Logo"
    >
    <span style="font-size:26px;color:black;font-weight:800;vertical-align:middle;">HYVOR BLOGS</span>
</div>

<div style="width:550px;max-width:100%;background-color:white;margin:auto;border-radius:20px;-webkit-border-radius:20px;box-shadow:0 0 30px rgba(0,0,0,0.05);-webkit-box-shadow:0 0 30px rgba(0,0,0,0.05);overflow:hidden;padding:40px">

    <div style="text-align:center;border-bottom: 1px solid #eee;padding-bottom: 25px;">
        <div style="text-align:center;font-size:26px;font-weight:600;">@yield('heading')</div>
    </div>

    <div style="padding:15px 0">
        @yield('content')
    </div>

</div>

<div style="width:650px;max-width:100%;padding:20px;text-align:center;margin:auto;font-size:12px;color:#555">
    <div>Sent By HYVOR on {{date('D \t\h\e jS \o\f M, Y')}}</div>
    <div style="padding-top: 2px">11 Rue Carnot, 94270 Le Kremlin-Bicêtre, France</div>
</div>

</body>
</html>