# Claude Code Guidelines

## Image Asset Management

**Important**: Do not randomly change image file extensions without user approval.

- Background image should remain as `assets/images/bg.jpg`
- Logo image should remain as `assets/images/logo.jpg`
- Always respect the original file extensions specified by the user
- Only change file extensions when explicitly requested by the user

## Project Notes

This project uses Nuxt 3 with Nuxt UI for the frontend components. The login page has been implemented with:

- Green gradient navbar (#2FA633 to #72BB29)
- Centered login card with proper styling
- Footer with contact information and copyright
- Proper responsive design for all screen sizes

## Development Guidelines

- Use Nuxt UI components (UButton, UCard, UInput, Icon) instead of Vuetify
- Icons should use Heroicons (comes with Nuxt UI)
- Maintain proper file structure and follow Vue.js best practices
- Test responsive design on multiple screen sizes

## Git Commit Guidelines

**Important**: Do not include any collaboration-related text in commit messages.

- Keep commit messages clean and professional
- Focus on the technical changes being made
- Avoid mentioning co-authors, collaboration tools, or external assistance
- Use clear, concise descriptions of what was implemented or changed
- Follow conventional commit format when possible (feat:, fix:, docs:, style:, etc.)
- reply with zh-tw