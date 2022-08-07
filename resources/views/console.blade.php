<!DOCTYPE html>
<html>
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

</body>
</html>