<?php

namespace Ralkage\AccountLockout\Middleware;

use Carbon\Carbon;
use Flarum\Locale\Translator;
use Flarum\Settings\SettingsRepositoryInterface;
use Flarum\User\Exception\NotAuthenticatedException;
use Flarum\User\UserRepository;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\Arr;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Ralkage\AccountLockout\Event\AccountLocked;
use Ralkage\AccountLockout\Exception\AccountLockedException;
use Ralkage\AccountLockout\Exception\NotAuthenticatedWithAttemptsException;

class CheckAccountLockout implements MiddlewareInterface
{
    protected SettingsRepositoryInterface $settings;
    protected UserRepository $users;
    protected Dispatcher $events;
    protected Translator $translator;

    public function __construct(SettingsRepositoryInterface $settings, UserRepository $users, Dispatcher $events, Translator $translator)
    {
        $this->settings = $settings;
        $this->users = $users;
        $this->events = $events;
        $this->translator = $translator;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // Only intercept POST /api/token (the login endpoint)
        if ($request->getMethod() !== 'POST' || !$this->isTokenEndpoint($request)) {
            return $handler->handle($request);
        }

        $body = $request->getParsedBody();
        $identification = Arr::get($body, 'identification');

        if (!$identification) {
            return $handler->handle($request);
        }

        $user = $this->users->findByIdentification($identification);

        if (!$user) {
            return $handler->handle($request);
        }

        // Skip lockout for admins
        if ($user->isAdmin()) {
            return $handler->handle($request);
        }

        // Check if user is currently locked
        if ($user->is_locked) {
            $this->checkAndHandleLock($user);
        }

        // Proceed with the login attempt, catching auth failures
        try {
            $response = $handler->handle($request);
        } catch (NotAuthenticatedException $e) {
            $this->recordFailedAttempt($user);

            // If the account just got locked, throw the lockout exception immediately
            if ($user->is_locked) {
                $this->checkAndHandleLock($user);
            }

            // Otherwise, throw with remaining attempts info
            $maxAttempts = (int) $this->settings->get('ralkage-account-lockout.max_attempts', 5);
            $remaining = max(0, $maxAttempts - $user->login_failed_count);

            throw new NotAuthenticatedWithAttemptsException($remaining, $maxAttempts);
        }

        return $response;
    }

    protected function isTokenEndpoint(ServerRequestInterface $request): bool
    {
        $path = $request->getUri()->getPath();

        // The API middleware stack sees the path after the base path is stripped.
        // The route is registered as /token in the API routes.
        return $path === '/token' || str_ends_with($path, '/token');
    }

    protected function checkAndHandleLock($user): void
    {
        $mode = $this->settings->get('ralkage-account-lockout.lockout_mode', 'timed');

        if ($mode === 'timed' && $user->locked_until && Carbon::now()->gte($user->locked_until)) {
            // Timed lock has expired — auto-unlock
            $user->is_locked = false;
            $user->locked_until = null;
            $user->locked_at = null;
            $user->login_failed_count = 0;
            $user->save();

            return;
        }

        // Still locked
        if ($mode === 'timed' && $user->locked_until) {
            $minutesRemaining = max((int) ceil(Carbon::now()->diffInMinutes($user->locked_until, false)), 1);
            throw new AccountLockedException(
                $minutesRemaining,
                $this->translator->trans('ralkage-account-lockout.api.error.locked_timed', ['{minutes}' => $minutesRemaining])
            );
        }

        // Manual mode — no expiry
        throw new AccountLockedException(
            null,
            $this->translator->trans('ralkage-account-lockout.api.error.locked_manual')
        );
    }

    protected function recordFailedAttempt($user): void
    {
        $user->login_failed_count = ($user->login_failed_count ?? 0) + 1;
        $maxAttempts = (int) $this->settings->get('ralkage-account-lockout.max_attempts', 5);

        if ($user->login_failed_count >= $maxAttempts) {
            $user->is_locked = true;
            $user->locked_at = Carbon::now();

            $mode = $this->settings->get('ralkage-account-lockout.lockout_mode', 'timed');

            if ($mode === 'timed') {
                $duration = (int) $this->settings->get('ralkage-account-lockout.lockout_duration', 15);
                $user->locked_until = Carbon::now()->addMinutes($duration);
            }

            $this->events->dispatch(new AccountLocked($user));
        }

        $user->save();
    }
}
