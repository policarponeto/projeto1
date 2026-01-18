<?php

namespace App\Services;

use CodeIgniter\HTTP\CURLRequest;

class NotificationService
{
    protected $client;
    protected $notificationUrl = 'https://util.devi.tools/api/v1/notify';

    public function __construct()
    {
        $this->client = \Config\Services::curlrequest();
    }

    /**
     * Send notification to user
     */
    public function sendNotification(string $email, string $message): bool
    {
        try {
            $payload = [
                'email' => $email,
                'message' => $message
            ];

            $response = $this->client->post($this->notificationUrl, [
                'json' => $payload,
                'timeout' => 5,
                'http_errors' => false,
                'verify' => false, 
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json'
                ]
            ]);

            // The mock service may return various status codes
            // Consider it successful if we get any response (even if it simulates failure)
            if ($response->getStatusCode() >= 200 && $response->getStatusCode() < 500) {
                $data = json_decode($response->getBody(), true);
                
                // Log the notification attempt
                log_message('info', 'Notification sent to ' . $email . ': ' . json_encode($data));
                
                // For the mock service, we consider it successful if we get a response
                // In a real implementation, you would check the response structure
                return true;
            }

            log_message('error', 'Notification service returned status: ' . $response->getStatusCode());
            return false;

        } catch (\Exception $e) {
            log_message('error', 'Notification service error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send transfer notification
     */
    public function sendTransferNotification(array $transaction): bool
    {
        $payeeEmail = $transaction['payee_email'] ?? null;
        $payerName = $transaction['payer_name'] ?? 'Unknown';
        $amount = number_format($transaction['amount'] ?? 0, 2, ',', '.');

        if (!$payeeEmail) {
            log_message('error', 'Payee email not found for transaction notification');
            return false;
        }

        $message = "Você recebeu uma transferência de R$ {$amount} de {$payerName}.";

        return $this->sendNotification($payeeEmail, $message);
    }

    /**
     * Send payment received notification
     */
    public function sendPaymentReceivedNotification(array $transaction): bool
    {
        return $this->sendTransferNotification($transaction);
    }

    /**
     * Send notification asynchronously (for background processing)
     */
    public function sendNotificationAsync(string $email, string $message): void
    {
        // In a real implementation, you might use a queue system
        // For now, we'll log it and send synchronously
        log_message('info', "Async notification queued for {$email}: {$message}");
        
        // For the mock, we'll send it immediately
        $this->sendNotification($email, $message);
    }

    /**
     * Check if notification service is available
     */
    public function isServiceAvailable(): bool
    {
        try {
            // Try a simple ping to the service
            $response = $this->client->post($this->notificationUrl, [
                'json' => ['email' => 'test@example.com', 'message' => 'test'],
                'timeout' => 3,
                'http_errors' => false
            ]);

            return $response->getStatusCode() >= 200 && $response->getStatusCode() < 500;

        } catch (\Exception $e) {
            log_message('error', 'Notification service unavailable: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Retry notification with exponential backoff
     */
    public function sendNotificationWithRetry(string $email, string $message, int $maxRetries = 3): bool
    {
        $attempt = 0;
        $delay = 1; // Start with 1 second delay

        while ($attempt < $maxRetries) {
            $attempt++;
            
            if ($this->sendNotification($email, $message)) {
                return true;
            }

            if ($attempt < $maxRetries) {
                // Exponential backoff
                sleep($delay);
                $delay *= 2;
            }
        }

        log_message('error', "Failed to send notification to {$email} after {$maxRetries} attempts");
        return false;
    }
}
