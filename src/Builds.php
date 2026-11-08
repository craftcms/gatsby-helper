<?php

declare(strict_types=1);

namespace CraftCms\GatsbyHelper;

use Illuminate\Container\Attributes\Singleton;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

use function Illuminate\Support\defer;

/**
 * Triggers Gatsby builds.
 *
 * @since 1.0.0
 */
#[Singleton]
class Builds
{
    public function __construct(
        private readonly Settings $settings,
    ) {}

    /**
     * Calls the Build Webhook URL once the request, console command or queue job has finished.
     *
     * Calling this several times in one request still sends a single build request.
     */
    public function trigger(): void
    {
        $url = $this->settings->buildWebhookUrl();

        if ($url === null) {
            return;
        }

        defer(fn () => $this->send($url), 'gatsby-helper:build');
    }

    private function send(string $url): void
    {
        try {
            Http::withHeaders(['x-preview-update-source' => 'Craft CMS'])
                ->withBody('', 'application/json')
                ->post($url)
                ->throw();
        } catch (ConnectionException|RequestException $e) {
            Log::error("Unable to trigger a Gatsby build: {$e->getMessage()}");
        }
    }
}
