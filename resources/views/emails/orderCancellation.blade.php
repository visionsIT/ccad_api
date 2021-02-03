<table width="600" cellpadding="2" style="font-family: arial; border: 1px solid #ddd; border-collapse: collapse;">
	<tr>
		<td align="left" valign="middle">
			<img src="{{ $image_url['blue_logo_img_url'] }}" alt="CCAD" width="100%" style="vertical-align: middle;" />
		</td>
	</tr>
	<tr>
		<td align="center" style="padding: 40px 20px; line-height: 1.6;">
			<img src="{{ $image_url['smile_img_url'] }}" alt="Thank You" width="100" />
			<h2 style="text-transform: uppercase; color: #2C72EC;">Order Cancellation!</h2>
			<h4>Hello {{ $data['username'] }},</h4>
			<h6>We have to cancel your order because the product is out of stock.</h6>
			<div>
				<div><b>Product Name: </b> {{ $data['product_name'] }}</div>
				<div><b>Value: </b> {{ $data['value'] }}</div>
				<div><b>Quantity: </b> {{ $data['quantity'] }}</div>
			</div>
		</td>
	</tr>
</table>