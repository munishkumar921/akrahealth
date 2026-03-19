export const doctorNavItems = [{
        route: "doctor.dashboard",
        icon: "fa-solid fa-house",
        label: "Dashboard",
    },
    {
        route: "doctor.patients",
        icon: "fa-solid fa-users",
        label: "All Patients",
    },
    {
        label: "Patient Details",
        icon: "fa-solid fa-file-invoice",
        isCollapsible: true,
        items: [{
                route: "doctor.demographics",
                icon: "fa-solid fa-user",
                label: "Demographics",
            },
            {
                route: "doctor.socialHistory.index",
                icon: "fa-solid fa-users",
                label: "Social History",
            },
            {
                route: "doctor.patient.history",
                icon: "fa-solid fa-users",
                label: "History",
            },
            {
                route: "doctor.family-history.index",
                icon: "fa-solid fa-users",
                label: "Family History",
            },

        ],
    },
    {
        label: "Medical History",
        icon: "fa-solid fa-book-medical",
        isCollapsible: true,
        items: [{
                route: "doctor.conditions.index",
                icon: "fa-solid fa-temperature-arrow-up",
                label: "Conditions",
            },
            {
                route: "doctor.medications.index",
                icon: "fa-solid fa-tablets",
                label: "Medications",
            },
            {
                route: "doctor.supplements.index",
                icon: "fa-solid fa-capsules",
                label: "Supplements",
            },
            {
                route: "doctor.immunizations.index",
                icon: "fa-solid fa-syringe",
                label: "Immunizations",
            },
            {
                route: "doctor.allergies.index",
                icon: "fa-solid fa-allergies",
                label: "Allergies",
            },
            {
                route: "doctor.alerts.index",
                icon: "fa-solid fas fa-bell",
                label: "Alerts",
            }
        ],
    },
    {
        label: "Medical Records",
        icon: "fa-solid fa-folder-open",
        isCollapsible: true,
        items: [{
                route: "doctor.documents.index",
                icon: "fa-solid fa-file-alt",
                label: "Documents",
            },
            {
                route: "doctor.results.index",
                icon: "fa-solid fa-flask",
                label: "Results",
            },
            {
                route: "doctor.forms.index",
                icon: "fa-solid fa-file-signature",
                label: "Forms",
            },
        ],
    },
    {
        label:"patient referral",
        icon: "fa-solid fa-envelope",
        route: "doctor.messages.index",
        
    },

    {
        label: "Finance",
        icon: "fa-solid fa-dollar-sign",
        isCollapsible: true,
        items: [{
                route: "doctor.orders.index",
                icon: "fa-solid fa-receipt",
                label: "Orders",
            },
            {
                route: "doctor.billing.index",
                icon: "bi bi-bank",
                label: "Billing",
            },
            {
                route: "doctor.insurance.index",
                icon: "bi bi-file-earmark-text",
                label: "Insurance",
            },
        ],
    },
    // {
    //     label: "Office",
    //     icon: "bi bi-building",
    //     isCollapsible: true,
    //     items: [
    //         {
    //             route: "doctor.vaccines",
    //             icon: "fa-solid fas fa-syringe",
    //             label: "Vaccines",
    //         },
    //         {
    //             route: "doctor.supplements",
    //             icon: "fas fa-notes-medical",
    //             label: "Supplements",
    //         },
    //         {
    //             route: "doctor.reports",
    //             icon: "bi bi-file-earmark-text",
    //             label: "Reports",
    //         },
    //     ],
    // },
    // {
    //     label: "Settings",
    //     icon: "bi bi-gear",
    //     isCollapsible: true,
    //     items: [
    //         {
    //             route: "doctor.configure.address_book",
    //             icon: "fa-solid fas fa-book-medical",
    //             label: "Address Book",
    //         },
    //         {
    //             route: "doctor.configure.my_forms",
    //             icon: "fa-solid far fa-file-alt",
    //             label: "My Forms",
    //         },
    //         {
    //             route: "doctor.configure.provider_exceptions",
    //             icon: "fa-solid fas fa-briefcase-medical",
    //             label: "Provider Exception",
    //         },
    //         {
    //             route: "doctor.configure.schedule_setup",
    //             icon: "fa-solid fa fa-calendar",
    //             label: "Schedule Setup",
    //         },
    //         {
    //             route: "doctor.configure.visit_type",
    //             icon: "f-solid fas fa-street-view",
    //             label: "Visit Types",
    //         },
    //     ],
    // },
    {
        route: "doctor.coordination.index",
        icon: "fa-solid fa-handshake-o",
        label: "Coordination of Care",
    },
];

