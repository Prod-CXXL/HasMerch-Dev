# Security

This document defines the security rules, known vulnerabilities, and best practices for the HasMerch platform. It applies to all contributors, agents, and anyone with access to the codebase or infrastructure.

---

## Ground Rules

These are non-negotiable and apply to every change, in every file, at every stage of development.

- **Never commit credentials.** No database passwords, API keys, Stripe secrets, or tokens of any kind belong in source code or documentation. Ever. Not even temporarily.
- **Never expose secret keys in the frontend.** Publishable keys (Stripe, Snipcart) may appear in client-side code. Secret keys never may — they belong exclusively in server-side environment variables.
- **Never trust user input.** Every value that comes from a request — URL parameters, form fields, headers — must be validated and sanitized before it is used in a query, file path, or output.
- **Never skip output escaping.** Every dynamic value rendered into HTML must be escaped with `htmlspecialchars()` unless it is known-safe HTML generated server-side.
- **Never modify payment logic without explicit explanation.** Any change touching Stripe, checkout sessions, webhooks, or payout logic must be explained in detail before implementation.

---

## Credentials and Secrets

### The Problem (Current State)

`backend/config/db.php` currently contains a hardcoded database password directly in source code. This is a critical vulnerability. If the repository is ever made public, or if access is granted to any third party, those credentials are compromised.

Similarly, the Snipcart API key appears in `storage/branding/branding.json` and a Stripe publishable key is hardcoded in `stripe-checkout.js`. These are lower severity (publishable keys are designed to be client-side), but they still should not be committed.

### The Fix (Required)

All credentials and environment-specific values must live in a `.env` file that is excluded from Git via `.gitignore`.

**`.env` structure:**

```
DB_HOST=localhost
DB_NAME=hasmerch
DB_USER=root
DB_PASS=your_password_here

STRIPE_SECRET_KEY=sk_test_...
STRIPE_PUBLISHABLE_KEY=pk_test_...
STRIPE_WEBHOOK_SECRET=whsec_...

SNIPCART_KEY=your_snipcart_key_here
```

**`.env.example`** — a copy of the above with all values blanked out — must be committed so contributors know what variables are required:

```
DB_HOST=
DB_NAME=
DB_USER=
DB_PASS=

STRIPE_SECRET_KEY=
STRIPE_PUBLISHABLE_KEY=
STRIPE_WEBHOOK_SECRET=

SNIPCART_KEY=
```

**In production**, these values live in Cloudflare Pages environment variables under Settings → Environment Variables. They are never in any file in the repository.

### `.gitignore` Must Include

```
.env
/backend/config/db.php
```

If `db.php` ever needs to remain as a file (for legacy compatibility), it must read from `$_ENV` or `getenv()` rather than containing literal values:

```php
return [
    'host'   => getenv('DB_HOST'),
    'dbname' => getenv('DB_NAME'),
    'user'   => getenv('DB_USER'),
    'pass'   => getenv('DB_PASS'),
];
```

---

## Output Escaping (XSS Prevention)

Cross-site scripting (XSS) occurs when untrusted data is rendered into HTML without escaping. On HasMerch, this is particularly high-risk because product data, store names, and branding values originate from user-controlled sources.

### Rule

Every dynamic value output into HTML must be wrapped in `htmlspecialchars()`:

```php
// Correct
echo htmlspecialchars($storeName);
echo htmlspecialchars($product['title']);
echo htmlspecialchars($product['description']);

// Dangerous — never do this with user data
echo $storeName;
echo $product['title'];
```

### Known Gaps

The following views currently output unescaped variables and must be corrected:

- `frontend/views/store/product.php` — `$product['title']`, `$product['description']`, `$product['price']`
- `frontend/views/partials/header.php` — the `$storeName` output in some places lacks escaping
- Any view that renders `$body` (the Markdown-rendered product body from `ContentService`) — this must only ever contain HTML generated server-side from trusted `.md` files, never from user-submitted content

### Markdown Output

`ContentService` renders Markdown to HTML via `Parsedown`. This output should be treated as trusted only when the source `.md` files are controlled by HasMerch or verified creators. If product descriptions are ever accepted as user input through a web form in the future, the rendered HTML must be passed through an HTML sanitizer before output.

---

## SQL Injection Prevention

### Rule

All database queries must use **prepared statements with bound parameters**. String concatenation into SQL queries is never acceptable.

```php
// Correct — prepared statement
$stmt = $this->db->prepare("SELECT * FROM branding WHERE user_id = ?");
$stmt->execute([$userId]);

// Dangerous — never do this
$stmt = $this->db->query("SELECT * FROM branding WHERE user_id = " . $userId);
```

### Current Status

`BrandLoader.php` and `ProductService.php` both use PDO prepared statements correctly. This pattern must be maintained in all future database code.

---

## Routing and Path Safety

### The Router

`index.php` accepts a `route` parameter from `$_GET` and maps it against a whitelist (`$viewMap`) before including any file. This pattern is correct — unknown routes return a 404 and no file is included.

```php
// Safe — whitelist checked before any include
$viewMap = [
    'home'  => 'store/home.php',
    'shop'  => 'store/shop.php',
    ...
];

if (!isset($viewMap[$page])) {
    // 404 — no file included from user input
}
```

