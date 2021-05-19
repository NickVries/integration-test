# MyParcel.com - e-Commerce Integration Skeleton
This is a skeleton application for a microservice that converts e-Commerce orders coming from a remote API into MyParcel.com Shipment resources.

This skeleton already handles parts of the logic related to transforming order data into Shipment resources in accordance to the [MyParcel.com API Specification](https://api-specification.myparcel.com).

A complete integration would:
- Handle authentication with the remote API
    - For OAuth 2.0 Authorization Code flow a boilerplate is already implemented within the skeleton
- Fetch orders from the remote API, filtered by date range and transform them into MyParcel.com Shipment resources

The skeleton is a standard Laravel application with configured Laravel Sail. Developers creating integrations using this skeleton should be familiar with the Laravel Framework. 

## Content
- [Installation](#installation)
- [Setup](#setup)
- [TODOs](#todos)
- [Error handling](#error-handling)
- [Authentication](#authentication)
- [Shipments](#shipments)
- [Things to keep in mind](#things-to-keep-in-mind)
- [Testing](#testing)

### Installation
The project uses Docker to run a local development environment. To install Docker, follow the steps in the [documentation](https://docs.myparcel.com/development/docker/).

### Setup
First, install the composer dependencies:
```shell
docker run --rm \
    -u "$(id -u):$(id -g)" \
    -v $(pwd):/opt \
    -w /opt \
    laravelsail/php80-composer:latest \
    composer install --ignore-platform-reqs
```

Next, bring the sail-backed docker-compose services up:
```shell
vendor/bin/sail up -d
```

Thirdly, run the test suite to verify everything is set up correctly:
```shell
vendor/bin/sail test
```

Tip: for convenience you can assign the following alias to the `sail` command in your .bashrc (or .zshrc):
```shell
alias sail='bash vendor/bin/sail'
```

Finally, replace all occurrences of 'skeleton' with the name of the integration. Places to look for include:
- .env
- .env.example
- docker-compose.yaml
- composer.json

### TODOs
There are several `TODO` comments added to the codebase to help you get started on what to implement. 
There are also several tests to check if everything is working as it is supposed to. 
The Feature tests are the starting point to check if authentication and fetching shipments is working.

### Error handling
Errors from the carrier should be transformed to [JSON API error objects](https://jsonapi.org/format/#error-objects). 
You can implement the `render()` method in your custom exception classes. See [Laravel's docs instructions](https://laravel.com/docs/8.x/errors#renderable-exceptions) and App\Authentication\Domain\Exceptions\AuthRequestException::render() for an example.  

### Authentication
The skeleton ships a OAuth 2.0 Authorization Code flow boiler place which is located in app/Authentication. 
In case the platform you are integrating with also provides OAuth 2.0 (with authorization code flow) you can use the existing code. It consists of the following elements:
- `AuthenticationController` - responsible for generating authorization links and saving access tokens.
- `Token` - a Laravel model responsible for maintaining access tokens in a local database.
- `AuthServer` - a gateway for connecting with the remote OAuth 2.0 server. Execute requests for acquiring new access tokens and refreshing existing ones. **You need to expand this class, so it can communicate with the OAuth 2.0 server.**
- `AuthorizationLink` - provides means of customizing the authorization code grant link. **You need to edit this class to accommodate the platform-specific link.**

Note that every request that will be send to the integration will contain a `shop_id` query parameter. The boilerplate comes with implementation that will save token in a local postgres database using the shop_id as a primary key. 

If you need assistance please [post a new discussion on our GitHub Discussions page](https://github.com/MyParcelCOM/integration-skeleton/discussions).

### Shipments
An example of how to transform shipments is available in the [ShipmentController](app/Shipments/Http/Controllers/ShipmentController.php).
The controller depends on classes coming from the [MyParcelCOM/integration-commons](https://github.com/MyParcelCOM/integration-commons) package which is included by default.
Note the following important aspects:
- `shop_id` will always be provided and within the controller is accessible via `$request->shopId()`
- `start_date` and `end_date` are always provided and are required query parameters when fetching shipments. They can be accessed via `$request->startDate()` and `$request->endDate()` respectively.
- In case you rely on the `app/Authentication` boilerplate you can also easily get the relevant access token for the remote API via `$request->token()`.


### Things to keep in mind
- The `App\Shipments\Http\Controllers\ShipmentController::get()` method is responsible for fetching shipments. Intentionally, the method does not return a standard Laravel response object, but an array of Shipment objects. This is intentional and these objects are later converted to json-api responses [automatically by a middleware](https://github.com/MyParcelCOM/integration-commons/blob/master/src/Http/Middleware/TransformsToJsonApi.php).  

### Testing
We strongly advise you to keep the testing suite up to date. The skeleton ships a set of fundamental feature and unit tests and a number of them are marked as incomplete.
We recommend that you unmark them and write full tests for your integration. 
