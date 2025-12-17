<?php

declare(strict_types=1);

namespace EZUnsub\Resources;

use EZUnsub\EZUnsubClient;

class Contacts
{
    private EZUnsubClient $client;

    public function __construct(EZUnsubClient $client)
    {
        $this->client = $client;
    }

    /**
     * List contacts
     *
     * @param int $page Page number (default: 1)
     * @param int $limit Items per page (default: 50, max: 200)
     * @param string|null $linkCode Filter by link code
     * @return array<int, array<string, mixed>>
     */
    public function list(int $page = 1, int $limit = 50, ?string $linkCode = null): array
    {
        $query = ['page' => $page, 'limit' => $limit];

        if ($linkCode !== null) {
            $query['linkCode'] = $linkCode;
        }

        return $this->client->request('GET', '/api/contacts', query: $query);
    }

    /**
     * Get a contact by ID
     *
     * @param string $contactId Contact ID
     * @return array<string, mixed>
     */
    public function get(string $contactId): array
    {
        return $this->client->request('GET', "/api/contacts/{$contactId}");
    }

    /**
     * Delete a contact (admin only)
     *
     * @param string $contactId Contact ID
     * @return array<string, mixed>
     */
    public function delete(string $contactId): array
    {
        return $this->client->request('DELETE', "/api/contacts/{$contactId}");
    }

    /**
     * Get contact statistics
     *
     * @return array<string, mixed>
     */
    public function stats(): array
    {
        return $this->client->request('GET', '/api/contacts/stats');
    }
}
