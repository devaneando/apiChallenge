# API Challenge

## Installation

### Fill the settings

Create a `.env.dev.local` file:

~~~bash
touch .env.dev.local
~~~

The most important values are:

~~~bash
APP_SECRET=xxx
DATABASE_URL="mysql://xxx:xxx@api_mysql:3306/xxx?serverVersion=8.0"
JWT_PASSPHRASE=xxx
API_KEY_ALPHAVANTAGE=xxx
API_KEY_STOOQ=xxx
SENDER_EMAIL=xxx
SENDER_NAME=xxx
~~~

Replace `xxx` as needed.

**Important!**

You will need to create a .env.test.local file with the correct connection strings.

### Create JWT Keys

~~~bash
openssl genpkey -algorithm RSA -out config/jwt/private.pem -pkeyopt rsa_keygen_bits:4096
openssl rsa -pubout -in config/jwt/private.pem -out config/jwt/public.pem
~~~

You probably will need to fix the permissions:

~~~bash
chmod 644 config/jwt/private.pem
chmod 644 config/jwt/public.pem
~~~

### Start Docker

~~~bash
cd Docker
docker-compose up -d
~~~

### Initialize the database

Assuming you are using Docker:

~~~bash
docker exec -it api_php php bin/console doctrine:schema:create
docker exec -it api_php php bin/console doctrine:migrations:migrate --all-or-nothing --no-interaction
~~~

## Usage

Use the `/user/register` endpoint to create a new user.

Use the `/login` endpoint to get a new token.

In your curl request (or client), add a header parameter named `Authorization`, and the value should be something like `Bearer {{token}}`.

It should look somewhat like this:

~~~bash
curl --location 'http://127.0.0.1:8000/user/2' \
--header 'Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9...[token]...'
~~~

Use any endpoint you want.

**Important!**

After each call to the `/stock` endpoint, an email message will be added to the queue to be delivered to the customer.

To ensure the email is delivered, execute:

~~~bash
docker exec -it api_php php bin/console app:send-emails
~~~

or configure it as a cron job.

**Note!**

You can check the emails by opening <http://localhost:8025> in your browser.

## Postman collection

You can import the endpoint collection to Postman using the `postman_collection.json` file.

## Commands

### app:send-emails

This command sends emails to customers.

Example usage:

~~~bash
docker exec -it api_php php bin/console app:send-emails
~~~

## Endpoints

### /login

This endpoint allows you to log in with an existing user to access private endpoints.
It's **public**.

<http://127.0.0.1:8000/login>

Example request:

~~~json
{
    "email": "your_unique@email.com",
    "password": "some2$Password"
}
~~~

Example response:

~~~json
{
    "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9...[token]..."
}
~~~

### /user/register

This endpoint creates a new user.
It's **public**.

<http://127.0.0.1:8000/user/register>

Example request:

~~~json
{
    "email": "your_unique@email.com",
    "roles": ["ROLE_USER"],
    "password": "some2$Password"
}
~~~

Example response:

~~~json
{
    "message": "User created successfully",
    "user": {
        "id": 1,
        "email": "your_unique@email.com",
        "roles": [
            "ROLE_USER"
        ]
    }
}
~~~

### /user/{id}

This endpoint shows an existing user.
It's **private**.

<http://127.0.0.1:8000/user/{id}>

Example response:

~~~json
{
    "id": 1,
    "email": "your_unique@email.com",
    "roles": [
        "ROLE_USER"
    ]
}
~~~

### /stock

This endpoint shows the stock details for a given symbol.
It's **private**.

<http://127.0.0.1:8000/stock>

Example response:

~~~json
{
    "name": "IBM",
    "symbol": "IBM",
    "open": 254.735,
    "high": 255.99,
    "low": 248.245,
    "close": 252.44
}
~~~

#### Parameters

+ **q**: the symbol to be queried (**mandatory**)
    + Example: IBM (May vary depending on the provider).
    + <http://127.0.0.1:8000/stock?q=IBM>
+ **provider**: the provider to be used (**optional**)
    + Example: alpha (Defaults to alpha, but possible options are `"alpha"` or `"stooq"`).
    + <http://127.0.0.1:8000/stock?q=IBM&provider=alpha>

### /history

This endpoint shows the request history of the user.
It's **private**.

<http://127.0.0.1:8000/history>

Example response:

~~~json
[
    {
        "symbol": "IBM",
        "name": "IBM",
        "open": 248.75,
        "high": 255.48,
        "low": 248.1,
        "close": 248.1,
        "date": "2025-03-05T08:02:00Z"
    },
    {
        "symbol": "IBM",
        "name": "IBM",
        "open": 248.75,
        "high": 255.48,
        "low": 248.1,
        "close": 248.1,
        "date": "2025-03-05T08:00:46Z"
    }
]
~~~
