export const billsToSubmitData = {
    headings: [
        "date of service",
        "first name",
        "last name",
        "chief complaint",
        "charges",
        "total balance",
    ],
    data: {
        bills_to_submit: [
            {
                "date of service": "2024-10-01",
                "first name": "John",
                "last name": "Doe",
                "chief complaint": "Headache",
                charges: "$150",
                "total balance": "$50",
            },
            {
                "date of service": "2024-09-25",
                "first name": "Jane",
                "last name": "Smith",
                "chief complaint": "Back pain",
                charges: "$200",
                "total balance": "$100",
            },
            {
                "date of service": "2024-10-10",
                "first name": "Michael",
                "last name": "Johnson",
                "chief complaint": "Fever",
                charges: "$100",
                "total balance": "$0",
            },
            {
                "date of service": "2024-09-30",
                "first name": "Emily",
                "last name": "Davis",
                "chief complaint": "Sore throat",
                charges: "$120",
                "total balance": "$40",
            },
            {
                "date of service": "2024-09-28",
                "first name": "David",
                "last name": "Brown",
                "chief complaint": "Knee pain",
                charges: "$180",
                "total balance": "$80",
            },
        ],
    },
};

export const processedBillsData = {
    headings: [
        "date of service",
        "first name",
        "last name",
        "chief complaint",
        "charges",
        "total balance",
    ],
    data: {
        processed_bills: [
            {
                "date of service": "2024-10-01",
                "first name": "John",
                "last name": "Doe",
                "chief complaint": "Headache",
                charges: "$150",
                "total balance": "$50",
            },
            {
                "date of service": "2024-09-25",
                "first name": "Jane",
                "last name": "Smith",
                "chief complaint": "Back pain",
                charges: "$200",
                "total balance": "$100",
            },
            {
                "date of service": "2024-10-10",
                "first name": "Michael",
                "last name": "Johnson",
                "chief complaint": "Fever",
                charges: "$100",
                "total balance": "$0",
            },
            {
                "date of service": "2024-09-30",
                "first name": "Emily",
                "last name": "Davis",
                "chief complaint": "Sore throat",
                charges: "$120",
                "total balance": "$40",
            },
            {
                "date of service": "2024-09-28",
                "first name": "David",
                "last name": "Brown",
                "chief complaint": "Knee pain",
                charges: "$180",
                "total balance": "$80",
            },
        ],
    },
};

export const outstandingBalancesData = {
    headings: ["id", "first name", "last name", "balance", "notes"],
    data: {
        outstanding_balances: [
            {
                id: 1,
                "first name": "Alice",
                "last name": "Walker",
                balance: "$200",
                notes: "Pending payment",
            },
            {
                id: 2,
                "first name": "Bob",
                "last name": "Anderson",
                balance: "$0",
                notes: "Paid in full",
            },
            {
                id: 3,
                "first name": "Carlos",
                "last name": "Diaz",
                balance: "$350",
                notes: "Installment plan",
            },
            {
                id: 4,
                "first name": "Dana",
                "last name": "Chen",
                balance: "$120",
                notes: "Due next month",
            },
            {
                id: 5,
                "first name": "Eva",
                "last name": "Smith",
                balance: "$75",
                notes: "Partial payment made",
            },
        ],
    },
};

export const monthlyFinancialReportData = {
    headings: [
        "month",
        "patients seen",
        "total billed",
        "total payments",
        "DNKA",
        "LMC",
    ],
    data: {
        monthly_financial_report: [
            {
                month: "2024-01",
                "patients seen": 120,
                "total billed": "$15,000",
                "total payments": "$12,000",
                DNKA: 5,
                LMC: 0,
            },
            {
                month: "2024-02",
                "patients seen": 110,
                "total billed": "$14,000",
                "total payments": "$11,500",
                DNKA: 3,
                LMC: 1,
            },
            {
                month: "2024-03",
                "patients seen": 130,
                "total billed": "$16,500",
                "total payments": "$13,000",
                DNKA: 0,
                LMC: 0,
            },
            {
                month: "2024-04",
                "patients seen": 100,
                "total billed": "$13,000",
                "total payments": "$10,000",
                DNKA: 2,
                LMC: 0,
            },
            {
                month: "2024-05",
                "patients seen": 115,
                "total billed": "$14,500",
                "total payments": "$12,200",
                DNKA: 4,
                LMC: 0,
            },
        ],
    },
};

export const yearlyFinancialReportData = {
    headings: [
        "month",
        "patients seen",
        "total billed",
        "total payments",
        "DNKA",
        "LMC",
    ],
    data: {
        yearly_financial_report: [
            {
                month: "2024",
                "patients seen": 120,
                "total billed": "$15,000",
                "total payments": "$12,000",
                DNKA: 5,
                LMC: 0,
            },
            {
                month: "2023",
                "patients seen": 110,
                "total billed": "$14,000",
                "total payments": "$11,500",
                DNKA: 3,
                LMC: 1,
            },
            {
                month: "2022",
                "patients seen": 130,
                "total billed": "$16,500",
                "total payments": "$13,000",
                DNKA: 0,
                LMC: 0,
            },
        ],
    },
};
