# HealthPort / Zenmedix ERP — User Guide

This guide explains how staff typically move through the system. Menu labels match the ERP-style layout (`resources/views/layouts/app.blade.php`).

## 1. Sign in and store context

1. Open the app URL and **log in** with your store user (Jetstream).
2. Most data is **scoped to your store** automatically (medicines, sales, batches, expenses).

## 2. Dashboard

- **Route:** Dashboard (header button or `/dashboard`).
- **Shows:** Today’s revenue, gross/net profit, receivables/payables summary, stock and expiry alerts, 7-day chart, fast movers, recent sales, quick links.
- **Tip:** Cards link to **Receipts**, **Payments**, **Stock**, **S/R Expiry**, etc., when configured.

## 3. POS — retail sale

- **Route:** **Sale** (sidebar) → `/pos`.
- **Flow:**
  1. Enter **patient / doctor / party** details as needed.
  2. **Search** for a product; pick a row (or use keyboard highlight + Enter).
  3. Adjust **quantity**, **MRP/strip price** if allowed, then **ADD**.
  4. Repeat for more lines; verify **grand total**.
  5. Set **payment method** and, for credit/partial, **amount paid**.
  6. **SAVE (END)** to complete the sale.
- **After sale:** An **invoice** overlay appears; **Print** or start **New Sale** (Esc also exits invoice mode when not fullscreen).
- **Stock:** The system reduces stock on the **same batch** as shown on the line (FEFO is not used at checkout).

## 4. Stock status (pharmacy)

- **Route:** **Stock Status** → `/pharmacy`.
- **Use for:** Master medicine list, **stock-in**, batch edit, low stock and **near-expiry** batches.

## 5. Purchase invoice (suppliers)

- **Route:** **Purchase Invoice** → `/suppliers`.
- **Tabs:** Suppliers, new purchase, history, supplier ledger.
- **Purchase:** Add lines (medicine, batch, expiry, qty, prices); system creates a **Purchase** and **MedicineBatch** rows with quantities in **units** (qty × units per strip).

## 6. Accounting & MIS

- **Route:** **Cash & Bank Book** / MIS links → `/accounting?tab=...`.
- **Tabs:**
  - **MIS Dashboard** — today KPIs, profit, chart, fast movers.
  - **Day Book** — today’s sales, batch-based purchase movements, expenses.
  - **Outstanding** — customer receivables and supplier payables; **VIEW** opens detail; **Clear all dues** when appropriate.
  - **Re-Order** — low stock + **90-day expiry** tracker with return action.
  - **Sales Book** — last invoices; **VIEW** opens a printable-style receipt modal.
  - **Purchase Book** — recent purchase bills.

## 7. Receipts, payments, ledger

- **Receipts** — customer-side collections (route `receipts.index` when enabled).
- **Payments** — supplier payments (route `payments.index`).
- **Ledger** — account ledger statement (`ledger.index`).

## 8. S/R expiry

- **Route:** **S/R Expiry** → `/sr-expiry` (when enabled in routes).
- **Use for:** Near-expired and expired sellable/returnable batch tracking.

## 9. Keyboard shortcuts (layout)

- **F1** — Shortcut help (if the shortcut modal is present in your build).
- **Alt+X** — **Log out** (POST form; do not bookmark `/logout` as GET).

## 10. Support and troubleshooting

| Problem | What to check |
|---------|----------------|
| **419 / logout error** | Use the in-app **Exit** / **POST** logout, not a bookmarked GET `/logout`. |
| **Wrong stock after sale** | Confirm the **batch** on the line matches physical pick; report if batch totals drift. |
| **Tests won’t run** | PHP version vs `composer.json` / Pest / PHPUnit requirements (see `docs/PROJECT_REPORT.md`). |

For technical changes or deployment, refer to your team’s `.github/workflows/deploy.yml` and Laravel env configuration (`.env`).
