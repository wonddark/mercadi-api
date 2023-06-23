# API for the auction house "Stocked"

> Built with  [Symfony](https://symfony.com/)/[API Platform](https://api-platform.com/)

### Current DB schema:
> Using [MariaDB](https://mariadb.org/) as DB engine

##### account
  - email
  - password
  - roles
  
##### user
  - account
  - firstname
  - lastname
  
##### item
  - user
  - name
  - initial-bid
  - description
  - images
  - date-placed
  
##### bid
  - user
  - item
  - quantity
  - date-placed
