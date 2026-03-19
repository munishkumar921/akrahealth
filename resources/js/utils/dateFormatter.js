/**
 * Centralized Date Formatting Utility
 * Provides consistent date formatting across the application
 * 
 * Default format: "Feb 7, 2026" (F j, Y)
 */

// Common date format patterns
export const DATE_FORMATS = {
    DEFAULT: 'F j, Y',           // "Feb 7, 2026"
    SHORT: 'M j, Y',            // "Feb 7, 2026" (short month)
    LONG: 'F j, Y',             // "Feb 7, 2026"
    NUMERIC: 'MM/DD/YYYY',     // "02/07/2026"
    NUMERIC_DASH: 'YYYY-MM-DD', // "2026-02-07"
    WITH_TIME: 'F j, Y g:i A', // "Feb 7, 2026 3:30 PM"
    TIME_ONLY: 'hh:mm A',        // "3:30 PM"
    RELATIVE: 'relative',       // "2 hours ago"
};

const parseDate = (date) => {
    if (!date) return null;

    if (date instanceof Date) {
        return isNaN(date.getTime()) ? null : date;
    }

    if (typeof date === "string") {

        // Clean up formatting issues
        date = date.trim();
        date = date.replace(/\s+,/g, ","); // remove space before comma
        date = date.replace(/^([a-z]+)/i, (match) => 
            match.charAt(0).toUpperCase() + match.slice(1).toLowerCase()
        );

        // YYYY-MM-DD
        if (/^\d{4}-\d{2}-\d{2}$/.test(date)) {
            const [year, month, day] = date.split("-");
            return new Date(year, month - 1, day);
        }

        // DD-MM-YYYY
        if (/^\d{2}-\d{2}-\d{4}$/.test(date)) {
            const [day, month, year] = date.split("-");
            return new Date(year, month - 1, day);
        }
    }

    const parsed = new Date(date);
    return isNaN(parsed.getTime()) ? null : parsed;
};

export const formatDate = (dateString, formatKey) => {
    const date = dateString ? parseDate(dateString) : new Date();
    if (!date) return "N/A";

    // Handle TIME_ONLY format
    if (formatKey === DATE_FORMATS.TIME_ONLY) {
        return new Intl.DateTimeFormat("en-US", {
            hour: 'numeric',
            minute: '2-digit',
            hour12: true
        }).format(date);
    }

    // Default format (M d, Y)
    return new Intl.DateTimeFormat("en-US", {
        month: "short",
        day: "2-digit",
        year: "numeric"
    }).format(date);
};



/**
 * Format date to "Feb 7, 2026" format (default)
 * @param {string|Date|null} date 
 * @returns {string}
 */
export const formatDateLong = (date) => formatDate(date, DATE_FORMATS.LONG);

/**
 * Format date to short format (Feb 7, 2026)
 * @param {string|Date|null} date 
 * @returns {string}
 */
export const formatDateShort = (date) => formatDate(date, DATE_FORMATS.SHORT);

/**
 * Format date to numeric format (MM/DD/YYYY)
 * @param {string|Date|null} date 
 * @returns {string}
 */
export const formatDateNumeric = (date) => formatDate(date, DATE_FORMATS.NUMERIC);

/**
 * Format date to ISO format (YYYY-MM-DD)
 * @param {string|Date|null} date 
 * @returns {string}
 */
export const formatDateISO = (date) => formatDate(date, DATE_FORMATS.NUMERIC_DASH);

/**
 * Format date with time (Feb 7, 2026 3:30 PM)
 * @param {string|Date|null} date 
 * @returns {string}
 */
export const formatDateTime = (date) => formatDate(date, DATE_FORMATS.WITH_TIME);

/**
 * Format time only (3:30 PM)
 * @param {string|Date|null} date 
 * @returns {string}
 */
export const formatTimeOnly = (date) => formatDate(date, 
    DATE_FORMATS.TIME_ONLY
);

/**
 * Format date to relative time (e.g., "2 hours ago", "Yesterday", "3 days ago")
 * @param {string|Date|null} date 
 * @returns {string}
 */