export const adminNavItems = [{
        route: "admin.dashboard",
        icon: "fa-solid fa-house",
        label: "Dashboard",
    },
    {
        label: "User Management",
        icon: "fa-solid fas fa-user-cog",
        isCollapsible: true,
        items: [
            // {
            //     route: "admin.admins.index",
            //     icon: "fa-solid fa fa-user",
            //     label: "Admins",
            // },
            {
                route: "admin.users.index",
                icon: "fa-solid fa fa-user-md",
                label: "Users",
            },
            {
                route: "admin.patients.index",
                icon: "fa-solid fas fa-wheelchair",
                label: "Patients",
            },
            {
                route: "admin.labs.index",
                icon: "fa-solid fas fa-vial",
                label: "Labs",
            },
            {
                route: "admin.pharmacies.index",
                icon: "fa-solid fas fa-laptop-medical",
                label: "Pharmacy",
            },
        ],
    },
    {
        label: "Manage",
        icon: "fa-solid fa-calendar-alt",
        isCollapsible: true,
        items: [{
                route: "admin.specialities.index",
                icon: "fa-solid fas fa-stethoscope",
                label: "Specialities",
            },
            {
                route: "admin.medicines.index",
                icon: "fa-solid fas fa-capsules",
                label: "Medicines",
            },
            {
                route: "admin.lab-test-categories.index",
                icon: "fa-solid fa fa-medkit",
                label: "Lab Test Categories",
            },
            {
                route: "admin.lab-tests.index",
                icon: "fa-solid fas fa-vials",
                label: "Lab Tests",
            },
        ],
    },
    // {
    //     route: "admin.prescriptions.index",
    //     icon: "fa-solid fas fa-prescription",
    //     label: "Prescriptions",
    // },
    {
        route: "admin.allAppointments",
        icon: "fa-solid fa-calendar-plus",
        label: "Calendar",
    },
    //  {
    //     label: "Forms",
    //     icon: "fa-solid fa-file-signature",
    //     route: "admin.forms.index",
    // },
    
    {
        label: "Financial Management",
        icon: "fa-solid fas fa-chart-pie",
        isCollapsible: true,
        items: [{
                route: "admin.transaction.list",
                icon: "fa-solid fas fa-history",
                label: "Transactions",
            },
            {
                route: "admin.invoice.list",
                icon: "fa-solid fas fa-file-invoice",
                label: "Invoices",
            },
            {
                route: "admin.subscription",
                icon: "fa-solid fa-credit-card",
                label: "My Subscription",
            },
        ],
    },
    {
        label: "Order Management",
        icon: "fa-solid fas fa-shopping-basket",
        isCollapsible: true,
        items: [
            // {
            //     route: "admin.dashboard",
            //     icon: "fa-solid fa fa-tasks",
            //     label: "Assign Orders",
            // },
            {
                route: "admin.lab-orders.list",
                icon: "fa-solid fas fa-first-aid",
                label: "Lab Orders",
            },
            {
                route: "admin.pharmacy-orders.list",
                icon: "fa-solid fas fa-prescription-bottle-alt",
                label: "Pharmacy Orders",
            },
        ],
    },
    {
        label: "Inventory",
        icon: "fa-solid fas fa-boxes",
        isCollapsible: true,
        items: [
            {
                route: "admin.supplements.index",
                icon: "fa-solid fas fa-capsules",
                label: "Supplements",
            },
            {
                route: "admin.vaccines.index",
                icon: "fa-solid fas fa-syringe",
                label: "Vaccines",
            },
            {
                route: "admin.vaccines.temperature.index",
                icon: "fa-solid fas fa-temperature-high",
                label: "Vaccine Temperatures",
            }
        ],
    },
   
    {
        label: "Settings",
        icon: "fa-solid fa fa-cogs",
        isCollapsible: true,
        items: [
            // {
            //     route: "admin.settings.practice.list",
            //     icon: "fa-solid fas fa-tasks",
            //     label: "Practice",
            // },
            {
                route: "admin.visit-types.index",
                icon: "fa-solid fas fa-diagnoses",
                label: "Visit Types",
            },
            {
                route: "admin.hospital-timing",
                icon: "fa-solid fa-calendar",
                label: "Schedule Setup",
            },
            {
                route: "admin.provider-exception.index",
                icon: "fa-solid fa-calendar-check",
                label: "Exceptions",
            },
            // {
            //     route: "admin.rolesandpermission",
            //     icon: "fa-solid fas fa-user-cog",
            //     label: "Roles & Permissions",
            // },
            {
                route: "admin.epayment",
                icon: "fa-solid fa fa-credit-card",
                label: "E-Payment",
            },
            // {
            //     route: "admin.payment-platforms.index",
            //     icon: "fa-solid fa fa-credit-card",
            //     label: "Payment Platforms",
            // },
            // {
            //     route: "admin.subscription",
            //     icon: "fa-solid fa-font-awesome",
            //     label: "Subscriptions",
            // },
           
            // {
            //     route: "admin.settings.index",
            //     icon: "fa-solid fas fa-globe",
            //     label: "General",
            // },
            {
                route: "admin.notification",
                icon: "fa-solid fas fa-bell",
                label: "Notification",
            },
        ],
    },
    {
        label: "Reports",
        icon: "fa-solid fa fa-file-text-o",
        isCollapsible: true,
        items: [{
                route: "admin.CCDAReports",
                icon: "fa-solid fas fa-notes-medical",
                label: "CCDA's",
            },
           
            // {
            //     route: "admin.dashboard",
            //     icon: "fa-solid fa-user",
            //     label: "Demographics",
            // },  
            {
                route: "admin.labReports",
                icon: "fa-solid fas fa-vial",
                label: "Lab Reports",
            },
            {
                route: "admin.pharmacyReports",
                icon: "fa-solid fas fa-laptop-medical",
                label: "Pharmacy Reports",
            },
            {
                route: "admin.myReports",
                icon: "fa-solid fa fa-file-alt",
                label: "My Reports",
            },
        ],
    },
    {
        label: "Logs",
        icon: "fa-solid fa fa-history",
        isCollapsible: true,
        items: [
            {
                route: "admin.audit-logs.index",
                icon: "fa-solid fa fa-file",
                label: "Audit logs",
            },
            {
                route: "admin.callLogs.list",
                icon: "fa-solid fa-phone",
                label: "Call logs",
            },
            // {
            //     route: "admin.dashboard",
            //     icon: "fa-solid fa fa-whatsapp",
            //     label: "WhatsApp logs",
            // },
        ],
    },
];
export const SadminNavItems = [{
        route: "superAdmin.dashboard",
        icon: "fa-solid fa-house",
        label: "Dashboard",
    },
     {
        label: "User Management",
        icon: "fa-solid fas fa-user-cog",
        isCollapsible: true,
        items: [{
                route: "superAdmin.userdashboard",
                icon: "fa-solid fa-house",
                label: "Dashboard",
            },
            {
                route: "superAdmin.userlist",
                icon: "fa-solid fa-contact-card",
                label: "User List",
            },
            {
                route: "superAdmin.activitymonitoring",
                icon: "fa-solid fas fa-wheelchair",
                label: "Activity Monitoring",
            },
        ],
    },
     {
                route: "superAdmin.services.index",
                icon: "fa-solid fa fa-plus-circle",
                label: "Services",
            },
    {
        label: "Financial Management",
        icon: "fa-solid fas fa-chart-pie",
        isCollapsible: true,
        items: [{
                route: "superAdmin.financedashboard",
                icon: "fa-solid fa-house",
                label: "Dashboard",
            },
            {
                route: "superAdmin.transaction",
                icon: "fa-solid fas fa-credit-card",
                label: "Transactions",
            },
            {
                route: "superAdmin.subcriptionPlan",
                icon: "fa-solid fas fa-book",
                label: "Subscription Plans",
            },
            {
                route: "superAdmin.subscribers",
                icon: "fa-solid fas fa-users",
                label: "Subscriber",
            },
            {
                route: "superAdmin.payment",
                icon: "fa-solid fas fa-file-invoice",
                label: "Payment",
            },
            {
                route: "superAdmin.invoice",
                icon: "fa-solid fas fa-file-invoice-dollar",
                label: "Invoices",
            },
        ],
    },
     {
        label: "Email Notifications",
        icon: "fa-solid fa fa-history",
        isCollapsible: true,
        items: [{
                route: "superAdmin.massnotification",
                icon: "fa-solid fas fa-bullhorn",
                label: "Mass Notifications",
            },
            {
                route: "superAdmin.systemnotification",
                icon: "fa-solid fas fa-bell",
                label: "System Notifications",
            },
            {
                route: "superAdmin.massmailnotification",
                icon: "fa-solid fas fa-mail-bulk",
                label: "Mass Mail Notifications",
            },
        ],
    },
    {
        label: "General Settings",
        icon: "fa-solid fa fa-cogs",
        isCollapsible: true,
        items: [{
                route: "superAdmin.globalsetting",
                icon: "fa-solid fa fa-globe",
                label: "Global Settings",
            },
            {
                route: "superAdmin.smtpsetting",
                icon: "fa-solid fa fa-envelope",
                label: "SMTP Settings",
            },
            {
                route: "superAdmin.languagesetting",
                icon: "fa-solid fa fa-language",
                label: "Language",
            },
            {
                route: "superAdmin.rolesandpermission",
                icon: "fa-solid fa fa-user-shield",
                label: "Roles & Permissions",
            },
        ],
    },
];

