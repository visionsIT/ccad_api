<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Laravel</title>
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
        <script>
            $(document).ready(function(){
                var value = readCookie('obligations');
                console.log(value);
                // $.ajax({url: "demo_test.txt", success: function(result){
                //     $("#div1").html(result);
                // }});
            });
            function readCookie(name) {
                var nameEQ = name + "=";
                var ca = document.cookie.split(';');
                for(var i=0;i < ca.length;i++) {
                    var c = ca[i];
                    while (c.charAt(0)==' ') c = c.substring(1,c.length);
                    if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length,c.length);
                }
                return null;
            }
        </script>

    </head>
    <body>
        <div>Welcome to SSO</div>
        <?php
            echo "<pre>"; print_r($_REQUEST);
                print_r($_COOKIE);
        ?>
    </body>
</html>
