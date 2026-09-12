# AegisBank-Auth-Core 🛡️

[![Laravel Version](https://shields.io)](https://laravel.com)
[![PHP Version](https://shields.io)](https://php.net)
[![Test Coverage](https://shields.io)](https://pestphp.com)
[![License](https://shields.io)](LICENSE)

A high-security, regulatory-compliant authentication architecture engineered with **Laravel 11, Jetstream (Inertia.js + Vue 3), and Pest 3.x**. This application is strictly designed to meet European banking-grade security standards, aligning with the core requirements of **PSD2/RTS (Revised Payment Services Directive / Regulatory Technical Standards)** and **EBA (European Banking Authority) Guidelines**.

Designed as a bulletproof proof-of-concept (PoC) that showcases **100.0% Test Coverage via Pest 3.x**, strict session isolation, and real-time data leak protection required for global-tier engineering roles.

---

## 📈 Engineering Quality Strategy

- **Test-Driven Rigor**: 100% strict test coverage defended via **Pest 3.x** and **PCOV**. Every edge case in security middleware and rate limiters is structurally validated.
- **Resilient Middleware Architecture**: All defensive guardrails are fully isolated with explicit exception handling (`try-catch` structures) ensuring failure-free redirection mechanisms during system or database anomalies (e.g., preventing 500 crashes during audit log DB failure).
- **Intentional Error Verbose**: Detailed validation messages are explicitly retained in this PoC layer to streamline reviewer verification and frontend state debugging; in a strict production environment, these are wrapped behind obfuscated generic exceptions to mitigate enumeration vectors.
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
- **Lockout Penalty**: Immediate 15-minute global cool-down response containing precise structured JSON status codes (`429 Too Requests`).

### 3. Regulatory Password Lifecycles & Complex Policies

- **90-Day Password Expiration (`EnsurePasswordNotExpired`)**: Enforces absolute credential lifecycles by verifying `password_changed_at` timestamps against explicit system parameters.
- **Banking-Grade Password Policy**: Enforces a strict password policy requiring a minimum of 12 characters, including uppercase letters, lowercase letters, numbers, special characters, and compromise checks (uncompromised passwords via HaveIBeenPwned API).
- **Isolated Mitigation View**: Compliant with global financial standards, expired sessions are directed onto `/user/password-expired` preventing route looping and processing deadlocks.
- **Secure Salvation Blueprint**: Allows instant password updates utilizing rigorous pattern complexity guidelines while retaining audit trace histories.

### 4. Non-Repudiation: Immutable Structured Audit Logging

Every mission-critical security milestone (`security.password.expired`, `security.password.renewed`, authentication lockouts) is formatted inside structured database payloads (`audit_logs` table) mapping `user_id`, `event`, `ip_address`, `user_agent`, and granular diagnostic `payload` (JSON).

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

## ⚙️ Installation & Local Setup

### Prerequisites

- Docker & Docker Compose (Laravel Sail)

### Step-by-Step Deployment

1. **Clone the Repository:**
    ```bash
    git clone https://github.com
    cd AegisBank-Auth-Core
    ```
2. **Environment Configuration:**
    ```bash
    cp .env.example .env
    # Configure your local application keys and database credentials
    ```
3. **Orchestrate Containers & Dependencies:**
    ```bash
    ./vendor/bin/sail up -d
    ./vendor/bin/sail composer install
    ./vendor/bin/sail npm install
    ./vendor/bin/sail npm run build
    ```
4. **Execute Strategic Migrations & Seeders:**
    ```bash
    # Generates clean bank schemas along with specific demo environment seed data
    ./vendor/bin/sail artisan migrate:fresh --seed
    ```

---

## 🏁 Verification & Testing Suite

To trigger the complete test suite and verify the **100.0% total system coverage** claim:

```bash
./vendor/bin/sail pest --coverage
```

```text
  Actions/Fortify/CreateNewUser ............................................................................... 100.0%
  Actions/Fortify/PasswordValidationRules ..................................................................... 100.0%
  Actions/Fortify/ResetUserPassword ........................................................................... 100.0%
  Actions/Fortify/UpdateUserPassword .......................................................................... 100.0%
  Actions/Fortify/UpdateUserProfileInformation ................................................................ 100.0%
  Actions/Jetstream/DeleteUser ................................................................................ 100.0%
  Http/Controllers/AuditLogController ......................................................................... 100.0%
  Http/Controllers/Controller ................................................................................. 100.0%
  Http/Middleware/EnsurePasswordNotExpired .................................................................... 100.0%
  Http/Middleware/EnsureTwoFactorEnabled ...................................................................... 100.0%
  Http/Middleware/HandleInertiaRequests ....................................................................... 100.0%
  Models/AuditLog ............................................................................................. 100.0%
  Models/User ................................................................................................. 100.0%
  Providers/AppServiceProvider ................................................................................ 100.0%
  Providers/FortifyServiceProvider ............................................................................ 100.0%
  Providers/JetstreamServiceProvider .......................................................................... 100.0%
  ────────────────────────────────────────────────────────────────────────────────────────────────────────────────────
                                                                                                        Total: 100.0 %
```

---

## 🔒 Strategic Sandbox Isolation & Data Immutability

Please note that the local/online live demo environment is intentionally orchestrated in a **stateless, isolated sandbox perimeter** to strictly align with PSD2/RTS compliance and enterprise audit principles:

1. **Audit Log Immutability (EBA/PSD2 Compliant)**:
   To preserve the absolute integrity and strict referential transparency of cryptographic audit logs, the core database state is structurally frozen. Destructive or arbitrary data mutations (such as permanent profile updates or account deletions) are blocked at the infrastructure layer to prevent temporal discrepancies in compliance records.
2. **Demo Idempotency for the 90-Day Isolation Workflow**:
   The forced isolation middleware (`EnsurePasswordNotExpired`) requires a persistent, exact historical timestamp to demo the user redirection flow gracefully. Bypassing state persistence ensures that the 90-day expiration hurdle can be tested repeatedly and reliably without manual database re-seeding.
3. **MFA & Stateless Orchestration**:
   Multi-Factor Authentication (MFA/2FA) utilizing TOTP algorithms operates entirely on stateless, deterministic time-slice verification and session-state synchronization. Thus, MFA remains 100% functional within this isolated perimeter without requiring database persistence.

---

## 🚀 Live Demo & Roadmap (Phase 4)

- **Live Demo**: https://bank-auth-app-production.up.railway.app/
- **CI/CD Integration**: Formalizing Pest coverage gating in GitHub Actions.
- **Structural Upgrade**: Gradual framework upgrade to sync with modern enterprise LTS lifecycles.
- **FIDO2 / Passkey Support**: WebAuthn integration for seamless biometric authentication.

---

## License

This project is licensed under the Apache License 2.0.

You may use, modify, and distribute this software under the terms of the Apache License.
A full copy of the license is available at:

https://apache.org

## Contributing

Contributions are welcome.
Feel free to open issues or submit pull requests.

## Additional Author Notice (Non‑Legal)

This project includes original architectural logic, structural defense patterns, and high-security workflow designs engineered by **Code‑Integrity**.

While the Apache License permits reuse and modification, the author requests the following courtesy guidelines:

- Please provide proper attribution when using or extending the structural security concepts introduced in this repository (e.g., the 90-day forced isolation middleware graph, immutable non-repudiation audit trails, and automated Pest 3.x total coverage design).
- Do not misrepresent these specific full-stack state management workflows or automated testing matrices as your own original invention.
- When this core architecture is utilized within corporate training, academic cybersecurity material, or enterprise security frameworks, please include clear credit to **Code‑Integrity**.

These courtesy guidelines do not alter the Apache License terms and are provided to preserve the professional integrity of the author's work.

_Developed under global financial security frameworks, prioritizing runtime reliability, architectural immutability, and zero-defect deployments._

<!-- cache bust: 20260830 -->
