# Frontend Architecture

## Structure

```text
src/
  app/ (router, providers, error boundary)
  features/ (appointments, clinical, billing, laboratory, notifications)
  components/ (catalog primitives)
  layouts/ (patient, doctor, admin, print)
  api/ (typed OpenAPI client, auth/interceptors)
  schemas/ (Zod request/view schemas)
  pages/ (route composition)
  styles/ (tokens, Tailwind layers)
  tests/
```

## State boundaries

- TanStack Query: all API/server data; invalidate from mutations.
- React Hook Form: transient form state.
- Context: auth identity, feature flags, theme only.
- Zustand: optional non-sensitive UI state only.
- Never persist tokens, clinical records, or financial data in browser localStorage.

## Routing and access

Public → auth; authenticated routes nest under `/patient`, `/doctor`, `/admin`. The route guard improves UX; API ownership checks provide security. Error boundaries map 401/403/404/409/422/429/5xx to distinct accessible states.

## Design implementation

Tailwind token values mirror approved MedTech color/spacing/type tokens. shadcn/ui provides source-owned accessible primitives; product components remain in the repository. Data tables always expose mobile scroll and a non-chart textual summary.

## Delivery plan

Build Storybook-equivalent component previews only if needed after the catalog stabilizes. Migrate one read route then one write route at a time behind feature flags, with screenshot/E2E comparison against preserved PHP behavior.
