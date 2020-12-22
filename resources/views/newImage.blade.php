<html>
    <head>
        <style>
            body {
                min-height: 350px;
                margin: 0; 
                padding: 0;
            }
            td {
                margin:0;
                padding:0;
            }
        </style>
    </head>
    <body>
        <table width="640" cellpadding="0" style="font-family: arial; color: #333333; border-collapse: collapse; margin: auto;">
            <tr>
                <td>
                    <table width="100%" style="border-collapse: collapse;">
                        <tr>
                            <td align="center">
                                <?php
                                $prev_img = '/uploaded/e_card_images/'.$image;
                                $prev_img_path = url($prev_img);
                                ?>
                                <table width="100%" style="background-image: url({{$prev_img_path}});background-size: cover;background-repeat: no-repeat;background-position: center; border-collapse: collapse; height: 456px;">
                                    <tr>
                                        <td style="height: 100%; width: 100%; background-color: rgba(0,0,0,0); text-align: left;" valign="top">
                                            <h4 style="color: #000;font-size: 22px;max-width: 440px;margin: 135px auto 0;font-weight: normal;">{{$message}}</h4>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </body>
</html>