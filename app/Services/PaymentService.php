<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Http\Request;


class PaymentService
{
    protected $client;
    protected $apiKey;
    protected $siteId;

    public function __construct()
    {
        $this->client = new Client();
        $this->apiKey = env('CINETPAY_API_KEY');
        $this->siteId = env('CINETPAY_SITE_ID');
    }

    public function processPayment($amount,$description, $currency, $options = [])
    {
        $transactionId = uniqid('txn_');
        // dd($this->apiKey, $this->siteId);
        $response = $this->client->post('https://api.monetbil.com/widget/v2.1/FKRZBK0tX0gzRif7Ut8h6XB4tohQccgS', array_merge([
            'json' => [
                'payment_ref' => $transactionId,
                'amount' => $amount,
                'currency' => $currency,
                'country' =>"CM",
                'locale'=> 'fr',
                'return_url' => "https://bunker-shop.store/home",
                'notify_url' => route('payment.notify'),
            ]
        ], $options));

        $responseBody = json_decode($response->getBody(), true);
        // dd($responseBody);
        if ($responseBody['success'] == true) {
            return [
                'status' => 'success',
                'transaction_reference' =>$transactionId,
                "payment_url" => $responseBody['payment_url'],
            ];
        }

        return [
            'status' => 'failed',
            'message' => $responseBody['message'],
        ];
    }

    public function handleCallback(Request $request)
    {
        $transactionId = $request->input('transaction_id');
        $response = $this->client->get("https://api.cinetpay.com/v1/payment/verify/{$transactionId}", [
            'query' => [
                'apikey' => $this->apiKey,
                'site_id' => $this->siteId,
            ]
        ]);

        $responseBody = json_decode($response->getBody(), true);

        if ($responseBody['code'] == '00') {
            // Handle successful payment
            return [
                'status' => 'success',
                'transaction_reference' => $responseBody['data']['transaction_id'],
                'message' => $responseBody['message'],
            ];
        }

        // Handle failed payment
        return [
            'status' => 'failed',
            'message' => $responseBody['message'],
        ];
    }

    public function handleNotification(Request $request)
    {
        // Handle payment notification
        $transactionId = $request->input('transaction_id');
        $response = $this->client->get("https://api.cinetpay.com/v1/payment/verify/{$transactionId}", [
            'query' => [
                'apikey' => $this->apiKey,
                'site_id' => $this->siteId,
            ]
        ], [
            'verify' => false, // Disable SSL verification
        ]);

        $responseBody = json_decode($response->getBody(), true);

        if ($responseBody['code'] == '00') {
            // Handle successful payment notification
            return [
                'status' => 'success',
                'transaction_reference' => $responseBody['data']['transaction_id'],
                'message' => $responseBody['message'],
            ];
        }

        // Handle failed payment notification
        return [
            'status' => 'failed',
            'message' => $responseBody['message'],
        ];
    }

    public function verifyTransaction($transactionId, $options= [])
    {
        $response = $this->client->post('https://api.monetbil.com/payment/v1/checkPayment&paymentId='.$transactionId, array_merge([
            'json' => []
        ], $options));

        $responseBody = json_decode($response->getBody(), true);

        if ($responseBody['code'] == '00') {
            return [
                'status' => 'success',
                'data' => $responseBody['data'],
            ];
        }

        return [
            'status' => 'failed',
            'message' => $responseBody['message'],
        ];
    }
}
