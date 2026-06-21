# Development Workflow

This document explains how code moves from local development to GitHub and then out to Cloudflare Pages. Every deployment starts on your machine and ends automatically in production — no manual uploads, no FTP, no separate deploy step.

---

## How the Pipeline Works

```
Your Machine
    ↓
  Edit files locally
    ↓
  Test in browser
    ↓
git add → git commit → git push
    ↓
GitHub Repository
    ↓
  Cloudflare Pages detects the push
    ↓
  Cloudflare builds and deploys automatically
    ↓
hasmerch.com (live)
```

A push to the `main` branch is all it takes to trigger a deployment. Cloudflare Pages watches the repository and handles the rest.

---

## Local Development Setup

### Prerequisites

Make sure these are installed before starting:

- **Git** — version control
- **A local web server** — MAMP, XAMPP, Laravel Herd, or PHP's built-in server
- **A code editor** — VS Code recommended
- **A browser** for testing

### First-Time Clone

If you're setting up the project on a new machine:

```bash
git clone https://github.com/your-org/hasmerch.git
cd hasmerch
```

### Starting the Local Server

The project runs PHP, so you need a local PHP server pointed at `frontend/public/` as the web root.

Using PHP's built-in server:

```bash
php -S hasmerch.localhost:8080 -t frontend/public
```

Then visit `http://hasmerch.localhost:8080` in your browser.

For subdomain testing (e.g. `cxxl.hasmerch.localhost`), you'll need to add entries to your `/etc/hosts` file or use a tool like MAMP Pro that supports wildcard subdomains. The `StoreResolver` is already written to handle `.localhost` domains correctly.

### Environment Variables

Never put credentials in source files. Copy the example env file and fill in your local values:

```bash
cp .env.example .env
```

The `.env` file is excluded from Git via `.gitignore`. It should contain at minimum:

```
DB_HOST=localhost
DB_NAME=hasmerch
DB_USER=root
DB_PASS=your_local_password

STRIPE_SECRET_KEY=sk_test_...
STRIPE_PUBLISHABLE_KEY=pk_test_...

STRIPE_WEBHOOK_SECRET=whsec_...
```

The production values for these live in Cloudflare Pages environment variables, not in any file in the repository.

---

## Making Changes

### The Basic Loop

Every change follows the same pattern:

```bash
# 1. Make sure you're up to date
git pull origin main

# 2. Edit files

# 3. Test locally in your browser

# 4. Stage your changes
git add .

# 5. Write a clear commit message
git commit -m "describe what you changed and why"

# 6. Push to GitHub
git push origin main
```

That push kicks off a Cloudflare deployment automatically.

### Writing Good Commit Messages

A clear commit history makes it easy to understand what changed and roll back if needed. Follow this pattern:

```
type: short description of the change

Examples:
fix: correct missing alt text on product images
feat: add coming-soon state to product buy button
style: update nav link hover color
docs: update DEVELOPMENT.md with env setup steps
refactor: move ContentService call into router
```

Types to use: `feat`, `fix`, `style`, `docs`, `refactor`, `chore`.

---

## Branching

For small solo changes, committing directly to `main` is fine. For anything larger — new features, payment system changes, structural refactors — use a branch so `main` stays stable.

### Creating a Branch

```bash
git checkout -b feature/stripe-checkout
```

Work on the branch, commit as normal, then merge it back when ready:

```bash
git checkout main
git merge feature/stripe-checkout
git push origin main
```

Or open a Pull Request on GitHub for review before merging.

### Branch Naming Conventions

```
feature/what-youre-adding       → new functionality
fix/what-youre-fixing           → bug fixes
refactor/what-youre-changing    → restructuring without new features
docs/what-youre-documenting     → documentation only
chore/what-youre-cleaning-up    → dependency updates, file moves
```

---

## GitHub

The GitHub repository is the single source of truth for all code. No changes should exist only on your local machine — push regularly.

### Repository Rules

