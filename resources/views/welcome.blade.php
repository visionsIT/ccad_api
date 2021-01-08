<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Laravel</title>

    </head>
    <body>
        <div>Welcome to SSO</div>
        <?php
            echo "<pre>"; print_r($_REQUEST);
                print_r($_COOKIE);
        ?>
    </body>
</html>
