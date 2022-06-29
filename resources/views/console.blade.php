<!DOCTYPE html>
<html>
<head>
    <title>Console - Hyvor Blogs</title>
    <meta name="robots" content="nofollow, noindex">

    <link rel="stylesheet" href="/css/console.css" />
    <link rel="stylesheet" href="/js/console.css" />

    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>
    <div id="app"></div>

    @paddleJS

    <script>
        var appConfig = <?php echo json_encode($config); ?>;
    </script>

    <script src="/js/console.js"></script>

</body>
</html>