export const formatRelative = (date) => {
    const parsed = parseDate(date);
    if (!parsed) return '-';
    
    const now = new Date();
    const diffMs = now - parsed;
    const diffSec = Math.floor(diffMs / 1000);
    const diffMin = Math.floor(diffSec / 60);
    const diffHour = Math.floor(diffMin / 60);
    const diffDay = Math.floor(diffHour / 24);
    const diffWeek = Math.floor(diffDay / 7);
    const diffMonth = Math.floor(diffDay / 30);
    const diffYear = Math.floor(diffMonth / 12);
    
    // Future dates
    if (diffMs < 0) {
        const absSec = Math.abs(diffSec);
        const absMin = Math.abs(diffMin);
        const absHour = Math.abs(diffHour);
        const absDay = Math.abs(diffDay);
        
        if (absSec < 60) return 'in a few seconds';
        if (absMin < 60) return `in ${absMin} minute${absMin > 1 ? 's' : ''}`;
        if (absHour < 24) return `in ${absHour} hour${absHour > 1 ? 's' : ''}`;
        if (absDay < 7) return `in ${absDay} day${absDay > 1 ? 's' : ''}`;
        if (absWeek < 4) return `in ${absWeek} week${absWeek > 1 ? 's' : ''}`;
        if (absMonth < 12) return `in ${absMonth} month${absMonth > 1 ? 's' : ''}`;
        return `in ${absYear} year${absYear > 1 ? 's' : ''}`;
    }
    
    // Past dates
    if (diffSec < 60) return 'just now';
    if (diffMin < 60) return `${diffMin} minute${diffMin > 1 ? 's' : ''} ago`;
    if (diffHour < 24) return `${diffHour} hour${diffHour > 1 ? 's' : ''} ago`;
    if (diffDay === 1) return 'yesterday';
    if (diffDay < 7) return `${diffDay} days ago`;
    if (diffWeek === 1) return 'last week';
    if (diffWeek < 4) return `${diffWeek} weeks ago`;
    if (diffMonth === 1) return 'last month';
    if (diffMonth < 12) return `${diffMonth} months ago`;
    if (diffYear === 1) return 'last year';
    return `${diffYear} years ago`;
};

/**
 * Get the start of day for a date
 * @param {string|Date|null} date 
 * @returns {Date}
 */
export const getStartOfDay = (date) => {
    const parsed = parseDate(date);
    if (!parsed) return new Date();
    return new Date(parsed.getFullYear(), parsed.getMonth(), parsed.getDate());
};

/**
 * Get the end of day for a date
 * @param {string|Date|null} date 
 * @returns {Date}
 */
export const getEndOfDay = (date) => {
    const parsed = parseDate(date);
    if (!parsed) return new Date();
    return new Date(parsed.getFullYear(), parsed.getMonth(), parsed.getDate(), 23, 59, 59, 999);
};

/**
 * Check if two dates are on the same day
 * @param {string|Date} date1 
 * @param {string|Date} date2 
 * @returns {boolean}
 */
export const isSameDay = (date1, date2) => {
    const d1 = parseDate(date1);
    const d2 = parseDate(date2);
    if (!d1 || !d2) return false;
    return d1.getFullYear() === d2.getFullYear() &&
           d1.getMonth() === d2.getMonth() &&
           d1.getDate() === d2.getDate();
};

/**
 * Calculate age from date of birth
 * @param {string|Date|null} birthDate 
 * @returns {string|null}
 */
export const calculateAge = (birthDate) => {
    const parsed = parseDate(birthDate);
    if (!parsed) return null;
    
    const today = new Date();
    let age = today.getFullYear() - parsed.getFullYear();
    const monthDiff = today.getMonth() - parsed.getMonth();
    
    if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < parsed.getDate())) {
        age--;
    }
    
    return age > 0 ? `${age} years` : null;
};

/**
 * Format date for input[type="date"]
 * @param {string|Date|null} date 
 * @returns {string} YYYY-MM-DD format
 */
export const formatForInput = (date) => formatDate(date, 'YYYY-MM-DD');

/**
 * Format a date using locale-aware formatting
 * @param {string|Date|null} date 
 * @param {object} options - Intl.DateTimeFormat options
 * @param {string} locale - Locale string (default: 'en-US')
 * @returns {string}
 */
export const formatLocaleDate = (date, options = {}, locale = 'en-US') => {
    const parsed = parseDate(date);
    if (!parsed) return '-';
    
    return parsed.toLocaleDateString(locale, {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        ...options
    });
};

/**
 * Format a date with locale-aware datetime
 * @param {string|Date|null} date 
 * @param {string} locale - Locale string (default: 'en-US')
 * @returns {string}
 */
export const formatLocaleDateTime = (date, locale = 'en-US') => {
    const parsed = parseDate(date);
    if (!parsed) return '-';
    
    return parsed.toLocaleString(locale, {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};

export default {
    formatDate,
    formatDateLong,
    formatDateShort,
    formatDateNumeric,
    formatDateISO,
    formatDateTime,
    formatTimeOnly,
    formatRelative,
    formatLocaleDate,
    formatLocaleDateTime,
    formatForInput,
    calculateAge,
    isSameDay,
    getStartOfDay,
    getEndOfDay,
    DATE_FORMATS
};

