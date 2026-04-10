<?php

use App\Mcp\Servers\ApplicationMcpServer;
use Laravel\Mcp\Facades\Mcp;

Mcp::web('/mcp/application', ApplicationMcpServer::class);
