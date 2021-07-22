# Introductoin

A class to CRUD mysql server with simple functions. with aim: ```to reduce no-of-characters to CRUD```.

## Description
  Following is the description of Connection class. Before getting ahead, you shall know
  about the format used to describe each element in the structure.
    
  #### Format:

    name (default-value) [description]

## Structure:
  Structure of Connection class:

    Connection
      +--------Properties
      |             +--------syms ([*, ', ", \])
      |             |             [These are symbols to be filtered in while validating user input].
      |             |
      |             +--------kw ([SELECT, INSERT, UPDATE, DELETE, TRUNCATE, DROP])
      |             |           [These are kewords to be filtered in user inputs].
      |             |
      |             +--------con (<mysqli_connect output>)
      |             |            [__construct() method creates mysql object that is stored in this variable].
      |             |
      |             +--------con_dat (hst->localhost, usr->root, pwd->, db->)
      |             |           |    [Stores basic details used for creating mysql connection].
      |             |           |
      |             |           +--------hst (localhost)
      |             |           |            [The name of host].
      |             |           |
      |             |           +--------usr  (root)
      |             |           |             [Username used for db access].
      |             |           |
      |             |           +--------pwd  (<empty-string>)
      |             |           |             [Password used for db access]
      |             |           |
      |             |           +--------db  (<empty-string>)
      |             |                        [Name of database. It is updated by swdb]
      |             |
      |             |
      |             +--------sql ()
      |             |            [This variable stores sql string of last query].
      |             |
      |             +--------qry ()
      |             |            [This variable stores last query object].
      |             |
      |             +--------tbl ()
      |                          [This variable stores table name of last table used in last query]
      |
      +--------Methods
      .           +--------__construct  [Creates mysql connection].
      .           |
      .           +--------get  [Getter function for getting variables].
      .           |
      .           +--------vld  [Validate a single string].
      .           |
      .           +--------arr_vld  [Validate keys and values of an array].
      .           |
      .           +--------build [It is most important method that is seperately described at end].
      .           |
      .           +--------qry  [Run raw sql in mysql connection. It also updates sql and qry properties].
      .           |
      .           +--------swdb  [Switch database by running query in server and updating con_dat].
      .           |
      .           +--------swtbl  [Switch table by executing query and updating tbl property].
      .           |
      .           +--------mkdb  [Create a new database].
      .           |
      .           +--------insert  [Insert rows into a table].
      .           |
      .           +--------select  [Select data from table].
      .           |
      .           +--------join_select  [Select from multiple tables by inner join].
      .           |
      .           +--------update  [Update row(s) in a table].
      .           |
      .           +--------delete  [Delete row(s) from a table].
      .           |
      .           +--------truncate  [Truncate a table].
      .           |
      .           +--------drop  [Drop a table].
      .           |
      .           +--------dropdb  [Drop a database].

## Build Method:

Build method builds single string from array. It is used in several ways. It has seven different modes
that creates strings in different formats. Input and output examples may describe these modes

    +-------------+-------------------------------+----------------------------------+
    |     Mode    |               Input           |              Output              |
    +-------------+-------------------------------+----------------------------------+
    |      1      |     [name, email, phone]      |     `name`, `email`, `phone`     |
    +-------------+-------------------------------+----------------------------------+
    |      2      |   [name->abdullah, pwd->123]  | `name`='abdullah' AND `pwd`=123  |
    +-------------+-------------------------------+----------------------------------+
    |      3      |   [user->name, brandh->code]  |  `user`.`name`, `branch`.`code`  |
    |             |-------------------------------+----------------------------------+
    |             |    [ users->[                 |  `users`.`name`, `users`.`email`,|
    |             |        name,                  |  `departments`.`title`,          |
    |             |        email],                |  `departments`.`code`            |
    |             |      departments->[           |                                  |
    |             |        title,                 |                                  |
    |             |        code]                  |                                  |
    |             |     ]                         |                                  |
    +-------------+-------------------------------+----------------------------------+
    |      4      |    [ customers->[             |  INNER JOIN `customers` ON       |
    |             |        customers->uid,        |  `customers`.`uid`=              |
    |             |        user->id],             |  `user`.`id` INNER JOIN          |
    |             |      departments->[           |  `departments` ON                |
    |             |        user->dptid,           |  `usser`.`dptid`=`departments`.  |
    |             |        departments->id]       |  `id`                            |
    |             |     ]                         |                                  |
    +-------------+-------------------------------+----------------------------------+
    |      5      |  [                            |  `user`.`id`=`customer`.`id`     |
    |             |   [user->id, customer->id],   |   AND `user`.`dptid`=`dpt`.`id`  |
    |             |   [user->dptid, dpt->id]      |                                  |
    |             |  ]                            |                                  |
    |             +-------------------------------+----------------------------------+
    |             |  [                            |  `user`.`role`='admin' AND       |
    |             |   [user->role, admin],        |  `user`.`name`='abdullah' AND    |
    |             |   [user->name, abdullah],     |  `dpt`.`id`='10' AND             |
    |             |   [dpt->id, 10],              |  `user`.`id`=`customer`.`id`     |
    |             |   [user->id, customer->id     |                                  |
    |             |  ]                            |                                  |
    +-------------+-------------------------------+----------------------------------+
    |      6      |   [ [John, 27, 12000] ]       |    ('john', '27', '1200')        |
    |             +-------------------------------+----------------------------------+
    |             |   [                           |  ('foo', '25', '11000'),         |
    |             |     [foo, 25, 11000],         |  ('bar', '28', '19000')          |
    |             |     [bar, 28, 19000]          |                                  |
    |             |   ]                           |                                  |
    +-------------+-------------------------------+----------------------------------+
    |      7      |   [                           |  `prefix`='Main', `code`='20',   |
    |             |     [prefix->Main],           |  `location`='Pakistan'           |
    |             |     [code->20],               |                                  |
    |             |     [location->Pakistan]      |                                  |
    |             |   ]                           |                                  |
    +-------------+-------------------------------+----------------------------------+

***    

    Developer Info:
    ========= =====
        By        :       Abdullah
        Date      :       Apr 12, 2020