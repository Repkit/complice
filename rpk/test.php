<?php

error_reporting(-1);
ini_set('display_errors', 1);

$tz = new DateTimeZone('Europe/Bucharest');
$today = new DateTime("now", $tz );
echo 'now: ' . $today->format('Y-m-d H:i:s'). PHP_EOL;
$moment_livrare = '25/11/2021';
$ora_livrare = '02:00 AM';
$delivery_date = DateTime::createFromFormat('d/m/Y H:i A', $moment_livrare. ' '. $ora_livrare, $tz );
echo 'delivery_date: ' . $delivery_date->format('Y-m-d H:i:s'). PHP_EOL;

$diff = $today->diff( $delivery_date );
$diffDays = (integer)$diff->format( "%R%a" );
echo 'diff days: ' . $diffDays . PHP_EOL;

$diffHours = (integer)$diff->format( "%R%h" );
echo 'diff hours: ' . $diffHours . PHP_EOL;

if($diffHours >= 1){
    echo 'will send in ' . $diffHours . ' hours'. PHP_EOL;
}else{
  echo 'sent';
}

exit('done');

$pdf = getcwd() . '/' . 'order_2.pdf';
$data = [
    'intro' => 'Buna Marie,',
    'outro' => 'Cu drag, '. PHP_EOL . 'Ion',
    'description' => 'some <strong>awesome</strong> description',
    'supplier' => 'informatii despre experienta si furnizori',
    'valability' => '6 luni de la emiterea voucherului',
    'extra_info' => 'Extra 20%',
    'oid' => '2',
];

download('http://complice.qbo.ro:8060/rpk/voucher.php', $data, $pdf);
// echo 'downloaded';

function download($Url, $Data, $filename)
{
    $fp = fopen($filename, 'wb');
    // $verbose = fopen('php://temp', 'w+');
    $curl = curl_init();

    curl_setopt_array($curl, array(
      CURLOPT_URL => $Url,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_FILE => $fp,
      CURLOPT_HEADER => 0,
      // CURLOPT_VERBOSE => true,
      // CURLOPT_STDERR => $verbose,
      CURLOPT_PROXY => $_SERVER['SERVER_ADDR'] . ':' .  $_SERVER['SERVER_PORT'],
      CURLOPT_CUSTOMREQUEST => 'POST', //
      CURLOPT_POSTFIELDS => http_build_query($Data),
      CURLOPT_HTTPHEADER => array(
        'Content-Type: application/x-www-form-urlencoded'
      ),
    ));

    $result = curl_exec($curl);
    // if ($result === FALSE) {
    //     printf("cUrl error (#%d): %s<br>\n", curl_errno($curl),
    //            htmlspecialchars(curl_error($curl)));
    //     rewind($verbose);
    //     $verboseLog = stream_get_contents($verbose);

    //     echo "Verbose information:\n<pre>", htmlspecialchars($verboseLog), "</pre>\n";
    // }else{
    //     echo $result;
    // }

    curl_close($curl);

    // Close file
    fclose($fp);

    return;
}
