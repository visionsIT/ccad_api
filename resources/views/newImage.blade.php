<html>
    <head>
    </head>
    <body style="margin: 0px;">
        <div style="position: relative;">
            <?php
                $prev_img = '/uploaded/e_card_images/'.$image;
                $prev_img_path = url($prev_img);

                $count = strlen($message);
                if($count >= '250'){
                    $fontpx = '16px';
                }else{
                    $fontpx = '22px';
                }
            ?>
            <img src="{{$prev_img_path}}" />
            <h4 style="color: #000;font-size: {{$fontpx}};max-width: 475px;width: 100%;font-weight: normal;margin: auto;padding: 15px;min-height: 250px;position: absolute;top: 110px;left: 80px;">{{$message}}</h4>
        </div>
    </body>
</html>
