<?php

namespace App\Service;

use PayPalCheckoutSdk\Core\PayPalHttpClient;
use PayPalCheckoutSdk\Core\SandboxEnvironment;
use PayPalCheckoutSdk\Core\ProductionEnvironment;
use PayPalCheckoutSdk\Orders\OrdersCreateRequest;
use PayPalCheckoutSdk\Orders\OrdersCaptureRequest;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class PayPalService
{
    private PayPalHttpClient $client;
    private UrlGeneratorInterface $urlGenerator;

    public function __construct(
        private ParameterBagInterface $params,
        UrlGeneratorInterface $urlGenerator
    ) {
        $this->urlGenerator = $urlGenerator;
        
        // Set up PayPal environment
        $clientId = $this->params->get('paypal_client_id');
        $clientSecret = $this->params->get('paypal_client_secret');
        $mode = $this->params->get('paypal_mode');
        
        // Create the appropriate environment
        if ($mode === 'sandbox') {
            $environment = new SandboxEnvironment($clientId, $clientSecret);
        } else {
            $environment = new ProductionEnvironment($clientId, $clientSecret);
        }
        
        // Create client
        $this->client = new PayPalHttpClient($environment);
    }
    
    public function createPayment(string $rdvName, float $price, int $rdvId, string $clientName): ?object
    {
        try {
            // Format the price properly with 2 decimal places
            $formattedPrice = number_format($price, 2, '.', '');
            
            // Create the order request
            $request = new OrdersCreateRequest();
            $request->prefer('return=representation');
            
            // Define the order
            $request->body = [
                'intent' => 'CAPTURE',
                'purchase_units' => [
                    [
                        'reference_id' => 'RDV-' . $rdvId,
                        'description' => "Réservation pour $rdvName",
                        'amount' => [
                            'currency_code' => 'EUR',
                            'value' => $formattedPrice,
                            'breakdown' => [
                                'item_total' => [
                                    'currency_code' => 'EUR',
                                    'value' => $formattedPrice
                                ]
                            ]
                        ],
                        'items' => [
                            [
                                'name' => $rdvName,
                                'quantity' => '1',
                                'unit_amount' => [
                                    'currency_code' => 'EUR',
                                    'value' => $formattedPrice
                                ]
                            ]
                        ]
                    ]
                ],
                'application_context' => [
                    'return_url' => $this->urlGenerator->generate('payment_success', [
                        'rdv_id' => $rdvId
                    ], UrlGeneratorInterface::ABSOLUTE_URL),
                    'cancel_url' => $this->urlGenerator->generate('payment_cancel', [], 
                        UrlGeneratorInterface::ABSOLUTE_URL),
                    'brand_name' => 'Service de Réservation',
                    'user_action' => 'PAY_NOW'
                ]
            ];
            
            // Call API with the request
            $response = $this->client->execute($request);
            
            // Return the created order
            return $response;
        } catch (\Exception $e) {
            // Log error
            error_log('PayPal Error: ' . $e->getMessage());
            return null;
        }
    }
    
    public function capturePayment(string $orderId): ?object
    {
        try {
            // Create the capture request
            $request = new OrdersCaptureRequest($orderId);
            $request->prefer('return=representation');
            
            // Call API with the request
            $response = $this->client->execute($request);
            
            // Return the captured order
            return $response;
        } catch (\Exception $e) {
            error_log('PayPal Error: ' . $e->getMessage());
            return null;
        }
    }
    
    // Helper method to get approval URL from order response
    public function getApprovalLink($order): ?string
    {
        foreach ($order->result->links as $link) {
            if ($link->rel === 'approve') {
                return $link->href;
            }
        }
        return null;
    }
}