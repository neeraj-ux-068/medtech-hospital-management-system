# Android Technical Plan

## Recommendation

React Native + Expo + TypeScript, initiated only after web API v1 reaches role/ownership parity. Android is not a Phase 2 implementation task.

## App architecture

Expo Router navigation; typed OpenAPI client; TanStack Query for server state; feature modules matching web API resources; offline cache only for approved non-sensitive summaries. Use `expo-secure-store` for refresh/session token material, not AsyncStorage. Expo documents SecureStore as encrypted local key-value storage; payloads must remain small. [SecureStore](https://docs.expo.dev/versions/latest/sdk/securestore/). React Native itself warns against putting sensitive information in ordinary persisted storage. [RN Security](https://reactnative.dev/docs/security).

## Push and privacy

Use `expo-notifications` only after consent/preferences and a configured delivery backend. Notification payloads contain no diagnosis, result, invoice amount, or other sensitive content; deep links re-authorize on open. [Expo notifications](https://docs.expo.dev/versions/latest/sdk/notifications/).

## Mobile release scope

1. Patient sign-in, dashboard, appointments, notifications.
2. Records/Rx/labs/invoices as read-only protected views.
3. Doctor tasks after ownership/error/offline policies are proven.

## Gates

API security tests, device/session revoke, Android permission disclosure, encrypted transport, crash-report PII scrub, physical Android device accessibility/network testing, Play Store policy/legal review. No health claims or production rollout without governance approval.
