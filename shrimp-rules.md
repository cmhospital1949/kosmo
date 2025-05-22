# KOSMO Foundation Website Development Guidelines

## Project Overview

- KOSMO Foundation Website is a bilingual non-profit organization website supporting Korean students and athletes
- **Primary languages are Korean and English**
- Main features include admin panel, donation system, programs information, gallery, news, events calendar, and volunteer management
- Project integrates with Sanity.io CMS, KakaoPay, and other third-party services

## Project Architecture

### Technology Stack

- **PHP**: Primary server-side language for main pages and functionality
- **TypeScript/React**: Used for interactive components and modern UI elements
- **Tailwind CSS**: Used for styling
- **MySQL**: Used for database storage
- **SHRIMP**: AI task management system for project development

### Directory Structure

- `/assets/` - CSS, JavaScript, and image files
- `/components/` - Reusable UI components (React/TypeScript)
- `/lib/` - Library files for various integrations (Sanity, KakaoPay, etc.)
- `/admin/` - Admin interface files
- `/messages/` - Language translation files
- `/SHRIMP/` - AI task management system files
- `/context/` - React context files
- `/docs/` - Documentation files

## Multilingual Content Rules

- **ALWAYS update both language files when modifying text content**
- Language files are stored in `/messages/` directory as JSON files:
  - `/messages/en.json` - English content
  - `/messages/ko.json` - Korean content
- When adding new text content, keys must match exactly across language files
- Structure must be identical between language files
- Never leave translations incomplete or use placeholder text in production

### Examples

**CORRECT:**
```json
// en.json
{
  "donate": {
    "new_field": "Donation Receipt"
  }
}

// ko.json
{
  "donate": {
    "new_field": "기부 영수증"
  }
}
```

**INCORRECT:**
```json
// en.json
{
  "donate": {
    "new_field": "Donation Receipt"
  }
}

// ko.json is missing the translation!
```

## Admin Panel Development

- Admin panel files include `admin.php`, `admin-*.php`, and files in `/admin/` directory
- **Authentication must use prepared statements** to prevent SQL injection
- Admin interface sections (`admin-events.php`, `admin-news.php`, etc.) must maintain consistent UI and UX
- All form submissions must include CSRF protection
- Implement access control based on user roles
- Never expose sensitive information in client-side code or URLs

### Security Rules

- **MUST**: Validate all input data server-side
- **MUST**: Use prepared statements for all database queries
- **MUST**: Implement proper access control checks for every admin action
- **MUST**: Hash and salt passwords for admin users
- **MUST NOT**: Store sensitive configuration in public accessible files
- **MUST NOT**: Use client-side validation exclusively

## SHRIMP Task Management System

- SHRIMP is an AI task management system used for project development
- Located in `/SHRIMP/` directory
- Supports templates in multiple languages (`templates_en`, `templates_zh`)
- Core files include:
  - `loader.ts` - Template loading system
  - `index.ts` - Exports all generators
  - `generators/` - Task generators

### SHRIMP Template Rules

- When modifying templates, update all language versions
- Templates should be located in the corresponding language directory
- Do not modify the core loader functionality unless absolutely necessary
- New task types must be added to both the generators directory and exported in index.ts

## File Dependencies and Synchronization

- **Critical dependencies that must be updated together:**

1. **PHP Pages and Language Files**
   - When adding/modifying text in any PHP page, corresponding entries must be added to `/messages/en.json` and `/messages/ko.json`

2. **Admin Panel Files**
   - Changes to admin functionality may require updates to multiple admin-*.php files
   - Security changes must be consistent across all admin files

3. **SHRIMP Templates**
   - Updates to SHRIMP templates must be made to all language versions

### Component Reuse

- Reuse components from `/components/` directory
- Component styling should use Tailwind CSS classes
- Common functionality should be extracted to libraries in `/lib/`

## Integration Guidelines

### Sanity.io CMS

- Integration code in `/lib/sanity.ts`
- Content types must be properly defined in Sanity schema
- Always use structured queries for data retrieval
- Implement proper error handling for API requests

### KakaoPay Integration

- Integration code in `/lib/kakao.ts`
- API keys must be stored securely using environment variables
- Implement proper webhook handling for payment notifications
- Always include transaction IDs in payment processing

### Multilingual Support

- Use the i18n system defined in `/lib/i18n.ts`
- Always provide translations for all supported languages
- Test interface with all supported languages

## Maintenance Protocols

### Database Management

- Use the database creation scripts in the root directory for setting up tables
- Follow the naming convention for database scripts:
  - `create-*-table.php` - For creating new tables
  - `update-*-*.php` - For updating existing tables or data
- Always include proper SQL transactions in database scripts
- Implement proper error handling and logging

### Gallery Management

- Gallery system requires special attention due to past issues
- Use the fix-gallery-*.php scripts for maintenance
- Always implement proper error handling for file uploads
- Ensure image optimization for performance

## Development Workflow

### Local Development

- Use local environment with PHP and MySQL
- Configure environment variables using `.env` file (template available as `.env.example`)
- Always test in both languages before deploying

### Deployment

- Deployment via FTP to dothome.co.kr
- Include proper file permissions
- Backup database before major updates
- Follow incremental deployment approach for large changes

## Prohibited Actions

- **NEVER** expose database credentials or API keys in client-side code
- **NEVER** use direct SQL queries without prepared statements
- **NEVER** update one language file without updating the other
- **NEVER** deploy without testing on multiple devices and browsers
- **NEVER** modify critical system files without proper backups
- **NEVER** add features that compromise existing security measures

## Mobile Responsiveness

- All pages must be fully responsive
- Test on multiple device sizes before deployment
- Use Tailwind's responsive classes consistently
- Ensure touch targets are appropriately sized for mobile

## Decision-making Standards

- When facing ambiguous situations:
  1. **Priority 1**: User security and data protection
  2. **Priority 2**: Consistency across languages
  3. **Priority 3**: Mobile responsiveness
  4. **Priority 4**: Performance optimization
  5. **Priority 5**: Feature enhancement
