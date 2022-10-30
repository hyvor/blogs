<!DOCTYPE html>
<html lang="en">
<head>
    <title>Console - Hyvor Blogs</title>
    <meta name="robots" content="nofollow, noindex">

    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>
    <div id="app"></div>

    <script>
        var appConfig = <?php echo json_encode($config); ?>;
    </script>

    @viteReactRefresh
    @vite('resources/js/console/console.tsx')

    <script>
        function setUpPaddle() {
            @if (config('services.paddle.sandbox'))
                Paddle.Environment.set('sandbox');
            @endif
            Paddle.Setup({
                vendor: {{ config('services.paddle.vendor_id')  }}
            });
        }
    </script>
    <script async src="https://cdn.paddle.com/paddle/paddle.js" onload="setUpPaddle()"></script>

    @include('shared.chat')
    @include('shared.tracking')

</body>
</html>