<table width="600" cellpadding="2" style="font-family: arial; border: 1px solid #ddd; border-collapse: collapse;">
	<tr>
		<td align="left" valign="middle">
			<img src="{{ $image_url['blue_logo_img_url'] }}" alt="CCAD" width="100%" style="vertical-align: middle;" />
		</td>
	</tr>
	<tr>
		<td align="center" style="padding: 40px 20px; line-height: 1.6;">
			<img src="{{ $image_url['smile_img_url'] }}" alt="Thank You" width="100" />
			<h2 style="text-transform: uppercase; color: #2C72EC;">Feedback Message</h2>
			<h4>{{ $data['feedback'] }}</h4>
			<h5 style="margin-bottom: 0; "><strong>Sent by:</strong> {{$data['email']}}</h5>
		</td>
	</tr>
</table>