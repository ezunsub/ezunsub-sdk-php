<?php

declare(strict_types=1);

namespace EZUnsub\Resources;

use EZUnsub\EZUnsubClient;

class Links
{
    private EZUnsubClient $client;

    public function __construct(EZUnsubClient $client)
    {
        $this->client = $client;
    }

    /**
     * List unsubscribe links
     *
     * @param int $page Page number
     * @param int $limit Items per page
     * @param string|null $offerId Filter by offer ID
     * @return array<int, array<string, mixed>>
     */
    public function list(int $page = 1, int $limit = 50, ?string $offerId = null): array
    {
        $query = ['page' => $page, 'limit' => $limit];

        if ($offerId !== null) {
            $query['offerId'] = $offerId;
        }

        return $this->client->request('GET', '/api/links', query: $query);
    }

    /**
     * Get a link by code
     *
     * @param string $code Link code
     * @return array<string, mixed>
     */
    public function get(string $code): array
    {
        return $this->client->request('GET', "/api/links/{$code}");
    }

    /**
     * Create an unsubscribe link
     *
     * @param string $offerId Offer ID
     * @param string|null $name Optional link name
     * @return array<string, mixed>
     */
    public function create(string $offerId, ?string $name = null): array
    {
        $data = ['offerId' => $offerId];

        if ($name !== null) {
            $data['name'] = $name;
        }

        return $this->client->request('POST', '/api/links', json: $data);
    }
}
