# FINORA Visual Tour & Design System Specification

This document showcases the user interface, design system tokens, typography, and page walkthroughs across **FINORA – Personal Finance Intelligence**.

---

## 1. Design System Tokens & Color Palette

FINORA employs a modern fintech SaaS aesthetic inspired by leading global wealth management interfaces.

| Token Name | Hex Value | Semantic Usage |
|---|---|---|
| **Primary Navy** | `#101828` | Sidebar navigation, primary headings, dark brand accents |
| **Secondary Navy** | `#1D2939` | Active navigation pills, table header backgrounds |
| **Emerald Green** | `#10B981` | Positive cashflow, income indicators, savings targets |
| **Light Background** | `#F5F7FA` | Canvas background, content area background |
| **Card White** | `#FFFFFF` | Surface container cards, elevated modals |
| **Border Soft** | `#EAECF0` | Subtle hairline dividers and card boundaries |
| **Danger Red** | `#EF4444` | Expense metrics, over-budget warnings, deletion buttons |
| **Blue Accent** | `#3B82F6` | Primary action buttons, interactive hyperlinks, badges |
| **Warning Amber** | `#F59E0B` | Budget 80% threshold warnings, pending states |

### Layout & Sizing Standards
- **Sidebar Width:** 250px (Fixed on desktop, collapsible off-canvas on mobile)
- **Top Header Height:** 72px
- **Card Border Radius:** 16px (`border-radius: 1rem`)
- **Button / Input Border Radius:** 10px (`border-radius: 0.625rem`)
- **Typography:** `Inter`, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif
- **Base Grid:** 8px rhythm system

---

## 2. Comprehensive Page Walkthrough

### 2.1. Authentication Suite
1. **Login Page (`/login`)**:
   - Clean split card layout with branded vector logo.
   - Email and password inputs with client and server-side validation.
   - "Remember Me" checkbox and "Forgot Password" link.
   - Demo credentials quick-fill trigger for recruiters and evaluators.
2. **Registration Page (`/register`)**:
   - Legal name, email, password, and password confirmation fields.
   - Automatic generation of default account settings, baseline currency (PKR), and category set upon registration.
3. **Forgot Password (`/forgot-password`)**:
   - Email submission form that generates a secure expiring token in `password_resets`.

### 2.2. Executive Dashboard (`/dashboard`)
- **Top Metric Cards (KPIs)**:
  - Total Net Worth / Balance (reconciled across all active accounts in PKR).
  - Monthly Income (with month-over-month growth percentage).
  - Monthly Expenses (with burn rate percentage).
  - Total Accumulated Savings (aggregated across active savings targets).
- **Interactive Visualizations**:
  - **Cash Flow History Chart**: 6-month interactive Chart.js line graph comparing historical income vs. expense trajectories.
  - **Expense Breakdown Chart**: Categorical doughnut chart highlighting top expense drivers with percentage share.
- **Widgets**:
  - Recent transactions list with type badges and quick action links.
  - Top active budget utilization progress bars with real-time status pills.
  - Active savings goal progress meters with days remaining counters.

### 2.3. Transaction Management (`/transactions`)
- **Master Transaction Table**:
  - Filterable by type (Income, Expense, Transfer), category, account, and date range.
  - Full-text search across description, notes, and reference IDs.
  - Sortable by date and amount.
  - Pagination controls with customizable per-page limits.
- **Add Transaction (`/transactions/create`)**:
  - Form with type selector, source account dropdown, category picker, date picker, payment method, amount, and notes.
  - Dedicated transfer mode allowing seamless inter-account capital movement.
- **Edit Transaction (`/transactions/{id}/edit`)**:
  - Pre-populated form with atomic database updates and balance re-calculation.
- **CSV Import (`/transactions/import`)**:
  - File upload interface supporting batch import of bank statements with error handling.

### 2.4. Budget Planning & Threshold Warnings (`/budgets`)
- Monthly budget management organized by expense category.
- Visual progress meters with three status levels:
  - **Normal (`< 80%`)**: Emerald green progress fill.
  - **Warning (`80% - 100%`)**: Amber warning fill alerting the user to impending overspending.
  - **Exceeded (`> 100%`)**: Crimson red progress fill indicating over-budget status.
- Add/Edit budget modal with automatic category deduplication.

### 2.5. Savings Goals & Accumulation (`/savings`)
- Target tracking with visual cards displaying target amount, current amount, and calculated completion percentage.
- Real-time days remaining countdown based on target date.
- **Contribution Ledger (`/savings/{id}/contributions`)**:
  - History of deposits linked to specific accounts.
  - Ability to log new contributions that atomically update the goal's current balance.

### 2.6. Multi-Account Management (`/accounts`)
- Overview of checking accounts, savings accounts, and cash wallets.
- Color-coded account cards displaying account number, account type, and real-time reconciled balance.
- **Account Ledger View (`/accounts/{id}`)**:
  - Transaction history filtered specifically to the selected account.
  - Summary of total credits, debits, and net flow for that account.

### 2.7. Financial Reports & Analytics (`/reports`)
- Comprehensive reporting engine filterable by Monthly, Weekly, Yearly, or Custom Date Ranges.
- Key figures: Total Income, Total Expenses, Net Savings, and Savings Rate percentage.
- Breakdown table listing spending by category with percentage distribution and transaction counts.
- **Printable Report View (`/reports?print=1`)**:
  - Clean, print-optimized stylesheet without sidebar or navigation, formatted for PDF export or physical printing.

### 2.8. In-App Notifications (`/notifications`)
- Notification feed categorized by type:
  - Budget overspending warnings.
  - Savings goal milestone celebrations.
  - Monthly financial statement readiness.
  - Security login notices.
- Individual "Mark as Read" action and "Mark All as Read" bulk button.

### 2.9. User Profile & Preferences (`/profile` & `/settings`)
- User details update (name, email, avatar).
- Password change modal with current password verification.
- Localization settings: Currency selection (PKR, USD, EUR, GBP, SAR, AED), currency symbol display, date format preference.
- Theme toggle supporting Light Mode and Dark Mode.
- Notification preference checkboxes.

### 2.10. Error Handling Pages
- **404 Not Found (`/404`)**: Branded missing page template with friendly navigation back to dashboard.
- **500 Server Error (`/500`)**: Graceful error handling template concealing sensitive stack traces in production mode.
