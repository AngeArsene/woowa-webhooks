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
    ```

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
  - `Services/WhatsAppMessenger.php`: Handles sending messages via WhatsApp.
  - `Services/MessageHandler.php`: Interface for handling messages.
  - `Services/Exceptions/MessagingException.php`: Custom exception for handling messaging errors.
  - `Request.php`: Handles sending HTTP requests using GuzzleHttp.
- `utils/`: Contains utility functions.
  - `functions.php`: Utility functions used in the application.
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

### WhatsAppMessenger Class

- **Description**: Handles sending messages via WhatsApp.
- **Methods**:
  - `__construct()`: Initializes the HTTP client.
  - `send_message()`: Sends a message via WhatsApp.
  - `send_request()`: Sends a request to the given URL with the provided message and recipient.
  - `base_params()`: Generates the base parameters for the request.

### MessageHandler Interface

- **Description**: Interface for handling messages.
- **Methods**:
  - `send_message()`: Sends a message.

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
- **get_phone_number()**: Retrieves the phone number from the provided cart URL.

## License

This project is licensed under the MIT License. See the [LICENSE](LICENSE) file for details.

## Authors

- **Ange Arsene** - [nkenmandenga@gmail.com](mailto:nkenmandenga@gmail.com)
