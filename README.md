# LYDIC group / Rapid API CRUD Bundle
This software enables rapid and flexible Symfony API CRUD development.

## What it does
This package uses several Symfony components to generate CRUD functionality for API's.
It creates endpoints which:

| Action                    | HTTP Method   | URL           |
| ------------------------- | ------------- | ------------- |
| Create                    | POST          | /users        |
| List all (by criteria)    | GET           | /users        |
| Find one                  | POST          | /users/{id}   |
| Update                    | PUT           | /users/{id}   |
| Delete                    | DELETE        | /users/{id}   |

Association field (relations to other entities) will be normalized/denormalized to/from the ID of the entity. 

## How to use
- Create an entity and implement the RapidApiCrudEntity
- Create a controller and extend the RapidApiCrudController in this package
- Implement the required methods and set the correct implementation

## Roadmap
- Extend this documentation with examples
- Don't rely on the symfony framework bundling using an abstract controller, but find a different way (interface?)
- Create more specific exceptions
- Split service up a bit
- Make translations more flexible
- Adding endpoints for adding/removing one entity from an association collection
- TODO's in code 