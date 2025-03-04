# API Challenge

## Installation

### Fill the settings

create a `.env.dev.local` file:

~~~bash
touch .env.dev.local
~~~

The most important values are:

~~~bash
APP_SECRET=xxx
DATABASE_URL="sqlite:///%kernel.project_dir%/var/data.db"
JWT_PASSPHRASE=xxx
API_KEY_ALPHAVANTAGE=xxx
API_KEY_STOOQ=xxx
JWT_PASSPHRASE=xxx
SENDER_EMAIL=xxx
SENDER_NAME=xxx
~~~

Replace xxx as you need.

If you going to use sqlite, execute:

~~~bash
touch var/data.db
~~~

### Create JWT Keys

~~~bash
openssl genpkey -algorithm RSA -out config/jwt/private.pem -pkeyopt rsa_keygen_bits:4096
openssl rsa -pubout -in config/jwt/private.pem -out config/jwt/public.pem
~~~

You problably will need to fix the permissions:

~~~bash
chmod 600 config/jwt/private.pem
chmod 644 config/jwt/public.pem
~~~

## Usage

Use the `/user/register` endpoint to create a new user.

Use the `/login` endpoint to get a new token.

In your curl request (or client) add a header parameter named `Authorization`, and the value should be something like `Bearer {{token}}`.

It should look somewhat like this:

~~~bash
curl --location 'http://127.0.0.1:8000/user/2' \
--header 'Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJpYXQiOjE3NDEwODcxOTEsImV4cCI6MTc0MTA5MDc5MSwicm9sZXMiOlsiUk9MRV9VU0VSIl0sInVzZXJuYW1lIjoia2lsbDAwMUBnbWFpbC5jb20ifQ.FhcMurhtJVnv4bLeXrBFrnexE-no9pg6kLvJWicd2ypadWIul3Pxf9qGtpDaXuQFqGiarVUK7QunMUxqQn7gZVtIJ4iBLUFwxrj0gV65As1dMsgx0fqa-7Hn5iqXUjNdCsVd9WBujOlnMMVCdmUS8wF79CcYIe6VjipHSpxmYPLEXtl_S5e1jbeMF7CttAvtynaJy-VCzS4hAcCoyO8VbImmcqZeMLYtLEipL0T-5NZVEx09aFKoh7e95RrosYEzsdHtQ84BqwsPtvmvgan0vWGv-73turS1IDSIhslnXSFDLPO5PO-TIBp8f9w18Fb2fghn77KWdrshdmzZNYY66mgEfdNyHugmok7MNFpmqtRXY2jsoADuD6UtjWTPfGnwLpAi9FqTqmgKY8nHew-ha_O5WIJH_7ld1szyC8eXeiuyoGud3Ji3Wc8buxRKT_anBhRkeay7c5urXv0L2an08t11zpjBw58CKFmsRoPg4urc2UpP51yXuZdgTRCRltvgVow83mm0HeRM8hrQwPPnQRlZjBrdADnY7MohQSLNLf5xQvkeboftm73oay5uPQ6UoVAFNINeT9HQBAcu4rcbKQZqSqF-7v-y23byrnNJH8EXzx_7wMUMHjpoe8Ymao9Zb6g_av2jaiDS0R5l1rvjtEfWfqK0U4jlqr2fE6hof4g'
~~~

Use any endpoint you want.

**Important!**

After each call of the `/stock` endpoint, a email message will be added to the queue to be delivered to the customer.

To ensure the email is delivered, execute the `bin/console app:send-emails` command or configure it as a cronjob.

## Commands

### app:send-emails

This command sends the emails to the customers.

Example usage:

~~~bash
php bin/console app:send-emails
~~~

## Endpoints

### /login

This endpoint allows you to login with an existing user to have access to the private endpoints.
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
    "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJpYXQiOjE3NDEwODcxOTEsImV4cCI6MTc0MTA5MDc5MSwicm9sZXMiOlsiUk9MRV9VU0VSIl0sInVzZXJuYW1lIjoia2lsbDAwMUBnbWFpbC5jb20ifQ.FhcMurhtJVnv4bLeXrBFrnexE-no9pg6kLvJWicd2ypadWIul3Pxf9qGtpDaXuQFqGiarVUK7QunMUxqQn7gZVtIJ4iBLUFwxrj0gV65As1dMsgx0fqa-7Hn5iqXUjNdCsVd9WBujOlnMMVCdmUS8wF79CcYIe6VjipHSpxmYPLEXtl_S5e1jbeMF7CttAvtynaJy-VCzS4hAcCoyO8VbImmcqZeMLYtLEipL0T-5NZVEx09aFKoh7e95RrosYEzsdHtQ84BqwsPtvmvgan0vWGv-73turS1IDSIhslnXSFDLPO5PO-TIBp8f9w18Fb2fghn77KWdrshdmzZNYY66mgEfdNyHugmok7MNFpmqtRXY2jsoADuD6UtjWTPfGnwLpAi9FqTqmgKY8nHew-ha_O5WIJH_7ld1szyC8eXeiuyoGud3Ji3Wc8buxRKT_anBhRkeay7c5urXv0L2an08t11zpjBw58CKFmsRoPg4urc2UpP51yXuZdgTRCRltvgVow83mm0HeRM8hrQwPPnQRlZjBrdADnY7MohQSLNLf5xQvkeboftm73oay5uPQ6UoVAFNINeT9HQBAcu4rcbKQZqSqF-7v-y23byrnNJH8EXzx_7wMUMHjpoe8Ymao9Zb6g_av2jaiDS0R5l1rvjtEfWfqK0U4jlqr2fE6hof4g"
}
~~~

### /user/register

This endpoint creates a new user.
It's essential to have access to other endpoints.
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
    + Ex.: IBM (May vary depending on the provider).
    + <http://127.0.0.1:8000/stock?q=IBM>
+ **provider**: the provider to be user (**optional**)
    + Ex.: alpha (Defaults to alpha, but the possible options are "alpha" or "stooq").
    + <http://127.0.0.1:8000/stock?q=IBM&provider=alpha>

### /history

This endpoint shows the request history of the the user.
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
~~~
