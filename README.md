# Student Apply Platform - Development Bible

## Core Principles

1. **Professional Coding Quality** - Production-ready, maintainable, and well-structured code
2. **Scalable Architecture** - Modular design that grows with the project
3. **Dynamic Implementation** - Flexible, reusable components and patterns
4. **Less Code, More Quality** - Elegant solutions over verbose implementations
5. **Easy to Read** - Self-documenting code, clear naming conventions
6. **No Comments, Responsive** - Code should be self-explanatory, all UIs must be mobile-first
7. **Consistent UI/UX** - Follow shadcn-inspired design system across all screens

---

## Tech Stack

### Frontend
- **Framework**: Vue 3 with TypeScript
- **State Management**: Pinia
- **Routing**: Vue Router 4
- **Styling**: Tailwind CSS 4
- **UI Inspiration**: shadcn/ui design patterns
- **Build Tool**: Vite
- **Internationalization**: Vue I18n

### Backend
- **Framework**: Laravel 12
- **Authentication**: Laravel Sanctum
- **Permissions**: Spatie Laravel Permission
- **API**: RESTful JSON API

---

## Architecture Standards

### Frontend Structure

```
frontend/src/
├── components/
│   ├── ui/          # Reusable UI components (Button, Input, Card, Toast)
│   ├── auth/        # Authentication components
│   └── layout/      # Layout components
├── views/           # Page-level components
├── stores/          # Pinia stores
├── composables/     # Reusable composition functions
├── services/        # API service layer
├── router/          # Route definitions
└── assets/          # Global styles and assets
```

### Backend Structure

```
backend/
├── app/
│   ├── Domain/      # Business logic
│   ├── Presentation/ # Controllers, Middleware
│   └── Infrastructure/ # Repositories, Services
├── routes/
│   └── api.php      # API routes
└── database/
    ├── migrations/  # Database migrations
    └── seeders/     # Database seeders
```

---

## Coding Standards

### TypeScript Guidelines

- Use TypeScript strict mode
- Define interfaces for all data structures
- Avoid `any` type - use `unknown` or proper types
- Use type inference where appropriate
- Define return types for functions

```typescript
interface User {
  id: number
  name: string
  email: string
  role: 'super_admin' | 'staff' | 'student'
  whatsapp?: string
  passport_no?: string
}

function createUser(data: Partial<User>): Promise<User> {
  return api.post('/users', data)
}
```

### Vue Component Patterns

- Use `<script setup>` syntax exclusively
- Define props and emits with TypeScript
- Use composables for reusable logic
- Extract complex logic to composables
- Keep components focused and single-purpose

```vue
<script setup lang="ts">
interface Props {
  userId: number
  readonly?: boolean
}
const props = withDefaults(defineProps<Props>(), {
  readonly: false
})

const emit = defineEmits<{
  update: [value: User]
  delete: [id: number]
}>()
</script>
```

### Pinia Store Patterns

- Use store composition for related state
- Keep stores focused on single domains
- Use actions for async operations
- Use getters for computed state

```typescript
export const useAppStore = defineStore('app', () => {
  const items = ref<Item[]>([])
  const loading = ref(false)
  
  const filtered = computed(() => 
    items.value.filter(i => i.active)
  )
  
  async function fetch() {
    loading.value = true
    try {
      items.value = await api.list()
    } finally {
      loading.value = false
    }
  }
  
  return { items, loading, filtered, fetch }
})
```

### Laravel Backend Patterns

- Use Form Requests for validation
- Use Resources for API responses
- Keep controllers thin, logic in services
- Use repositories for data access
- Follow RESTful conventions

---

## UI/UX Standards

### Design System

