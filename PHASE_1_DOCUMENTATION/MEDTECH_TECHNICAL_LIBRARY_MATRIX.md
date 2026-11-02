# Technical Library Matrix

Research date: 2026-11-02. Versions are intentionally not pinned until Phase 2 lockfile creation; use current stable releases supported by the selected Node/PHP/Android toolchains.

| Selection | Purpose / phase | Why selected | Alternatives considered / drawbacks | License/cost / compatibility |
| --- | --- | --- | --- | --- |
| React + TypeScript | Web UI, P2 | Typed reusable role components; official React TS support. [React TS](https://react.dev/learn/typescript) | Vue/Svelte: viable, but no reason to split stack. | MIT/open source; supported Node LTS. |
| Vite | Web build, P2 | Fast SPA build; React-TS template supported. [Vite](https://vite.dev/guide/) | Next.js: defer; SSR is not required for authenticated portals and adds server runtime. | MIT; Node compatibility per current Vite docs. |
| React Router | Client routing, P2 | Explicit role routes, guards, nested layouts. | Framework routing: only if Next later approved. | MIT/free. |
| Tailwind CSS + shadcn/ui | Tokens/primitives, P2 | Fast consistent implementation while components remain owned source, not a proprietary UI runtime. | MUI: heavier visual opinion; avoid two component systems. | MIT/free; define a single token layer. |
| TanStack Query | Server state, P2 | Caching, mutations, invalidation, retries; avoids global server-state store. [Install](https://github.com/TanStack/query/blob/main/docs/framework/react/installation.md) | Redux for server state: avoid; native fetch alone lacks cache policy. | MIT/free. |
| Zustand | Small client state, later P2 | Drawer/UI preferences only. | React Context sufficient for static auth/theme; do not persist clinical data in browser state. | MIT/free. |
| React Hook Form + Zod | Forms/schema validation, P2 | Performant forms and shared client validation; server remains authoritative. [RHF](https://react-hook-form.com/docs) | TanStack Form: promising but avoid alpha-risk; Formik: larger legacy footprint. | MIT/free. |
| Native Fetch | API transport, P2 | Browser-native, abort support, fewer dependencies. | Axios: not necessary initially. | Native/free. |
| Recharts | Charts, later P2 | Accessible labels/table fallback can be designed around it. | Chart.js: acceptable; choose one only. | MIT/free. |
| Lucide React | Icons, P2 | Consistent SVG icon set. | Font icon bundles: avoid mixed icon stacks. | ISC/free. |
| PHP existing domain helpers | Backend, P2 | Lowest-risk preservation of working rules. | Laravel: evaluate after API characterization. | Existing code; PHP 8.0 must upgrade. |
| OpenAPI 3.1 | API contract, P2 | Typed clients, documentation, contract testing. | Ad-hoc docs: reject. | Open standard/free. |
| React Native + Expo + TypeScript | Android, later P5 | Shared language/team; managed workflow initially. | Native Kotlin: revisit if device integrations demand it. | MIT/free; Expo service usage varies. |
| expo-secure-store | Android token storage, later P5 | Encrypted key-value storage. [Expo](https://docs.expo.dev/versions/latest/sdk/securestore/) | AsyncStorage: never for tokens. | MIT/free; small payloads only. |
| Expo Router | Android navigation, later P5 | File-based navigation integrated with Expo. | React Navigation direct: viable, but choose one. | MIT/free. |
| Vitest + Testing Library | Web unit/component, P2 | Vite-aligned fast tests. | Jest: viable but duplicate tooling. | MIT/free. |
| Playwright + axe-core | Browser/E2E/accessibility, P2 | Cross-browser E2E plus automated accessibility checks. [Playwright](https://playwright.dev/docs/accessibility-testing) | Cypress: viable; use one. Automated a11y is not sufficient alone. | Apache-2.0/MIT packages. |
| PHPUnit | PHP unit/integration, P2 | Standard PHP test foundation. | Pest: optional syntax layer, not required. | BSD-3-Clause/free. |
| OWASP ZAP | Security regression, later P2 | Repeatable passive/active staging scans. | Commercial DAST: later if governance requires. | Apache-2.0/free. |
| GitHub Actions | CI/CD, P2 | Existing Git/GitHub workflow fit. | GitLab CI: viable if hosting changes. | Usage billed after allowances. |
| Sentry | Error monitoring, P3 | Structured frontend/backend errors with PII scrubbing. | Self-hosted GlitchTip: lower cost, more operations. | Commercial/free tiers vary. |

## Deliberate exclusions

No Redux, Axios, multiple UI kits, SSR framework, microservices, Kubernetes, or mobile dependencies in Phase 2 without an approved requirement. Check official release compatibility immediately before installation.
