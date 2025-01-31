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

    public function processPayment($amount,$description, $currency, $options = [])
    {
        $transactionId = uniqid('txn_');
        // dd($this->apiKey, $this->siteId);
        $response = $this->client->post('https://api-checkout.cinetpay.com/v2/payment', array_merge([
            'json' => [
                'apikey' => "4659668566c4d543a545d1.86010226",
                'site_id' => "5879943",
                'transaction_id' => $transactionId,
                'amount' => $amount,
                'currency' => $currency,
                'description' => $description,
                "channels"=>"ALL",
                'return_url' => route('payment.callback'),
                'notify_url' => route('payment.notify'),
            ]
        ], $options));

        $responseBody = json_decode($response->getBody(), true);
        // dd($responseBody);
        if ($responseBody['code'] == '201') {
            return [
                'status' => 'success',
                'transaction_reference' =>$transactionId,
                "payment_token" => $responseBody['data']['payment_token'],
                "payment_url" => $responseBody['data']['payment_url'],
                'message' => $responseBody['description'],
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

    public function verifyTransaction($transactionId, $siteId , $options= [])
    {
        $response = $this->client->post('https://api-checkout.cinetpay.com/v2/payment/check', array_merge([
            'json' => [
                'apikey' => "4659668566c4d543a545d1.86010226",
                'site_id' => "5879943",
                'transaction_id' => $transactionId,
            ]
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