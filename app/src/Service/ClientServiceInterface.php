<?php

namespace App\Service;

interface ClientServiceInterface
{
    /**
     * @param array<string, array<string, string>  $options
     *
     * @return array<string, mixed>
     */
    public function request(string $url, array $options = []): array;
}
