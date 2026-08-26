# 🧪 Testing Strategy & Architecture Blueprint

This document outlines the engineering philosophy, testing architecture, and execution guidelines for validating the **AegisBank-Auth-Core** security framework.

In a banking-grade ecosystem aligned with **PSD2/RTS** and **EBA Guidelines**, software regression is not a mere inconvenience—it represents financial risk and potential regulatory non-compliance. Therefore, this project enforces a strict **100.0% Test Coverage Target** across all core authentication and isolation pipelines.

---

## 🎯 Strategic Objective: Defending 100% Coverage

Achieving 100% coverage is often criticized as a vanity metric. However, for **mission-critical authentication guardrails**, anything less than 100% means an unverified edge case exists in production.

Our strategy focuses on **Deep Integration and Behavioral Testing** rather than fragile mock-heavy unit tests, ensuring that the system behaves exactly as specified under real-world stress.

### Core Testing Pillars:

1. **Zero-Trust Middleware Validation**: Ensuring users are aggressively quarantined when conditions (2FA missing, password expired) are met.
2. **Resilience & Fault Tolerance**: Verifying that secondary system failures (e.g., Audit Log database write errors) cannot bypass or crash primary security redirections.
3. **State and Pipeline Integrity**: Validating that independent multi-layered rate limiters chain together seamlessly without leaking requests.

---

## 🛠️ Testing Stack

- **Testing Engine**: [Pest 3.x](https://pestphp.com) (The state-of-the-art PHP testing framework)
- **Coverage Driver**: `PCOV` (Engineered for high-performance execution and precise statement tracking)
- **Database Strategy**: In-Memory `SQLite` for local CI pipelines, with automated parallel execution optimization.

---

## 💎 Advanced Test Patterns & Breakthroughs

Below are the key architectural challenges solved during the development of this suite:

### 1. Simulating Fortify Internals (The "Quarantine & Rescue" Test)

To test the `EnsurePasswordNotExpired` and `EnsureTwoFactorEnabled` middleware accurately without testing Fortify itself, the suite directly manipulates the database state via Pest's factories.

- **Challenge**: Fortify demands valid structural cryptographic keys for 2FA validation.
- **Solution**: The suite injects real-time Base32 dummy secret sequences into the user record during runtime setup, ensuring the middleware reads a fully compliant security state and routes the user perfectly to `/user/password-expired` or the dashboard.

### 2. Defending Redirections Against Database Crashes (`try-catch` Isolation)

A critical vulnerability was identified where a database crash during audit logging could crash the whole application, allowing potential security bypasses.

- **Implementation**: The middleware wraps audit log generation in robust `try-catch` structures.
- **Pest Validation**: We engineered a test case that forces or mocks an exception within `AuditLog::create()`. Pest verifies that even if logging fails, the user is still **successfully redirected** to the secure location, prioritizing system safety over logging availability.

### 3. Eliminating In-Memory SQLite Race Conditions

When scale testing under high-speed parallel runners, SQLite database locks (`VACUUM` and write collisions) occasionally caused intermittent CI flakes.

- **Fix**: Refactored the test suite lifecycle to isolate transaction boundaries natively within Pest's `beforeEach` hooks, establishing a pristine database state for every isolated closure.

---

## 💻 Test Execution Guide

### Local Environment (Laravel Sail)

To run the full suite and view the strict 100% coverage report locally:

```bash
./vendor/bin/sail pest --coverage
```

### Continuous Integration (GitHub Actions Preview)

The suite is optimized to block pull requests automatically if coverage drops below the 100% threshold:

```bash
./vendor/bin/pest --coverage --min=100
```

---

## 📊 Sample Output (All Passed)

```text
  Pass  Http/Controllers/PasswordExpiredControllerTest ........................................ 100.0%
  Pass  Http/Middleware/EnsurePasswordNotExpiredTest .......................................... 100.0%
  Pass  Http/Middleware/EnsureTwoFactorEnabledTest ............................................ 100.0%
  Pass  Http/Middleware/RateLimiterPipelineTest ............................................... 100.0%
  ───────────────────────────────────────────────────────────────────────────────────────────────────
                                                                                Total: 100.0 %
```

---

## 🧠 Architectural Lessons Learned

- **Namespace Rigor**: Early Pest execution caught a critical runtime bug where a missing root backslash on the `Log` facade (`Log::error` vs `\Log::error`) inside a catch block would cause a `500 Internal Server Error`. The test suite successfully intercepted this before it ever reached production.
- **Fail-Safe Design**: Security software must always fail closed, never open. If the audit log fails, the app must stop the user, not let them pass.
