# Woowa Webhooks

## Description

Integrate WhatsApp order notifications into your WooCommerce store with this package. It enables real-time updates for customers via WhatsApp whenever an order is placed, updated, or completed, enhancing customer engagement and improving order tracking.

## Installation

1. Clone the repository:
    ```sh
    git clone https://github.com/yourusername/woowa-webhooks.git
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

## Project Structure

- `index.php`: The entry point of the application.
- `src/`: Contains the main application classes.
  - `Application.php`: The main application class for handling webhooks.
  - `services/WhatsAppMessageHandler.php`: Handles sending messages via WhatsApp.
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

### WhatsAppMessageHandler Class

- **Description**: Handles sending messages via WhatsApp.
- **Methods**:
  - `__construct()`: Initializes the HTTP client.
  - `send_message()`: Sends a message via WhatsApp.
  - `base_params()`: Generates the base parameters for the request.

### Utility Functions

- **env()**: Retrieves the environment variables as an object.
- **json_format()**: Formats an associative array into a JSON string.

## License

This project is licensed under the MIT License. See the [LICENSE](LICENSE) file for details.

## Authors

- **Ange Arsene** - [nkenmandenga@gmail.com](mailto:nkenmandenga@gmail.com)
