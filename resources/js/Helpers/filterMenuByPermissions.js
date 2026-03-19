import { useSubscriptionPermissions } from '../Composables/useSubscriptionPermissions';

/**
 * Filter menu items based on subscription permissions
 * @param {Array} menuItems - Array of menu items to filter
 * @returns {Array} Filtered menu items
 */
export function filterMenuByPermissions(menuItems) {
    const { hasPermission } = useSubscriptionPermissions();
    
    if (!menuItems || !Array.isArray(menuItems)) {
        return [];
    }
    
    return menuItems
        .map(item => {
            // If item has sub-items, filter them recursively first
            if (item.items && Array.isArray(item.items)) {
                const filteredItems = filterMenuByPermissions(item.items);
                
                // If all sub-items are filtered out, hide parent item
                if (filteredItems.length === 0 && item.isCollapsible) {
                    return null;
                }
                
                // Return item with filtered sub-items
                return {
                    ...item,
                    items: filteredItems
                };
            }
            
            // If item has a route, check permission
            if (item.route) {
                // Use explicit permission if set, otherwise derive from route
                const permissionToCheck = item.permission || item.route;
                
                // Check if user has permission
                if (!hasPermission(permissionToCheck)) {
                    return null;
                }
            }
            
            // If item has no route or has permission, show it
            return item;
        })
        .filter(item => item !== null);
}

