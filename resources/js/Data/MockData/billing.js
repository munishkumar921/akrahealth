export const billingTableMockData = {
    encounters: [
        {
            id: 1,
            date: "2024-01-01",
            description: "Encounter 1",
            status: "Pending",
        },
        {
            id: 2,
            date: "2024-01-02",
            description: "Encounter 2",
            status: "Completed",
        },
    ],
    misc_bills: [
        { id: 3, date: "2024-02-01", description: "Bill 1", amount: "$100" },
        { id: 4, date: "2024-02-02", description: "Bill 2", amount: "$200" },
    ],
    bluebutton_data: [
        {
            id: 5,
            date: "2024-03-01",
            description: "Bluebutton Data 1",
            provider: "Provider A",
        },
        {
            id: 6,
            date: "2024-03-02",
            description: "Bluebutton Data 2",
            provider: "Provider B",
        },
    ],
};
