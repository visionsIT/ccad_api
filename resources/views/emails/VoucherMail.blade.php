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

				<p style="margin: 22px 0px 24px; font-size: 16px;">Good news! You have received a TAKREEM IPV worth {{$data['points']}} points.</p>

				<p style="margin: 22px 0px 24px; font-size: 16px;">Your code is {{$data['code']}} and it will expire on {{$data['end_datetime']}}</p>

				<p style="margin: 22px 0px 24px; font-size: 16px;">For any assistance, please contact us on customerexperience@meritincentives.com</p>

				<p style="font-size: 16px; margin-bottom: 0px;">Kindly regards,</p>
				<p style="font-size: 16px; margin-top: 0px;"><span style="color: #2C72EC;">Cleveland Clinic Abu Dhabi</span></p>
			</td>
		</tr>
	</table>
</body>
</html>