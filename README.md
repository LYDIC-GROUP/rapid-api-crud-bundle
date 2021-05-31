# LYDIC group / Rapid API CRUD Bundle
This software enables rapid and flexible Symfony API CRUD development.

## What it does
- Creates an endpoint for CRUD actions on a given entity (configurable)
- Validates the entity by using the annotations in the class
- Ex-/Includes specific properties from output by using the @Groups annotation (e.g. exclude $id from 'detail', but not from 'list')
- Association fields (relations to other entities) will be normalized to the ID of the entity and vice versa.

Created endpoints by extending RapidApiCrudController:

| Action                  | HTTP Method | Example URL               | HTTP Response code   | HTTP Response body                                                                                 |
|-------------------------|-------------|---------------------------|----------------------|----------------------------------------------------------------------------------------------------|
| Create an entity        | POST        | /users                    | 201                  | Created entity (detail)                                                                            |
| List all entities  (*)  | GET         | /users                    | 200                  | Array of entities (detail)                                                                         |
| Find one entity         | GET         | /users/1                  | 200                  | Entity (detail)                                                                                    |
| Update an entity        | PUT         | /users/1                  | 200                  | Updated entity (detail)                                                                            |
| Delete an entity        | DELETE      | /users/1                  | 204                  | -                                                                                                  |
| Find association   (**) | GET         | /users/1/best-friends     | 200                  | ToOne: A single associated entity (detail) <br />ToMany: An array of associated entities (detail)  |
| Create association (**) | POST        | /users/1/best-friends/2   | 201                  | Entity (detail)                                                                                    |
| Delete association (**) | DELETE      | /users/1/best-friends/2   | 204                  | -                                                                                                  |


(*) These endpoints accepts some query parameters.

(\*\*) These endpoints work for ToOne and ToMany associations. `best-friends` is the name of the association on the User model: `$bestFriends`.

Based on the filterMode you can either filter by property: `/users?name=Steve`
Or filter with a more complex query: `/users?filter=name:eq:Steve OR age:gt:21`
You can also add sorting to your result: `/users?sort=age ASC` 
You can also add paging queries: `/users?page=1&limit=10`
Associated entities are normalized to ID's by default for performance reasons. If you want to include the entire entity you can use a comma separated query param like so: `?include=bestFriends`.

## How to use

### The fast/flexible way
1. Create an entity and implement the RapidApiCrudEntity (optionally use Symfony validation annotations)
2. Create a controller that extends the RapidApiCrudController
3. Implement the required method(s) and use the config DTO to enable/disable certain routes

### The fully customizable way
1. Create or edit an existing model and implement the RapidApiCrudEntity interface
2. Create or edit an existing controller and **don't** extend the RapidApiCrudController
3. Inject the CrudService
4. Create the desired methods/routes and use the logic from CrudService to be up and running super fast

Feel free to use the ControllerFacade for specific route functionality.
If you need even more specific logic, use the CrudService.

## Roadmap
Take a look at our [kanban board here](https://github.com/LYDIC-GROUP/rapid-api-crud-bundle/projects/1)

## Support
Hey 👋 If you like our libraries. Support us by  [buying](https://www.buymeacoffee.com/LYDICGROUP) us a coffee!
