---
name: laravel-security
description: Sanctum token patterns, file upload safety, security headers middleware, CORS config, and log redaction for Laravel APIs.
paste-into: .claude/skills/laravel-security/
stack: laravel
type: skill
author: claude-php-laravel-kit
---

# Laravel Security

Implementation-time security patterns for Laravel APIs and web apps. Complements the `security` rule — that rule covers mass assignment, authz, CSRF, rate limits, and secrets (paste it into `CLAUDE.md` first). This skill covers the gaps.

## When to use

- Implementing authentication with Sanctum or Passport.
- Handling file uploads in a controller or action.
- Adding security headers to an API or web application.
- Configuring CORS for an API consumed by a browser.
- Logging request or response data without leaking secrets.

## Sanctum token patterns

Issue short-lived tokens with scoped abilities. Revoke on logout:

```php
// Issue token on login — scope to specific abilities, expire after 30 days
$token = $user->createToken(
    name: 'mobile-app',
    abilities: ['orders:read', 'orders:write'],
    expiresAt: now()->addDays(30),
);

return response()->json(['token' => $token->plainTextToken]);
```

```php
// Revoke the current token on logout
$request->user()->currentAccessToken()->delete();
```

```php
// Revoke all tokens — use on password change or account compromise
$request->user()->tokens()->delete();
```

Protect routes with `auth:sanctum` and ability checks:

```php
Route::middleware(['auth:sanctum', 'abilities:orders:read'])->group(function () {
    Route::get('/orders', [OrdersController::class, 'index']);
});
```

## File upload safety

Validate MIME type and size server-side; store outside the public directory:

```php
// In your Form Request
public function rules(): array
{
    return [
        'document' => ['required', 'file', 'mimes:pdf,docx', 'max:10240'], // 10 MB
    ];
}
```

```php
// In your controller or action — store privately, never under public/
$path = $request->file('document')->store('documents', 'private');
```

- Never store user-uploaded files under `public/` or `storage/app/public/` unless you have verified they cannot be executed by the web server.
- Validate MIME type server-side. The client's `Content-Type` header can be forged.
- Generate a UUID filename for storage so the original name is never used as a path component.

## Security headers middleware

Create a middleware that injects hardened headers on every response:

```php
// app/Http/Middleware/SecurityHeaders.php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        $response->headers->set(
            'Content-Security-Policy',
            "default-src 'self'; script-src 'self'; style-src 'self' 'unsafe-inline'; img-src 'self' data:; font-src 'self'; connect-src 'self'"
        );

        return $response;
    }
}
```

Register globally in `bootstrap/app.php` (Laravel 11+):

```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->append(\App\Http\Middleware\SecurityHeaders::class);
})
```

Adjust the CSP `script-src` and `style-src` directives to match what your app actually loads. Start strict and loosen as needed — not the reverse.

## CORS config

Lock down `config/cors.php` for API endpoints:

```php
return [
    'paths'                    => ['api/*', 'sanctum/csrf-cookie'],
    'allowed_methods'          => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],
    'allowed_origins'          => [env('FRONTEND_URL', 'https://app.example.com')],
    'allowed_origins_patterns' => [],
    'allowed_headers'          => ['Content-Type', 'X-Requested-With', 'Authorization'],
    'exposed_headers'          => [],
    'max_age'                  => 0,
    'supports_credentials'     => true,
];
```

- Never set `allowed_origins` to `['*']` on routes that use `auth:sanctum` or session cookies — browsers will block credentialed requests to wildcard origins anyway, and the misconfiguration signals intent.
- Set `supports_credentials: true` only when the frontend sends cookies (SPA with Sanctum cookie-based auth).
- Drive `FRONTEND_URL` from an environment variable so it differs between local, staging, and production.

## Log redaction

Never log passwords, tokens, or sensitive fields. Use Laravel's built-in mechanisms:

```php
// In your model — $hidden prevents these fields appearing in toArray() / toJson() / logs
protected $hidden = ['password', 'remember_token', 'api_token'];
```

Never log raw request data:

```php
// BAD — logs passwords, tokens, anything in the payload
Log::info('Request', $request->all());

// GOOD — log only what you need
Log::info('Order placed', ['order_id' => $order->id, 'user_id' => $user->id]);
```

If you must log request data for debugging, explicitly exclude sensitive keys:

```php
Log::debug('Request payload', $request->except(['password', 'token', 'secret', 'api_key', 'card_number']));
```
