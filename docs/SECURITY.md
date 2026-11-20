# FINORA Security Architecture & Threat Mitigation Audit

This document outlines the security controls, defensive countermeasures, and architectural boundaries implemented across **FINORA – Personal Finance Intelligence**.

---

## 1. Security Architecture Principles

Financial platforms demand defense-in-depth where no single defensive layer is treated as sufficient on its own. FINORA enforces:

1. **Strict Multi-Tenant Isolation**: Every database query touching user records unconditionally binds `user_id = ?` using the authenticated session ID. No client parameter can override tenant scoping.
2. **Deterministic Input Whitelisting**: Every incoming payload is validated against strict rules (`numeric`, `date`, `in:...`, `min`, `max`) before business execution.
3. **Cryptographic Integrity**: Modern cryptographic algorithms (`bcrypt`, `random_bytes`, `hash_equals`) are employed exclusively.
4. **Context-Aware Output Escaping**: All dynamic template variables are sanitized via `htmlspecialchars($val, ENT_QUOTES, 'UTF-8')`.

---

## 2. OWASP Top 10 Mitigation Matrix

| OWASP Vulnerability | Risk Level | Implementation Defense in FINORA |
|---|---|---|
| **A01: Broken Access Control** | Critical | Controller actions verify `Auth::id()` on every data request. Models enforce `WHERE id = ? AND user_id = ?` on all lookups, updates, and deletes. |
| **A02: Cryptographic Failures** | High | Passwords hashed using `PASSWORD_BCRYPT` with cost factor 12. Plaintext secrets are never stored, logged, or returned in session arrays. |
| **A03: Injection (SQL / Command)** | Critical | 100% of SQL queries utilize PDO prepared statements with positional or named parameter binding. `PDO::ATTR_EMULATE_PREPARES` is set to `false`. |
| **A04: Insecure Design** | High | Transaction transfers enforce distinct source and destination accounts. Budget limits cannot be negative. Account balances are recalculated from immutable ledgers. |
| **A05: Security Misconfiguration** | Medium | Production mode disables error display (`display_errors = 0`) and redirects exceptions to `storage/logs/error.log`. Default directory indexing disabled. |
| **A06: Vulnerable / Outdated Components** | Low | Minimal external dependencies. Core MVC framework, router, validation, and session layers are self-contained without monolithic third-party frameworks. |
| **A07: Identification & Auth Failures** | High | Rate-limiting locks accounts after 5 failed attempts for 15 minutes. Session fixation prevented by regenerating session IDs upon login and logout. |
| **A08: Software & Data Integrity Failures** | Medium | CSRF verification tokens required on all POST, PUT, and DELETE actions. Serialized user inputs are restricted strictly to JSON (`json_encode` / `json_decode`). |
| **A09: Security Logging & Monitoring** | Medium | In-app notification engine records login events, budget threshold spikes, and unauthorized data mutations in `notifications` table. |
| **A10: Server-Side Request Forgery (SSRF)** | Low | No user-supplied URLs are fetched by the server backend. File uploads are stored locally with sanitized filenames. |

---

## 3. Deep-Dive Security Controls

### 3.1. Cross-Site Request Forgery (CSRF) Defense
State-changing operations require a valid CSRF token.
- **Generation**: Generated via `SecurityHelper::randomToken(32)` yielding a 64-character cryptographic hex string stored in the active session.
- **Verification**: Evaluated using constant-time string comparison (`hash_equals()`) to eliminate side-channel timing attack vulnerabilities:
  ```php
  public static function validate(?string $token): bool
  {
      $sessionToken = Session::get(self::SESSION_KEY);
      if (!$sessionToken || !$token) {
          return false;
      }
      return hash_equals($sessionToken, $token);
  }
  ```

### 3.2. Session Security & Cookie Hardening
PHP session cookies are configured with security flags:
- `httponly`: Set to `true` to block JavaScript access via `document.cookie`.
- `samesite`: Set to `Lax` to prevent cross-site request inclusion.
- `secure`: Dynamically enabled when running under HTTPS (`!empty($_SERVER['HTTPS'])`).
- `session_regenerate_id(true)`: Invoked immediately upon successful credential validation to invalidate old session identifiers.

### 3.3. Brute-Force & Credential Stuffing Rate Limiting
The authentication pipeline enforces progressive lockouts:
1. Every failed attempt increments `login_attempts` in the `users` table.
2. Upon reaching 5 consecutive failures, `lockout_until` is populated with `NOW() + INTERVAL 15 MINUTE`.
3. Subsequent login attempts are rejected at the database level before password verification computation, preventing CPU starvation from expensive bcrypt calculations.

### 3.4. Input Validation & Data Cleansing
All controllers invoke domain validators (`app/Validators/`) implementing `ValidatorInterface`. The validation engine evaluates data types, string length boundaries, numerical limits, and date formats.
