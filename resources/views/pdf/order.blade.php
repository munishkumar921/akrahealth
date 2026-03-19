<html>
	<head>
		<style>
			body {
				font-family: Arial, sans-serif;
				font-size: 12px;
				margin: 0;
				padding: 0;
			}
			h2 {
				text-align: center;
				margin: 10px 0;
			}
			p {
				text-align: center;
				margin: 5px 0;
			}
			b.smallcaps {
				font-variant: small-caps;
				font-size: 14px;
			}
			div.outline {
				border: 1px solid #000;
				padding: 5px;
			}
			p.borders {
				border: 1px solid #000;
				padding: 5px;
			}
			table {
				width: 100%;
				border-collapse: collapse;
				margin-bottom: 10px;
				table-layout: fixed;
				page-break-inside: avoid;
			}
			th, td {
				border: 1px solid #000;
				padding: 8px;
				vertical-align: top;
				word-wrap: break-word;
			}
			th {
				background-color: #666;
				color: #FFFFFF;
				font-weight: bold;
				text-align: left;
			}
			hr {
				border: none;
				border-top: 1px solid #000;
				margin: 5px 0;
			}
			.logo-container {
				width: 100px;
				height: 60px;
				display: inline-block;
				vertical-align: top;
			}
			.practice-info {
				text-align: right;
				vertical-align: top;
			}
		</style>
	</head>
	<body>
		<table style="border: none;">
			<tr>
				
				<td style="border: none; width: 70%;  vertical-align: top;">
					<b><?php echo isset($practiceName) ? $practiceName : ''; ?></b><br>
					<?php echo isset($practiceInfo) ? $practiceInfo : ''; ?><br>
					<?php echo isset($website) ? $website : ''; ?>
				</td>
                <td style="border: none; width: 30%; text-align: right; vertical-align: top;">
					<div class="logo-container">
						<?php echo isset($practiceLogo) ? $practiceLogo : ''; ?>
					</div>
				</td>
			</tr>
		</table>
		
		<div style="border-bottom: 1px solid #000000; text-align: center; padding-bottom: 10px; margin: 15px 0;">
			<b class="smallcaps"><?php echo isset($top) ? $top : 'Physician Order'; ?></b>
		</div>
		
		<table>
			<thead>
				<tr>
					<th style="width:50%">PATIENT DEMOGRAPHICS</th>
					<th style="width:50%">GUARANTOR AND INSURANCE INFORMATION</th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td>
						<?php echo isset($patientInfo) ? ($patientInfo->user->name ?? ''): ''; ?><br>
						Date of Birth: <?php echo isset($dob) ? $dob : ''; ?><br>Gender: <?php echo isset($sex) ? $sex : ''; ?><br>
						<?php echo isset($patientInfo->address->street_address_1) ? $patientInfo->street_address_2 : ''; ?><br>
						<?php echo isset($patientInfo->address->city) ? $patientInfo->address->city . ', ' . ($patientInfo->address->state ?? '') . ' ' . ($patientInfo->address->zip ?? '') : ''; ?><br>
						<?php echo isset($patientInfo->address->phone) ? $patientInfo->address->phone : ''; ?><br>
					</td>
					<td>
						<?php echo isset($insuranceInfo) ? $insuranceInfo : 'No insurance information available'; ?>
					</td>
				</tr>
			</tbody>
		</table>
		
		<br>
		
		<table>
			<thead>
				<tr>
					<th style="width:78%"><?php echo isset($title1) ? $title1 : 'PROVIDER'; ?></th>
					<th style="width:22%">DATE</th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td style="width:78%">
						<?php if(isset($address)): ?>
							<?php echo isset($address->displayname) ? $address->displayname : ''; ?><br>
							<?php echo isset($address->street_address1) ? $address->street_address1 : ''; ?><br>
							<?php if (isset($address->street_address2) && $address->street_address2 != ''): ?>
								<?php echo $address->street_address2 . '<br>'; ?>
							<?php endif; ?>
							<?php echo isset($address->city) ? $address->city . ', ' . ($address->state ?? '') . ' ' . ($address->zip ?? '') : ''; ?><br>
							<?php echo isset($address->phone) ? $address->phone : ''; ?><br>
						<?php else: ?>
							Provider information not available
						<?php endif; ?>
					</td>
					<td style="width:22%"><?php echo isset($order_date) ? $order_date : (isset($orders_date) ? $orders_date : date('m/d/Y')); ?></td>
				</tr>
			</tbody>
		</table>
		
		<br>
		
		<table>
			<thead>
				<tr>
					<th style="width:50%">DIAGNOSES</th>
					<th style="width:50%">SIGNATURE</th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td><?php echo isset($dx) ? $dx : 'No diagnoses provided'; ?></td>
					<td>
						<?php echo isset($signature) ? $signature : ''; ?>
						<hr/>
						<div style="text-align: center; font-size: 10px;">
							Physician Signature
						</div>
					</td>
				</tr>
			</tbody>
		</table>
		
		<br>
		
		<table>
			<thead>
				<tr>
					<th style="width:100%"><?php echo isset($title2) ? $title2 : 'ORDER DETAILS'; ?></th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td><?php echo isset($text) ? $text : 'No order details provided'; ?></td>
				</tr>
			</tbody>
		</table>
	</body>
</html>