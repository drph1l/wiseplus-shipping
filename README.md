# WisePlus Shipping

A flexible WordPress/WooCommerce shipping plugin that calculates shipping rates based on shipping class, city/region, and weight.

## Features

- **Multiple Rate Calculation Methods**
  - Shipping Class + City rates (priority)
  - Weight + City rates (fallback)

- **Flexible Configuration**
  - Manage cities and regions
  - Configure weight-based rates with custom weight ranges
  - Configure class-based rates for different shipping classes

- **Priority System**
  - Class + City rates always take precedence
  - Weight + City rates used when no class rate is found
  - "Shipping not available" shown when no matching rate exists

- **Easy-to-Use Admin Interface**
  - Dedicated admin menu for managing shipping settings
  - Separate pages for cities, weight rates, and class rates
  - Enable/disable individual rates
  - Bulk rate configuration

## Requirements

- WordPress 5.8 or higher
- WooCommerce 5.0 or higher
- PHP 7.4 or higher

## Installation

1. **Upload the Plugin**
   - Upload the `wiseplus-shipping` folder to `/wp-content/plugins/` directory
   - Or install by zipping the folder and uploading via WordPress admin

2. **Activate the Plugin**
   - Navigate to **Plugins** in WordPress admin
   - Find "WisePlus Shipping" and click **Activate**

3. **Database Tables**
   - Database tables are automatically created upon activation
   - Tables created:
     - `wp_wiseplus_cities` - Stores cities/regions
     - `wp_wiseplus_weight_rates` - Stores weight-based rates
     - `wp_wiseplus_class_rates` - Stores class-based rates

## Configuration

### Step 1: Add Cities

1. Go to **WisePlus Shipping → Cities**
2. Add cities where you want to offer shipping
3. Optionally add region/state for organization
4. Enable/disable cities as needed

### Step 2: Configure Shipping Classes (Optional)

If you want to use class-based rates:

1. Go to **WooCommerce → Settings → Shipping → Shipping Classes**
2. Add shipping classes (e.g., "Heavy Items", "Fragile", "Express")
3. Assign shipping classes to your products

### Step 3: Configure Rates

**Option A: Weight-Based Rates**
1. Go to **WisePlus Shipping → Weight Rates**
2. Select a city
3. Define weight range (min/max)
4. Set shipping cost
5. Enable the rate

Example:
- City: New York
- Min Weight: 0 kg
- Max Weight: 5 kg
- Cost: $10

**Option B: Class-Based Rates**
1. Go to **WisePlus Shipping → Class Rates**
2. Select a city
3. Select a shipping class
4. Set shipping cost
5. Enable the rate

Example:
- City: New York
- Shipping Class: Heavy Items
- Cost: $25

### Step 4: Enable the Shipping Method

1. Go to **WooCommerce → Settings → Shipping → Shipping Zones**
2. Select or create a shipping zone
3. Add shipping method **"WisePlus Shipping"**
4. Configure the method title (what customers see at checkout)
5. Enable the method

## How It Works

### Rate Priority Logic

When a customer proceeds to checkout:

1. **Class + City Check (Priority 1)**
   - System checks if cart contains products with shipping classes
   - Looks for matching class + city rates
   - If found, sums up all matching class rates
   - Uses this total as the shipping cost

2. **Weight + City Check (Priority 2)**
   - If no class rates found, calculates total cart weight
   - Looks for matching weight + city rate
   - Uses the matching weight rate as shipping cost

3. **No Match**
   - If neither class nor weight rates match, shipping is not available
   - Customer cannot proceed with checkout to this city

### Example Scenarios

**Scenario 1: Class Rate Takes Priority**
- Cart: 2x "Heavy Item" (Shipping Class: Heavy Items)
- Destination: New York
- Configured Rates:
  - Class Rate: Heavy Items → New York = $25
  - Weight Rate: 0-10kg → New York = $10
- **Result: $25** (class rate used, weight rate ignored)

**Scenario 2: Fallback to Weight Rate**
- Cart: 2x "Regular Item" (No shipping class)
- Total Weight: 3kg
- Destination: Los Angeles
- Configured Rates:
  - Weight Rate: 0-5kg → Los Angeles = $15
- **Result: $15** (weight rate used)

**Scenario 3: No Shipping Available**
- Cart: 1x Item
- Destination: Miami
- Configured Rates: None for Miami
- **Result: Shipping not available**

## Admin Interface

### Cities Management
- Add/Edit/Delete cities
- Enable/disable shipping per city
- Organize cities by region

### Weight Rates Management
- Configure weight ranges
- Set costs per weight tier
- Link rates to specific cities

### Class Rates Management
- Configure rates per shipping class
- Set costs per class
- Link rates to specific cities

## Database Schema

### wp_wiseplus_cities
```sql
id - Primary key
city_name - City name (matches checkout city)
region - Optional region/state
enabled - Enable/disable flag
created_at - Timestamp
```

### wp_wiseplus_weight_rates
```sql
id - Primary key
city_id - Foreign key to cities
min_weight - Minimum weight (inclusive)
max_weight - Maximum weight (inclusive)
shipping_cost - Cost for this weight range
enabled - Enable/disable flag
created_at - Timestamp
```

### wp_wiseplus_class_rates
```sql
id - Primary key
city_id - Foreign key to cities
shipping_class_id - WooCommerce shipping class term ID
shipping_cost - Cost for this class
enabled - Enable/disable flag
created_at - Timestamp
```

## Troubleshooting

### Shipping not showing at checkout
1. Verify the shipping method is enabled in shipping zones
2. Check that destination city exists in the cities list
3. Verify at least one rate is configured for that city
4. Ensure rates are enabled

### Wrong rate calculated
1. Remember: Class rates take priority over weight rates
2. Check if products have shipping classes assigned
3. Verify weight ranges don't overlap
4. Check that product weights are set correctly

### Cities not matching
- City names must match exactly (case-sensitive)
- Ensure city is enabled in the database
- Check for extra spaces or special characters

## Support

For issues or feature requests, please contact your development team.

## Changelog

### Version 1.0.0
- Initial release
- City/region management
- Weight-based rates
- Class-based rates
- Priority rate calculation system
- Admin interface

## License

Proprietary - All rights reserved
