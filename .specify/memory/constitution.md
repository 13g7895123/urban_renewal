<!--
SYNC IMPACT REPORT
==================
Version Change: 1.0.0 → 1.0.1
Amended: 2025-10-24

This is a PATCH update reinforcing existing Principle I (Traditional Chinese First).

Principles Modified:
- I. Traditional Chinese First (zh-TW) - Reinforced enforcement section with explicit
  documentation hierarchy and verification procedures

Changes Made:
- Enhanced enforcement section in Principle I with clearer documentation hierarchy
- Clarified MUST requirements for spec.md, plan.md, tasks.md files
- Added explicit verification checklist for Traditional Chinese compliance
- No semantic changes to principle intent - only clarification

Templates Requiring Updates:
- ✅ .specify/templates/spec-template.md (already compliant)
- ✅ .specify/templates/plan-template.md (already compliant)
- ✅ .specify/templates/tasks-template.md (already compliant)

Follow-up TODOs: None

Rationale for Version 1.0.1:
- PATCH version (clarification only, no semantic change)
- User input reaffirms existing Principle I
- Enhanced enforcement clarity without changing core requirements
- All existing specifications remain fully compliant
-->

# Urban Renewal Voting System Constitution
都更計票系統開發準則

## Core Principles

### I. Traditional Chinese First (繁體中文優先)

**REQUIREMENT**: All specifications, plans, implementation guides, and user-facing
documentation MUST be written in Traditional Chinese (zh-TW).

**Rationale**: The target users are Taiwanese urban renewal project stakeholders
(管理員、理事長、會員、觀察員). Traditional Chinese ensures:
- Clear communication with end users
- Accurate translation of domain-specific terms (都更、會議、投票)
- Compliance with local regulatory documentation standards
- Reduced ambiguity in requirements and acceptance criteria

**Exceptions**:
- Code comments MAY be in English where technical clarity requires it
- Third-party library documentation remains in original language
- Git commit messages MUST follow conventional commit format in English
  (feat:, fix:, docs:, etc.) per CLAUDE.md
- API endpoint names and JSON keys remain in English for international
  standards compliance

**Enforcement**:
- **CRITICAL**: All `.md` files in `/specs/` directory MUST use Traditional Chinese
  - `spec.md` - Feature specifications MUST be in Traditional Chinese
  - `plan.md` - Implementation plans MUST be in Traditional Chinese
  - `tasks.md` - Task descriptions MUST be in Traditional Chinese
  - `research.md` - Research findings SHOULD be in Traditional Chinese
  - `data-model.md` - Entity descriptions MUST be in Traditional Chinese
  - `quickstart.md` - User guides MUST be in Traditional Chinese
- User-facing error messages in code MUST use Traditional Chinese
- Database comments and migration descriptions SHOULD use Traditional Chinese
- Code review checklist includes language verification for:
  - UI labels, tooltips, error messages
  - API response messages shown to users
  - Documentation files in `/specs/` directories

**Verification Checklist**:
1. All user-facing text in Traditional Chinese
2. All `/specs/*/*.md` files in Traditional Chinese
3. All error messages returned to users in Traditional Chinese
4. Database migration descriptions in Traditional Chinese
5. Commit messages in English with conventional commit format

---

### II. Security-First Development (安全優先開發)

**REQUIREMENT**: Security measures MUST be implemented before feature deployment.

**Non-Negotiable Security Standards**:
- **Authentication**: JWT tokens with bcrypt password hashing (MUST use firebase/php-jwt)
- **Authorization**: Role-based access control (RBAC) enforced at both API and route levels
- **Session Management**: Account lockout after 5 failed attempts, 24h token expiration,
  7-day refresh tokens
- **Audit Logging**: All authentication events MUST be logged (login success/failure,
  logout, token refresh)
- **Data Protection**: Sensitive fields (password_hash, tokens) MUST be removed from
  API responses
- **Input Validation**: All user inputs MUST be validated and sanitized before database
  operations
- **HTTPS**: Production deployment MUST use HTTPS (development may use HTTP localhost)

**Rationale**: Urban renewal voting involves sensitive stakeholder data and financial
decisions. Security breaches could compromise voting integrity and user privacy.

**Enforcement**:
- Security review required before merging authentication/authorization changes
- Automated tests MUST cover permission denial scenarios (403/401 responses)
- Code review checklist includes OWASP Top 10 verification

