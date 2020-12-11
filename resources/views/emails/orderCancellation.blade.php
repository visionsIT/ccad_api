<table width="650" cellpadding="0" style="font-family: arial; color: #333333; margin: auto; border: 1px solid #ddd; background-image: url({{ $image_url['blue_curve_img_url'] }}); background-size: 76%; background-repeat: no-repeat; box-shadow: 0px 0px 10px rgba(0,0,0,0.05); border-collapse: collapse;">
	<tr>
		<td align="center" style="padding: 40px 0; ">
        <img src="{{ $image_url['blue_logo_img_url'] }}" alt="ABU Dhabi Ports" width="230" />
		</td>
	</tr>
	<tr>
		<td align="center" style="padding: 40px 20px; line-height: 1.6;">
			<img src="{{ $image_url['smile_img_url'] }}" alt="Thank You" width="100" />
			<h2 style="text-transform: uppercase; color: #274a88;">Order Cancellation!</h2>
            <h4>Hello {{ $data['username'] }},</h4>
            <h6>We have to cancel your order because the product is out of stock.</h6>
            <div>
                <div><b>Product Name: </b> {{ $data['product_name'] }}</div>
                <div><b>Value: </b> {{ $data['value'] }}</div>
            </div>
		</td>
	</tr>
	<tr>
		<td align="center" style="background-color: #2c559f; padding: 10px 0;">
			<img src="{{ $image_url['white_logo_img_url'] }}" alt="Thank You" width="200" />
		</td>
	</tr>
</table>
