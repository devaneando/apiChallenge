# API Challenge

## Endpoints

### /user/register

This endpoint creates a new user.
It's essential to have access to other endpoints.
It's **public**.

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

### /user/{id}/delete

This endpoint deletes an existing user.
It's **private**.

Example response:

~~~json
{
    "message": "User deleted!"
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
