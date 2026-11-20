# Contributing to FINORA

Thank you for your interest in contributing to **FINORA – Personal Finance Intelligence**! We welcome contributions from software engineers, UI/UX designers, and open-source enthusiasts.

To maintain professional engineering standards, please adhere to the following workflow and guidelines.

---

## 1. Code of Conduct

We are committed to providing a welcoming, inclusive, and professional environment for all contributors. Respectful communication, constructive code reviews, and collaborative problem-solving are expected at all times.

---

## 2. Git Workflow & Branching Strategy

We follow a GitFlow-inspired branching strategy:

- **`master`**: Production-ready, stable codebase. Direct commits to `master` are prohibited.
- **`develop`**: Integration branch for the next release.
- **`feature/<feature-name>`**: Branch off `develop` for new features or user stories.
- **`bugfix/<issue-description>`**: Branch off `develop` to resolve defects.
- **`hotfix/<critical-patch>`**: Branch off `master` for urgent production fixes.

```mermaid
gitGraph
    commit id: "v1.0.0"
    branch develop
    checkout develop
    commit id: "Initial setup"
    branch feature/budget-alerts
    checkout feature/budget-alerts
    commit id: "feat: add threshold calculations"
    commit id: "test: unit tests for budgets"
    checkout develop
    merge feature/budget-alerts id: "Merge feature"
    checkout master
    merge develop id: "Release v1.1.0"
```

---

## 3. Commit Message Conventions (Conventional Commits)

All commits must follow the [Conventional Commits](https://www.conventionalcommits.org/) specification:

```text
<type>(<scope>): <short description>

[optional body]

[optional footer(s)]
```

### Supported Types
- **`feat`**: A new user-facing feature or domain capability.
- **`fix`**: A bug fix or defect correction.
- **`docs`**: Documentation updates or markdown clarifications.
- **`style`**: Code formatting, whitespace, or layout adjustments without logic change.
- **`refactor`**: Code restructuring without altering external behavior.
- **`test`**: Adding or updating automated tests.
- **`chore`**: Maintenance tasks, build tooling, or dependency updates.

### Examples
- `feat(budgets): implement 80% threshold overspending alert`
- `fix(accounts): resolve transfer balance reconciliation bug`
- `test(auth): add unit test coverage for bcrypt password verification`

---

## 4. Coding Standards & Guidelines

1. **PHP Version**: Target PHP 8.2+ compatibility. Use strict types in all files:
   ```php
   <?php
   declare(strict_types=1);
   ```
2. **Coding Style**: Follow PSR-12 coding standard rules (4-space indentation, camelCase methods, PascalCase classes, UPPER_CASE constants).
3. **Database Security**: Never execute raw concatenated SQL queries. Always use PDO prepared statements with parameter binding:
   ```php
   $stmt = $pdo->prepare("SELECT * FROM transactions WHERE user_id = ? AND id = ?");
   $stmt->execute([$userId, $id]);
   ```
4. **XSS Mitigation**: Never output unsanitized user variables directly into HTML templates. Use `e($variable)` or `SecurityHelper::escape()`.
5. **No Monolithic Frameworks**: Keep the application purely written in Core PHP to preserve framework-agnostic architectural clarity.

---

## 5. Pull Request Process

1. **Fork & Branch**: Fork the repository and create your feature branch off `develop`.
2. **Write Tests**: Implement unit or integration tests in `tests/` covering your changes.
3. **Verify Locally**: Run the master automated test runner and verify 100% pass rate:
   ```bash
   php tests/run_tests.php
   ```
4. **Submit PR**: Open a Pull Request targeting `develop` with a clear summary of changes, motivation, and test verification output.
5. **Review**: Address review feedback constructively. Once approved, your PR will be squash-merged.
