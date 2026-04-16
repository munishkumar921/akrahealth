const TIMEZONE_VALUES = [
    "Asia/Kolkata",
    "America/New_York",
    "America/Detroit",
    "America/Kentucky/Louisville",
    "America/Indiana/Indianapolis",
    "America/Chicago",
    "America/Indiana/Knox",
    "America/Menominee",
    "America/North_Dakota/Center",
    "America/North_Dakota/New_Salem",
    "America/North_Dakota/Beulah",
    "America/Denver",
    "America/Boise",
    "America/Phoenix",
    "America/Los_Angeles",
    "America/Anchorage",
    "America/Juneau",
    "America/Sitka",
    "America/Metlakatla",
    "America/Yakutat",
    "America/Nome",
    "Pacific/Honolulu",
];

const getTimezoneOffsetLabel = (timezone) => {
    try {
        const formatter = new Intl.DateTimeFormat("en-US", {
            timeZone: timezone,
            timeZoneName: "shortOffset",
        });

        const parts = formatter.formatToParts(new Date());
        const timezonePart = parts.find((part) => part.type === "timeZoneName");

        return timezonePart ? timezonePart.value.replace("GMT", "UTC") : "";
    } catch {
        return "";
    }
};

export const timezones = TIMEZONE_VALUES.map((timezone) => {
    const offset = getTimezoneOffsetLabel(timezone);

    return {
        value: timezone,
        label: `${timezone.replace(/_/g, " ")}${offset ? ` ${offset}` : ""}`,
    };
}).sort((a, b) => a.label.localeCompare(b.label));

export default timezones;
