<?php

namespace App\Services;

use App\Libraries\CopainClient;
use App\Services\CopainLegacyReaderService;
use App\Workflows\ImportWorkflow;

class CopainImportService
{
    protected CopainClient $client;
    protected CopainLegacyReaderService $legacyReader;
    protected ImportWorkflow $workflow;

    public function __construct(
        CopainClient $client,
        CopainLegacyReaderService $legacyReader,
        ImportWorkflow $workflow
    ) {
        $this->client = $client;
        $this->legacyReader = $legacyReader;
        $this->workflow = $workflow;
    }

    /**
     * Point d’entrée unique
     *
     * @param array $params
     *  - ['id' => int]              → import COPAIN
     *  - ['zip_path' => string]     → import ZIP local
     *
     * @return array
     */
    public function import(array $params): array
    {
        // 1. Déterminer la source
        $source = $this->resolveSource($params);

        // 2. Charger les données
        $rawData = $this->load($source, $params);

        // 3. Normaliser (format unique)
        $normalized = $this->normalize($rawData);

        // 4. Lancer le workflow (async ou non)
        return $this->workflow->start($normalized);
    }

    /**
     * Détermine la source d'import
     */
    protected function resolveSource(array $params): string
    {
        if (!empty($params['zip_path'])) {
            return 'zip';
        }

        if (!empty($params['id'])) {
            return 'copain';
        }

        throw new \InvalidArgumentException('Missing import parameters');
    }

    /**
     * Charge les données depuis la bonne source
     */
    protected function load(string $source, array $params): array
    {
        return match ($source) {

            'copain' => $this->client->fetchCompetitionData(
                $params['id'],
                $params['type'] ?? 0
            ),

            'zip' => $this->legacyReader->read(
                $params['zip_path']
            ),
        };
    }

    /**
     * Normalisation UNIQUE (clé du système)
     *
     * Règles :
     *  - type = (urs_id ? regional : national)
     *  - year = saison
     *  - folder = competXX
     */
    protected function normalize(array $data): array
    {
        return [
            'compet' => $data['compet'],
            'images' => $data['photos'] ?? []
        ];
    }
}