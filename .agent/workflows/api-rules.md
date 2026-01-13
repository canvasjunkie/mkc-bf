---
description: API configuration rules for Gemini and OpenRouter
---

# API Configuration Rules

**Last Updated:** 2026-01-12
**Status:** LOCKED - Do not change without explicit approval

---

## Core Principles

1. **Security First** - API keys are NEVER exposed to the frontend
2. **Simplicity** - Users don't need to configure Gemini
3. **Cost Control** - One hardcoded model for predictable costs

---

## Rule 1: Gemini API Key Source

✅ **ONLY** from Netlify environment variable `GEMINI_API_KEY`
❌ **NEVER** from:
- Frontend code
- Bot configuration
- User input
- LocalStorage/IndexedDB

**Location:** `netlify/functions/chat.ts`
```typescript
const apiKey = process.env.GEMINI_API_KEY;
```

---

## Rule 2: Gemini Model

✅ **ONE model only:** `gemini-2.0-flash`
❌ **NO user selection** - model is hardcoded in backend
❌ **NO model dropdown** in UI

**Rationale:** Cost efficiency and stability

**Location:** `netlify/functions/chat.ts`
```typescript
const geminiModel = 'gemini-2.0-flash';
```

---

## Rule 3: All Gemini Calls Through Backend

✅ Frontend → `/api/chat` → Gemini API
❌ Frontend → Gemini API (direct calls)

**Files affected:**
- `src/components/ChatWidget.tsx` - Uses `callChatProxy()` only
- `src/core/GeminiService.ts` - NOT used by frontend (can be deleted)

---

## Rule 4: OpenRouter is PRO ONLY

OpenRouter is **completely hidden** unless:
- User has Pro plan
- `subscription.limits.own_api_key === true`

When Pro is enabled, user sees:
- AI Provider dropdown (Gemini vs OpenRouter)
- OpenRouter API Key input field
- Model text field (user enters any model string)

**Location:** `src/components/BotBuilder.tsx`
```tsx
{subscription?.limits?.own_api_key && (
  // Show OpenRouter options
)}
```

---

## Rule 5: No Frontend Gemini Configuration

The UI should show:
- ✅ Simple info panel: "Your bot uses Gemini 2.0 Flash"
- ❌ Model selection dropdown
- ❌ API key input field
- ❌ Any Gemini-specific configuration

---

## Environment Variables

### Netlify (Required)
```
GEMINI_API_KEY=your-key-from-ai-studio
```

### Local Development (.env.local)
```
GEMINI_API_KEY=your-key-from-ai-studio
```

---

## Deployment Checklist

When deploying:
1. Ensure `GEMINI_API_KEY` is set in Netlify environment variables
2. Get key from [Google AI Studio](https://aistudio.google.com/app/apikey)
3. Select the project with billing enabled
4. After deploy, test chat to verify API works

---

## Troubleshooting

### 429 Error (Quota Exceeded)
1. Check billing is linked to the API key's project
2. Generate a new key from AI Studio (not Cloud Console)
3. Update Netlify environment variable
4. Redeploy

### Wrong API Key Being Used
1. Clear browser cache (hard refresh: Ctrl+Shift+R)
2. Check Netlify environment variables
3. Redeploy the site

---

## Files Modified for These Rules

| File | Change |
|------|--------|
| `netlify/functions/chat.ts` | Hardcoded model, env-only API key |
| `src/components/ChatWidget.tsx` | Removed direct Gemini calls |
| `src/components/BotBuilder.tsx` | Removed model selection, simplified Gemini panel |
| `src/types/Bot.ts` | Removed `geminiApiKey` from types |
