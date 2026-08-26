# AegisBank-Auth-Core 🛡️

[![Laravel Version](https://shields.io)](https://laravel.com)
[![PHP Version](https://shields.io)](https://php.net)
[![Pest Test Coverage](https://shields.io)](https://pestphp.com)
[![License](https://shields.io)](LICENSE)

A high-security, regulatory-compliant authentication architecture engineered with **Laravel 11, Jetstream (Inertia.js + Vue 3), and Pest 3.x**. This application is strictly designed to meet European banking-grade security standards, aligning with the core requirements of **PSD2/RTS (Revised Payment Services Directive / Regulatory Technical Standards)** and **EBA (European Banking Authority) Guidelines**.

---

## 📈 Engineering Quality Strategy

- **Test-Driven Rigor**: 100% strict test coverage defended via **Pest 3.x** and **PCOV**.
- **Resilient Middleware Architecture**: All defensive guardrails are fully isolated with explicit exception handling (`try-catch` structures) ensuring failure-free redirection mechanisms during system or database anomalies.
- **Production-Validated**: Verified against physical target DB environments utilizing multi-layered dummy credential sequences, eliminating structural gaps between local testing and production realities.

---

## 🛡️ Regulatory Compliance & Core Security Blueprint

### 1. PSD2-Compliant Strong Customer Authentication (SCA)

- **Absolute 2FA Isolation (`EnsureTwoFactorEnabled`)**: Any authenticated user without two-factor authentication (2FA) configured is immediately and strictly quarantined from the application dashboard, forcing isolation onto the profile customization layer until compliant.
- **Cryptographic Secrets**: Utilizes Fortify's standard-compliant Base32 cryptographic secret keys to safeguard authenticator interaction pipelines.

### 2. EBA-Grade Account Defense & Brute-Force Mitigation

- **Dual-Pipeline Rate Limiting (`login-account` | `login-ip`)**: Implements two completely decoupled throttling pipelines executing synchronously:
    - **Per-Account Limiter**: Restricts discrete account targeting to a maximum of 3 failed attempts per minute.
    - **Per-IP Limiter**: Caps continuous local infrastructure origin attacks to 5 failed attempts per minute across multiple credentials.
- **Lockout Penalty**: Immediate 15-minute global cool-down response containing precise structured JSON status codes (`429 Too Many Requests`).

### 3. Regulatory Password Lifecycles & Graceful Recovery

- **90-Day Password Expiration (`EnsurePasswordNotExpired`)**: Enforces absolute credential lifecycles by verifying `password_changed_at` timestamps against explicit system parameters.
- **Isolated Mitigation View**: Compliant with global financial standards, expired sessions are directed onto `/user/password-expired` preventing route looping and processing deadlocks.
- **Secure Salvation Blueprint**: Allows instant password updates utilizing rigorous pattern complexity guidelines while retaining audit trace histories.

### 4. Non-Repudiation: Immutable Structured Audit Logging

- Every mission-critical security milestone (`security.password.expired`, `security.password.renewed`, authentication lockouts) is formatted inside structured database payloads mapping:
    - `user_id`, `event`, `ip_address`, `user_agent`, and granular diagnostic JSON payloads.

---

## 🛠️ Tech Stack & Requirements

- **Backend Framework**: PHP 8.4+ / Laravel 11.x
- **Frontend Architecture**: Vue 3 / Inertia.js / Vite / Tailwind CSS
- **Testing Engine**: Pest 3.x + PCOV / In-memory SQLite Spec
- **Local Runtime Environment**: Laravel Sail (Docker architectureized)

---

## 🏁 Verification & Testing Suite

To trigger the comprehensive test suite and verify the **100.0% total system coverage** claim:

```bash
./vendor/bin/sail pest --coverage
```

```text
  Http/Middleware/EnsurePasswordNotExpired .................................................... 100.0%
  Http/Middleware/EnsureTwoFactorEnabled ...................................................... 100.0%
  ───────────────────────────────────────────────────────────────────────────────────────────────────
                                                                                Total: 100.0 %
```

```bash
# Verify real-time database schema states
./vendor/bin/sail artisan tinker
```

---

_Developed under global financial security frameworks, prioritizing runtime reliability, architectural immutability, and zero-defect deployments._
