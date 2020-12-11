<table width="650" cellpadding="0" style="font-family: arial; color: #333333; margin: auto; border: 1px solid #ddd; background-image: url('images/blue-curve.png'); background-size: 76%; background-repeat: no-repeat; box-shadow: 0px 0px 10px rgba(0,0,0,0.05); border-collapse: collapse;">
	<tr>
		<td align="center" style="padding: 40px 0; background-color: #f1f1f1;">
			<img src="{{ $image_url['blue_logo_img_url'] }}" alt="ABU Dhabi Ports" width="230" />
		</td>
	</tr>
	<tr>
		<td>
			<table width="100%" style="border-collapse: collapse; background-color: #f1f1f1;">
				<tr>
					<td align="center">
						<h2 style="font-size:20px;text-transform: uppercase; color: #274a88;margin-bottom: 0;">{{ $data["card_title"] }}</h2>
						<h4 style="font-size:18px;">Hello {{ $data['username'] }},</h4>
			            <h5 style="font-size:16px;">You have received a new Ecard from {{ $data['sendername'] }}.</h5>
					</td>
				</tr>
			</table>
			<table width="100%" style="border-collapse: collapse; background-color: #f1f1f1;">
				<tr>
					<td align="center" style="padding: 40px 20px;">
						<table width="100%" style="background-image: url('{{ $data['image'] }}');background-size: contain;background-repeat: no-repeat;background-position: center; border-collapse: collapse; height: 344px;">
							<tr>
								<td style="height: 100%; width: 100%; background-color: rgba(0,0,0,0); text-align: center;">
									<h4 style="margin: 0;color: #000;font-size: 18px;">{{ $data["image_message"] }}</h4>
								</td>
							</tr>
						</table>
					</td>
				</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td align="center" style="background-color: #2c559f; padding: 10px 0;">
			<img src="{{ $image_url['white_logo_img_url'] }}" alt="Thank You" width="200" />
		</td>
	</tr>
</table>