# Daily Data Archiving System

## Overview
The daily data archiving system automatically cleans up yesterday's customer data to keep the database focused on current day operations and prevent data flooding.

## Features
- **Automatic Daily Archiving**: Runs every day at 2:00 AM
- **Complete Data Preservation**: Data is moved to archive tables, not deleted
- **Multiple Table Support**: Archives customers, queue events, priority verifications, and more
- **Manual Control**: Can be run manually with custom parameters

## Archive Tables Created
- `customers_archive` - Archived customer data
- `queue_events_archive` - Archived queue events
- `priority_verifications_archive` - Archived priority verifications
- `activity_logs_archive` - Archived activity logs
- `analytics_data_archive` - Archived analytics data
- `table_assignments_archive` - Archived table assignments
- `staff_sessions_archive` - Archived staff sessions

## Usage

### Automatic Archiving
The system runs automatically every day at 2:00 AM via Laravel's task scheduler.

### Manual Archiving
```bash
# Archive yesterday's data (default)
php artisan archive:daily-data

# Archive data from 2 days ago
php artisan archive:daily-data --days=2

# Archive data from 7 days ago
php artisan archive:daily-data --days=7
```

### Check Archive Status
```bash
# View help
php artisan archive:daily-data --help
```

## What Gets Archived
- **Customers**: All customer registrations from the specified business operation date (`registered_at`)
- **Queue Events**: All queue events related to archived customers and events from the specified date (`event_time`)
- **Priority Verifications**: All priority verification records from the specified date (`created_at`)
- **Activity Logs**: All system activity logs from the specified date (`created_at`)
- **Analytics Data**: All analytics and reporting data from the specified date (`created_at`)
- **Table Assignments**: All table assignment records from the specified date (`created_at`)
- **Staff Sessions**: All staff login/logout sessions from the specified date (`created_at`)

**Note**: The archiving system focuses on **business operation dates** rather than database creation dates to ensure only actual business operations from previous days are archived.

## Benefits
1. **Database Performance**: Keeps main tables focused on current day operations
2. **Data Preservation**: All data is preserved in archive tables
3. **Storage Management**: Prevents database from growing indefinitely
4. **Query Performance**: Faster queries on current day data
5. **Compliance**: Maintains historical data for reporting and auditing

## Archive Table Structure
Each archive table includes:
- All original columns from the main table
- `archived_at` timestamp showing when the data was archived
- Proper indexing for efficient queries

## Monitoring
- All archiving operations are logged
- Success/failure status is tracked
- Archive counts are reported after each run

## Safety Features
- **No Data Loss**: Data is moved, not deleted
- **Transaction Safety**: Uses database transactions
- **Error Handling**: Comprehensive error handling and logging
- **Overlap Prevention**: Prevents multiple archiving processes from running simultaneously

## Configuration
The archiving schedule is configured in `routes/console.php`:
```php
Schedule::command('archive:daily-data --days=1')
    ->dailyAt('02:00')
    ->name('archive-daily-data')
    ->withoutOverlapping()
    ->runInBackground();
```

## Troubleshooting
- Check Laravel logs for archiving errors
- Verify database permissions for archive table creation
- Ensure Laravel scheduler is running (`php artisan schedule:work`)
- Check archive table structures match main tables

## Future Enhancements
- Archive data compression
- Automated archive cleanup (e.g., delete archives older than 1 year)
- Archive data export functionality
- Archive restoration capabilities
