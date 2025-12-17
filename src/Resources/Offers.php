<?php

declare(strict_types=1);

namespace EZUnsub\Resources;

use EZUnsub\EZUnsubClient;

class Offers
{
    private EZUnsubClient $client;

    public function __construct(EZUnsubClient $client)
    {
        $this->client = $client;
    }

    /**
     * List offers
     *
     * @param int $page Page number
     * @param int $limit Items per page
     * @return array<int, array<string, mixed>>
     */
    public function list(int $page = 1, int $limit = 50): array
    {
        return $this->client->request('GET', '/api/offers', query: [
            'page' => $page,
            'limit' => $limit,
        ]);
    }

    /**
     * Get an offer by ID
     *
     * @param string $offerId Offer ID
     * @return array<string, mixed>
     */
    public function get(string $offerId): array
    {
        return $this->client->request('GET', "/api/offers/{$offerId}");
    }
}
