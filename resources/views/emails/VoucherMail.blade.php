<!DOCTYPE html>
<html>
<head>
	<title></title>
</head>
<body>
	<table width="600" cellpadding="2" style="font-family: arial; border: 1px solid #ddd; border-collapse: collapse;margin:30px auto 0px; ">
		<tr>
			<td align="left" valign="middle">
				<img src="{{ $image_url['banner_img_url'] }}" width="100%" style="vertical-align: middle;" />
			</td>
		</tr>
		<tr>
			<td align="left" valign="middle" style="padding: 20px;">
				<h6 style="font-size:20px;margin: 10px 0px 20px;">Dear {{ $data['name'] }},</h6>

				<p style="margin: 22px 0px 24px; font-size: 16px;">You have earned {{$data['points']}} amount of points.</p>

				<p style="margin: 22px 0px 24px; font-size: 16px;">To redeem them go to <a href="https://ccad.takreem.ae">ccad.takreem.ae</a> and use this this voucher code - <b>{{$data['code']}}</b></p>

				<p style="font-size: 16px; margin-bottom: 0px;">Kindly regards,</p>
				<p style="font-size: 16px; margin-top: 0px;"><span style="color: #2C72EC;">Cleveland Clinic Abu Dhabi</span></p>
			</td>
		</tr>
	</table>
</body>
</html>