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
				<h4 style="font-size:20px;margin: 10px 0px 20px;">Hello {{ $data['name'] }},</h4>

				<p style="margin: 22px 0px 24px; font-size: 16px;">Your account has been blocked due to many wrong login attempts.</p>

				<p style="margin: 22px 0px 24px; font-size: 16px;">Please contact us at <a href="mailto:info@meriteincentives.com">info@meriteincentives.com</a> to unblock your account.</p>

				<p style="font-size: 16px; margin-bottom: 0px;">Kind regards,</p>
				<p style="font-size: 16px; margin-top: 0px;"><span style="color: #2C72EC;">Cleveland Clinic Abu Dhabi</span></p>
			</td>
		</tr>
	</table>
</body>
</html>