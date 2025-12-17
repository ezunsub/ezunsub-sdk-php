<?php

declare(strict_types=1);

namespace EZUnsub\Resources;

use EZUnsub\EZUnsubClient;

class Exports
{
    private EZUnsubClient $client;

    public function __construct(EZUnsubClient $client)
    {
        $this->client = $client;
    }

    /**
     * List exports
     *
     * @param int $page Page number
     * @param int $limit Items per page
     * @return array<int, array<string, mixed>>
     */
    public function list(int $page = 1, int $limit = 50): array
    {
        return $this->client->request('GET', '/api/exports', query: [
            'page' => $page,
            'limit' => $limit,
        ]);
    }

    /**
     * Get an export by ID
     *
     * @param string $exportId Export ID
     * @return array<string, mixed>
     */
    public function get(string $exportId): array
    {
        return $this->client->request('GET', "/api/exports/{$exportId}");
    }

    /**
     * Create an export job
     *
     * @param string $name Export name
     * @param array<string, mixed>|null $filters Optional filters
     * @param string $format Export format (csv)
     * @return array<string, mixed>
     */
    public function create(string $name, ?array $filters = null, string $format = 'csv'): array
    {
        $data = ['name' => $name, 'format' => $format];

        if ($filters !== null) {
            $data['filters'] = $filters;
        }

        return $this->client->request('POST', '/api/exports', json: $data);
    }
}
