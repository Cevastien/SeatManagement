# Café Gervacio's Self-Service Kiosk System - Front-End Prototype

## Project Overview

This project is a **front-end prototype designed for demonstration purposes** that simulates a complete self-service kiosk system for Café Gervacio's restaurant. It demonstrates the user experience and system flow without requiring a real backend infrastructure. This Laravel-based subsystem focuses on the customer registration and priority verification workflow, creating a seamless demonstration experience for Figma integration.

## System Architecture

### Core Concept
This Laravel subsystem creates a **complete wireframe experience** by interconnecting multiple views and components that simulate the full system workflow. All user interactions and data flow are simulated through client-side JavaScript and session management, creating a seamless demonstration experience.

### Technology Stack
- **Framework**: Laravel 12.x (PHP 8.2+) - Used for rapid prototyping and view management
- **Frontend**: Blade templating with Livewire components for dynamic interactions
- **Styling**: Tailwind CSS with custom theming
- **Data Simulation**: Session-based data persistence for demonstration purposes
- **Interactive Elements**: JavaScript for form validation and user interactions

### Key Components

#### 1. **Customer Registration Subsystem**
- **Attract Screen**: Eye-catching welcome interface with priority access indicators
- **Registration Flow**: Multi-step customer registration process with form validation
- **Priority Verification**: Simulated staff verification system for senior citizens, PWD, and pregnant guests
- **Review & Edit Details**: Customer can review and edit their information before final confirmation
- **Digital Receipt**: Simulated receipt display with queue number and estimated wait time

#### 2. **Staff Verification Interface**
- **Verification Screen**: Staff interface for ID verification and priority approval
- **Status Updates**: Real-time verification status updates across the system
- **Priority Processing**: Simulated workflow for handling priority customer requests

#### 3. **Data Flow Simulation**
- **Session Management**: Customer data persists across page transitions for demonstration
- **Form Validation**: Client-side validation provides realistic user experience
- **Priority Logic**: Simulated priority handling with different customer types
- **Queue Simulation**: Mock queue numbering system (P001, R001 format) for demonstration

## Technical Implementation

### Data Flow Simulation
- **Shared Session Data**: All components use session-based data persistence for demonstration
- **Form Interconnection**: Multiple views work together to simulate complete system flow
- **Persistent State**: User data persists across page refreshes and browser sessions for realistic experience

### Key Features

#### Priority Access Simulation
- **Special Categories**: Senior Citizens, PWD (Persons with Disabilities), Pregnant guests
- **Verification Workflow**: Simulated ID verification process with staff approval interface
- **Queue Priority**: Mock priority queue management with different customer types
- **Staff Interface**: Demonstration of staff verification and approval process

#### Queue Management Simulation
- **Dynamic Numbering**: Simulated queue number generation with priority prefixes (P001, R001)
- **Wait Time Estimation**: Mock wait time calculations for demonstration purposes
- **Status Tracking**: Complete simulated customer journey from registration to completion
- **Interactive Updates**: JavaScript-driven updates to simulate real-time system behavior

#### User Experience Features
- **Form Validation**: Client-side validation provides realistic user interaction
- **Edit Functionality**: Users can edit their details and see changes reflected immediately
- **Seamless Navigation**: Smooth transitions between different system components
- **Visual Feedback**: Loading states, success messages, and error handling for realistic UX

## Demo Data Flow

### Customer Journey Example:
1. **Registration**: Customer enters name "Juan Dela Cruz" with party of 4
2. **Priority Check**: Customer indicates senior citizen status
3. **Verification**: Staff verification process (simulated)
4. **Queue Assignment**: Receives priority number P042
5. **Staff Notification**: Dashboard shows new priority customer
6. **Table Assignment**: Staff assigns Table 5 based on party size
7. **Completion**: Customer seated, table status updated, analytics recorded

### Staff Workflow:
1. **Dashboard Monitoring**: Real-time view of all system components
2. **Manual Entry**: Staff can bypass kiosk and manually enter customers
3. **Table Management**: Visual interface for table status and assignment
4. **Queue Processing**: Call next customer and manage seating flow
5. **Analytics Review**: Monitor performance metrics and business insights

## File Structure

```
SeatManagement/
├── resources/
│   ├── views/
│   │   ├── kiosk/                 # Customer-facing interfaces
│   │   │   ├── attract-screen.blade.php    # Welcome/attract screen
│   │   │   ├── registration.blade.php      # Customer registration form
│   │   │   ├── review-details.blade.php    # Review and edit details
│   │   │   ├── staffverification.blade.php # Staff verification interface
│   │   │   └── receipt.blade.php           # Digital receipt display
│   │   └── layouts/               # Page templates and layouts
│   └── css/                       # Custom styling and themes
├── app/
│   ├── Http/Controllers/          # View controllers for demonstration
│   │   └── RegistrationController.php
│   └── Models/                    # Data models for simulation
├── public/                        # Static assets and images
└── routes/                        # Route definitions for navigation
```

## Integration with Figma

This prototype is specifically designed for **Figma integration** and presentation purposes:

- **Complete User Flow**: Demonstrates every step from customer arrival to seating
- **Interactive Elements**: All buttons, forms, and navigation work as intended
- **Realistic Data**: Hardcoded examples that represent typical usage scenarios
- **Visual Fidelity**: High-quality UI that matches professional design standards

## Usage Instructions

### For Demonstrations:
1. **Start with Kiosk**: Open the application in browser to begin customer registration flow
2. **Monitor Staff View**: Navigate to staff verification interface to see staff perspective
3. **Complete Journey**: Follow a customer from registration through verification to receipt
4. **Explore Features**: Test priority verification, edit details, and different customer types

### For Development:
- **Staff Access**: Use staff verification interface for priority customer approval
- **Customer Interface**: Use registration flow for customer registration demonstration
- All views are self-contained and demonstrate the complete user experience
- Session-based data persistence creates realistic demonstration experience
- Real-time updates work across different interface components

## Demo Scenarios

### Scenario 1: Regular Customer
- Customer registers through kiosk interface
- Receives regular queue number (R001)
- Waits in standard queue simulation
- Gets seated when table becomes available

### Scenario 2: Priority Customer
- Senior citizen registers through kiosk
- Triggers priority verification process
- Receives priority queue number (P001)
- Gets faster seating based on priority status

### Scenario 3: Staff Verification
- Staff verifies ID documents for priority customers
- Approves or rejects priority status
- Updates customer verification status
- Allows customer to proceed with priority benefits

## Technical Specifications

- **Frontend Framework**: Laravel Blade with Livewire components
- **Styling**: Tailwind CSS with custom color scheme
- **Icons**: Font Awesome 6.5.2
- **Responsive Design**: Optimized for kiosk displays and staff workstations
- **Session Management**: Persistent data across page transitions for realistic experience

This prototype successfully demonstrates a complete restaurant queue management system without requiring any backend infrastructure, making it perfect for client presentations, stakeholder demonstrations, and Figma integration workflows.