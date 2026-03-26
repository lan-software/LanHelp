<?php

namespace App\Services;

use App\DTOs\LanCoreUser;
use App\Exceptions\InvalidLanCoreUserException;
use App\Exceptions\LanCoreDisabledException;
use App\Exceptions\LanCoreRequestException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

class LanCoreClient
{
    public function __construct()
    {
        //
    }

    /**
     * Build the URL the browser should be redirected to for SSO authorization.
     *
     * @throws LanCoreDisabledException
     */
    public function ssoAuthorizeUrl(): string
    {
        $this->ensureEnabled();

        return rtrim(config('lancore.base_url'), '/').'/sso/authorize?'.http_build_query([
            'app' => config('lancore.app_slug'),
            'redirect_uri' => config('lancore.callback_url'),
        ]);
    }

    /**
     * Exchange a single-use SSO code for a LanCoreUser.
     *
     * @throws LanCoreDisabledException
     * @throws LanCoreRequestException
     * @throws InvalidLanCoreUserException
     */
    public function exchangeCode(string $code): LanCoreUser
    {
        $this->ensureEnabled();

        $response = $this->withRetries(
            fn () => $this->apiClient()->post('/api/integration/sso/exchange', ['code' => $code])
        );

        if (! $response->successful()) {
            $message = $response->json('error') ?? 'SSO code exchange failed.';
            throw new LanCoreRequestException($message, $response->status());
        }

        return $this->parseUser($response->json('data', []));
    }

    /**
     * Resolve a LanCore user by their ID.
     *
     * @throws LanCoreDisabledException
     * @throws LanCoreRequestException
     * @throws InvalidLanCoreUserException
     */
    public function resolveUserById(int $userId): LanCoreUser
    {
        $this->ensureEnabled();

        $response = $this->withRetries(
            fn () => $this->apiClient()->post('/api/integration/user/resolve', ['user_id' => $userId])
        );

        if (! $response->successful()) {
            $message = $response->json('error') ?? 'User resolve failed.';
            throw new LanCoreRequestException($message, $response->status());
        }

        return $this->parseUser($response->json('data', []));
    }

    /**
     * Resolve a LanCore user by their email address.
     *
     * @throws LanCoreDisabledException
     * @throws LanCoreRequestException
     * @throws InvalidLanCoreUserException
     */
    public function resolveUserByEmail(string $email): LanCoreUser
    {
        $this->ensureEnabled();

        $response = $this->withRetries(
            fn () => $this->apiClient()->post('/api/integration/user/resolve', ['email' => $email])
        );

        if (! $response->successful()) {
            $message = $response->json('error') ?? 'User resolve failed.';
            throw new LanCoreRequestException($message, $response->status());
        }

        return $this->parseUser($response->json('data', []));
    }

    /**
     * @throws InvalidLanCoreUserException
     */
    private function parseUser(array $data): LanCoreUser
    {
        if (empty($data['id']) || empty($data['username'])) {
            throw new InvalidLanCoreUserException;
        }

        return LanCoreUser::fromArray($data);
    }

    /**
     * @throws LanCoreRequestException
     */
    private function withRetries(callable $callback): \Illuminate\Http\Client\Response
    {
        $retries = config('lancore.retries', 2);
        $delay = config('lancore.retry_delay', 100);
        $attempt = 0;

        while (true) {
            try {
                return $callback();
            } catch (ConnectionException $e) {
                if ($attempt >= $retries) {
                    throw new LanCoreRequestException('LanCore is unreachable: '.$e->getMessage());
                }
                $attempt++;
                usleep($delay * 1000);
            }
        }
    }

    private function apiClient(): \Illuminate\Http\Client\PendingRequest
    {
        $baseUrl = config('lancore.internal_url') ?? config('lancore.base_url');

        return Http::baseUrl(rtrim($baseUrl, '/'))
            ->timeout(config('lancore.timeout', 5))
            ->withToken(config('lancore.token'))
            ->acceptJson();
    }

    /**
     * @throws LanCoreDisabledException
     */
    private function ensureEnabled(): void
    {
        if (! config('lancore.enabled')) {
            throw new LanCoreDisabledException;
        }
    }
}
