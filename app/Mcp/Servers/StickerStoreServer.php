<?php

namespace App\Mcp\Servers;

use App\Mcp\Tools\GetStickerPriceTool;
use Laravel\Mcp\Server;
use Laravel\Mcp\Server\Attributes\Instructions;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Version;

#[Name('Sticker Store MCP Server')]
#[Version('1.0.0')]
#[Instructions('Use this server to get mirrorcote sticker sizes and prices from the Laravel database for the StickerTermurah store.')]
class StickerStoreServer extends Server
{
    protected array $tools = [
        GetStickerPriceTool::class,
    ];

    protected array $resources = [];

    protected array $prompts = [];
}
