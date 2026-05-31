<?php

namespace App\Mcp\Tools;

use App\Mcp\Clients\AgnoMcpClient;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;

#[Description('Run a read-only query against the Agno documentation filesystem using shell-like commands (rg, cat, head, tree, ls, etc.). Use this to read full documentation pages, search with regex, or explore the docs structure.')]
class QueryAgnoDocsTool extends Tool
{
    public function handle(Request $request): Response
    {
        $validated = $request->validate([
            'command' => ['required', 'string', 'max:1000'],
        ]);

        $command = trim($validated['command']);

        if ($command === '') {
            return Response::json([
                'error' => 'Command cannot be empty.',
            ]);
        }

        try {
            $client = new AgnoMcpClient;
            $result = $client->queryDocsFilesystem($command);

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
            'command' => $schema->string()
                ->description('Shell command to run against the Agno documentation filesystem (e.g., "rg -il \\"agent\\" /", "cat /quickstart.mdx", "tree / -L 2")')
                ->required(),
        ];
    }
}
