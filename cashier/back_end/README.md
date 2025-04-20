# Cashier Backend

This directory contains the backend processing scripts for the Cashier module of the DTI-PHP application.

## File Structure

- `db_connection.php` - Database connection wrapper that includes the main DBConnection.php and handles session.
- `utils.php` - Utility functions used across multiple backend files.
- `get_pending_vouchers.php` - Retrieves pending vouchers for payment.
- `submit_payment.php` - Processes individual payment submissions.
- `batch_ada_payment.php` - Processes batch ADA payments.
- `return_to_chief.php` - Handles returning DVs to the Chief Accountant.
- `index.php` - Prevents directory listing and direct access.

## How it Works

1. Frontend pages like `pending_payments.php` call these backend scripts for data processing.
2. Each script handles a specific business function and returns appropriate responses/redirects.
3. All scripts share common utilities and database connection.

## Security Features

- Input sanitization through the `utils.php` functions.
- Prevention of direct directory access via `index.php`.
- Transaction management for database integrity.
- Input validation before processing.
- Action logging for audit trails.

## Adding New Backend Functionality

1. Create a new PHP file in this directory.
2. Include `db_connection.php` and `utils.php`.
3. Follow the standard pattern of:
   - Input validation
   - Data processing in a transaction if needed
   - Response generation
   - Appropriate redirect or JSON output

## Usage Example

Frontend form action:

```html
<form method="POST" action="back_end/submit_payment.php">
    <!-- Form fields -->
    <button type="submit" name="submit_payment">Submit</button>
</form>
```

## Maintenance

- Keep backend logic separated from presentation.
- Update utility functions as needed for common operations.
- Maintain consistent error handling and response formats. 