<?php

namespace App\Mcp\Tools;

use App\Mcp\Clients\AgnoMcpClient;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;

#[Description('Search across the Agno knowledge base to find relevant information, code examples, API references, and guides about building AI agents with Agno.')]
class SearchAgnoDocsTool extends Tool
{
    public function handle(Request $request): Response
    {
        $validated = $request->validate([
            'query' => ['required', 'string', 'max:500'],
        ]);

        $query = trim($validated['query']);

        if ($query === '') {
            return Response::json([
                'error' => 'Query cannot be empty.',
            ]);
        }

        try {
            $client = new AgnoMcpClient;
            $result = $client->searchDocs($query);

            return Response::json($result);
        } catch (\Throwable $e) {
            return Response::json([
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'query' => $schema->string()
                ->description('Search query for finding Agno documentation, code examples, and API references')
                ->required(),
        ];
    }
}
