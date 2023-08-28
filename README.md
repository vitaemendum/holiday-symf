# Holiday-finder

This guide will walk you through the steps to set up and run this Symfony application locally.

## Prerequisites

Before you begin, make sure you have the following software installed on your local machine:

1. [PHP](https://www.php.net/downloads.php) (PHP 8.1 or higher recommended)
2. [Composer](https://getcomposer.org/download/) - A PHP dependency manager.
3. [Symfony CLI](https://symfony.com/download) - The Symfony command-line tool.
4. [Redis](https://redis.io/download) - An in-memory data store.


## Application Setup

Follow these steps to set up and run your Symfony application with Redis caching:

1.  **Clone the Repository:**
    
-  `git clone https://github.com/vitaemendum/holiday-symf.git`
-   `cd holiday-symf` 
    
2. **Install Dependencies:**
    
- Use Composer to install the project's PHP dependencies:
    
    `composer install` 
    
3.   **Redis Configuration:**
    
   - By default, Symfony uses Redis for caching. Ensure Redis is running on your machine. You don't need to configure it explicitly for Symfony; Symfony will use the default configuration.
    
4.   **Database Configuration (Optional):**
    
   - This application may have a PostgreSQL database setup, but it might not be actively used. You can ignore this step if you're solely interested in the Redis configuration.
    
5.   **Run the Symfony Application:**
    
   - Start the Symfony development server
    	`symfony server:start` 
    
   - Your Symfony application should now be accessible at `http://localhost:8000` in your web browser.

    
  - Open a web browser and go to `http://localhost:8000` to access your Symfony application.