**Colors:**
- Success: Green (#10b981, #059669)
- Error: Red (#ef4444, #dc2626)
- Warning: Yellow (#f59e0b, #d97706)
- Primary: Blue (#3b82f6, #2563eb)
- Muted: Gray (#6b7280, #9ca3af)

**Spacing:**
- Use Tailwind spacing scale (0.5, 1, 1.5, 2, 3, 4, 6, 8, 12, 16, 20, 24)
- Consistent padding: `p-4` for cards, `p-6` for modals

**Typography:**
- Headings: `text-xl`, `text-2xl`, `text-3xl` with `font-semibold`
- Body: `text-sm`, `text-base`
- Muted text: `text-muted` (gray-600)

**Components:**
- Cards: `rounded-xl shadow-lg ring-1 ring-black/5 bg-white`
- Buttons: `rounded-lg px-4 py-2 font-medium`
- Inputs: `rounded-lg border border-black/10 px-3 py-2`

### Toast Notifications

Use shadcn-inspired toast system with modern design:

```typescript
import { useToast } from '@/composables/useToast'

const toast = useToast()

toast.success('Operation completed successfully')
toast.error('Something went wrong')
toast.warning('Please check your input')
toast.info('Information message')
```

**Toast Specifications:**
- Success: Green background with checkmark icon
- Error: Red background with error icon
- Warning: Yellow background with warning icon
- Position: Fixed top-right, mobile responsive
- Animation: Smooth fade and slide transitions
- Auto-dismiss: 3 seconds default, configurable
- Backdrop blur effect for modern glassmorphism

### Responsive Design

- Mobile-first approach
- Breakpoints: `sm:`, `md:`, `lg:`, `xl:`, `2xl:`
- Touch-friendly: Minimum 44px touch targets
- Grid layouts: `grid-cols-1 md:grid-cols-2 lg:grid-cols-3`
- Navigation: Collapsible sidebar on mobile

---

## Project Requirements

### User Roles

#### Super Admin
- Manage system configuration and modules
- User management (create, edit, assign roles)
- Roles and permissions management
- System performance monitoring
- University and course management
- Staff assignment limits configuration
- Student application limits configuration

#### Staff
- Handle student applications
- Monitor application progress
- Manage assigned students (max 5 active applications)
- Track commission points system
- Two-way communication with students
- Document verification and approval
- Send letters and documents to students

#### Student
- Browse universities and courses
- Filter by course, acceptance percentage (50%-99%)
- Create account (email, password, WhatsApp, passport number)
- Create applications (max 3 per passport number)
- Track application progress through stages
- Upload documents per stage requirements
- Two-way communication with assigned staff
- View payment requirements and receipts

### Application Workflow

#### Stage 1: Initial Application
**Required Documents:**
- High school certificate (PDF)
- First page of passport (PNG/PDF)

**Process:**
- Student selects university and course
- System randomly assigns staff member
- Staff reviews and approves documents
- Student waits up to 2 weeks

**Outcome:**
- Staff sends Conditional Letter of Offer (MOL) as PDF
- Stage 2 automatically unlocks upon MOL release

#### Stage 2: Visa & Application Fee (EMGS)
**Required Documents:**
- Visa application receipt (Image/PDF)
- Full passport scanned (PDF)
- Embassy certificate NOC receipt
- Signed offer letter from Stage 1
- Passport image with white background

**Process:**
- Student makes required payments
- Uploads all documents
- Staff verifies and confirms
- Wait 2-3 weeks for eVAL

**Outcome:**
- Staff sends eVAL document as PDF
- Stage 3 automatically unlocks upon eVAL release

#### Stage 3: Visa Entry
**Requirements:**
- Single visa entry payment (118 RM)
- First page of passport (PDF/Image)
- Bank statement (under father's name, minimum 4 months) (PDF)
- Yellow fever card (PDF)
- eVAL document from Stage 2 (PDF)

**Process:**
- Student makes payment
- Uploads all required documents
- Staff verifies and confirms
- Wait 2-3 weeks for visa

**Outcome:**
- Staff sends Single Entry Visa as PDF
- Stage 4 automatically unlocks upon visa release

#### Stage 4: Arrival Preparation
**Required Documents:**
- E-Visa PDF from Stage 3
- Signed Offer Letter (PDF)
- Annual Fee Payment Slip (first year only)
- One-way ticket
- Airport Form (download, fill, upload)
- Student Accommodation Application Form (download, fill, upload)

**Process:**
- Student uploads all documents
- Staff verifies and sends arrival information
- Staff confirms airport pickup details
- Staff marks student as "arriving soon"

**Outcome:**
- Stage 5 automatically unlocks

#### Stage 5: Post-Arrival Steps
**Information Display:**
1. Settling Hostel - Clear instructions
2. Student Activation - Step-by-step guide
3. English Placement Test - Details and requirements

All explanations must be simple and clear, written for primary school level understanding.

### Communication System

- Real-time two-way chat between student and assigned staff
- Document upload and re-upload capability
- Notification system for new messages
- File sharing within chat interface

### Business Rules

**Staff Assignment:**
- Random assignment upon application creation
- Maximum 5 active applications per staff member
- Super admin can configure limits
- Soft delete of application frees up slot

**Student Application Limits:**
- Maximum 3 applications per passport number
- Tracked globally across all applications
- Soft delete allows creating new application
- Super admin can configure limits

**Points System:**
- Staff earns points based on application progress
- Points used for commission calculation
- Super admin can view and manage points
- Staff can monitor their own points

### Landing Page

**Features:**
- Platform information and overview
- University listing with filtering:
  - Filter by course type
  - Filter by acceptance percentage (50%-99%)
- Course details display
- Apply button (requires authentication)
- Responsive design for mobile and desktop

**User Flow:**
1. Student browses universities
2. Filters by preferences
3. Selects university and clicks "Apply"
4. If not authenticated: redirects to registration
5. After registration: proceeds to application creation

---

## API Standards

### Request Format
- Headers: `Accept: application/json`
- Authentication: `Authorization: Bearer {token}`
- Content-Type: `application/json` for POST/PUT/PATCH

### Response Format
```json
{
  "data": {},
  "message": "Success message",
  "status": "success"
}
```

### Error Response
```json
{
  "message": "Error message",
  "errors": {
    "field": ["Validation error"]
  }
}
```

### Endpoint Conventions
- `GET /api/{resource}` - List resources
- `GET /api/{resource}/{id}` - Show resource
- `POST /api/{resource}` - Create resource
- `PUT /api/{resource}/{id}` - Update resource
- `DELETE /api/{resource}/{id}` - Delete resource

---

## File Organization

### Naming Conventions

**Components:**
- PascalCase: `UserProfile.vue`, `ApplicationCard.vue`
- Descriptive and specific names

**Stores:**
- camelCase: `useAuthStore.ts`, `useApplicationStore.ts`
- Domain-based naming

**Composables:**
- camelCase with `use` prefix: `useToast.ts`, `useDebounce.ts`

**Types/Interfaces:**
- PascalCase: `User.ts`, `Application.ts`

### File Structure Rules

- One component per file
- Related components in same directory
- Shared UI components in `components/ui/`
- Feature-specific components in feature directories
- Keep files focused and under 300 lines when possible

---

## Performance Standards

- Lazy load routes and components
- Code splitting per route
- Optimize images and assets
- Debounce search inputs (300ms)
- Virtual scrolling for long lists
- Pagination for data tables

---

## Security Standards

- All API endpoints protected with authentication
- Role-based access control (RBAC)
- Input validation on frontend and backend
- XSS protection (Vue auto-escapes)
- CSRF protection (Laravel)
- Secure file uploads (validate type, size, scan)
- Password hashing (bcrypt)
- HTTPS in production

---

## Testing Standards

- Unit tests for business logic
- Component tests for UI components
- Integration tests for API endpoints
- E2E tests for critical user flows

---

## Deployment Standards

- Environment variables for configuration
- Database migrations version controlled
- Build optimization for production
- Error logging and monitoring
- Health check endpoints

---

## Development Workflow

1. Create feature branch from `main`
2. Implement following all standards
3. Test thoroughly
4. Code review
5. Merge to `main`
6. Deploy to staging
7. QA testing
8. Deploy to production

---

## Code Quality Checklist

- [ ] TypeScript types properly defined
- [ ] No `any` types used
- [ ] Responsive design tested on mobile
- [ ] Toast notifications for user actions
- [ ] Error handling implemented
- [ ] Loading states for async operations
- [ ] Accessibility considerations (ARIA labels)
- [ ] Performance optimized
- [ ] Security best practices followed
- [ ] Code is self-documenting (no comments needed)

---

## Quick Reference

### Create New Component
```vue
<script setup lang="ts">
interface Props {
  // define props
}
const props = defineProps<Props>()
</script>

<template>
  <div class="component-wrapper">
    <!-- component markup -->
  </div>
</template>
```

### Create New Store
```typescript
import { defineStore } from 'pinia'

export const useFeatureStore = defineStore('feature', () => {
  const state = ref()
  const computed = computed(() => state.value)
  
  async function action() {
    // implementation
  }
  
  return { state, computed, action }
})
```

### Create New API Endpoint
```php
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/resource', [Controller::class, 'index']);
    Route::post('/resource', [Controller::class, 'store']);
});
```

---

This README serves as the definitive guide for all development work on this project. Refer to it when implementing any feature or making architectural decisions.
