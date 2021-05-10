# LYDIC group / Rapid API CRUD Bundle
This software enables rapid and flexible Symfony API CRUD development.

## What it does
- Creates an endpoint for CRUD actions on a given entity (configurable)
- Validates the entity by using the annotations in the class
- Ex-/Includes specific properties from output by using the @Groups annotation (e.g. exclude $id from 'find', but not from 'list')
- Association fields (relations to other entities) will be normalized to the ID of the entity and vice versa.

Created endpoints by extending RapidApiCrudController:

| Action                    | HTTP Method   | Example URL                                   |
| ------------------------- | ------------- | --------------------------------------------- |
| Create                    | POST          | /users                                        |
| List all (by criteria)    | GET           | /users or /users?name=Steve                   |
| Find one                  | GET           | /users/c17b8101-758c-41fa-895b-f6184555eee0   |
| Update                    | PUT           | /users/c17b8101-758c-41fa-895b-f6184555eee0   |
| Delete                    | DELETE        | /users/c17b8101-758c-41fa-895b-f6184555eee0   |


## How to use

### The fast/flexible way
1. Create an entity and implement the RapidApiCrudEntity
2. Create a controller that extends the RapidApiCrudController
3. Implement the required methods and use the config DTO to enable/disable certain routes

### The fully customizable way
1. Create or edit an existing model and implement the RapidApiCrudEntity interface
2. Create or edit an existing controller and **don't** extend the RapidApiCrudController
3. Inject the CrudService
4. Create the desired methods/routes and use the logic from CrudService to be up and running super fast

Feel free to use the CrudControllerService for route functionality.
If you need even more specific logic, use the CrudService.

## Roadmap
- Extend this documentation with examples
- Create more specific exceptions
- Add the possibility for adding/removing _one_ or several (not all) entities from an association collection
- Make it possible to use annotation groups for creating/updating entities (not just GET methods)
- TODO's in code 

## Support

Hey 👋 If you like our libraries. Support us by  [buying](https://www.buymeacoffee.com/LYDICGROUP) us a coffee!