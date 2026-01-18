<?php

namespace App\Services;

use CodeIgniter\HTTP\CURLRequest;

class AuthorizationService
{
    protected $client;
    protected $authorizationUrl = 'https://util.devi.tools/api/v2/authorize';

    public function __construct()
    {
        $this->client = \Config\Services::curlrequest();
    }

    /**
     * Check if transaction is authorized
     */
    public function authorize(): array
    {
        try {
            $response = $this->client->get($this->authorizationUrl, [
                'timeout' => 2,
                'http_errors' => false,
                'verify' => false, 
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false
                ]
            ]);

            $statusCode = $response->getStatusCode();
            
            if ($statusCode !== 200) {
                log_message('error', 'Authorization service returned status: ' . $statusCode);
                return [
                    'authorized' => false,
                    'error' => 'Service unavailable',
                    'status_code' => $statusCode
                ];
            }

            $data = json_decode($response->getBody(), true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                log_message('error', 'Invalid JSON response from authorization service');
                return [
                    'authorized' => false,
                    'error' => 'Invalid response format'
                ];
            }

            // Check if the response indicates authorization
            $isAuthorized = false;
            $authCode = null;

            if (isset($data['data']['authorization']) && $data['data']['authorization'] === true) {
                $isAuthorized = true;
                $authCode = $data['data']['authorization_code'] ?? null;
            }
            // Fallback: if the structure is different, check for success status
            elseif (isset($data['status']) && $data['status'] === 'success') {
                $isAuthorized = true;
                $authCode = $data['authorization_code'] ?? null;
            }

            return [
                'authorized' => $isAuthorized,
                'authorization_code' => $authCode,
                'response_data' => $data
            ];

        } catch (\Exception $e) {
            log_message('error', 'Authorization service error: ' . $e->getMessage());
            return [
                'authorized' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Get authorization response details (DEPRECATED - use authorize() instead)
     */
    public function getAuthorizationResponse(): ?array
    {
        log_message('warning', 'getAuthorizationResponse() is deprecated. Use authorize() instead.');
        
        $result = $this->authorize();
        return $result['authorized'] ? $result['response_data'] : null;
    }

    /**
     * Check if authorization service is available
     */
    public function isServiceAvailable(): bool
    {
        try {
            $response = $this->client->get($this->authorizationUrl, [
                'timeout' => 1,
                'http_errors' => false,
                'verify' => false, // Disable SSL verification for development
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false
                ]
            ]);

            return $response->getStatusCode() === 200;

        } catch (\Exception $e) {
            log_message('error', 'Authorization service unavailable: ' . $e->getMessage());
            return false;
        }
    }
}
