<?php

namespace App\Services;

use GuzzleHttp\Client;

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

    public function processPayment($amount, $currency)
    {
        $transactionId = uniqid('txn_');
        $response = $this->client->post('https://api.cinetpay.com/v1/payment', [
            'json' => [
                'apikey' => $this->apiKey,
                'site_id' => $this->siteId,
                'transaction_id' => $transactionId,
                'amount' => $amount,
                'currency' => $currency,
                'description' => 'Order payment',
                'return_url' => route('payment.callback'),
                'notify_url' => route('payment.notify'),
            ]
        ]);

        $responseBody = json_decode($response->getBody(), true);

        if ($responseBody['code'] == '00') {
            return [
                'status' => 'success',
                'transaction_reference' => $responseBody['data']['transaction_id'],
                'message' => $responseBody['message'],
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
}