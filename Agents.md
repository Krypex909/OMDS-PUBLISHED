# OMDS Project — Permanent AI Instructions

## 1. Project Goal
We are developing an Online Medical Data System (OMDS). Continue the existing project from its current state. Do not start over or create a replacement project.

## 2. Preserve the Existing Project
- Do not rebuild the project from scratch.
- Preserve the existing HTML, CSS, JavaScript, page layouts, colors, and design unless a specific change is requested.
- Reuse existing files and functions whenever practical.
- Follow the current folder structure and naming conventions.
- Do not rename, delete, or move files without explaining why and getting my approval.
- Do not replace an entire file when a small, targeted edit is enough.
- Do not add a framework or unnecessary libraries. Use PHP and MySQL for the backend.

## 3. Development Workflow
- Implement only **one phase or clearly defined feature at a time**.
- Before editing, inspect the relevant existing files and understand how they work.
- Explain the proposed changes before making major modifications.
- Do not assume a feature exists just because it was planned or discussed previously.
- Do not overwrite existing work without checking it first.
- After implementation, test the feature and explain how I can test it manually.
- If something fails, investigate the actual error instead of rewriting everything.
- Do not claim that something works unless it has been tested or clearly identify it as untested.

## 4. Beginner-Friendly Explanations
I am a Computer Science student learning PHP, JavaScript, HTML, CSS, and MySQL.

- Explain technical concepts in simple, beginner-friendly language.
- Explain what each important file does and why it needs to change.
- When modifying code, identify the file and explain the important changes.
- Keep code understandable for a student.
- Avoid unnecessarily complicated code, advanced architecture, and overengineering.
- Help me understand the code instead of silently doing everything for me.
- Do not introduce unrelated changes while implementing a feature.

## 5. Technology Requirements
- Frontend: existing HTML, CSS, and JavaScript.
- Backend: PHP.
- Database: MySQL.
- Use the existing project setup and dependencies whenever possible.
- Use prepared statements for database queries.
- Store passwords using PHP's `password_hash()` and verify them with `password_verify()`.
- Validate important inputs on the server, not only in JavaScript.
- Use secure PHP sessions and check permissions on the server.
- Never store database passwords, secret keys, or other credentials in public frontend files or Git.

## 6. OMDS Account Requirements
The system has three account types:

### Regular User
- Registers using Gmail/email and password.
- Provides personal information, address, and blood type.
- Receives a unique 10-digit User ID.
- Receives a unique QR code associated with their account.

### Authorized Personnel
- Registers with personal information and professional details, including organization/company, specialization, role, and professional identification number.
- Starts with pending approval.
- Pending personnel may use permitted regular-user features but must not access protected medical records as authorized personnel.
- Gains authorized-personnel permissions only after an administrator approves the account.

### Organization
- Organization accounts must be created or managed through an authorized administrator workflow.
- Do not allow unrestricted public self-registration for organization accounts.

### Administrator
- The administrator account must be created through a controlled setup process, not public registration.
- Administrators need to review personnel applications and approve or deny them.
- Do not grant unrestricted medical-record access merely because someone is an administrator. Apply explicit permissions.

## 7. Medical Records and QR Security
- Never put medical information, passwords, or sensitive personal details directly inside a QR code.
- Use a non-guessable token or secure reference associated with the account.
- Check authentication, permissions, and consent/authorization on the server before showing protected medical records.
- A QR scan must not automatically grant access to private records.
- Prevent users from accessing other users' records without proper authorization.
- Keep an appropriate record of sensitive access and administrative approval actions.

## 8. Database and Data Safety
- Inspect the current database structure before changing tables or columns.
- Do not drop, reset, or overwrite databases or tables without my explicit approval.
- Do not delete existing user data during development or testing.
- Use appropriate unique constraints for Gmail/email and the 10-digit User ID.
- Handle duplicate User IDs safely instead of assuming they will never occur.
- Do not expose real medical data in sample screens, logs, or error messages.

## 9. Phase Completion and Handover
At the end of each phase:
1. List the files created or modified.
2. Explain what changed in simple language.
3. Identify what was tested and the results.
4. List any errors, incomplete work, or security concerns.
5. Give me the exact commands or steps needed to run and test the feature.
6. Update `PROJECT_STATUS.md` with the actual project state.
7. Recommend only the next logical task. Do not start it without approval.

## 10. When Starting a New Chat or Taking Over Work
- Read `AGENTS.md` and `PROJECT_STATUS.md` first.
- Inspect the actual current code before continuing.
- Treat the existing source code and database structure as the source of truth.
- Treat old conversations, plans, and AI-generated summaries as reference only.
- If the current implementation conflicts with a requirement, explain the conflict before changing it.
- If important information is missing, ask me instead of guessing.
- Start with a read-only audit when taking over from another AI account.

## Most Important Rule
**Preserve the existing OMDS project, make small and understandable changes, implement one feature at a time, and never sacrifice data safety or medical-record security for speed.**