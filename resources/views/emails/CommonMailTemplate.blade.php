<!DOCTYPE html>
<html>
<head>
	<title></title>
</head>

<body>
	<table width="600" cellpadding="2" style="font-family: arial; border: 1px solid #ddd; border-collapse: collapse;margin:30px auto 0px; ">
		<tr>
			<td align="left" valign="middle" style="padding: 20px;">
				{!! $data['content'] !!}
			</td>
		</tr>
		<tr>
			<td align="left" valign="middle">
				<img src="{{ $image_url['blue_logo_img_url'] }}" width="auto" style="vertical-align: middle;" />
			</td>
		</tr>
	</table>
</body>
</html>