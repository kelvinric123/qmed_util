<?php
require_once __DIR__ . ' /vendor/autoload.php';

use Exedra\Routing\Group;
use Exedra\Runtime\Context;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;
use Mike42\Escpos\Printer;
/**
 * Header settings
 */
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PATCH, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: X-Requested-With');
header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token, X-Bajet-Version, X-CSRF-TOKEN');
header('Access-Control-Max-Age: 600');

if(isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit;
}

date_default_timezone_set("Asia/Kuala_Lumpur");

/** @var \Exedra\Application $app */
$app = new \Exedra\Application(__DIR__);

$app->map['app']->any('/:installation-id')->group(function(Group $group) {
    $group->middleware(function(Context $context) {
        $response = $context->next($context);

        // ping server
        $base = file_exists(__DIR__ . '/local.app_url') ? file_get_contents(__DIR__ . '/local.app_url') : 'https://qmed.asia';

        @file_get_contents($base . '/api/installation/' . $context->param('installation-id') . '/ping/device/receipt_printer');

        return $response;
    });

    $group['print']->get('/print/:ticket-no/:room?')->execute(function(Context $context) {
        try {
            $datetime = new DateTime();

            $title = 'QueueMed';
            $subtitle = 'Ticket Number:';
            $ticket_number =    $context->param('ticket-no'); //'2122';
            $room_title = 'Room Number:';
            $room_number = $context->param('room', null); //'1';
            //$print_time =  $response[0]->created_at;  //'2019-10-14  11:32:00' ;
            //$print_time = date('d/m/Y h:i:s', time());
            $print_time = $datetime->format('Y-m-d h:i:A');

            $connector = null;
            $connector = new WindowsPrintConnector("Receipt Printer");

            $printer = new Printer($connector);

            $printer -> setJustification(Printer::JUSTIFY_CENTER);
            $printer -> feed(1);
            $printer -> selectPrintMode(Printer::MODE_DOUBLE_WIDTH);
            $printer -> text($title);
            $printer -> feed();

            $printer -> selectPrintMode();
            $printer -> text($subtitle);
            $printer -> feed(1);

            $printer -> setEmphasis(true);
            $printer ->selectPrintMode(Printer::MODE_DOUBLE_HEIGHT | Printer::MODE_EMPHASIZED | Printer::MODE_DOUBLE_WIDTH);
            $printer -> text($ticket_number);
            $printer -> setEmphasis(false);
            $printer -> feed(1);

            $printer -> selectPrintMode();
            if ($room_number) {
                $printer -> text($room_title);
                $printer -> selectPrintMode(Printer::MODE_DOUBLE_WIDTH);

                $printer -> text($room_number);
                $printer -> feed(1);
            }

            $printer -> selectPrintMode();
            $printer -> text($print_time);
            $printer -> feed(4);
            $printer -> cut();

            /* Close printer */
            @$printer -> close();

            return json_encode([
                'status' => 'success'
            ]);
        } catch (Exception $e) {
            return json_encode([
                'status' => 'error',
                'message' => 'Ticket printer is not yet installed.']);
        }
    });
});


$app->dispatch();



