---
name: laravel-patterns
description: Controllers→Services→Actions, DTOs, Service Container bindings, API response shape, Query Objects, transactions, caching, and events/queues for production Laravel apps.
paste-into: .claude/skills/laravel-patterns/
stack: laravel
type: skill
author: claude-php-laravel-kit
---

# Laravel Patterns

Production-grade architectural patterns for Laravel applications. Covers the layers and conventions that belong neither in Eloquent models nor in controllers.

> **Related skills:** For Eloquent query safety and N+1 prevention see the `eloquent-query-builder` skill. For Artisan generators see `artisan-make`. For Pest tests see `pest-test-writer`. For migration safety see the `make-migration` command.

## When to use

- Structuring a new feature: where does the logic live?
- Wiring a dependency to an interface in a service provider.
- Designing a JSON API response shape.
- Adding caching, events, or queued jobs to an existing feature.

## Project structure

Use conventional Laravel layout with explicit layer boundaries:

```
app/
├── Actions/            # Single-purpose use cases (CreateOrder, CancelSubscription)
├── Console/
├── Events/
├── Exceptions/
├── Http/
│   ├── Controllers/    # Thin HTTP adapters — resolve input, delegate, return response
│   ├── Middleware/
│   ├── Requests/       # Form request validation + authorization
│   └── Resources/      # API resources and transformers
├── Jobs/
├── Models/
├── Policies/
├── Providers/
├── Services/           # Orchestrating services that coordinate multiple actions/models
└── Support/            # Value objects, helpers, enums
```

## Controllers → Services → Actions

Keep controllers thin. Put orchestration in services; put single-purpose logic in actions.

### The flow

```
HTTP request
  → Form Request (validation + authorization)
  → Controller (resolve input, call action/service, return response)
  → Action or Service (business logic)
  → Model / Repository (persistence)
```

### DTO: bridge between Form Request and Action

Define a plain readonly class for the data crossing the boundary:

```php
final readonly class CreateOrderData
{
    public function __construct(
        public int $customerId,
        public array $items,
    ) {}
}
```

Add a `toDto()` method to the Form Request:

```php
final class StoreOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Order::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'customer_id' => ['required', 'integer', 'exists:customers,id'],
            'items'        => ['required', 'array', 'min:1'],
            'items.*.sku'  => ['required', 'string'],
            'items.*.qty'  => ['required', 'integer', 'min:1'],
        ];
    }

    public function toDto(): CreateOrderData
    {
        return new CreateOrderData(
            customerId: (int) $this->validated('customer_id'),
            items: $this->validated('items'),
        );
    }
}
```

### Action: single responsibility

```php
final class CreateOrderAction
{
    public function handle(CreateOrderData $data): Order
    {
        return DB::transaction(function () use ($data): Order {
            $order = Order::create(['customer_id' => $data->customerId]);
            foreach ($data->items as $item) {
                $order->items()->create($item);
            }
            return $order;
        });
    }
}
```

### Controller: thin adapter

```php
final class OrdersController extends Controller
{
    public function __construct(private CreateOrderAction $action) {}

    public function store(StoreOrderRequest $request): JsonResponse
    {
        $order = $this->action->handle($request->toDto());

        return response()->json([
            'success' => true,
            'data'    => OrderResource::make($order),
            'error'   => null,
            'meta'    => null,
        ], 201);
    }
}
```

## Service Container bindings

Bind interfaces to implementations in `AppServiceProvider` so consumers depend on contracts, not concrete classes:

```php
final class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(OrderRepository::class, EloquentOrderRepository::class);
        $this->app->singleton(PaymentGateway::class, StripeGateway::class);
    }
}
```

- Use `bind` for a fresh instance per resolve.
- Use `singleton` for a shared instance (stateless gateways, clients).
- Inject the interface in constructors — never resolve from the container with `app()` inside business logic.

## Consistent API response shape

Every JSON endpoint returns the same envelope:

```json
{
  "success": true,
  "data": { ... },
  "error": null,
  "meta": null
}
```

For paginated collections, populate `meta`:

```php
$orders = Order::query()->with('items')->paginate(25);

return response()->json([
    'success' => true,
    'data'    => OrderResource::collection($orders->items()),
    'error'   => null,
    'meta'    => [
        'page'     => $orders->currentPage(),
        'per_page' => $orders->perPage(),
        'total'    => $orders->total(),
    ],
]);
```

