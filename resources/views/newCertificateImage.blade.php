<html>
    <head>
	<style>
		.bottom-left {
		  position: absolute;
		  bottom: 31%;
		  left: 5%;
		  color: white;
		}
		.top-left {
			position: absolute;
			top: 40%;
			left: 5%;
			color: white;
		}
	</style>
    </head>
    <body style="margin: 0px;">
        <div style="position: relative;">
            <?php
                $prev_img = '/uploaded/certificate_images/'.$image;
                $prev_img_path = url($prev_img);
            ?>
            <img src="{{$prev_img_path}}" />
			<div class="bottom-left">
				<?php
				if(strlen($core_value) > 25){
					$subStr = str_split($core_value, 25);

					$coreVal = implode('<br>', $subStr);
					echo '<span style="margin-left: 5px;">'.$coreVal.'</span>';
				} else {
					echo '<span style="margin-left: 5px;">'.$core_value.'</span>';
				} ?>
			</div>
			<div class="top-left">
				<span style="margin-left: 5px;">{{$presented_to}}</span>
			</div>
			<div class="">
				<h4 style="color: #000;font-size: 14px;max-width: 475px;width: 100%;font-weight: normal;margin: auto;padding: 15px;min-height: 250px;position: absolute;top: 158px;left: 290px;text-align: justify;">{{$message}}</h4>
			</div>
		</div>
    </body>
</html>
