# LYDIC group / Rapid API CRUD Bundle
This software enables rapid and flexible Symfony API CRUD development.

## What it does
- Creates an endpoint for CRUD actions on a given entity (configurable)
- Validates the entity by using the annotations in the class
- Ex-/Includes specific properties from output by using the @Groups annotation
- Association fields (relations to other entities) will be normalized to the ID of the entity and vice versa.

Created endpoints:

| Action                    | HTTP Method   | Example URL                                   |
| ------------------------- | ------------- | --------------------------------------------- |
| Create                    | POST          | /users                                        |
| List all (by criteria)    | GET           | /users or /users?name=Steve                   |
| Find one                  | GET           | /users/c17b8101-758c-41fa-895b-f6184555eee0   |
| Update                    | PUT           | /users/c17b8101-758c-41fa-895b-f6184555eee0   |
| Delete                    | DELETE        | /users/c17b8101-758c-41fa-895b-f6184555eee0   |


## How to use
- Create an entity and implement the RapidApiCrudEntity
- Create a controller, extend the RapidApiCrudController, implement the required methods and configure as you like

## Roadmap
- Extend this documentation with examples
- Don't rely on the symfony framework bundling using an abstract controller, but find a different way (interface?)
- Create more specific exceptions
- Split service up a bit
- Make configuration like translations possible
- Add endpoints for adding/removing _one_ entity from an association collection
- TODO's in code 