For errors, keep `data` null and put the message in `error`:

```php
return response()->json([
    'success' => false,
    'data'    => null,
    'error'   => 'Order not found.',
    'meta'    => null,
], 404);
```

Never vary the envelope shape — clients should be able to type it once.

## Query Objects

Encapsulate complex filter logic in a dedicated class instead of repeating `where` chains at call sites:

```php
final class OrderQuery
{
    public function __construct(private Builder $query) {}

    public static function make(): self
    {
        return new self(Order::query());
    }

    public function forCustomer(int $customerId): self
    {
        return new self((clone $this->query)->where('customer_id', $customerId));
    }

    public function placedAfter(Carbon $date): self
    {
        return new self((clone $this->query)->where('created_at', '>=', $date));
    }

    public function get(): Collection
    {
        return $this->query->get();
    }

    public function paginate(int $perPage = 25): LengthAwarePaginator
    {
        return $this->query->paginate($perPage);
    }
}

// Usage
$orders = OrderQuery::make()
    ->forCustomer($user->id)
    ->placedAfter(now()->subMonth())
    ->paginate();
```

## Transactions

Wrap multi-step writes in `DB::transaction()` so they succeed or fail atomically:

```php
use Illuminate\Support\Facades\DB;

DB::transaction(function (): void {
    $order->update(['status' => 'paid']);
    $order->items()->update(['paid_at' => now()]);
    PaymentRecord::create([...]);
});
```

- If an exception is thrown inside the closure the transaction rolls back automatically.
- Avoid performing HTTP calls or queue dispatches inside a transaction — they cannot be rolled back.
- For long transactions, prefer chunked updates or dedicated jobs.

## Caching

Cache read-heavy results; invalidate on model events.

```php
use Illuminate\Support\Facades\Cache;

// Cache a computed result for 1 hour
$summary = Cache::remember("orders.summary.{$userId}", now()->addHour(), function () use ($userId): array {
    return Order::query()->where('customer_id', $userId)->selectRaw('count(*) as total, sum(total_cents) as revenue')->first()->toArray();
});
```

### Tag-based invalidation

Group related cache entries with tags so they can be cleared together:

```php
// Store with tags
Cache::tags(['orders', "customer.{$userId}"])->put("orders.recent.{$userId}", $orders, now()->addMinutes(30));

// Invalidate all order cache for a customer
Cache::tags(["customer.{$userId}"])->flush();
```

### Invalidate on model events

```php
// In Order model
protected static function booted(): void
{
    $flush = function (Order $order): void {
        Cache::tags(["customer.{$order->customer_id}"])->flush();
    };

    static::created($flush);
    static::updated($flush);
    static::deleted($flush);
}
```

> Note: tag-based cache requires a driver that supports tags (Redis, Memcached). File and database drivers do not support tags.

## Events, Jobs, and Queues

### Emit domain events for side effects

Fire an event after a state change; let listeners handle emails, analytics, webhooks:

```php
// In the action, after the write commits
event(new OrderPlaced($order));
```

```php
final class OrderPlaced
{
    public function __construct(public readonly Order $order) {}
}
```

```php
final class SendOrderConfirmation implements ShouldQueue
{
    public function handle(OrderPlaced $event): void
    {
        Mail::to($event->order->customer)->send(new OrderConfirmationMail($event->order));
    }
}
```

Register in `EventServiceProvider::$listen` (Laravel ≤10) or via `Event::listen()` in a provider boot (Laravel 11+).

### Queue slow work as jobs

For anything that takes more than a few hundred milliseconds (reports, exports, third-party API calls):

```php
ProcessOrderExport::dispatch($order)->onQueue('exports');
```

```php
final class ProcessOrderExport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(private readonly Order $order) {}

    public function handle(): void
    {
        // slow work here
    }

    public function retryUntil(): DateTime
    {
        return now()->addHours(2);
    }
}
```

### Idioms

- Prefer idempotent job handlers — the same job may run more than once on retry.
- Dispatch events **after** the transaction commits, not inside it.
- Use `->onQueue('name')` to separate fast and slow work onto different workers.
- Set `retryUntil()` or `$tries` on jobs that call external APIs.