export const patientNavItems = [{
        route: "patient.dashboard",
        icon: "fa-solid fa-house",
        label: "Dashboard",
    },
    {
        label: "Patient Details",
        icon: "fa-solid fa-file-invoice",
        isCollapsible: true,
        items: [{
                route: "patient.demographics",
                icon: "fa-solid fa-user",
                label: "Demographics",
            },
            {
                route: "patient.social-history",
                icon: "fa-solid fa-users",
                label: "Social History",
            },
            {
                route: "patient.history",
                icon: "fa-solid fa-users",
                label: "History",
            },
            {
                route: "patient.family-history",
                icon: "fa-solid fa-users",
                label: "Family History",
            },

        ],
    },
    {
        label: "Medical History",
        icon: "fa-solid fa-book-medical",
        isCollapsible: true,
        items: [{
                route: "patient.conditions",
                icon: "fa-solid fa-temperature-arrow-up",
                label: "Conditions",
            },
            {
                route: "patient.medications",
                icon: "fa-solid fa-pills",
                label: "Medications",
            },
            {
                route: "patient.supplements",
                icon: "fa-solid fa-capsules",
                label: "Supplements",
            },
            {
                route: "patient.immunizations",
                icon: "fa-solid fa-syringe",
                label: "Immunizations",
            },
            {
                route: "patient.allergies",
                icon: "fa-solid fa-allergies",
                label: "Allergies",
            },
        ],
    },
    {
        label: "Medical Records",
        icon: "fa-solid fa-folder-open",
        isCollapsible: true,
        items: [{
                route: "patient.documents",
                icon: "fa-solid fa-file-alt",
                label: "Documents",
            },
            {
                route: "patient.results",
                icon: "fa-solid fa-flask",
                label: "Results",
            },
            {
                route: "patient.forms",
                icon: "fa-solid fa-file-signature",
                label: "Forms",
            }
        ],
    },

    {
        route: "patient.orders",
        icon: "fa-solid fa-cart-plus",
        label: "Orders",
    },
    {
        route: "patient.encounters",
        icon: "fa-solid fa-user-doctor",
        label: "Encounters",
    },
    {
        route: "patient.providers",
        icon: "fa-solid fa-user-doctor",
        label: "Providers",
    },
    {
        label: "Finance",
        icon: "fa-solid fa-dollar-sign",
        isCollapsible: true,
        items: [{
            route: "patient.billing",
            icon: "fa-solid fa-money-check-alt",
            label: "Billing",
        }, ],
    },
];
const labNavItems = [
    { route: "lab.dashboard", icon: "fa-solid fa-house", label: "Dashboard" },
    { route: "lab.labs.index", icon: "fa-solid fas fa-vial", label: "Labs Oders" },
    { route: "lab.tests.index", icon: "fa-solid fa fa-medkit", label: "Lab Tests" },
    { route: "lab.reports.index", icon: "fa-solid fas fa-vial", label: "Lab Reports" },
    { route: "lab.transactions.index", icon: "fa-solid fas fa-history", label: "Transactions" },
];

  const pharmacyNavItems = [
    { route: "pharmacy.dashboard", icon: "fa-solid fa-house", label: "Dashboard" },
     { route: "pharmacy.medicines.index", icon: "fa-solid fa-capsules", label: "Medicines" },
    { route: "pharmacy.orders.index", icon: "fa-solid fa-prescription-bottle-alt", label: "Orders" },
    { route: "pharmacy.reports.index", icon: "fa-solid fas fa-file-alt", label: "Reports" },
    { route: "pharmacy.transactions.index", icon: "fa-solid fas fa-history", label: "Transactions" },
];

