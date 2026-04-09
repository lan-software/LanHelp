<?php

namespace App\Http\Controllers\Auth;

use App\Actions\SyncUserRolesFromLanCore;
use App\Http\Controllers\Controller;
use App\Services\UserSyncService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use LanSoftware\LanCoreClient\Exceptions\InvalidLanCoreUserException;
use LanSoftware\LanCoreClient\Exceptions\LanCoreDisabledException;
use LanSoftware\LanCoreClient\Exceptions\LanCoreRequestException;
use LanSoftware\LanCoreClient\Exceptions\LanCoreUnavailableException;
use LanSoftware\LanCoreClient\LanCoreClient;
use Symfony\Component\HttpFoundation\Response;

class LanCoreAuthController extends Controller
{
    public function __construct(
        private readonly LanCoreClient $client,
        private readonly UserSyncService $syncService,
        private readonly SyncUserRolesFromLanCore $syncRoles,
    ) {}

    /**
     * Redirect the browser to the LanCore SSO authorization page.
     */
    public function redirect(): Response
    {
        try {
            $url = $this->client->ssoAuthorizeUrl();
        } catch (LanCoreDisabledException) {
            return redirect()->route('login');
        }

        return Inertia::location($url);
    }

    /**
     * Handle the callback from LanCore after the user has authenticated.
     */
    public function callback(Request $request): RedirectResponse
    {
        $code = $request->string('code')->toString();

        if (strlen($code) !== 64) {
            return redirect()->route('home')->with('error', 'Invalid SSO callback. Please try again.');
        }

        try {
            $lanCoreUser = $this->client->exchangeCode($code);
            $user = $this->syncService->resolveFromUpstream($lanCoreUser);
            $this->syncRoles->handle($user, $lanCoreUser->roles ?? []);
        } catch (LanCoreUnavailableException) {
            return redirect()->route('home')->with('error', 'Could not connect to authentication service. Please try again later.');
        } catch (LanCoreRequestException $e) {
            $message = match ($e->statusCode) {
                400 => 'The login link has expired. Please try again.',
                401, 403 => 'Authentication service error. Please try again later.',
                default => 'Could not connect to authentication service. Please try again later.',
            };

            return redirect()->route('home')->with('error', $message);
        } catch (InvalidLanCoreUserException) {
            return redirect()->route('home')->with('error', 'Your account information is incomplete. Please contact support.');
        } catch (LanCoreDisabledException) {
            return redirect()->route('login');
        }

        Auth::login($user, remember: true);

        $request->session()->regenerate();

        return redirect()->intended(route('home'));
    }

    /**
     * Return the LanCore integration status as JSON.
     */
    public function status(): JsonResponse
    {
        return response()->json(['enabled' => (bool) config('lancore.enabled')]);
    }
}
