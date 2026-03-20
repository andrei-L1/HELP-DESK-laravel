# 🕒 Help Desk Workflows

## 🏥 SLA Enforcement Workflow
The system employs a sophisticated background monitoring engine to ensure service level agreements are met.

### 1. Policy Mapping
Every ticket is automatically assigned an **SLA Policy** based on its department and priority level. This policy dictates the `due_at` timestamp and notification thresholds.

### 2. Continuous Monitoring
Laravel's task scheduler executes the `app:check-sla-breaches` command **every minute**. This command evaluates all unresolved tickets against their assigned deadlines.

### 3. Tiered Escalation
- **⚠️ Warning Zone**: Triggered when a ticket approaches its resolution limit (configurable per policy).
  - **Recipients**: Notifies the **Assignee**.
  - **Escalation**: If the ticket is "Urgent", the **Department Manager** is also alerted.
- **🚨 Breach Zone**: Triggered immediately once the current time exceeds the `due_at` deadline.
  - **Recipients**: Notifies the **Assignee**, **Department Manager**, and all **System Administrators**.
  - **Status**: The ticket is permanently flagged as `is_sla_breached`.

### 4. Automated Audit Trail
Every SLA-triggered event (Warning or Breach) is automatically recorded in the **Activity Log**. These entries are attributed to a "System User" (fallback to Admin) to ensure data integrity and traceability even when no agent is actively logged in.
