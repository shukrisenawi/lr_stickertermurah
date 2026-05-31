<?php

namespace App\Mcp\Servers;

use App\Mcp\Tools\QueryAgnoDocsTool;
use App\Mcp\Tools\SearchAgnoDocsTool;
use Laravel\Mcp\Server;
use Laravel\Mcp\Server\Attributes\Instructions;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Version;

#[Name('Agno Documentation MCP Server')]
#[Version('1.0.0')]
#[Instructions('Use this server to search the Agno knowledge base and documentation. Agno is an AI agent platform for building, running, and managing AI agents. This server provides tools to search Agno docs, get code examples, and query the documentation filesystem.')]
class AgnoServer extends Server
{
    protected array $tools = [
        SearchAgnoDocsTool::class,
        QueryAgnoDocsTool::class,
    ];

    protected array $resources = [];

    protected array $prompts = [];
}