- `main` is always the live branch. What's in `main` is what's on `hasmerch.com`.
- Never commit credentials, API keys, or passwords. Ever. Use `.env` locally and Cloudflare environment variables in production.
- Never force-push to `main` (`git push --force`). If history needs to be corrected, do it carefully on a branch first.

### `.gitignore` Must Include

```
.env
/backend/config/db.php   ← if credentials are ever hardcoded here temporarily
/node_modules
/vendor
*.log
.DS_Store
Thumbs.db
```

---

## Cloudflare Pages

Cloudflare Pages hosts the site and handles deployment automatically on every push to `main`.

### How the Auto-Deploy Works

1. You push to `main` on GitHub
2. Cloudflare detects the push via a webhook
3. Cloudflare pulls the latest code
4. The build runs (if a build command is configured)
5. The new version goes live at `hasmerch.com`

The whole process typically takes 30–90 seconds.

### Build Configuration (Cloudflare Dashboard)

In your Cloudflare Pages project settings:

| Setting | Value |
|---|---|
| Production branch | `main` |
| Build command | *(leave empty — no build step needed for PHP/HTML)* |
| Build output directory | `frontend/public` |
| Root directory | `/` |

Since the project is PHP without a JavaScript bundler or static site generator, there is no build command. Cloudflare simply deploys the files as-is.

### Environment Variables in Cloudflare

Go to **Cloudflare Dashboard → Pages → hasmerch → Settings → Environment Variables** and add:

```
DB_HOST
DB_NAME
DB_USER
DB_PASS
STRIPE_SECRET_KEY
STRIPE_PUBLISHABLE_KEY
STRIPE_WEBHOOK_SECRET
```

Set these for the **Production** environment. Never paste these values into any file in the repository.

### Custom Domain Setup

The domain `hasmerch.com` is connected through Cloudflare's DNS. Subdomains (`*.hasmerch.com`) must be configured with a wildcard CNAME or DNS record so that `cxxl.hasmerch.com`, `brand.hasmerch.com`, and any other creator subdomains resolve to the same Cloudflare Pages deployment.

In Cloudflare DNS:

```
Type    Name              Content
CNAME   hasmerch.com      hasmerch.pages.dev
CNAME   *.hasmerch.com    hasmerch.pages.dev
```

`StoreResolver.php` handles the subdomain → store routing logic in code. DNS just needs to make all subdomains point at the same application.

### Preview Deployments

Cloudflare Pages automatically generates a preview URL for every non-main branch push. If you push a branch called `feature/new-homepage`, Cloudflare will deploy it to something like:

```
feature-new-homepage.hasmerch.pages.dev
```

This lets you share a live preview of a change for review before it goes to `main`. No extra setup needed — Cloudflare does this automatically.

---

## What to Do Before Every Push

Run through this quick checklist before pushing to `main`:

- [ ] Test the change locally in your browser
- [ ] Test on a small screen (mobile view in browser dev tools)
- [ ] Check that no API keys or passwords are in any staged file (`git diff --staged`)
- [ ] Confirm the commit message clearly describes the change
- [ ] If the change touches payments, the router, or the theme system — test those paths specifically

---

## Reverting a Bad Deployment

If a push to `main` breaks something on the live site, you have two options:

### Option 1 — Revert the commit

```bash
git revert HEAD
git push origin main
```

This creates a new commit that undoes the last one. Cloudflare will redeploy automatically.

### Option 2 — Roll back in Cloudflare

In the Cloudflare Pages dashboard, go to **Deployments**, find the last good deployment, and click **Rollback to this deployment**. This is instant and doesn't touch Git history. Then fix the issue locally and push a corrected commit.

---

## When to Update Documentation

Per the project's documentation requirements, certain changes must be reflected in other files:

| Change type | Files to update |
|---|---|
| New feature added | `ROADMAP.md`, `TODO.md` |
| Architecture changes | `ARCHITECTURE.md` |
| New deployment step or tool | This file (`DEVELOPMENT.md`) |
| Major feature shipped | `ROADMAP.md` (mark as complete) |
| New environment variable added | This file + `.env.example` |

Keep documentation in sync with code. A change that isn't documented is a change that will confuse you in three months.
