# EZUnsub PHP SDK

Official PHP SDK for [EZUnsub](https://ezunsub.com) - Contact suppression and unsubscribe management for affiliate marketing compliance.

## Requirements

- PHP 8.1+
- Guzzle HTTP client

## Installation

```bash
composer require ezunsub/ezunsub-sdk
```

## Quick Start

```php
use EZUnsub\EZUnsubClient;

// Initialize client
$client = new EZUnsubClient(
    apiKey: 'your-api-key',
    baseUrl: 'https://your-ezunsub-instance.com'
);

// List contacts
$contacts = $client->contacts()->list(page: 1, limit: 50);

// Get contact statistics
$stats = $client->contacts()->stats();
echo "Total contacts: " . $stats['total'];

// Create a webhook
$webhook = $client->webhooks()->create(
    name: 'My Webhook',
    url: 'https://my-app.com/webhooks/ezunsub',
    events: ['contact.created', 'contact.updated']
);
echo "Webhook secret (save this!): " . $webhook['secret'];
```

## Webhook Verification

Verify incoming webhooks from EZUnsub:

```php
use EZUnsub\WebhookVerifier;

$verifier = new WebhookVerifier('your-webhook-secret');

// Laravel example
public function handleWebhook(Request $request)
{
    $signature = $request->header('X-Webhook-Signature');
    $timestamp = $request->header('X-Webhook-Timestamp');
    $deliveryId = $request->header('X-Webhook-Delivery-Id');
    $body = $request->getContent();

    try {
        $payload = $verifier->verifyAndParse(
            signature: $signature,
            timestamp: $timestamp,
            body: $body,
            deliveryId: $deliveryId
        );

        switch ($payload['event']) {
            case 'contact.created':
                $this->handleNewContact($payload['data']);
                break;
            case 'contact.updated':
                $this->handleContactUpdate($payload['data']);
                break;
            case 'export.completed':
                $this->handleExportComplete($payload['data']);
                break;
        }

        return response()->json(['status' => 'ok']);
    } catch (\InvalidArgumentException $e) {
        return response()->json(['error' => $e->getMessage()], 400);
    }
}
```

### Vanilla PHP Example

```php
use EZUnsub\WebhookVerifier;

$verifier = new WebhookVerifier('your-webhook-secret');

$headers = getallheaders();
$body = file_get_contents('php://input');

try {
    $extracted = WebhookVerifier::extractHeaders($headers);

    $payload = $verifier->verifyAndParse(
        signature: $extracted['signature'],
        timestamp: $extracted['timestamp'],
        body: $body,
        deliveryId: $extracted['deliveryId']
    );

    // Handle the event
    if ($payload['event'] === 'contact.created') {
        // New contact added to suppression list
        $emailHash = $payload['data']['emailHash'] ?? null;
    }

    http_response_code(200);
    echo json_encode(['status' => 'ok']);
} catch (\InvalidArgumentException $e) {
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
}
```

## API Reference

### Client

```php
use EZUnsub\EZUnsubClient;

$client = new EZUnsubClient(
    apiKey: 'your-api-key',
    baseUrl: 'https://your-ezunsub-instance.com',
    timeout: 30.0  // optional, default 30s
);
```

### Contacts

```php
// List contacts
$contacts = $client->contacts()->list(page: 1, limit: 50, linkCode: 'abc123');

// Get single contact (admin only)
$contact = $client->contacts()->get('contact-id');

// Delete contact (admin only)
$client->contacts()->delete('contact-id');

// Get statistics
$stats = $client->contacts()->stats();
```

### Webhooks

```php
// List webhooks
$webhooks = $client->webhooks()->list();

// Create webhook
$webhook = $client->webhooks()->create(
    name: 'My Webhook',
    url: 'https://example.com/webhook',
    events: ['contact.created', 'contact.updated'],
    piiMode: 'hashes'  // full, hashes, or none
);

// Update webhook
$client->webhooks()->update(
    webhookId: 'webhook-id',
    isActive: false
);

// Delete webhook
$client->webhooks()->delete('webhook-id');

// Rotate secret
$newWebhook = $client->webhooks()->rotateSecret('webhook-id');

// Send test
$result = $client->webhooks()->test('webhook-id');

// Get delivery history
$deliveries = $client->webhooks()->deliveries('webhook-id', limit: 50);

// Get available events
$events = $client->webhooks()->events();
```

### Links

```php
// List links
$links = $client->links()->list(offerId: 'offer-id');

// Get link
$link = $client->links()->get('link-code');

// Create link
$link = $client->links()->create(offerId: 'offer-id', name: 'My Link');
```

### Offers

```php
// List offers
$offers = $client->offers()->list();

// Get offer
$offer = $client->offers()->get('offer-id');
```

### Exports

```php
// List exports
$exports = $client->exports()->list();

// Get export
$export = $client->exports()->get('export-id');

// Create export
$export = $client->exports()->create(
    name: 'My Export',
    filters: ['status' => 'suppressed']
);
```

## Webhook Events

| Event | Description |
|-------|-------------|
| `contact.created` | New contact added to suppression list |
| `contact.updated` | Contact record updated |
| `complaint.created` | New complaint filed |
| `complaint.updated` | Complaint status changed |
| `link.created` | New unsubscribe link created |
| `link.clicked` | Unsubscribe link was clicked |
| `export.completed` | Export job finished |

## Error Handling

```php
use EZUnsub\EZUnsubClient;
use EZUnsub\Exceptions\EZUnsubException;
use EZUnsub\Exceptions\AuthenticationException;
use EZUnsub\Exceptions\ValidationException;
use EZUnsub\Exceptions\NotFoundException;
use EZUnsub\Exceptions\RateLimitException;

$client = new EZUnsubClient(apiKey: 'your-api-key');

try {
    $contact = $client->contacts()->get('invalid-id');
} catch (AuthenticationException $e) {
    echo "Invalid API key";
} catch (NotFoundException $e) {
    echo "Contact not found";
} catch (RateLimitException $e) {
    echo "Rate limited, retry after " . $e->getRetryAfter() . " seconds";
} catch (ValidationException $e) {
    echo "Invalid request: " . $e->getMessage();
} catch (EZUnsubException $e) {
    echo "API error: " . $e->getMessage() . " (status: " . $e->getStatusCode() . ")";
}
```

## License

MIT
