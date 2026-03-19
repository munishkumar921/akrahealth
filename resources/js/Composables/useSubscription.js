/**
 * Composable for subscription day calculations
 * Handles timezone issues and provides accurate day counting
 * Matches PHP Carbon backend calculations
 */

import { computed } from 'vue';

/**
 * Parse date string to JavaScript Date object
 * Handles both "2025-01-20" and "2025-01-20T00:00:00" formats
 * Works with PHP's default date format
 * 
 * @param {string|null} dateString 
 * @returns {Date|null}
 */
const parseDate = (dateString) => {
    if (!dateString) return null;
    
    // Try parsing as ISO string first
    const date = new Date(dateString);
    
    // If valid date, return it
    if (!isNaN(date.getTime())) {
        return date;
    }
    
    // Try parsing as date-only string (PHP format: "2025-01-20")
    const match = dateString.match(/^(\d{4})-(\d{2})-(\d{2})$/);
    if (match) {
        // Create date in local time to match PHP's behavior
        const [, year, month, day] = match;
        return new Date(parseInt(year), parseInt(month) - 1, parseInt(day));
    }
    
    return null;
};

/**
 * Get today's date at local midnight
 * This matches PHP's now() behavior
 * 
 * @returns {Date}
 */
const getToday = () => {
    const now = new Date();
    return new Date(now.getFullYear(), now.getMonth(), now.getDate());
};

/**
 * Calculate exact days left between today and end date
 * Uses floor to get exact days (not ceil)
 * Matches PHP Carbon's diffInDays method
 * 
 * @param {Date} endDate 
 * @param {Date} today 
 * @returns {number}
 */
const calculateDaysLeft = (endDate, today) => {
    if (!endDate) return 0;
    
    // Get end date at local midnight
    const endAtMidnight = new Date(endDate.getFullYear(), endDate.getMonth(), endDate.getDate());
    
    // Get today at midnight
    const todayAtMidnight = new Date(today.getFullYear(), today.getMonth(), today.getDate());
    
    // Calculate difference in milliseconds
    const diffMs = endAtMidnight.getTime() - todayAtMidnight.getTime();
    
    // Convert to days (use floor for exact days, matches PHP)
    const diffDays = Math.floor(diffMs / (1000 * 60 * 60 * 24));
    
    return diffDays;
};

/**
 * Create subscription expiry object from end_date
 * Matches PHP's Carbon date handling
 * 
 * @param {string|null} endDateString 
 * @returns {object|null}
 */
const createExpiryInfo = (endDateString) => {
    if (!endDateString) return null;
    
    const endDate = parseDate(endDateString);
    if (!endDate) return null;
    
    const today = getToday();
    const daysLeft = calculateDaysLeft(endDate, today);
    
    // Determine if expired (PHP returns 0 or negative for expired)
    const isExpired = daysLeft <= 0;
    const isExpiringSoon = daysLeft > 0 && daysLeft <= 14;
    
    // Format date for display (matching PHP's format('M j, Y'))
    const monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
    const formattedDate = `${monthNames[endDate.getMonth()]} ${endDate.getDate()}, ${endDate.getFullYear()}`;
    
    return {
        daysLeft: daysLeft > 0 ? daysLeft : 0,
        isExpired: isExpired,
        isExpiringSoon: isExpiringSoon,
        endDate: formattedDate,
        rawEndDate: endDate
    };
};

/**
 * Format date for display
 * Matches PHP's date('M j, Y') format
 * 
 * @param {string|null} dateString 
 * @returns {string}
 */
const formatDate = (dateString) => {
    if (!dateString) return 'N/A';
    
    const date = parseDate(dateString);
    if (!date) return 'N/A';
    
    const monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
    return `${monthNames[date.getMonth()]} ${date.getDate()}, ${date.getFullYear()}`;
};

/**
 * Check if subscription is expired
 * 
 * @param {string|null} endDateString 
 * @returns {boolean}
 */
const isExpired = (endDateString) => {
    if (!endDateString) return false;
    
    const expiry = createExpiryInfo(endDateString);
    return expiry ? expiry.isExpired : false;
};

/**
 * Check if subscription is expiring soon (within 14 days)
 * 
 * @param {string|null} endDateString 
 * @returns {boolean}
 */
const isExpiringSoon = (endDateString) => {
    if (!endDateString) return false;
    
    const expiry = createExpiryInfo(endDateString);
    return expiry ? expiry.isExpiringSoon : false;
};

/**
 * Get simple days left count
 * 
 * @param {string|null} endDateString 
 * @returns {number|string}
 */
const getDaysLeft = (endDateString) => {
    if (!endDateString) return 0;
    
    const expiry = createExpiryInfo(endDateString);
    if (!expiry) return 0;
    
    if (expiry.isExpired) return 'Expired';
    return expiry.daysLeft;
};

/**
 * Get days left as number (0 if expired)
 * 
 * @param {string|null} endDateString 
 * @returns {number}
 */
const getDaysLeftNumber = (endDateString) => {
    if (!endDateString) return 0;
    
    const expiry = createExpiryInfo(endDateString);
    return expiry ? expiry.daysLeft : 0;
};

export function useSubscriptionDaysLeft() {
    return {
        parseDate,
        getToday,
        calculateDaysLeft,
        createExpiryInfo,
        formatDate,
        isExpired,
        isExpiringSoon,
        getDaysLeft,
        getDaysLeftNumber
    };
}

