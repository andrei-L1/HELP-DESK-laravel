# Architecture Guidelines: Role-Based Pages vs Shared Components

## The Pattern
In this Laravel/Inertia application, we often have different roles (Admin, Manager, Agent, User) that interact with the same underlying data (e.g., Tickets, Canned Responses). 

Currently, we maintain separate page views for these roles (e.g., `Pages/Admin/Tickets/Index.vue`, `Pages/Manager/Tickets/Index.vue`).

### Assessment
This approach of maintaining separate top-level pages for different roles is **safe and recommended** because it:
1. Prevents complex `v-if` spaghetti logic in a single file.
2. Enforces strict security and routing boundaries.
3. Allows for role-specific layouts and theming (e.g., different colored themes per role).

### When to Refactor (The "Goldilocks" Rule)
While separating top-level pages is good, duplicating the *entire contents* of those pages violates DRY (Don't Repeat Yourself) and makes maintenance difficult.

**Guideline:** 
When we find ourselves fixing the same bug or adding the same feature across multiple role-specific pages, we should extract the core functionality (tables, forms, complex UI) into **Shared Components** in the `resources/js/Components/` directory.

### Example Architecture
1. **Shared Component (`Components/Tickets/TicketTable.vue`)**: Contains the actual UI, pagination, and data logic.
2. **Role Pages (`Pages/Admin/Tickets/Index.vue`, `Pages/Staff/Tickets/Index.vue`)**: Act as wrappers that import the shared component and pass down role-specific props (e.g., permissions, themes).

```vue
<!-- Example of a Role Page Wrapper -->
<template>
    <AdminLayout>
        <SharedTicketTable 
            :tickets="tickets"
            :can-delete="true"
            theme="admin-indigo"
        />
    </AdminLayout>
</template>
```

By following this pattern, we maintain the security and flexibility of role-based pages while eliminating the maintenance nightmare of duplicated code.