export  const billerNavItems = [
    { 
        route: "biller.dashboard", 
        icon: "fa-solid fa-house", 
        label: "Dashboard" 
    },
     { 
        route: "biller.finance.bills_to_submit", 
        icon: "fa-solid fa-dollar-sign", 
        label: "Financial" 
    },
     { 
        route: "biller.billing", 
        icon: "bi bi-bank", 
        label: "Patient Billing Management" 
    },
    { 
        route: "biller.insurance.index", 
        icon: "bi bi-file-earmark-text", 
        label: "Insurance Claims Management" 
    },
];
export  const assistantNavItems = [
    {
        route: "assistant.dashboard",
        icon: "fa-solid fa-house",
        label: "Dashboard"
    },
    {
        route: "assistant.patients",
        icon: "fa-solid fa-users",
        label: "All Patients",
    },
    {
        label: "Patient Details",
        icon: "fa-solid fa-file-invoice",
        isCollapsible: true,
        items: [{
                route: "assistant.demographics",
                icon: "fa-solid fa-user",
                label: "Demographics",
            },
            {
                route: "assistant.socialHistory.index",
                icon: "fa-solid fa-users",
                label: "Social History",
            },
            {
                route: "assistant.patient.history",
                icon: "fa-solid fa-users",
                label: "History",
            },
            {
                route: "assistant.family-history.index",
                icon: "fa-solid fa-users",
                label: "Family History",
            },

        ],
    },
    {
        label: "Medical History",
        icon: "fa-solid fa-book-medical",
        isCollapsible: true,
        items: [{
                route: "assistant.conditions.index",
                icon: "fa-solid fa-temperature-arrow-up",
                label: "Conditions",
            },
            {
                route: "assistant.medications.index",
                icon: "fa-solid fa-tablets",
                label: "Medications",
            },
            {
                route: "assistant.supplements.index",
                icon: "fa-solid fa-capsules",
                label: "Supplements",
            },
            {
                route: "assistant.immunizations.index",
                icon: "fa-solid fa-syringe",
                label: "Immunizations",
            },
            {
                route: "assistant.allergies.index",
                icon: "fa-solid fa-allergies",
                label: "Allergies",
            },
        ],
    },
    {
        label: "Medical Records",
        icon: "fa-solid fa-folder-open",
        isCollapsible: true,
        items: [{
                route: "assistant.documents.index",
                icon: "fa-solid fa-file-alt",
                label: "Documents",
            },
            {
                route: "assistant.results.index",
                icon: "fa-solid fa-flask",
                label: "Results",
            },
            {
                route: "assistant.forms.index",
                icon: "fa-solid fa-file-signature",
                label: "Forms",
            },
        ],
    },
    {
        label: "Finance",
        icon: "fa-solid fa-dollar-sign",
        isCollapsible: true,
        items: [{
                route: "assistant.orders.index",
                icon: "fa-solid fa-receipt",
                label: "Orders",
            },
            {
                route: "assistant.billing.index",
                icon: "bi bi-bank",
                label: "Billing",
            },
            {
                route: "assistant.insurance.index",
                icon: "bi bi-file-earmark-text",
                label: "Insurance",
            },
        ],
    },
    {
        route: "assistant.coordination.index",
        icon: "fa-solid fa-handshake-o",
        label: "Coordination of Care",
    },
];

export const getNavItemsByRole = (role) => {
    switch (role) {
        case 'SuperAdmin':
            return SadminNavItems;
        case 'Admin':
            return adminNavItems;
        case 'Doctor':
            return doctorNavItems;
        case 'Patient':
            return patientNavItems;
        case 'Pharmacy':
            return pharmacyNavItems;
        case 'Lab':
            return labNavItems;
        case 'Biller':
            return billerNavItems;
        case 'Virtual Assistant':
            return assistantNavItems;
        default:
            return [];
    }
};
