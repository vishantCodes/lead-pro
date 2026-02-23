# Multi-Tenant CRM for Agencies (Laravel 12 + RILT)

A production-oriented **Multi-Tenant CRM SaaS** built with:

* **Laravel 12**
* **React + Inertia**
* **Tailwind CSS**
* **Single Database Multi-Tenancy (tenant_id strategy)**
* **RBAC (Role-Based Access Control)**

This project is a **learning product** built with real-world SaaS architecture principles.
It is currently in **active development**.
**AI-powered features are coming soon.**

---

## Project Purpose

This CRM is designed for:

* Marketing Agencies
* Sales Organizations
* Lead Generation Teams

Each tenant represents an independent agency operating within the same database while maintaining strict data isolation.

The goal of this project is to demonstrate:

* Scalable SaaS architecture
* Proper multi-tenant design
* Clean service-layer backend structure
* Role-based security
* Event-driven automation
* Production-ready structure

---

# Core Features

## 1. Multi-Tenancy (Single Database)

* `tenant_id` enforced on all business tables
* Global scope isolation
* Tenant-aware middleware
* Super Admin override capability
* Indexed queries for performance

---

## 2. RBAC (Role-Based Access Control)

Roles:

* Super Admin
* Agency Admin
* Manager
* Sales Executive

Permission-driven access control (not hardcoded).

Examples:

* view_leads
* assign_leads
* manage_campaigns
* manage_commissions
* view_team_performance

---

# Modules

## Leads

* Categorized by:

  * Global
  * Online
  * Offline
* State-based filtering
* Assignment tracking
* Status lifecycle:

  * new
  * contacted
  * qualified
  * converted
  * lost

### Auto Lead Assignment

* Round-robin distribution
* State-based assignment rules
* Manager fallback logic

---

## Campaigns

* Create and manage campaigns
* Attach leads
* Campaign task management
* Budget tracking
* Performance overview

---

## Campaign Tasks

* Assignable to team members
* Due dates
* Status tracking
* Kanban-style interface (UI)

---

## Client Notes

* Notes per lead
* Created by user tracking
* Audit-ready structure

---

## Converted Clients

When a lead is converted:

* Status updated
* Revenue tracked

---

## Commission Engine (In-progress)

* Commission calculated automatically on lead conversion
* Based on user commission rate
* Approval workflow
* Commission status:

  * pending
  * approved
  * paid

---

## Team Performance Dashboard

Metrics include:

* Total leads per user
* Conversion rate
* Revenue generated
* Commission earned
* Campaign performance summary

---

# Architecture Principles

* Service layer pattern (no fat controllers)
* Policies for authorization
* Event-driven automation
* Observers for workflow triggers
* Clean React + Inertia page separation
* Strong indexing for performance

---

# Folder Structure Highlights

```
app/
 ├── Models
 ├── Services
 ├── Policies
 ├── Events
 ├── Listeners
 ├── Http/
 │    ├── Controllers
 │    ├── Middleware
 │    └── Requests
```

Frontend:

```
resources/js/
 ├── Pages
 ├── Components
 ├── Layouts
 ├── Hooks
```

---

# Development Status

* Core multi-tenant structure implemented
* RBAC foundation complete
* Leads and Campaign modules under active iteration
* Commission automation improving
* Performance optimizations ongoing

This is a **learning product** and a **live architectural experiment** in building scalable SaaS systems using Laravel 12.

Expect frequent structural refinements.

---

# Upcoming Features

## AI Integration (Coming Soon)

Planned AI capabilities:

* Smart lead scoring
* Automated follow-up suggestions
* Conversion prediction
* AI-based campaign insights
* Intelligent lead assignment optimization
* Email reply generation
* Performance anomaly detection

AI layer will integrate via:

* Service abstraction
* Background jobs
* Prompt versioning

---

# Target Outcome

This project aims to become a:

* Fully scalable SaaS-ready CRM
* Production-grade architecture reference
* Advanced Laravel multi-tenant blueprint
* Foundation for AI-enhanced CRM systems

---

# Disclaimer

This is a **learning and evolving product**.
Features may change as architecture matures.
AI capabilities are under development and will be introduced progressively.

---
