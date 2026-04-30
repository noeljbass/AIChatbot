# AI Chatbot (PHP/MySQL, IONOS-ready)

## Setup
1. Upload `/ai-chatbot` to your hosting account.
2. Create a MySQL database and import `install.sql`.
3. Set env vars in IONOS PHP settings:
   - `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS`
   - `OPENAI_API_KEY`, `OPENAI_MODEL`
   - `CHATBOT_BASE_URL`, `MAIL_FROM_EMAIL`, `MAIL_FROM_NAME`, `CHATBOT_ADMIN_EMAIL`, `IP_HASH_SALT`
4. Create first admin user:
   - Generate hash via `<?php echo password_hash('your-password', PASSWORD_DEFAULT); ?>`
   - Insert into `chatbot_admin_users`.
5. Login at `/ai-chatbot/admin/login.php`.

## Embed code
```html
<script src="https://MYDOMAIN.com/ai-chatbot/widget.js" data-client-key="CLIENT_PUBLIC_KEY"></script>
```

## Example client setup
- Create client in admin with website URL `https://clientsite.com`
- Set `system_prompt` to brand voice and policies.
- Set `business_context` with services, locations, hours.
- Share the generated `public_key` in embed script.

## Notes
- OpenAI key is used only server-side in `chat.php` via `includes/openai.php`.
- Responses API endpoint: `POST /v1/responses`.
- Assistants API is deprecated and not used.