This whitelist must always be maintained. The route value from `$_GET` must never be passed directly to `include`, `require`, or any file system function.

### File Path Construction

`StoreResolver` constructs `$storeFolder` from the resolved subdomain. The subdomain value comes from `HTTP_HOST`, which is user-influenced. Before any file path is built from it, the value must be:

1. Matched against the database (which `BrandLoader` does — an unknown slug returns empty)
2. Lowercased and stripped of any characters that are not alphanumeric or hyphens before being used in a file path

As the file-based product loading via `ContentService` is wired in, ensure that no user-supplied value can traverse outside the intended store folder (e.g. `../../` in a subdomain or route).

---

## CSRF Protection

Cross-site request forgery (CSRF) allows an attacker to trick a logged-in user into submitting a form or triggering an action on their behalf.

### Current State

No CSRF protection exists yet because there are no authenticated forms (registration, login, dashboard) in production. This must be implemented before any of those features go live.

### Required Implementation

Every state-changing form (login, register, update store settings, add product, connect Stripe) must include a CSRF token:

**Generating and storing a token:**
```php
// On page load, generate a token and store it in the session
session_start();
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
```

**Including the token in the form:**
```html
<input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
```

**Validating on submission:**
```php
if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
    http_response_code(403);
    exit('Invalid request.');
}
```

---

## Authentication (Planned)

When the login and registration system is built, the following rules apply:

### Passwords

- Passwords must be hashed with `password_hash($password, PASSWORD_BCRYPT)` — never stored as plaintext or MD5/SHA1
- Verification uses `password_verify($input, $hash)` — never a direct string comparison
- Minimum password length of 8 characters must be enforced server-side (not just client-side)

### Sessions

- Call `session_regenerate_id(true)` immediately after a successful login to prevent session fixation
- Store only the user ID in the session — never the full user record or password hash
- Set session cookies with `HttpOnly` and `Secure` flags:

```php
session_set_cookie_params([
    'secure'   => true,
    'httponly' => true,
    'samesite' => 'Lax',
]);
```

### Access Control

- Every dashboard or creator-only route must verify the session before rendering
- Verify that the authenticated user owns the resource they are accessing — a logged-in creator must not be able to modify another creator's products, branding, or orders

---

## Stripe Security

### Webhook Verification

Stripe sends webhook events (payment succeeded, refund created, etc.) to your server via HTTP POST. These must be verified using the webhook signing secret — otherwise any attacker can send fake events.

```php
$payload   = file_get_contents('php://input');
$sigHeader = $_SERVER['HTTP_STRIPE_SIGNATURE'];
$secret    = getenv('STRIPE_WEBHOOK_SECRET');

try {
    $event = \Stripe\Webhook::constructEvent($payload, $sigHeader, $secret);
} catch (\Stripe\Exception\SignatureVerificationException $e) {
    http_response_code(400);
    exit();
}
```

### Checkout Sessions

- Checkout sessions must always be created server-side — never trust price or product data sent from the client
- The amount charged must be looked up from the server (the product record), not taken from a POST parameter
- After a successful payment, fulfillment logic must be triggered by the webhook, not by a redirect URL (redirect URLs can be manipulated)

### Keys

- `STRIPE_SECRET_KEY` — server-side only, never in any client-side file or HTML output
- `STRIPE_PUBLISHABLE_KEY` — safe for client-side use, but should still be loaded from environment config rather than hardcoded

---

## NFC Card Redirect System

The NFC redirect endpoint (`tap.hasmerch.com/{card-uid}`) performs a lookup by card UID and issues a redirect. This introduces two security considerations:

**Open redirect prevention** — the destination URL must be stored in the database and issued as a server-controlled redirect. The destination must never be taken from a query parameter supplied by the visitor. Validate that stored URLs use `http://` or `https://` schemes only before redirecting.

**Card UID enumeration** — UIDs should not be sequential integers. Use a random token (e.g. a UUID or `bin2hex(random_bytes(8))`) so that card UIDs cannot be guessed by incrementing a number.

---

## HTTP Security Headers

The following headers should be set on every response, either in `.htaccess` or in the PHP application bootstrap:

```apache
# .htaccess additions
Header always set X-Frame-Options "SAMEORIGIN"
Header always set X-Content-Type-Options "nosniff"
Header always set Referrer-Policy "strict-origin-when-cross-origin"
Header always set Permissions-Policy "geolocation=(), microphone=(), camera=()"
```

A Content Security Policy (CSP) header should be added once the full list of external scripts (Stripe, Snipcart, Google Fonts, Font Awesome) is finalized, to prevent unauthorized script execution.

---

## Dependency and Key Rotation

- If a key or password is ever committed to Git by mistake, treat it as compromised immediately — rotate it in the provider dashboard (Stripe, database, Snipcart) before anything else, then clean the Git history
- Regularly review any third-party scripts loaded via CDN (Snipcart, Stripe.js, Font Awesome) — pin to specific versions rather than `latest` where possible
- The Snipcart key in `branding.json` must be moved to environment configuration and removed from the file before that file is ever committed to a public repository

---

## Reporting a Vulnerability

If you discover a security issue in HasMerch, do not open a public GitHub issue. Report it directly to the project owner through a private channel. Include a description of the issue, the affected file or endpoint, and steps to reproduce if possible.
