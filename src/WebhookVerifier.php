<?php

declare(strict_types=1);

namespace EZUnsub;

/**
 * Verify and parse EZUnsub webhook payloads
 *
 * @example
 * ```php
 * $verifier = new WebhookVerifier('your-webhook-secret');
 *
 * // In your webhook handler (e.g., Laravel)
 * public function handleWebhook(Request $request)
 * {
 *     $signature = $request->header('X-Webhook-Signature');
 *     $timestamp = $request->header('X-Webhook-Timestamp');
 *     $body = $request->getContent();
 *
 *     try {
 *         $payload = $verifier->verifyAndParse($signature, $timestamp, $body);
 *
 *         if ($payload['event'] === 'contact.created') {
 *             // Handle new contact
 *         }
 *
 *         return response()->json(['status' => 'ok']);
 *     } catch (\InvalidArgumentException $e) {
 *         return response()->json(['error' => $e->getMessage()], 400);
 *     }
 * }
 * ```
 */
class WebhookVerifier
{
    private string $secret;
    private int $maxAgeSeconds;

    public function __construct(string $secret, int $maxAgeSeconds = 300)
    {
        $this->secret = $secret;
        $this->maxAgeSeconds = $maxAgeSeconds;
    }

    /**
     * Verify webhook signature
     *
     * @param string $signature Signature from X-Webhook-Signature header
     * @param int $timestamp Timestamp from X-Webhook-Timestamp header
     * @param string $body Raw request body
     * @return bool True if signature is valid
     */
    public function verifySignature(string $signature, int $timestamp, string $body): bool
    {
        // Check timestamp is within acceptable range
        $now = time();
        if (abs($now - $timestamp) > $this->maxAgeSeconds) {
            return false;
        }

        // Calculate expected signature
        $message = "{$timestamp}.{$body}";
        $expected = hash_hmac('sha256', $message, $this->secret);

        // Extract signature value (remove "sha256=" prefix if present)
        $sigValue = $signature;
        if (str_starts_with($signature, 'sha256=')) {
            $sigValue = substr($signature, 7);
        }

        return hash_equals($expected, $sigValue);
    }

    /**
     * Verify signature and parse webhook payload
     *
     * @param string $signature Signature from X-Webhook-Signature header
     * @param string|int $timestamp Timestamp from X-Webhook-Timestamp header
     * @param string $body Raw request body (JSON string)
     * @param string $deliveryId Optional delivery ID from X-Webhook-Delivery-Id header
     * @return array<string, mixed> Parsed webhook payload
     * @throws \InvalidArgumentException If signature is invalid or payload is malformed
     */
    public function verifyAndParse(
        string $signature,
        string|int $timestamp,
        string $body,
        string $deliveryId = ''
    ): array {
        $ts = is_string($timestamp) ? (int) $timestamp : $timestamp;

        // Verify signature
        if (!$this->verifySignature($signature, $ts, $body)) {
            throw new \InvalidArgumentException('Invalid webhook signature');
        }

        // Parse body
        $data = json_decode($body, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \InvalidArgumentException('Invalid JSON payload: ' . json_last_error_msg());
        }

        // Validate required fields
        if (!isset($data['event'])) {
            throw new \InvalidArgumentException("Missing 'event' field in payload");
        }
        if (!isset($data['timestamp'])) {
            throw new \InvalidArgumentException("Missing 'timestamp' field in payload");
        }
        if (!isset($data['data'])) {
            throw new \InvalidArgumentException("Missing 'data' field in payload");
        }

        return [
            'event' => $data['event'],
            'timestamp' => $data['timestamp'],
            'data' => $data['data'],
            'deliveryId' => $deliveryId,
        ];
    }

    /**
     * Extract webhook headers from a request
     *
     * @param array<string, string|array<string>> $headers Request headers
     * @return array{signature: string, timestamp: string, event: string, deliveryId: string}
     * @throws \InvalidArgumentException If required headers are missing
     */
    public static function extractHeaders(array $headers): array
    {
        // Normalize header keys to lowercase
        $normalized = [];
        foreach ($headers as $key => $value) {
            $normalized[strtolower($key)] = is_array($value) ? $value[0] : $value;
        }

        $signature = $normalized['x-webhook-signature'] ?? null;
        $timestamp = $normalized['x-webhook-timestamp'] ?? null;
        $event = $normalized['x-webhook-event'] ?? '';
        $deliveryId = $normalized['x-webhook-delivery-id'] ?? '';

        if ($signature === null) {
            throw new \InvalidArgumentException('Missing X-Webhook-Signature header');
        }
        if ($timestamp === null) {
            throw new \InvalidArgumentException('Missing X-Webhook-Timestamp header');
        }

        return [
            'signature' => $signature,
            'timestamp' => $timestamp,
            'event' => $event,
            'deliveryId' => $deliveryId,
        ];
    }
}
