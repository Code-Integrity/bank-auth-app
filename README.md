# AegisBank-Auth-Core 🛡️

![Laravel Version](https://shields.io)
![PHP Version](https://shields.io)
![Pest Version](https://shields.io)
![Test Coverage](https://shields.io)
![License](https://shields.io)

A high-security, regulatory-compliant authentication architecture engineered with **Laravel 11, Jetstream (Inertia.js + Vue 3), and Pest 3.x**. This application is strictly designed to meet European banking-grade security standards, aligning with the core requirements of **PSD2/RTS (Revised Payment Services Directive / Regulatory Technical Standards)** and **EBA (European Banking Authority) Guidelines**.

---

## 📈 Engineering Quality Strategy

- **Test-Driven Rigor**: 100% strict test coverage defended via **Pest 3.x** and **PCOV**. Every edge case in security middleware and rate limiters is structurally validated.
- **Resilient Middleware Architecture**: All defensive guardrails are fully isolated with explicit exception handling (`try-catch` structures) ensuring failure-free redirection mechanisms during system or database anomalies (e.g., preventing 500 crashes during audit log DB failure).
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

### 3. Regulatory Password Lifecycles & Complex Policies

- **90-Day Password Expiration (`EnsurePasswordNotExpired`)**: Enforces absolute credential lifecycles by verifying `password_changed_at` timestamps against explicit system parameters.
- **Banking-Grade Password Policy**: Enforces a strict password policy requiring a minimum of 12 characters, including uppercase letters, lowercase letters, numbers, special characters, and compromise checks (uncompromised passwords via HaveIBeenPwned API).
- **Isolated Mitigation View**: Compliant with global financial standards, expired sessions are directed onto `/user/password-expired` preventing route looping and processing deadlocks.
- **Secure Salvation Blueprint**: Allows instant password updates utilizing rigorous pattern complexity guidelines while retaining audit trace histories.

### 4. Non-Repudiation: Immutable Structured Audit Logging

Every mission-critical security milestone (`security.password.expired`, `security.password.renewed`, authentication lockouts) is formatted inside structured database payloads (`audit_logs` table) mapping:

- `user_id`, `event`, `ip_address`, `user_agent`, and granular diagnostic `payload` (JSON).

#### Example Schema / JSON Payload:

```json
{
    "id": 42,
    "user_id": 101,
    "event": "security.password.expired",
    "ip_address": "192.0.2.1",
    "user_agent": "Mozilla/5.0...",
    "payload": {
        "days_since_last_change": 95,
        "quarantine_triggered": true
    },
    "created_at": "2026-08-26T14:17:00Z"
}
```

---

## 🛠️ Tech Stack & Requirements

- **Backend Framework**: PHP 8.4+ / Laravel 11.x
- **Frontend Architecture**: Vue 3 (Composition API) / Inertia.js / Vite / Tailwind CSS
- **Testing Engine**: Pest 3.x + PCOV / In-memory SQLite Spec
- **Local Runtime Environment**: Laravel Sail (Docker architectures)

---

## 🏁 Verification & Testing Suite

To trigger the comprehensive test suite and verify the **100.0% total system coverage** claim:

```bash
./vendor/bin/sail pest --coverage
```

```text
  Http/Middleware/EnsurePasswordNotExpired .................................................... 100.0%
  Http/Middleware/EnsureTwoFactorEnabled ...................................................... 100.0%
  Http/Middleware/DetectSuspiciousActivity .................................................... 100.0%
  ───────────────────────────────────────────────────────────────────────────────────────────────────
                                                                                Total: 100.0 %
```

```bash
# Verify real-time database schema states
./vendor/bin/sail artisan tinker
```

---

## 🚀 Live Demo & Roadmap (Phase 4)

- **Live Demo**: _[Coming Soon / Link to Railway Deployment]_
- **CI/CD Integration**: formalizing Pest coverage gating in GitHub Actions.
- **FIDO2 / Passkey Support**: WebAuthn integration for seamless biometric authentication.

---

_Developed under global financial security frameworks, prioritizing runtime reliability, architectural immutability, and zero-defect deployments._

<!-- cache bust: 20260830 -->