---

### III. Test Coverage Requirements (測試覆蓋率要求)

**REQUIREMENT**: Minimum 80% code coverage for security-critical components, 70% overall.

**Coverage Targets by Component**:
- **Authentication/Authorization**: 95% line coverage (security-critical)
- **API Controllers**: 85% line coverage
- **Models**: 80% line coverage
- **Helpers**: 90% line coverage
- **Frontend Components**: 70% line coverage
- **Overall Project**: 70% minimum

**Test Types Required**:
- **Unit Tests**: Helpers, filters, models (PHPUnit for backend, Vitest for frontend)
- **Integration Tests**: API endpoints with authentication (70% of test effort)
- **RBAC Tests**: Permission matrix covering all role-action combinations
  (4 roles × 4 actions × N resources)
- **Security Tests**: SQL injection prevention, XSS prevention, unauthorized access blocking

**Rationale**: Voting system correctness and security cannot be manually verified.
Automated tests provide confidence in role-based access control and prevent regression.

**Enforcement**:
- CI/CD pipeline MUST run tests before merge
- Coverage reports generated via `XDEBUG_MODE=coverage vendor/bin/phpunit --coverage-html build/coverage`
- Pull requests with coverage decrease below thresholds are blocked
- Test fixtures MUST be used for consistent test data (no production data in tests)

---

### IV. API-First Architecture (API 優先架構)

**REQUIREMENT**: Backend and frontend MUST be decoupled via RESTful API contracts.

**Architecture Standards**:
- **Backend**: CodeIgniter 4 RESTful API (PHP 8.1+) providing JSON responses
- **Frontend**: Nuxt 3 SSR application (Vue 3 + Nuxt UI) consuming API
- **Communication**: All data exchange via HTTP JSON API (no direct database access
  from frontend)
- **Authentication**: JWT tokens in Authorization headers (`Bearer <token>`)
- **Versioning**: API endpoints MAY include version prefix (`/api/v1/`) for breaking changes
- **Documentation**: OpenAPI/Swagger specs SHOULD be maintained in `/specs/*/contracts/`

**Contract Requirements**:
- API responses MUST follow standardized format:
  ```json
  {
    "success": true|false,
    "data": {...},
    "error": {"code": "...", "message": "..."} | null,
    "message": "操作成功"
  }
  ```
- Error messages MUST be in Traditional Chinese
- HTTP status codes MUST follow RESTful conventions (200, 201, 400, 401, 403, 404, 422, 500)

**Rationale**: Decoupling enables independent backend/frontend development, easier
testing, and potential future mobile app development.

**Enforcement**:
- Frontend MUST NOT import backend code directly
- All API changes require OpenAPI spec update
- Integration tests verify contract compliance

---

### V. Code Quality & Maintainability (程式碼品質與可維護性)

**REQUIREMENT**: Code MUST be readable, maintainable, and follow established patterns.

**Quality Standards**:
- **Naming Conventions**:
  - PHP: PSR-12 coding standard, PascalCase for classes, camelCase for methods
  - JavaScript/Vue: camelCase for variables, PascalCase for components
  - Database: snake_case for tables and columns
  - Traditional Chinese in comments for business logic explanations

- **File Organization**:
  - Backend: Controllers in `app/Controllers/Api/`, Models in `app/Models/`,
    business logic in Services
  - Frontend: Pages in `pages/`, Components in `components/`, State in `stores/` (Pinia),
    API calls in `composables/`
  - Tests: Mirror source structure in `tests/` directory

- **Code Reusability**:
  - Extract repeated logic into Traits (backend) or Composables (frontend)
  - Permission checks MUST use `HasRbacPermissions` trait or `auth_*` helpers
  - Avoid copy-paste code (DRY principle)

- **Documentation**:
  - Public methods MUST have PHPDoc/JSDoc comments
  - Complex business logic MUST have explanatory comments in Traditional Chinese
  - README files MUST exist for major features in `/specs/` directory

**Rationale**: Maintainability ensures long-term project sustainability and eases
onboarding of new developers.

**Enforcement**:
- Code review checklist includes readability assessment
- Linters configured: PHP_CodeSniffer (PSR-12), ESLint (Vue.js rules)
- Pull requests with excessive complexity (>15 cyclomatic complexity) require justification

