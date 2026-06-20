<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class FirestoreService
{
    protected string $projectId;
    protected string $accessToken;
    protected string $baseUrl;

    public function __construct()
    {
        $this->projectId = env('FIREBASE_PROJECT_ID');
        $this->baseUrl = "https://firestore.googleapis.com/v1/projects/{$this->projectId}/databases/(default)/documents";
    }

    /**
     * Get access token from service account JSON.
     */
    protected function getAccessToken(): string
    {
        return Cache::remember('firebase_access_token', 3500, function () {
            $credentialsPath = env('FIREBASE_CREDENTIALS');
            if (!file_exists($credentialsPath)) {
                throw new \Exception('Firebase credentials file not found');
            }

            $credentials = json_decode(file_get_contents($credentialsPath), true);
            
            $jwt = $this->createJWT($credentials);
            
            $response = Http::asForm()->post('https://oauth2.googleapis.com/token', [
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion' => $jwt,
            ]);

            return $response->json('access_token');
        });
    }

    /**
     * Create JWT for service account.
     */
    protected function createJWT(array $credentials): string
    {
        $now = time();
        $header = base64_encode(json_encode(['alg' => 'RS256', 'typ' => 'JWT']));
        
        $payload = base64_encode(json_encode([
            'iss'   => $credentials['client_email'],
            'sub'   => $credentials['client_email'],
            'aud'   => 'https://oauth2.googleapis.com/token',
            'iat'   => $now,
            'exp'   => $now + 3600,
            'scope' => 'https://www.googleapis.com/auth/datastore https://www.googleapis.com/auth/cloud-platform',
        ]));

        $signature = '';
        openssl_sign("{$header}.{$payload}", $signature, $credentials['private_key'], 'SHA256');
        $signature = base64_encode($signature);

        return "{$header}.{$payload}.{$signature}";
    }

    /**
     * Format document to array.
     */
    protected function formatDocument(array $document): ?array
    {
        if (!isset($document['name'])) return null;

        // Extract ID from name: projects/.../documents/users/xxx
        $parts = explode('/', $document['name']);
        $id = end($parts);

        $data = ['id' => $id];
        
        if (isset($document['fields'])) {
            foreach ($document['fields'] as $key => $value) {
                $data[$key] = $this->decodeValue($value);
            }
        }

        if (isset($document['createTime'])) $data['created_at'] = $document['createTime'];
        if (isset($document['updateTime'])) $data['updated_at'] = $document['updateTime'];

        return $data;
    }

    /**
     * Decode Firestore value.
     */
    protected function decodeValue(array $value): mixed
    {
        $types = ['stringValue', 'integerValue', 'doubleValue', 'booleanValue', 'timestampValue', 'referenceValue', 'mapValue', 'arrayValue'];

        foreach ($types as $type) {
            if (isset($value[$type])) {
                return match ($type) {
                    'mapValue' => $this->formatFields($value[$type]['fields'] ?? []),
                    'arrayValue' => array_map(fn($v) => $this->decodeValue($v), $value[$type]['values'] ?? []),
                    'timestampValue' => $value[$type],
                    default => $value[$type],
                };
            }
        }
        return null;
    }

    /**
     * Encode value to Firestore format.
     */
    protected function encodeValue(mixed $value): array
    {
        if (is_string($value)) return ['stringValue' => $value];
        if (is_int($value)) return ['integerValue' => (string) $value];
        if (is_float($value)) return ['doubleValue' => $value];
        if (is_bool($value)) return ['booleanValue' => $value];
        if (is_array($value)) {
            if (array_keys($value) !== range(0, count($value) - 1)) {
                return ['mapValue' => ['fields' => $this->encodeFields($value)]];
            }
            return ['arrayValue' => ['values' => array_map(fn($v) => $this->encodeValue($v), $value)]];
        }
        if ($value === null) return ['nullValue' => null];
        return ['stringValue' => (string) $value];
    }

    protected function formatFields(array $fields): array
    {
        $result = [];
        foreach ($fields as $key => $value) {
            $result[$key] = $this->decodeValue($value);
        }
        return $result;
    }

    protected function encodeFields(array $data): array
    {
        $fields = [];
        foreach ($data as $key => $value) {
            $fields[$key] = $this->encodeValue($value);
        }
        return $fields;
    }

    protected function headers(): array
    {
        return [
            'Authorization' => 'Bearer ' . $this->getAccessToken(),
            'Content-Type' => 'application/json',
        ];
    }

    // ─── PUBLIC API ─────────────────────────────────────

    public function getAll(string $collection): array
    {
        $response = Http::withHeaders($this->headers())
            ->get("{$this->baseUrl}/{$collection}");

        $data = $response->json();
        $documents = $data['documents'] ?? [];

        return array_map(fn($doc) => $this->formatDocument($doc), $documents);
    }

    public function get(string $collection, string $docId): ?array
    {
        $response = Http::withHeaders($this->headers())
            ->get("{$this->baseUrl}/{$collection}/{$docId}");

        if ($response->failed()) return null;

        return $this->formatDocument($response->json());
    }

    public function set(string $collection, string $docId, array $data): array
    {
        $response = Http::withHeaders($this->headers())
            ->patch("{$this->baseUrl}/{$collection}/{$docId}", [
                'fields' => $this->encodeFields($data),
            ]);

        return $this->formatDocument($response->json());
    }

    public function delete(string $collection, string $docId): bool
    {
        $response = Http::withHeaders($this->headers())
            ->delete("{$this->baseUrl}/{$collection}/{$docId}");

        return $response->successful();
    }

    public function query(string $collection, array $where = [], int $limit = 50): array
    {
        $structuredQuery = [
            'from' => [['collectionId' => $collection]],
            'limit' => $limit,
        ];

        if (!empty($where)) {
            $filters = [];
            foreach ($where as $condition) {
                // ['field', 'operator', 'value']
                $filters[] = [
                    'fieldFilter' => [
                        'field' => ['fieldPath' => $condition[0]],
                        'op' => $condition[1], // EQUAL, LESS_THAN, GREATER_THAN, etc
                        'value' => $this->encodeValue($condition[2]),
                    ],
                ];
            }

            $structuredQuery['where'] = [
                'compositeFilter' => [
                    'op' => 'AND',
                    'filters' => $filters,
                ],
            ];
        }

        $response = Http::withHeaders($this->headers())
            ->post("{$this->baseUrl}:runQuery", [
                'structuredQuery' => $structuredQuery,
            ]);

        $results = $response->json();
        $documents = [];

        foreach ($results as $result) {
            if (isset($result['document'])) {
                $documents[] = $this->formatDocument($result['document']);
            }
        }

        return $documents;
    }

    public function add(string $collection, array $data): ?array
    {
        $response = Http::withHeaders($this->headers())
            ->post("{$this->baseUrl}/{$collection}", [
                'fields' => $this->encodeFields($data),
            ]);

        if ($response->failed()) return null;

        return $this->formatDocument($response->json());
    }

    public function count(string $collection): int
    {
        return count($this->getAll($collection));
    }
}
