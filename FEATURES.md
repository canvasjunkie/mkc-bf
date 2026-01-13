# Bot Factory - Feature List

## Bot Building & Management
- Create multiple chatbots with unique IDs
- Name and describe each bot
- Import/export bot configurations as JSON
- Export as standalone HTML widget (deployable anywhere)

## Widget Customization

### Header
- Custom title & subtitle
- Background color & text color
- Banner image upload

### Greeting
- Customizable opening message
- Toggle show on open

### Chat Bubble
- Color picker
- Custom icon/emoji
- Position (4 corners)
- Use avatar as bubble option

### Theme
- Primary color (user messages)
- Secondary color (accents)
- Font family (7 options)

### Avatars
- Bot & user avatars
- Emoji, upload, or library options
- Show/hide toggle
- Save to reusable library

## Bot Modules

| Module | Description |
|--------|-------------|
| FAQ | Automated Q&A responses, keyword-triggered |
| Lead Capture | Customizable forms, trigger keywords |
| Knowledge Base | Searchable articles with tags/categories |
| Hello World | Example developer module |

## AI Features
- Gemini AI integration (primary)
- OpenRouter support (for users with own keys)
- Custom system prompts
- Adjustable temperature & max tokens
- Conversation memory with auto-summarization (every 20 messages)
- Server-side memory storage with 24h session expiry

## Lead Management
- Leads Dashboard
- Admin commands: `/leads`, `/exportleads`, `/clear`, `/help`
- CSV export
- Netlify Blobs storage (per-bot)

## Deployment & Export
- Standalone HTML export
- Embed mode (`?embed=true`)
- API base URL configuration
- Netlify serverless backend

## Memory System (Mini MemoryKeep)
- Core memories (system prompt/persona)
- Notebook memories (key facts, last 20 kept)
- Experience memories (summaries, last 10 kept)
- Auto-pruning & 24h session timeout
- Server-side persistence via Netlify Blobs
