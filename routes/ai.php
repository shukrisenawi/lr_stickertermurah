<?php

use App\Mcp\Servers\StickerStoreServer;
use Laravel\Mcp\Facades\Mcp;

// HTTP transport endpoint (for web-based MCP clients)
Mcp::web('/mcp/sticker-store', StickerStoreServer::class);

// STDIO/local transport handle (run with: php artisan mcp:start sticker-store)
Mcp::local('sticker-store', StickerStoreServer::class);
