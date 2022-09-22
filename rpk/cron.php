<?php

error_reporting(-1);
ini_set('display_errors',1);

date_default_timezone_set('Europe/Bucharest');

require(__DIR__.'/../wp-load.php');

$orders = wc_get_orders( array( 'voucher_for_cron' => 1 ) );
echo 'orders count: ' . count($orders) . PHP_EOL; 
$tz = new DateTimeZone('Europe/Bucharest');
$today = new DateTime("now", $tz );
echo 'datetime: ' . $today->format('Y-m-d H:i:s'). PHP_EOL;
foreach($orders as $order){
    $order_id = $order->get_order_number();
    echo '------ORDER: ' . $order_id . '------' . PHP_EOL;
    $order_status = $order->get_status();
    if(!in_array( $order_status,['completed','processing'] ) ){
        echo 'current status is: ' . $order_status . PHP_EOL; 
        continue;
    }

    $moment_livrare = $order->get_meta('moment_livrare');
    echo 'moment_livrare is: ' . $moment_livrare . PHP_EOL;
    if(!isset($moment_livrare) || empty($moment_livrare)){
        $order->update_meta_data( '_voucher_for_cron', 2 );
        $order->save();
        continue;
    }
    $ora_livrare = $order->get_meta('ora_livrare');
    echo 'ora_livrare: ' . $ora_livrare . PHP_EOL;
    if(!isset($ora_livrare) || empty($ora_livrare)){
        $order->update_meta_data( '_voucher_for_cron', 2 );
        $order->save();
        continue;
    }

    $delivery_date = DateTime::createFromFormat('d/m/Y H:i A', $moment_livrare. ' '. $ora_livrare, $tz );
    echo 'delivery_date: ' . $delivery_date->format('Y-m-d H:i:s'). PHP_EOL;
    // set time part to midnight, in order to prevent partial comparison
    // $delivery_date->setTime( 0, 0, 0 );
    $diff = $today->diff( $delivery_date );
    $diffDays = (integer)$diff->format( "%R%a" );
    echo 'diff days: ' . $diffDays . PHP_EOL;
    if($diffDays < 0){
        echo $diffDays . ' days before'. PHP_EOL;
        $order->update_meta_data( '_voucher_for_cron', 3 );
        $order->save();
        continue;
    }
    if(0 != $diffDays){
        echo $diffDays . ' days ahead'. PHP_EOL;
        continue;
    }
    
    $diffHours = (integer)$diff->format( "%R%h" );
    echo 'diff hours: ' . $diffHours . PHP_EOL;
    if($diffHours >= 1){
        echo 'will send in ' . $diffHours . ' hours'. PHP_EOL;
        continue;
    }
    
    $email_beneficiar = $order->get_meta('email_beneficiar');
    echo 'email_beneficiar: ' . $email_beneficiar . PHP_EOL;
    if(!isset($email_beneficiar) || empty($email_beneficiar)){
        $order->update_meta_data( '_voucher_for_cron', 2 );
        $order->save();
        continue;
    }
    
    $result = send_beneficiar_email($order, $email_beneficiar);
    if($result){
        echo 'result: mail send' . PHP_EOL;
        $order->update_meta_data( '_voucher_for_cron', 0 );
    }else{
        echo 'result: failed to send mail' . PHP_EOL;
        $order->update_meta_data( '_voucher_for_cron', 4 );
    }
    $order->save();

}

echo 'done!'. PHP_EOL. PHP_EOL;

function send_beneficiar_email($order, $email_beneficiar = null, $email_client = null){
    global $woocommerce;
    try{
        if(is_int($order)){
            $order = wc_get_order( $order );
        }
        
        if(!isset($email_beneficiar) || empty($email_beneficiar)){
            $email_beneficiar = $order->get_meta('email_beneficiar');
        }
        if(!isset($email_beneficiar) || empty($email_beneficiar)){
            return false;
        }

        if(!isset($email_client) || empty($email_client)){
            $email_client = $order->billing_email;
        }
    
        $mailer = $woocommerce->mailer();
        $order_id = $order->get_order_number();
        
        $message_body = __( 'Buna.

Te invitam sa descoperi, atasat acestui mail, un cadou experiential primit din partea cuiva drag tie.

Asteptam cu nerabdare sa soliciti o programare si sa iti fim complice la o experienta memorabila, de care sa iti amintesti si peste 3 luni, 3 ani sau mult mai mult.

Sa te bucuri din plin de fiecare clipa!

-- 
Multumim frumos, Complice.ro | Tel: 0720153015 | Web: www.complice.ro
' );
        $message = $mailer->wrap_message(
           'Cadoul tau special', $message_body 
        );
        $headers = [
            "Content-Type: text/html\r\n",
            "Cc: " . $email_client,
            "Bcc: contact@complice.ro"
        ];
        $pdf = get_stylesheet_directory() . '/vouchers/order_'.$order_id.'.pdf';
        if(!file_exists($pdf)){
            rpk_generate_voucher($order, $pdf);
            sleep(1);
        }
        $attachments = [$pdf];
        return $mailer->send( $email_beneficiar, "Cadoul tau special", $message, $headers, $attachments );
    }catch(Exception $e){
        return false;
    }
}

function rpk_generate_voucher($order, $pdf){
    $data = [
        'intro' => $order->get_meta('voucher_start'),
        'outro' => $order->get_meta('voucher_end'),
        'description' => $vdesc,
        'supplier' => $order->get_meta('voucher_supplier'),
        'valability' => $order->get_meta('voucher_valability'),
        'extra_info' => implode(PHP_EOL, $xtrainfoarr),
        'oid' => $order_id,
    ];

    rpk_download_voucher('https://complice.ro/rpk/voucher.php', $data, $pdf);
}

function rpk_cron_download_voucher($Url, $Data, $filename)
{
    $fp = fopen($filename, 'wb');
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

    curl_close($curl);

    fclose($fp);

    return;
}