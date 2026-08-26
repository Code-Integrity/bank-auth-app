# 🌿 Contribution & Git Workflow Guidelines

To maintain the high-security posture and 100% test coverage of the **AegisBank-Auth-Core** repository, all developers must strictly adhere to the following Git and Branch Management rules.

These rules ensure that no broken code ever reaches production and that every change is thoroughly reviewed and tracked.

---

## 🛡️ Branch Protection Rules (GitHub Rulesets)

The `main` branch is the production-ready baseline of this application. It is highly protected by **GitHub Rulesets** to prevent accidental overrides.

1. **No Direct Pushes**: Direct pushes to the `main` branch are strictly prohibited.
2. **Pull Request Mandate**: All changes must be submitted via a **Pull Request (PR)** from a feature branch.
3. **CI/CD Quality Gate (Roadmap)**: Code will only be allowed to merge if the automated test suite returns a 100% success rate with zero regression.

---

## 🔄 Branching Strategy (Feature-Branch Workflow)

We utilize a clean and predictable branching framework to isolate new features and bug fixes:

- `main` : Production-only branch. Always stable, always tested.
- `feature/*` : Used for developing new features or extensions (e.g., `feature/webauthn-passkey`).
- `bugfix/*` : Used for resolving specific system anomalies or errors (e.g., `bugfix/sqlite-transaction-lock`).

---

## 🚀 Step-by-Step Development Workflow

### 1. Create a Feature Branch

Always pull the latest changes from `main` before creating your isolated branch:

```bash
git checkout main
git pull origin main
git checkout -b feature/your-awesome-feature
```

### 2. Local Testing Before Push

Before creating a Pull Request, you must run the local **Pest 3.x** test suite via Laravel Sail to verify that your changes do not break existing guardrails or lower the 100% coverage baseline:

```bash
./vendor/bin/sail pest --coverage
```

### 3. Commit and Push

Keep your commit messages clear, short, and intentional:

```bash
git add .
git commit -m "feat: implement dual-pipeline rate limiting for security"
git push origin feature/your-awesome-feature
```

### 4. Create a Pull Request (PR)

When opening a PR on GitHub, ensure you provide:

- **Description**: A brief explanation of the problem solved or the feature added.
- **Verification**: A confirmation that `./vendor/bin/sail pest --coverage` was executed locally and achieved 100% success.

---

## 🔒 Security & Code Quality Standards

- **Zero Secrets in Git**: Never commit raw API keys, `APP_KEY`, or database credentials. Use `.env.example` as a template, and keep real credentials in your local `.env`.
- **Atomic Commits**: Keep your PRs small and focused on a single logical task. This makes code reviews fast and reliable.
