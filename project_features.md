# Warga App - Project Features

This document outlines the current features and modules available in the `warga-app` project, including the core Laravel application and the integrated Chatbot service.

## 1. Core Application (Laravel + Filament)

The core application is built using Laravel with FilamentPHP as the admin panel.

### Resident Management (Data Warga)
Comprehensive management of resident data.
- **Personal Information**: Full Name, NIK, Phone Number.
- **Demographics**: Place & Date of Birth, Occupation, Marital Status (Single, Married, Divorced, etc.), Religion.
- **Residency Status**:
  - Types: Permanent, Contract, Periodic.
  - Status management with color-coded badges.
- **Family Data**:
  - Family Card Number (No. KK).
  - Head of Family toggle.
  - Family Relations: Husband, Wife, Child, Other.
- **Documents**: Capability to upload and store digital copies of KK (Kartu Keluarga) and KTP.
- **Relationships**: Linked to Houses and Vehicles.

### Inventory Management (Inventaris Warga)
Track assets and inventory items owned by residents.
- **Ownership**: Linked to specific Resident.
- **Item Details**: Name of item, Quantity, Description.
- **History/Logs**: (Based on typical Filament behavior) Creation and update timestamps.

### House Management
- Management of housing units within the community.
- Organized by Block and Number.
- Linked to Residents.

### Financial & Payments
- **IPL Payments**: Management of Iuran Pemeliharaan Lingkungan (Maintenance Fees).
- **Expense Management**: Tracking of community expenses.

### Vehicle Management
- Registration of resident vehicles.
- Linked to Residents.

### System & User Management
- **User Resource**: Management of system administrators.
- **System Settings**: Configuration for system-wide variables.

---

## 2. Chatbot Service (`chatbotwa`)

A separate Node.js microservice for WhatsApp automation, integrated with the main app.

### Backend Features (Node.js/Express)
- **Multi-Session Support**:
  - Ability to manage multiple WhatsApp accounts simultaneously.
  - Dynamic session creation (`initSession`) and deletion.
- **Baileys Integration**: Uses `@whiskeysockets/baileys` only for stable WhatsApp Web API connection.
- **API Endpoints**:
  - `GET /api/sessions`: List all active sessions and their status.
  - `POST /api/sessions`: Create a new session.
  - `DELETE /api/sessions/:id`: Remove a session.
  - `POST /api/send`: Send text messages programmatically.
  - `GET /api/groups/:sessionId`: Retrieve list of WhatsApp groups.
  - `GET /api/status/:sessionId`: Check connection status.
- **Real-time Updates**: Socket.io integration to stream connection status and QR codes to the frontend.
- **Rules Engine**:
  - Auto-reply system based on keywords.
  - Supports Exact Match (and potentially others).
  - API to add/remove rules dynamically.

### Frontend Features (React/Vite)
- **Dashboard**: Interface to manage bot instances.
- **QR Code Scanning**: Real-time display of QR codes for linking devices.
- **Status Monitoring**: Live connection status (Connected, Disconnected) via WebSockets.

## 3. Technology Stack

- **Backend Framework**: Laravel 10/11
- **Admin Panel**: FilamentPHP v3
- **Database**: MariaDB/MySQL
- **Chatbot Service**: Node.js, Express, Socket.io, Baileys
- **Chatbot Client**: React, Vite, TailwindCSS
