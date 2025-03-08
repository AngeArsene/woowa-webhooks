# Woowa Webhooks

## Description

Integrate WhatsApp order notifications into your WooCommerce store with this package. It enables real-time updates for customers via WhatsApp whenever an order is placed, updated, or completed, enhancing customer engagement and improving order tracking. Additionally, it sends notifications for abandoned carts to encourage customers to complete their purchases.

## Installation

1. Clone the repository:

   ```sh
   git clone https://github.com/AngeArsene/woowa-webhooks.git
   ```
2. Navigate to the project directory:

   ```sh
   cd woowa-webhooks
   ```
3. Install the dependencies using Composer:

   ```sh
   composer install
   ```
4. Create a `.env` file in the root directory and add your environment variables:

   ```properties
   api_key = "your_api_key"
   base_url = "https://notifapi.com"
   dev_contact = "developer_contact_number"
   admins = "admin1_phone,admin2_phone"
   ca_intervals = "+1 day, +3 days, +7 days"
   ```
5. Add a `credentials.json` file in the `files` folder for Google Sheets API authentication.

## Usage

1. Start the application:

   ```sh
   php -S localhost:8000
   ```
2. Send a POST request to the application with the payload:

   ```json
   {
       "order_id": "12345",
       "status": "completed"
   }
   ```
3. To send an abandoned cart notification, send a POST request with the payload:

   ```json
   {
       "checkout_url": "https://example.com/checkout",
       "phone": "customer_phone_number"
   }
   ```

## Project Structure

- `index.php`: The entry point of the application.
- `src/`: Contains the main application classes.
  - `Application.php`: The main application class for handling webhooks.
  - `Collections/`: Contains collection classes.
    - `Collection.php`: An abstract base class for collections.
    - `NewOrderCollection.php`: A collection class for handling new orders.
  - `Services/WhatsAppMessenger.php`: Handles sending messages via WhatsApp.
  - `Services/GoogleSheets.php`: Handles interactions with Google Sheets.
  - `Services/Spreadsheets.php`: Handles operations related to spreadsheets.
  - `Services/MessageHandler.php`: Interface for handling messages.
  - `Services/Exceptions/MessagingException.php`: Custom exception for handling messaging errors.
  - `Request.php`: Handles sending HTTP requests using GuzzleHttp.
- `utils/`: Contains utility functions.
  - `templates.php`: Functions for rendering templates.
  - `env_debug.php`: Functions for environment variable handling and debugging.
  - `utilities.php`: Additional utility functions.
- `templates/`: Contains message templates.
  - `customer_order_message.txt`: Template for customer order messages.
  - `admin_order_message.txt`: Template for admin order messages.
- `files/`: Contains necessary files for the application.
  - `credentials.json`: Google Sheets API authentication file.
- `.env`: Environment variables file.
- `composer.json`: Composer configuration file.

## Classes and Functions

### Application Class

- **Description**: The main application class for handling webhooks.
- **Methods**:
  - `__construct()`: Initializes the environment, retrieves the payload, handles it, and sends a message.
  - `init_env()`: Initializes environment variables using Dotenv.
  - `get_payload()`: Retrieves the payload from the request.
  - `handle()`: Handles the payload by either aborting or processing it.
  - `abort()`: Aborts the request processing.
  - `process()`: Processes the payload.
  - `process_order()`: Processes an order payload.
  - `process_abandoned_cart()`: Processes an abandoned cart payload.
  - `store()`: Stores the payload in Google Sheets.

### Collection Class

- **Description**: An abstract base class for collections.
- **Methods**:
  - `bootstrap()`: Initializes the collection with the given payload.
  - `filter()`: Abstract method to filter the collection data.

### NewOrderCollection Class

- **Description**: A collection class for handling new orders.
- **Methods**:
  - `bootstrap()`: Initializes the collection with the given payload.
  - `filter()`: Filters the collection data.
  - `product_names()`: Gets the product names from the given products.

### WhatsAppMessenger Class

- **Description**: Handles sending messages via WhatsApp.
- **Methods**:
  - `__construct()`: Initializes the HTTP client.
  - `send_message()`: Sends a message or an image URL via WhatsApp.
  - `send_image_url()`: Sends one or multiple image URLs via WhatsApp.
  - `send_scheduler()`: Sends a scheduled message via WhatsApp.
  - `send_request()`: Sends a request to the given URL with the provided message and recipient.
  - `base_params()`: Generates the base parameters for the request.

### GoogleSheets Class

- **Description**: Handles interactions with Google Sheets.
- **Methods**:
  - `__construct()`: Initializes the Google Sheets service.
  - `bootstrap()`: Initializes the Google client with necessary configurations.
  - `read()`: Reads data from a specified range in the Google Spreadsheet.
  - `__call()`: Handles dynamic method calls for updating or appending values to a Google Sheets spreadsheet.

### Spreadsheets Class

- **Description**: Handles operations related to spreadsheets.
- **Methods**:
  - `__construct()`: Initializes the spreadsheet.
  - `add_row()`: Adds a new row to the spreadsheet.
  - `read_all()`: Reads all data from the spreadsheet.
  - `edit_row()`: Edits a specific row in the spreadsheet with new data.
  - `delete_row()`: Deletes a row from the spreadsheet.
  - `save()`: Saves the current state of the spreadsheet.

### MessageHandler Interface

- **Description**: Interface for handling messages.
- **Methods**:
  - `send_message()`: Sends a message or an image URL.

### MessagingException Class

- **Description**: Custom exception for handling messaging errors.
- **Methods**:
  - `__construct()`: Initializes the exception with a custom error message.

### Request Class

- **Description**: Handles sending HTTP requests using GuzzleHttp.
- **Methods**:
  - `__construct()`: Initializes the HTTP client.
  - `send()`: Sends an HTTP request.

### Utility Functions

- **env()**: Retrieves the environment variables as an object.
- **debug()**: Debugs a payload by dumping its contents.
- **render()**: Renders a template with the given variables.
- **replace_placeholders()**: Replaces placeholders in a template with the given variables.
- **cart_phone_number()**: Retrieves the phone number from the provided cart URL.
- **get_phone_number()**: Retrieves the phone number from the payload or cart URL.
- **product_names()**: Splits a string of product names into an array.
- **format()**: Formats an array of product names into a string with each product name followed by a line of dashes.
- **get_image_links_from()**: Retrieves image links from the provided HTML content.
- **jakarta_date()**: Gets a future date and time in Jakarta timezone.
- **intervals()**: Retrieves the intervals for sending scheduled messages.

## License

This project is licensed under the MIT License. See the [LICENSE](LICENSE) file for details.

## Authors

- **Ange Arsene** - [WhatsApp](https://wa.me/237699512438) - [Email](mailto:angearsene@gmail.com)
