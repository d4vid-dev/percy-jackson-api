// Expo injects EXPO_PUBLIC_* values from mobile/.env into the app bundle.
export const API_URL = (process.env.EXPO_PUBLIC_API_URL ?? '').trim().replace(/\/+$/, '');
