<section class="block padded bg-white page-content" id="payment">
	<div class="container-fluid container-fluid-stretch">
		<div class="row">
			<div class="col-12">

				<?php 
				//var_dump($_GET);
				?>

				<?php 
				
				$mode = 'TEST';
				$endpoint = 'https://pilot-payflowpro.paypal.com';
				$endpoint2 = 'https://pilot-payflowlink.paypal.com';

				$mode = 'LIVE';
				$endpoint = 'https://payflowpro.paypal.com';
				$endpoint2 = 'https://payflowlink.paypal.com';

				$partner = 'PayPal';
				$vendor = 'avenuepvd';
				$user = 'workshop';
				$pwd = 'Cmi!!2012';
				$trxtype = 'S';
				$createsecuretoken = 'Y';
				$securetokenid = uniqid('', true);


				$amt = $_GET['amt'];
				$email = $_GET['email'];
				$firstname = $_GET['firstname'];
				$lastname = $_GET['lastname'];
				$address1 = $_GET['address1'];								
				$address2 = $_GET['address2'];
				$city = $_GET['city'];
				$state = $_GET['state'];
				$zip = $_GET['zip'];
				$phone = $_GET['phone'];

				$postData = 
				'USER=' . $user .
				'&PARTNER=' . $partner .
				'&VENDOR=' . $vendor .
				'&PWD=' . $pwd .
				'&TRXTYPE=' . $trxtype .
				'&AMT=' . $amt .
				'&CREATESECURETOKEN=' . $createsecuretoken .
				'&SECURETOKENID=' . $securetokenid . 
				'&BILLTOFIRSTNAME=' . $firstname .
				'&BILLTOLASTNAME=' . $lastname .
				'&BILLTOSTREET=' . $address1 .
				'&BILLTOCITY=' . $city .
				'&BILLTOSTATE=' . $state .
				'&BILLTOZIP=' . $zip .
				'&EMAIL=' . $email .
				'&PHONENUM=' . $phone
				;

				// echo $postData;
				// echo '<br>';
				// echo '<br>';

				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, $endpoint);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
				curl_setopt($ch, CURLOPT_POST, TRUE);
				curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);

				$resp = curl_exec($ch);

				//var_dump($resp);
				//echo '<br>';
				//echo '<br>';

				if( $resp ){
					parse_str($resp, $arr);
					// echo $arr['SECURETOKEN'];
					// echo '<br>';
					// echo '<br>';
				}

				?>

				<iframe src="<?php echo $endpoint2; ?>?MODE=<?php $mode; ?>&SECURETOKENID=<?php echo $securetokenid ?>&SECURETOKEN=<?php echo $arr['SECURETOKEN']; ?>&TEMPLATE=MOBILE"
					name="test_iframe" scrolling="no"  id="paypal-iframe"></iframe>

				</div>
			</div>
		</div>
	</section>