---

## Documentation Requirements (文件要求)

### User-Facing Documentation
**MUST** be written in Traditional Chinese (zh-TW):
- Feature specifications (`/specs/*/spec.md`)
- Implementation plans (`/specs/*/plan.md`)
- Task lists (`/specs/*/tasks.md`)
- User guides and quickstart tutorials (`/specs/*/quickstart.md`)
- API error messages returned to users
- Database migration descriptions (comments in SQL)
- UI labels, tooltips, and help text

### Developer-Facing Documentation
**MAY** be written in English or Traditional Chinese:
- Code comments (English for technical clarity, Traditional Chinese for business logic)
- Git commit messages (MUST use English with conventional commit format)
- Technical research findings (`/specs/*/research.md`)
- Inline code documentation (PHPDoc/JSDoc)

### Hybrid Documentation
**SHOULD** provide both languages when feasible:
- README.md (bilingual recommended)
- Architecture diagrams (labels in both languages)
- API contracts (English endpoint names, Traditional Chinese descriptions)

---

## Development Workflow (開發流程)

### Branching Strategy
- **Main branch**: `master` (production-ready code)
- **Feature branches**: `###-feature-name` (e.g., `002-admin-user`)
- **Naming convention**: Sequential number + kebab-case description
- **Merge strategy**: Pull requests with code review required

### Code Review Requirements
1. **Security Review**: Required for authentication, authorization, data validation changes
2. **Test Coverage**: New code MUST include tests, coverage MUST meet thresholds
3. **Language Compliance**: User-facing text MUST be in Traditional Chinese
4. **Code Quality**: Linter passes, no excessive complexity
5. **Documentation**: Public APIs documented, complex logic explained

### Commit Message Format
**MUST** follow conventional commit format (English):
```
<type>(<scope>): <description>

[optional body in Traditional Chinese for business context]
```

**Types**: feat, fix, docs, style, refactor, test, chore

**Example**:
```
feat(auth): add authentication event audit logging

實作認證事件審計日誌功能,記錄所有登入、登出、Token 更新事件
```

---

## Technology Standards (技術標準)

### Backend (CodeIgniter 4)
- **PHP Version**: 8.1+ required
- **Framework**: CodeIgniter 4.x
- **Database**: MariaDB (via Docker)
- **Authentication**: firebase/php-jwt library
- **Testing**: PHPUnit 10.5+
- **Code Standard**: PSR-12

### Frontend (Nuxt 3)
- **Node.js**: Latest LTS
- **Framework**: Nuxt 3.13+
- **UI Library**: Nuxt UI 2.18+
- **State Management**: Pinia 2.2+
- **Testing**: Vitest 3.2+
- **Icons**: Heroicons (via @heroicons/vue)

### Development Environment
- **Containerization**: Docker + Docker Compose
- **Local Ports**: 4001 (frontend), 4002 (backend), 4306 (database), 4003 (phpMyAdmin)
- **Production Ports**: 9128 (frontend), 9228 (backend), 9328 (database), 9428 (phpMyAdmin)

---

## Governance

### Amendment Process
1. **Proposal**: Document proposed change with rationale
2. **Review**: Discuss impact on existing code and templates
3. **Approval**: Requires project maintainer approval
4. **Implementation**: Update constitution with version bump
5. **Propagation**: Update dependent templates and documentation
6. **Migration**: Create migration plan if changes affect existing code

### Versioning Policy
**Semantic Versioning** (MAJOR.MINOR.PATCH):
- **MAJOR**: Backward-incompatible principle removals or redefinitions
- **MINOR**: New principles added or material expansions
- **PATCH**: Clarifications, typo fixes, non-semantic refinements

### Compliance Verification
- All pull requests MUST verify compliance with this constitution
- `/speckit.plan` command includes "Constitution Check" gate
- Code reviews include constitution compliance checklist
- Violations MUST be justified or corrected before merge

### Enforcement Hierarchy
1. **This Constitution** (highest authority)
2. **Project README** (general project guidance)
3. **CLAUDE.md** (AI assistant-specific guidelines)
4. **Individual team agreements** (lowest authority)

In case of conflicts, this constitution supersedes all other documents.

---

**Version**: 1.0.1 | **Ratified**: 2025-10-24 | **Last Amended**: 2025-10-24
