<?php

namespace App\Mcp\Clients;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class AgnoMcpClient
{
    private string $endpoint;

    private const PROTOCOL_VERSION = '2025-11-25';

    public function __construct(?string $endpoint = null)
    {
        $this->endpoint = $endpoint ?? 'https://docs.agno.com/mcp';
    }

    public function searchDocs(string $query): array
    {
        $sessionId = $this->initialize();
        $this->sendInitialized($sessionId);

        return $this->callTool($sessionId, 'search_agno', [
            'query' => $query,
        ]);
    }

    public function queryDocsFilesystem(string $command): array
    {
        $sessionId = $this->initialize();
        $this->sendInitialized($sessionId);

        return $this->callTool($sessionId, 'query_docs_filesystem_agno', [
            'command' => $command,
        ]);
    }

    private function initialize(): string
    {
        $response = Http::post($this->endpoint, [
            'jsonrpc' => '2.0',
            'id' => 1,
            'method' => 'initialize',
            'params' => [
                'protocolVersion' => self::PROTOCOL_VERSION,
                'capabilities' => new \stdClass,
                'clientInfo' => [
                    'name' => 'laravel-mcp-agno',
                    'version' => '1.0.0',
                ],
            ],
        ]);

        if ($response->failed()) {
            throw new RuntimeException(
                'Failed to initialize Agno MCP session: '.$response->body()
            );
        }

        $sessionId = $response->header('MCP-Session-Id')
            ?? $response->header('mcp-session-id');

        if (! $sessionId) {
            $body = $response->json();
            throw new RuntimeException(
                'No MCP-Session-Id header received from Agno MCP server. Response: '.json_encode($body)
            );
        }

        return $sessionId;
    }

    private function sendInitialized(string $sessionId): void
    {
        Http::withHeaders(['MCP-Session-Id' => $sessionId])
            ->post($this->endpoint, [
                'jsonrpc' => '2.0',
                'method' => 'notifications/initialized',
                'params' => new \stdClass,
            ]);
    }

    private function callTool(string $sessionId, string $toolName, array $arguments): array
    {
        $response = Http::withHeaders(['MCP-Session-Id' => $sessionId])
            ->post($this->endpoint, [
                'jsonrpc' => '2.0',
                'id' => 2,
                'method' => 'tools/call',
                'params' => [
                    'name' => $toolName,
                    'arguments' => $arguments,
                ],
            ]);

        if ($response->failed()) {
            throw new RuntimeException(
                "Failed to call Agno MCP tool '{$toolName}': ".$response->body()
            );
        }

        $body = $response->json();

        if (isset($body['error'])) {
            throw new RuntimeException(
                "Agno MCP tool '{$toolName}' error: ".json_encode($body['error'])
            );
        }

        return $body['result'] ?? [];
    }
}
