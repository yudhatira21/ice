<?php
include 'curl.php';


echo "Your referral code : ";
$reffcode = trim(fgets(STDIN));

while(true) {
	register($reffcode);
}


function register($reffcode) {
$date = date('Y-md');
$tgl = explode('-', $date);
$fake_name = curl('https://fakenametool.net/generator/random/id_ID/indonesia', 'GET', null, null, false);
preg_match_all('/<td>(.*?)<\/td>/s', $fake_name, $result);
$name = $result[1][0];
$secmail = curl('https://www.1secmail.com/api/v1/?action=getDomainList', 'GET', null, null, false);
$domain = json_decode($secmail);
$rand = array_rand($domain);
$email = str_replace(' ', '', strtolower($name)).'@'.$domain[$rand];
$password = '@'.random(8);
$user = explode('@', $email);
$ex_name = explode(' ', $name);
$device_id = random(16);

$headers = [

	'Host: w.api.ice.io',
	'accept: application/json, text/plain, /',
	'mobile-app-version: android - 1.6.0',
	'x-language: id',
	'content-type: application/json',
	'user-agent: okhttp/4.9.2',

];



echo "\n[*] Try to register\n";
$register = curl('https://w.api.ice.io/v1w/auth/sendSignInLinkToEmail', 'POST', '{"email":"'.$email.'","deviceUniqueId":"'.$device_id.'","language":"id"}', $headers, false);
$json_register = json_decode($register, true);
$decode = base64_decode($register);
$confirmation_code = fetch_value($decode, '"confirmationCode":"','"');
echo "[*] Get verification link\n";
sleep(5);
$msg = curl('https://www.1secmail.com/api/v1/?action=getMessages&login='.$user[0].'&domain='.$user[1], 'GET', null, false, false);
$id_msg = fetch_value($msg, '"id":',',"from"');

if ($id_msg !== "") {
	$read = curl('https://www.1secmail.com/api/v1/?action=readMessage&login='.$user[0].'&domain='.$user[1].'&id='.$id_msg, 'GET', null, false, false);
	$json_read = json_decode($read, true);

	$link_verif = fetch_value($json_read['body'], '<a href="','" target="_blank">Masuk ke ice</a>');

	if ($link_verif !== "") {
		$confirmation_link = curl($link_verif, 'GET');
		$token = fetch_value($confirmation_link, '<a href="https://app.ice.io/auth-code?token=','&amp;lang=id">Found</a>.');

		$header = [
			"authority: w.api.ice.io",
			"accept: application/json, text/plain, */*",
			"accept-language: en-US,en;q=0.9",
			"authorization: Bearer null",
			"content-type: application/json",
			"origin: https://app.ice.io",
			"referer: https://app.ice.io/",
			"user-agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/115.0.0.0 Safari/537.36 OPR/101.0.0.0"
		];


		$verif_confirmation = curl('https://w.api.ice.io/v1w/auth/signInWithEmailLink', 'POST', '{"emailToken":"'.$token.'","confirmationCode":"'.$confirmation_code.'","lang":"id"}', $header);


		if (stripos($verif_confirmation, '200')) {
			echo "[*] Success to verification code\n";
			echo "[*] Try to get access token\n";

			$getConfirmationStatus = curl('https://w.api.ice.io/v1w/auth/getConfirmationStatus', 'POST', '{"loginSession":"'.$json_register['loginSession'].'"}', $headers, false);
			
			$json_confirmation = json_decode($getConfirmationStatus, true);

			if ($json_confirmation['accessToken'] !== "") {
				echo "[*] Try to getMetadata\n";
				$headers_auth = [
					'Host: w.api.ice.io',
					'accept: application/json, text/plain, /',
					'content-type: application/x-www-form-urlencoded',
					'mobile-app-version: android - 1.6.0',
					'authorization: Bearer '.$json_confirmation['accessToken'],
					'x-language: id',
					'user-agent: okhttp/4.9.2'
				];

				$getMetadata = curl('https://w.api.ice.io/v1w/auth/getMetadata', 'POST', ' ', $headers_auth, false);
				$json_metadata = json_decode($getMetadata, true);


				if ($json_metadata['metadata'] !== "") {

					$headers_login = [
						'Host: w.api.ice.io',
						'accept: application/json, text/plain, /',
						'mobile-app-version: android - 1.6.0',
						'authorization: Bearer '.$json_confirmation['accessToken'],
						'x-language: id',
						'x-account-metadata: '.$json_metadata['metadata'],
						'content-type: application/json',
						'user-agent: okhttp/4.9.2',
					];

					$headers_bonus = array(
					    'Host: w.api.ice.io',
					    'accept: application/json, text/plain, /',
					    'mobile-app-version: android - 1.6.0',
					    'authorization: Bearer '.$json_confirmation['accessToken'],
					    'x-language: id',
					    'x-account-metadata: '.$json_metadata['metadata'],
					    'content-type: multipart/form-data; boundary=c4f8e9ff-cad4-479d-8411-fd1ea66e518b',
					    'user-agent: okhttp/4.9.2',
					);


					$get_ice_referrall = curl('https://w.api.ice.io/v1r/user-views/username?username='.$reffcode, 'GET', null, $headers_login, false);
					$json_referral = json_decode($get_ice_referrall, true);
					

					$users = curl('https://w.api.ice.io/v1w/users', 'POST', '{"firstName":"'.$ex_name[0].'","lastName":"'.$ex_name[1].'","referredBy":"'.$json_referral['id'].'","email":"'.$email.'","phoneNumber":null,"phoneNumberHash":null,"clientData":{"phoneNumberIso":null},"language":"id"}', $headers_login, false);
					$json_users = json_decode($users, true);

					$post_data = '--c4f8e9ff-cad4-479d-8411-fd1ea66e518b
content-disposition: form-data; name="checksum"
Content-Length: 19

1693384247105446172
--c4f8e9ff-cad4-479d-8411-fd1ea66e518b
content-disposition: form-data; name="clientData"
Content-Length: 72

{"phoneNumberIso":null,"registrationProcessFinalizedSteps":["iceBonus"]}
--c4f8e9ff-cad4-479d-8411-fd1ea66e518b--';

					$post_a = '--c4f8e9ff-cad4-479d-8411-fd1ea66e518b
content-disposition: form-data; name="checksum"
Content-Length: 19

1693384247105446172
--c4f8e9ff-cad4-479d-8411-fd1ea66e518b
content-disposition: form-data; name="username"
Content-Length: 7

'.$user[0].'
--c4f8e9ff-cad4-479d-8411-fd1ea66e518b--';


					echo $post = curl('https://w.api.ice.io/v1w/users/'.$json_users['id'], 'PATCH', $post_data, $headers_bonus, false);
					echo "\n";
					echo $username = curl('https://w.api.ice.io/v1w/users/'.$json_users['id'], 'PATCH', $post_a, $headers_bonus, false);
					echo "\n";
					echo $mining = curl('https://w.api.ice.io/v1w/tokenomics/'.$json_users['id'].'/mining-sessions', 'POST', '{}', $headers_login, false);

				} else {
					echo "[*] Failed to getMetadata\n";
				}


			} else {
				echo "[*] Failed to get access token\n";
			}

		} else {
			echo "[*] Failed to verification code\n";
		}
	} else {
		echo "[!] Link verification not found\n";
	}

} else {
	
	echo "[*] Email verification not found\n";
}

}