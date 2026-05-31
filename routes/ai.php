<?php

use App\Mcp\Servers\AgnoServer;
use App\Mcp\Servers\StickerStoreServer;
use Laravel\Mcp\Facades\Mcp;

// HTTP transport endpoints (for web-based MCP clients)
Mcp::web('/mcp/sticker-store', StickerStoreServer::class);
Mcp::web('/mcp/agno', AgnoServer::class);

// STDIO/local transport handles (run with: php artisan mcp:start <handle>)
Mcp::local('sticker-store', StickerStoreServer::class);
Mcp::local('agno', AgnoServer::class);
